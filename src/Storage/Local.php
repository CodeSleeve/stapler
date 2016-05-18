<?php

namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\Interfaces\Storage as StorageInterface;
use Codesleeve\Stapler\Exceptions;
use Codesleeve\Stapler\Attachment;

class Local implements StorageInterface
{
    /**
     * The current attachedFile object being processed.
     *
     * @var \Codesleeve\Stapler\Attachment
     */
    public $attachedFile;

    /**
     * Constructor method.
     *
     * @param Attachment $attachedFile
     */
    public function __construct(Attachment $attachedFile)
    {
        $this->attachedFile = $attachedFile;
    }

    /**
     * Return the url for a file upload.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function url(string $styleName) : string
    {
        return $this->attachedFile->getInterpolator()->interpolate($this->attachedFile->url, $this->attachedFile, $styleName);
    }

    /**
     * Return the path (on disk) of a file upload.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function path(string $styleName) : string
    {
        return $this->attachedFile->getInterpolator()->interpolate($this->attachedFile->path, $this->attachedFile, $styleName);
    }

    /**
     * Remove an attached file.
     *
     * @param array $filePaths
     */
    public function remove(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            $directory = dirname($filePath);
            $this->emptyDirectory($directory, true);
        }
    }

    /**
     * Move an uploaded file to it's intended destination.
     * The file can be an actual uploaded file object or the path to
     * a resized image file on disk.
     *
     * @param string $file
     * @param string $filePath
     */
    public function move(string $file, string $filePath)
    {
        $this->buildDirectory($filePath);
        $this->moveFile($file, $filePath);
        $this->setPermissions($filePath, $this->attachedFile->override_file_permissions);
    }

    /**
     * Rename and uploaded file.
     *
     * @param  string $oldName
     * @param  string $newName
     *
     * @return void
     */
    public function rename(string $oldName, string $newName)
    {
        @rename($oldName, $newName);
    }

    /**
     * Determine if a style directory needs to be built and if so create it.
     *
     * @param string $filePath
     */
    protected function buildDirectory(string $filePath)
    {
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Set the file permissions of a file upload
     * Does not ignore umask.
     *
     * @param string $filePath
     * @param bool   $overrideFilePermissions
     */
    protected function setPermissions(string $filePath, bool $overrideFilePermissions = null)
    {
        if ($overrideFilePermissions) {
            chmod($filePath, $overrideFilePermissions & ~umask());
        } elseif (is_null($overrideFilePermissions)) {
            chmod($filePath, 0666 & ~umask());
        }
    }

    /**
     * Attempt to move and uploaded file to it's intended location on disk.
     *
     * @param string $file
     * @param string $filePath
     *
     * @throws Exceptions\FileException
     */
    protected function moveFile(string $file, string $filePath)
    {
        if (!@rename($file, $filePath)) {
            $error = error_get_last();
            throw new Exceptions\FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $file, $filePath, strip_tags($error['message'])));
        }
    }

    /**
     * Recursively delete the files in a directory.
     *
     * @param string $directory
     * @param bool   $deleteDirectory
     */
    protected function emptyDirectory(string $directory, bool $deleteDirectory = false)
    {
        if (!is_dir($directory) || !($directoryHandle = opendir($directory))) {
            return;
        }

        while (false !== ($object = readdir($directoryHandle))) {
            if ($object == '.' || $object == '..') {
                continue;
            }

            if (!is_dir($directory.'/'.$object)) {
                unlink($directory.'/'.$object);
            } else {
                $this->emptyDirectory($directory.'/'.$object, true);    // The object is a folder, recurse through it.
            }
        }

        if ($deleteDirectory) {
            closedir($directoryHandle);
            rmdir($directory);
        }
    }
}
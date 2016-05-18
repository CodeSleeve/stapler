<?php

namespace Codesleeve\Stapler\Storage;

use League\Flysystem\FilesystemInterface;
use Codesleeve\Stapler\Attachment;

abstract class CloudStorage
{
    /**
     * The current attachment object being processed.
     *
     * @var \Codesleeve\Stapler\Attachment
     */
    public $attachment;

    /**
     * The AWS S3Client instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * Constructor method.
     *
     * @param Attachment           $attachment
     * @param FilesystemInterface  $filesystem
     */
    public function __construct(Attachment $attachment, FilesystemInterface $filesystem)
    {
        $this->attachment = $attachment;
        $this->filesystem = $filesystem;
    }

    /**
     * Return the key the uploaded file object is stored under within a bucket.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function path(string $styleName) : string
    {
        $path = $this->attachment->path;
        $interpolator = $this->attachment->getInterpolator();

        return $interpolator->interpolate($path, $this->attachment, $styleName);
    }

    /**
     * Remove an attached file.
     *
     * @param array $filePaths
     */
    public function remove(array $filePaths)
    {
        if ($filePaths) {
            foreach ($filePaths as $filePath) {
                $this->filesystem->delete($filePath);
            }
        }
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
        $this->filesystem->rename($oldName, $newName);
    }

    /**
     * Return the url for a file upload.
     *
     * @param string $styleName
     *
     * @return string|void
     */
    abstract public function url(string $styleName) : string;

    /**
     * Move an uploaded file to its intended destination.
     *
     * @param string $file
     * @param string $filePath
     */
    abstract public function move(string $file, string $filePath);
}
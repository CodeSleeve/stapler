<?php

namespace Codesleeve\Stapler\Storage;

use League\Flysystem\FilesystemInterface;
use Codesleeve\Stapler\Attachment;

abstract class CloudStorage
{
    /**
     * The current attachedFile object being processed.
     *
     * @var \Codesleeve\Stapler\Attachment
     */
    public $attachedFile;

    /**
     * The AWS S3Client instance.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * Constructor method.
     *
     * @param Attachment           $attachedFile
     * @param FilesystemInterface  $filesystem
     */
    public function __construct(Attachment $attachedFile, FilesystemInterface $filesystem)
    {
        $this->attachedFile = $attachedFile;
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
        $path = $this->attachedFile->path;
        $interpolator = $this->attachedFile->getInterpolator();

        return $interpolator->interpolate($path, $this->attachedFile, $styleName);
    }

    /**
     * Remove an attached file.
     *
     * @param array $filePaths
     */
    public function remove(array $filePaths) : string
    {
        if ($filePaths) {
            foreach ($filePaths as $filePath) {
                $this->filesystem->delete($filePath);
            }
        }
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

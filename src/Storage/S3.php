<?php

namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\Interfaces\Storage as StorageInterface;

class S3 extends CloudStorage implements StorageInterface
{
    /**
     * Boolean flag indicating if this attachment's bucket currently exists.
     *
     * @var array
     */
    protected $bucketExists = false;

    /**
     * Return the url for a file upload.
     *
     * @param string $styleName
     *
     * @return string|void
     */
    public function url(string $styleName) : string
    {
        $bucket = $this->attachedFile->s3_object_config['Bucket'];
        $path = $this->path($styleName);

        return $this->filesystem
            ->getAdapter()
            ->getClient()
            ->getObjectUrl($bucket, $path, null, ['PathStyle' => true]);
    }

    /**
     * Move an uploaded file to its intended destination.
     *
     * @param string $file
     * @param string $filePath
     */
    public function move(string $file, string $filePath)
    {
        $objectConfig = $this->attachedFile->s3_object_config;
        $fileSpecificConfig = ['Key' => $filePath, 'SourceFile' => $file, 'ContentType' => $this->attachedFile->contentType()];
        $mergedConfig = array_merge($objectConfig, $fileSpecificConfig);
        $this->filesystem->put($filePath, file_get_contents($file), $mergedConfig);
        @unlink($file);
    }
}

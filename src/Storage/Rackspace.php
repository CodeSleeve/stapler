<?php

namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\Interfaces\Storage as StorageInterface;

class Rackspace extends CloudStorage implements StorageInterface
{
    /**
     * Return the url for a file upload.
     *
     * @param string $styleName
     *
     * @return string|void
     */
    public function url(string $styleName) : string
    {
        $cdn = $this->filesystem
            ->getAdapter()
            ->getContainer()
            ->getCdn();

        $path = $this->path($styleName);

        if ($this->attachedFile->use_ssl === true) {
            return $cdn->getCdnSslUri() . "/$path";
        } else {
            return $cdn->getCdnUri() . "/$path";
        }
    }

    /**
     * Move an uploaded file to its intended destination.
     *
     * @param string $file
     * @param string $filePath
     */
    public function move(string $file, string $filePath)
    {
        $this->filesystem->put($filePath, fopen($file, 'r'));
        @unlink($file);
    }
}

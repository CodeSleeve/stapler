<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\Stapler;
use Codesleeve\Stapler\Attachment as AttachedFile;
use Codesleeve\Stapler\Storage\Local as LocalStorage;
use Codesleeve\Stapler\Storage\S3 as S3Storage;
use Codesleeve\Stapler\Storage\Rackspace as RackspaceStorage;

class Storage
{
    /**
     * Build a storage instance.
     *
     * @param AttachedFile $attachment
     *
     * @return \Codesleeve\Stapler\Interfaces\Storage
     */
    public static function create(AttachedFile $attachment)
    {
        $filesystem = Stapler::filesystemForAttachment($attachment);

        switch ($attachment->storage) {
            case 'local':
                return new LocalStorage($attachment, $filesystem);
                break;

            case 's3':
                return new S3Storage($attachment, $filesystem);
                break;

            case 'rackspace':
                return new RackspaceStorage($attachment, $filesystem);
                break;

            default:
                return new LocalStorage($attachment, $filesystem);
                break;
        }
    }
}

<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\{Stapler, Attachment as AttachedFile};
use Codesleeve\Stapler\Storage\{Local as LocalStorage, S3 as S3Storage, Rackspace as RackspaceStorage};
use Codesleeve\Stapler\Interfaces\StorageInterface;

class StorageFactory
{
    /**
     * Build a storage instance.
     *
     * @param AttachedFile $attachment
     *
     * @return StorageInterface
     */
    public static function create(AttachedFile $attachment) : StorageInterface
    {
        switch ($attachment->storage) {
            case 'local':
                return new LocalStorage($attachment);
                break;

            case 's3':
                $filesystem = Stapler::filesystemForAttachment($attachment);

                return new S3Storage($attachment, $filesystem);
                break;

            case 'rackspace':
                $filesystem = Stapler::filesystemForAttachment($attachment);

                return new RackspaceStorage($attachment, $filesystem);
                break;

            default:
                return new LocalStorage($attachment);
                break;
        }
    }
}

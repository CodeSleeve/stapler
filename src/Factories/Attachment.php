<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\Stapler;
use Codesleeve\Stapler\AttachmentConfig;
use Codesleeve\Stapler\Factories\Storage as StorageFactory;

class Attachment
{
    /**
     * Create a new attachment object.
     *
     * @param string $name
     * @param array  $options
     *
     * @return \Codesleeve\Stapler\Attachment
     */
    public static function create($name, array $options)
    {
        $options = static::mergeOptions($options);
        Stapler::getValidatorInstance()->validateOptions($options);
        list($config, $interpolator, $resizer) = static::buildDependencies($name, $options);

        $className = Stapler::getConfigInstance()->get('bindings.attachment');
        $attachment = new $className($config, $interpolator, $resizer);

        $storageDriver = StorageFactory::create($attachment);
        $attachment->setStorageDriver($storageDriver);

        return $attachment;
    }

    /**
     * Build out the dependencies required to create
     * a new attachment object.
     *
     * @param string $name
     * @param array  $options
     *
     * @return array
     */
    protected static function buildDependencies($name, array $options)
    {
        return [
            new AttachmentConfig($name, $options),
            Stapler::getInterpolatorInstance(),
            Stapler::getResizerInstance($options['image_processing_library']),
        ];
    }

    /**
     * Merge configuration options.
     * Here we'll merge user defined options with the stapler defaults in a cascading manner.
     * We start with overall stapler options.  Next we merge in storage driver specific options.
     * Finally we'll merge in attachment specific options on top of that.
     *
     * @param array $options
     *
     * @return array
     */
    protected static function mergeOptions(array $options)
    {
        $config = Stapler::getConfigInstance();
        $defaultOptions = $config->get('stapler');
        $options = array_merge($defaultOptions, (array) $options);
        $storage = $options['storage'];
        $options = array_replace_recursive($config->get($storage), $options);
        $options['styles'] = array_merge((array) $options['styles'], ['original' => '']);

        return $options;
    }
}

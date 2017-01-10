<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\{Stapler, AttachmentConfig, Attachment};
use Codesleeve\Stapler\Factories\StorageFactory;

class AttachmentFactory
{
    /**
     * Create a new attachment object.
     *
     * @param string $name
     * @param array  $options
     *
     * @return Attachment
     */
    public static function create(string $name, array $options) : Attachment
    {
        $options = static::mergeOptions($options);
        Stapler::getValidatorInstance()->validateOptions($options);
        list($config, $interpolator, $resizer, $dispatcher) = static::buildDependencies($name, $options);

        $attachmentClassName = Stapler::getConfigInstance()->get('bindings.attachment');
        $attachment = new $attachmentClassName($config, $interpolator, $resizer, $dispatcher);

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
    protected static function buildDependencies(string $name, array $options)
    {
        return [
            new AttachmentConfig($name, $options),
            Stapler::getInterpolatorInstance(),
            Stapler::getResizerInstance($options['image_processing_library']),
            Stapler::getDispatcherInstance()
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
    protected static function mergeOptions(array $options) : array
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

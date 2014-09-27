<?php namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Config\ConfigurableInterface;
use Codesleeve\Stapler\File\Image\Resizer;
use Aws\S3\S3Client;

/**
 * Easy file attachment management for Eloquent (Laravel 4).
 *
 * Credits to the guys at thoughtbot for creating the
 * paperclip plugin (rails) from which this package is inspired.
 * https://github.com/thoughtbot/paperclip
 *
 * @package Codesleeve/Stapler
 * @version v1.0.0
 * @author Travis Bennett <tandrewbennett@hotmail.com>
 * @link
 */
class Stapler
{
    /**
     * Holds the hash value for the current STAPLER_NULL constant.
     *
     * @var string
     */
    protected static $staplerNull;

    /**
     * An instance of the interpolator class for processing interpolations.
     *
     * @var \Codesleeve\Stapler\Interpolator
     */
    protected static $interpolator;

    /**
     * An instance of the validator class for validating attachment configurations.
     *
     * @var \Codesleeve\Stapler\Validator
     */
    protected static $validator;

    /**
     * An instance of the resizer class for processing images.
     *
     * @var \Codesleeve\Stapler\File\Image\Resizer
     */
    protected static $resizer;

    /**
     * A configuration object instance.
     *
     * @var \Codesleeve\Stapler\Config\ConfigurableInterface
     */
    protected static $config;

    /**
     * An array of image processing libs.
     * Each time an new image processing lib (GD, Gmagick, or Imagick)
     * is used, we'll cache it here in order to prevent
     * memory leaks.
     *
     * @var array
     */
    protected static $imageProcessors = [];

    /**
     * A key value store of S3 clients.
     * Because S3 clients are model-attachment specific, each
     * time we create a new one (for a given model/attachment combo)
     * we'll need to cache it here in order to prevent
     * memory leaks.
     *
     * @var array
     */
    protected static $s3Clients = [];

    /**
     * Boot up stapler.
     * Here, we'll register any needed constants and prime up
     * the settings required by the package.
     */
    public static function boot()
    {
        static::$staplerNull = sha1(time());

        if (!defined('STAPLER_NULL')) {
            define('STAPLER_NULL', static::$staplerNull);
        }
    }

    /**
     * Return a shared of instance of the Interpolator class.
     * If there's currently no instance in memory we'll create one
     * and then hang it as a property on this class.
     *
     * @return \Codesleeve\Stapler\Interpolator
     */
    public static function getInterpolatorInstance()
    {
        if (static::$interpolator === null)
        {
            static::$interpolator = new Interpolator;
        }

        return static::$interpolator;
    }

    /**
     * Return a shared of instance of the Validator class.
     * If there's currently no instance in memory we'll create one
     * and then hang it as a property on this class.
     *
     * @return \Codesleeve\Stapler\Interpolator
     */
    public static function getValidatorInstance()
    {
        if (static::$validator === null)
        {
            static::$validator = new Validator();
        }

        return static::$validator;
    }

    /**
     * Return a resizer object instance.
     *
     * @param string $type
     * @return \Codesleeve\Stapler\File\Image\Resizer
     */
    public static function getResizerInstance($type)
    {
        $imagineInstance = static::getImagineInstance($type);

        if (static::$resizer === null) {
            static::$resizer = new Resizer($imagineInstance);
        }
        else {
            static::$resizer->setImagine($imagineInstance);
        }

        return static::$resizer;
    }

    /**
     * Return an instance of Imagine interface.
     *
     * @param string $type
     * @return \Imagine\Image\ImagineInterface
     */
    public static function getImagineInstance($type)
    {
        if (!isset(static::$imageProcessors[$type])) {
            static::$imageProcessors[$type] = new $type;
    	}

    	return static::$imageProcessors[$type];
    }

    /**
     * Return an S3Client object for a specific attachment type.
     * If no instance has been defined yet we'll buld one and then
     * cache it on the s3Clients property (for the current request only).
     *
     * @param  Attachment $attachedFile
     * @return S3Client
     */
    public static function getS3ClientInstance(Attachment $attachedFile)
    {
        $modelName = $attachedFile->getInstanceClass();
        $attachmentName = $attachedFile->getConfig()->name;
        $key = "$modelName.$attachmentName";

        if (array_key_exists($key, static::$s3Clients)) {
            return static::$s3Clients[$key];
        }

        static::$s3Clients[$key] = static::buildS3Client($attachedFile);

        return static::$s3Clients[$key];
    }

    /**
     * Return a configuration object instance.
     * If no instance is currently set, we'll return an instance
     * of Codesleeve\Stapler\Config\NativeConfig.
     *
     * @return \Codesleeve\Stapler\Config\ConfigurableInterface
     */
    public static function getConfigInstance()
    {
        if (!static::$config) {
            static::$config = new Config\NativeConfig;
        }

        return static::$config;
    }

    /**
     * Set the configuration object instance.
     *
     * @param ConfigurableInterface $config
     */
    public static function setConfigInstance(ConfigurableInterface $config){
        static::$config = $config;
    }

    /**
     * Build an S3Client instance using the information defined in
     * this class's attachedFile object.
     *
     * @param $attachedFile
     * @return S3Client
     */
    protected static function buildS3Client(Attachment $attachedFile)
    {
        return S3Client::factory($attachedFile->s3_client_config);
    }
}
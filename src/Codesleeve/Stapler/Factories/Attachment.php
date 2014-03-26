<?php namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\Factories\Resizer as ResizerFactory;

class Attachment
{
	/**
	 * An instance of the interpolator class for processing interpolations.
	 *
	 * @var Codesleeve\Stapler\Interpolator
	 */
	protected static $interpolator;

	/**
	 * Create a new attachment object.
	 *
	 * @param string $name
	 * @param array $options
	 * @return Attachment
	 */
	public static function create($name, $options)
    {
        list($config, $interpolator, $resizer) = $this->buildDependencies($name, $options);

        return new Attachment($config, $interpolator, $resizer);
    }

    /**
     * Build out the dependencies required to create
     * a new attachment object.
     *
     * @param string $name
	 * @param array $options
     * @return array
     */
    protected function buildDependencies($name, $options)
    {
    	return [
    		$this->buildConfig($name, $options),
    		$this->buildInterpolator(),
            ResizerFactory::create($options['image_processing_library'])
    	];
    }

    /**
     * Return a new configuration object.
     *
     * @param string $name
	 * @param array $options
     * @return Config
     */
    protected function buildConfig($name, $options)
    {
        return new Config($name, $options);
    }

    /**
     * Return a shared of instance of the Interpolator class.
     * If there's currently no instance in memory we'll create one
     * and then hang it as a property on this factory.
     *
     * @return Interpolator
     */
    protected function buildInterpolator()
    {
    	if (static::$interpolator === null)
    	{
            $inflector = new ICanBoogie\Inflector;
            static::$interpolator = new Interpolator($inflector);
        }

        return static::$interpolator;
    }
}
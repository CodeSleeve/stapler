<?php namespace Codesleeve\Stapler\Factories;

use Imagine\Gd\Imagine as GD;
use Imagine\Imagick\Imagine as Imagick;
use Imagine\Gmagick\Imagine as Gmagick;

class Imagine
{
	/**
	 * An array of image processing libs that can be used to
	 * resize images with.
	 *
	 * @var array
	 */
	protected static $imageProcessors = [];

	/**
	 * Create an instance of Imagine interface.
	 *
	 * @param string $type
	 * @return Attachment
	 */
	public static function create($type)
    {
    	if (!isset(static::imageProcessors[$type])) {
    		static::imageProcessors[$type] = new $type;
    	}

    	return static::imageProcessors[$type];
    }
}
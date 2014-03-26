<?php namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\Factories\Imagine as ImagineFactory;
use Codesleeve\Stapler\File\Resizer;

class Resizer
{
	/**
	 * An instance of the resizer class for processing images.
	 *
	 * @var Codesleeve\Stapler\Resizer
	 */
	protected static $resizer;

	/**
	 * Create a resizer object.
	 *
	 * @param string $type
	 * @return Attachment
	 */
	public static function create($type)
    {
    	$imagineInstance = ImagineFactory::create($type);

    	if (static::$resizer === null) {
            static::$resizer = new Resizer($imagineInstance); 
        }
        else {
        	static::$resizer->setImagine->($imagineInstance);
        }

    	return static::$resizer;
    }
}
<?php namespace Codesleeve\Stapler;

class Style
{
	/**
	 * The name of the style.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The style value.
	 * This can be either a string or a callable type.
	 *
	 * @var mixed
	 */
	public $value;

	/**
	 * The image conversion options for this style.
	 *
	 * @var array
	 */
	public $convertOptions;

	/**
	 * Whether or not the image should be auto-oriented
	 * using embedded EXIF data.
	 *
	 * @var boolean
	 */
	public $autoOrient;

	/**
	 * Constructor method.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $convertOptions
	 */
	function __construct($name, $value, $convertOptions)
	{
		$this->name = $name;
		$this->value = $value;

		if (array_key_exists('auto-orient', $convertOptions)) {
			$this->autoOrient = $convertOptions['auto-orient'];
			unset($convertOptions['auto-orient']);
		}

		$this->convertOptions = $convertOptions;
	}
}
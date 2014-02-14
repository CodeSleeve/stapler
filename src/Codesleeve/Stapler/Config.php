<?php namespace Codesleeve\Stapler;

use stdClass;

class Config
{
	/**
	 * The name of the attachment.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * An array of attachment configuration options.
	 * 
	 * @var array
	 */
	protected $options;

	/**
	 * An array of stdClass style objects.
	 * 
	 * @var array
	 */
	protected $styleObjects;

	/**
	 * Constructor method.
	 *
	 * @param string $name
	 * @param array $options
	 */
	function __construct($name, $options)
	{
		$this->name = $name;
		$this->options = $options;
	}

	/**
	 * Handle the dynamic setting of attachment options.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Handle the dynamic retrieval of attachment options.
     * Style options will be converted into a php stcClass.
     *
     * @param  string $optionName
     * @return mixed
     */
    public function __get($optionName)
    {
		if (array_key_exists($optionName, $this->options))
		{
		    if ($optionName == 'styles') {
		    	return $this->convertStylesToObject($this->options[$optionName]);
		    }

		    return $this->options[$optionName];
		}

		return null;
    }

	/**
	 * Utility method for converting an associative array into an array of php stdClass objects.
	 * Both array keys and array values will be conveted to object properties.
	 * 
	 * @param  mixed $styles 
	 * @return mixed
	 */
	protected function convertStylesToObject($styles)
	{
		if (!$this->styleObjects) 
		{
			foreach ($styles as $styleName => $styleValue) 
			{
				$style = new stdClass();
				$style->name = $styleName;
				$style->value = $styleValue;
				$style->convert_options = $this->getStyleConvertOptions($styleName);

				$this->styleObjects[] = $style;
			}
		}

		return $this->styleObjects;
	}

	/**
	 * Return the convert options for a styles.
	 * 
	 * @param  string $styleName
	 * @return array       
	 */
	protected function getStyleConvertOptions($styleName)
	{
		if (array_key_exists($styleName, $this->options['convert_options'])) {
			return $this->options['convert_options'][$styleName];
		}

		return [];
	}
}
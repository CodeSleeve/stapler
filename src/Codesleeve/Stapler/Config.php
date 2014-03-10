<?php namespace Codesleeve\Stapler;

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
	 * An array of Codesleeve\Stapler\Style objects.
	 *
	 * @var array
	 */
	protected $styles;

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
		$this->styles = $this->buildStyleObjects($options['styles']);
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
		    	return $this->styles;
		    }

		    return $this->options[$optionName];
		}

		return null;
    }

	/**
	 * Convert the styles array into an array of Style objects.
	 * Both array keys and array values will be converted to object properties.
	 *
	 * @param  mixed $styles
	 * @return array
	 */
	protected function buildStyleObjects($styles)
	{
		$styleObjects = [];

		foreach ($styles as $styleName => $styleValue)
		{
			$convertOptions = $this->getStyleConvertOptions($styleName);
			$styleObjects[] = new Style($styleName, $styleValue, $convertOptions);
		}

		return $styleObjects;
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
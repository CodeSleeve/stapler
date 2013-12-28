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
		    	return $this->convertToObject($this->options[$optionName]);
		    }

		    return $this->options[$optionName];
		}

		return null;
    }


	/**
	 * Utility method for converting an associative array into an array of php stdClass objects.
	 * Both array keys and array values will be conveted to object properties.
	 * 
	 * @param  mixed $arrayElements 
	 * @return mixed
	 */
	protected function convertToObject($arrayElements)
	{
		$objects = [];
		
		foreach ($arrayElements as $key => $value) {
			$object = new \stdClass();
			$object->name = $key;
			$object->value = $value;
			$objects[] = $object;
		}

		return $objects;
	}
}
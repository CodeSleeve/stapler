<?php namespace Codesleeve\Stapler;

class Utility
{
	
	/**
	 * Utility method for converting an associative array into an array of php stdClass objects.
	 * Both array keys and array values will be conveted to object properties.
	 * 
	 * @param  mixed $arrayElements 
	 * @return mixed
	 */
	public function convertToObject($arrayElements)
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
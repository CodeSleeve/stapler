<?php namespace Codesleeve\Stapler;

use Illuminate\Support\Str;

Abstract class Interpolator
{
	/**
	 * The id of the database record the attachment is tied to.
	 * 
	 * @var integer
	 */
	protected $recordId;

	/**
	 * The name of the model the attachment belongs to
	 * 
	 * @var string
	 */
	protected $modelName;

	/**
	 * The attachment attributes on the model
	 * 
	 * @var array
	 */
	protected $attributes;

	/**
	 * Returns a sorted list of all interpolations.  This list is currently hard coded
	 * (unlike its paperclip counterpart) but can be changed in the future so that
	 * all interpolation methods are broken off into their own class and returned automatically
	 *
	 * @return array
	*/
	protected function interpolations() 
	{
		return [
			':attachment' => 'attachment',
			':basename' => 'basename',
			':class' => 'getClass',
			':extension' => 'extension',
			':filename' => 'filename',
			':id' => 'id',
			':id_partition' => 'idPartition',
			':laravel_root' => 'laravelRoot',
			':style' => 'style'
		];
	}

	/**
	 * Interpolating a string.
	 *
	 * @param  string $string
	 * @param string $styleName
	 * @return string
	*/
	protected function interpolateString($string, $styleName = '')
	{
		foreach ($this->interpolations() as $key => $value)
		{
			$string = preg_replace("/$key\b/", $this->$value($styleName), $string);
		}

		return $string;
	}

	/**
	 * Returns the pluralized form of the attachment name. e.g.
     * "avatars" for an attachment of :avatar.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function attachment($styleName = '') 
	{
		return Str::plural($this->name);
	}

	/**
	 * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function basename($styleName = '') 
	{
		return pathinfo($this->attributes['fileName'], PATHINFO_FILENAME);
	}

	/**
	 * Returns the current class name, taking into account namespaces, e.g
	 * 'Swingline\Stapler' will become Swingline/Stapler.
	 *
	 * @param string $styleName
	 * @return string
	*/
    protected function getClass($styleName = '') 
    {
    	return $this->handleBackslashes($this->modelName);
    }

    /**
	 * Returns the extension of the attached file, e.g 'jpg' for file.jpg.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function extension($styleName = '') 
	{
		return pathinfo($this->attributes['fileName'], PATHINFO_EXTENSION);
	}

	/**
	 * Returns the file name.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function filename($styleName = '') 
	{
		return $this->attributes['fileName'];
	}

	/**
	 * Returns the id of the current object instance.
	 *
	 * @param string $styleName
	 * @return string
	*/
    protected function id($styleName = '') 
    {
     	return $this->recordId;
    }

	/**
	 * Returns the root of the Laravel project.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function laravelRoot($styleName = '') 
	{
		return $this->basePath();
	}

	/**
	 * Returns the style, or the default style if an empty style is supplied.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function style($styleName = '') 
	{
		return $styleName ?: $this->default_style;
	}

	/**
	 * Utility function to return the string offset of the directory
	 * portion of a file path with an :id or :idPartition interpolation.
	 *
	 * <code>
	 *		// Returns an offset of '27'.
	 *      $directory = '/some_directory/000/000/001/some_file.jpg'
	 *		return $this->getOffset($directory, $attachment);
	 * </code>
	 *
	 * @param string $string
	 * @param string $styleName
	 * @return string
	 */
	public function getOffset($string, $styleName = '') 
	{
		// Get the partition of the id
		$idPartition = $this->idPartition($styleName);
		$match = strpos($string, $idPartition);
		
		if ($match !== false)
		{
			// Id partitioning is being used, so we're looking for a
			// directory that has the pattern /000/000/001 at the end,
			// so we know we'll need to add 11 spaces to the string offset.
			$offset = $match + 11;
		}
		else
		{
			// Id partitioning is not being used, so we're looking for
			// a directory that has the pattern /1 at the end, so we'll
			// need to add the length of the record id + 1 to the string offset.
			$match = strpos($string, (string) $this->recordId);
			$offset = $match + strlen($this->recordId);
		}

		return $offset;
	}

	/**
	* Generates the id partition of a record, e.g
	* return /000/001/234 for an id of 1234.
	*
	* @param string $styleName
	* @return mixed
	*/
	protected function idPartition($styleName = '')
	{
		$id = $this->id($styleName);

		if (is_numeric($id))
		{
			return implode('/', str_split(sprintf('%09d', $id), 3));
		}
		elseif (is_string($id))
		{
			return implode('/', array_slice(str_split($id, 3), 0, 3));
		}
		else
		{
			return null;
		}
	}

	/**
	 * Utitlity function to turn a backslashed string into a string
	 * suitable for use in a file path, e.g '\foo\bar' becomes 'foo/bar'.
	 *
	 * @param string $string
	 * @return string
	 */
	protected function handleBackslashes($string) 
	{
		return str_replace('\\', '/', ltrim($string, '\\'));
	}

	/**
	 * Returns a path to the project base directory.
	 * 
	 * @return mixed        
	 */
	abstract protected function basePath();
	

	/**
	 * Returns a path to the project document root directory.
	 * 
	 * @return mixed        
	 */
	abstract protected function publicPath();
	

	/**
	 * Wrapper for php's realPath function.
	 * 
	 * @param  string $value 
	 * @return mixed        
	 */
	abstract protected function realPath($value);
	
}
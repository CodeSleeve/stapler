<?php namespace Codesleeve\Stapler;

use App;

class Attachment
{
	/**
	 * The model the attachment belongs to.
	 * 
	 * @var string
	 */
	public $instance;

	/**
	 * The name of the attachment.
	 * 
	 * @var string
	 */
	public $name;

	/**
	 * An instance of the underlying storage driver that is being used.
	 * 
	 * @var mixed.
	 */
	protected $storageDriver;

	/**
	 * The attachment options.
	 * 
	 * @var array
	 */
	protected $options;

	/**
	 * An instance of the interpolator class for processing interpolations.
	 * 
	 * @var Codesleeve\Stapler\Interpolator
	 */
	protected $interpolator;

	/**
	 * The uploaded file object for the attachment.
	 * 
	 * @var Codesleeve\Stapler\UploadedFile
	 */
	protected $uploadedFile;

	/**
	 * Constructor method
	 * 
	 * @param array $foo
	 */
	function __construct($name, $options = [], $interpolator) 
	{
		$this->name = $name;
		$this->options = $options;
		$this->interpolator = $interpolator;
		$this->storageDriver = App::make($this->storage, $this);
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
	 * Mutator method for the uploadedFile property.
	 * Takes a symfony uploaded file object and builds a Codesleeve\Stapler\UploadedFile from it.
	 * 
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
	 */
	public function setUploadedFile($uploadedFile)
	{
		$this->uploadedFile = APP::make('UploadedFile', $uploadedFile);
	}

	/**
	 * Accessor method for the uploadedFile property.
	 * 
	 * @return Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}

	public function setInterpolator($value)
	{
		$this->interpolator = $value;
	}

	/**
	 * Accessor method for the uploadedFile property.
	 * 
	 * @return Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function getInterpolator()
	{
		return $this->interpolator;
	}

	/**
	 * Bootstrap the attachment.  
	 * This provides a mechanism for the attachment to access properties of the
	 * corresponding model instance it's attached to.
	 * 
	 * @param  Model $instance      
	 * @return void             
	 */
	public function bootstrap($instance)
	{
		$this->instance = $instance;
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
			$match = strpos($string, (string) $this->instance->getKey());
			$offset = $match + strlen($this->instance->getKey());
		}

		return $offset;
	}

	/**
	 * Handle dynamic method calls on the attachment.
	 * This allows us to call methods on the underlying 
	 * storage or utility objects directly via the attachment.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		// Storage methods
		$callable = ['reset', 'remove', 'findDirectory', 'buildDirectory', 'cleanDirectory', 'emptyDirectory', 'move'];
		
		if (in_array($method, $callable)) {
			return call_user_func_array([$this->storageDriver, $method], $parameters);
		}

		// Utility methods
		$callable = ['convertToObject'];

		if (in_array($method, $callable))
		{
			$utility = App::make('Utility', $this);
			return call_user_func_array([$utility, $method], $parameters);
		}
	}

	/**
	 * Process an attachment (i.e build out file directories, resize images, move uploaded files, etc)
	 *
	 * @param  stdClass $style
	 * @return void
	 */
	public function process($style)
	{
		$this->buildDirectory($style->name, $this);
		$this->cleanDirectory($style->name, $this);

		if ($style->value && $this->uploadedFile->isImage()) {
			$tmpFilePath = $this->processStyle($style);
			$this->move($tmpFilePath, $this->path($style->name), $this->override_file_permissions);
		}
		else {
			$this->move($this->uploadedFile, $this->path($style->name), $this->override_file_permissions);
		}
	}

	/**
	 * Generates the url to a file upload.
	 *
	 * @param string $styleName
	 * @return string
	*/
	public function url($styleName = '')
	{
		if ($this->originalFilename()) {
			//return $this->getInterpolator()->interpolate($this->url, $this, $styleName);
			return $this->storageDriver->url($styleName, $this);
		}
		
		return $this->defaultUrl($styleName);
	}

	/**
	 * Generates the file system path to an uploaded file.  This is used for saving files, etc.
	 *
	 * @param string $styleName
	 * @return string
	*/
	public function path($styleName = '')
	{
		if ($this->originalFilename()) {
			//return $this->getInterpolator()->interpolate($this->path, $this, $styleName);
			return $this->storageDriver->path($styleName, $this);
		}

		return $this->defaultPath($styleName);
	}

	/**
	 * Returns the creation time of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_created_at attribute of the model.
	 * This attribute may conditionally exist on the model, it is not one of the four required fields.
     * 
	 * @return datetime
	 */
	public function createdAt()
	{
		return $this->instance->getAttribute("{$this->name}_created_at");
	}

	/**
	 * Returns the last modified time of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_updated_at attribute of the model.
     * 
	 * @return datetime
	 */
	public function updatedAt()
	{
		return $this->instance->getAttribute("{$this->name}_updated_at");
	}

	/**
	 * Returns the content type of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_content_type attribute of the model.
     * 
	 * @return string
	 */
	public function contentType()
	{
		return $this->instance->getAttribute("{$this->name}_content_type");
	}

	/**
	 * Returns the size of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_file_size attribute of the model.
     * 
	 * @return integer
	 */
	public function size()
	{
		return $this->instance->getAttribute("{$this->name}_file_size");
	}

	/**
	 * Returns the name of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_file_name attribute of the model.
     * 
	 * @return string
	 */
	public function originalFilename()
	{
		return $this->instance->getAttribute("{$this->name}_file_name");
	}

	/**
	 * Generates the default url if no file attachment is present.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function defaultUrl($styleName = '')
	{
		if ($url = $this->default_url) {
			return $this->getInterpolator()->interpolate($url, $this, $styleName);
		}
		
		return '';
	}

	/**
	 * Generates the default path if no file attachment is present.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function defaultPath($styleName = '')
	{
		return $this->publicPath() . $this->defaultUrl($styleName);
	}

	/**
	 * processStyle method 
	 * 
	 * Parse the given style dimensions to extract out the file processing options,
	 * perform any necessary image resizing for a given style.
	 *
	 * @param  stdClass $style
	 * @return boolean
	 */
	protected function processStyle($style)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'STP');
		$resizer = App::make('Resizer', $this->uploadedFile);

		if (strpos($style->value, 'x') === false) 
		{
			// Width given, height automagically selected to preserve aspect ratio (landscape).
			$width = $style->value;
			$resizer->resize($width, null, 'landscape')->save($filePath);

			return $filePath;
		}
		
		$dimensions = explode('x', $style->value);
		$width = $dimensions[0];
		$height = $dimensions[1];
		
		if (empty($width)) 
		{
			// Height given, width automagically selected to preserve aspect ratio (portrait).
			$resizer->resize(null, $height, 'portrait')->save($filePath);

			return $filePath;
		}
		
		$resizing_option = substr($height, -1, 1);
		switch ($resizing_option) {
			case '#':
				// Resize, then crop.
				$height = rtrim($height, '#');
				$resizer->resize($width, $height, 'crop')->save($filePath);
				break;

			case '!':
				// Resize by exact width/height (does not preserve aspect ratio).
				$height = rtrim($height, '!');
				$resizer->resize($width, $height, 'exact')->save($filePath);
				break;
			
			default:
				// Let the script decide the best way to resize.
				$resizer->resize($width, $height, 'auto')->save($filePath);
				break;
		}

		return $filePath;
	}

	/**
	 * Wrapper for laravel's native public_path function.
	 * 
	 * @return mixed        
	 */
	protected function publicPath()
	{
		return realPath(public_path());
	}
}
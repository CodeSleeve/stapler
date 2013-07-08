<?php namespace Codesleeve\Stapler;

use App;

class Attachment extends Interpolator
{
	/**
	 * The name of the attachment
	 * 
	 * @var string
	 */
	protected $name;

	/**
	 * The attachment options
	 * 
	 * @var array
	 */
	protected $options;

	/**
	 * The uploaded file object for the attachment
	 * 
	 * @var Codesleeve\Stapler\UploadedFile
	 */
	protected $uploadedFile;

	/**
	 * Constructor method
	 * 
	 * @param array $foo
	 */
	function __construct($name, $options = []) 
	{
		$this->name = $name;
		$this->validateOptions($options);
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
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
		if (array_key_exists($name, $this->options)) 
		{
		    if ($name == 'styles') {
		    	return $this->convertToObject($this->options[$name]);
		    }

		    return $this->options[$name];
		}

		return null;
    }

    /**
     * Accessor for the name property
     * 
     * @return string
     */
    public function getName()
    {
    	return $this->name;
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

    /**
	 * Returns a file upload resource location (path or url).
	 *
	 * @param string $type
	 * @param string $styleName
	 * @return string
	*/
	public function returnResource($type, $styleName = '')
	{
		if ($type == 'path') {
			return $this->returnPath($styleName);
		}
		elseif ($type == 'url') {
			return $this->returnUrl($styleName);
		}

		return '';
	}

	/**
	 * Returns the path to a file upload resource location.
	 * 
	 * @param string $styleName
	 * @return string
	 */
	protected function returnPath($styleName)
	{
		$resource = $this->path($styleName);

		if (file_exists($resource)) {
			return $resource;
		}
		else {
			return $this->defaultPath($styleName);
		}
	}

	/**
	 * Returns the url to a file upload resource location.
	 * 
	 * @param string $styleName
	 * @return string
	 */
	protected function returnUrl($styleName)
	{
		$resource = $this->absoluteUrl($styleName);
		
		if (file_exists($resource)) {
			return $this->url($styleName);
		}
		else {
			return $this->defaultUrl($styleName);
		}
	}

	/**
	 * Generates the file system path to an uploaded file.  This is used for saving files, etc.
	 *
	 * @param string $styleName
	 * @return string
	*/
	public function path($styleName = '')
	{
		return $this->publicPath() . $this->url($styleName);
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
	 * Generates the absolute url to an uploaded file.
	 * 
	 * @param string $styleName     
	 * @return string             
	 */
	protected function absoluteUrl($styleName = '')
	{
		return realpath($this->publicPath() . $this->url($styleName));
	}

	/**
	 * Generates the url to a file upload.
	 *
	 * @param string $styleName
	 * @return string
	*/
	protected function url($styleName = '')
	{
		return $this->interpolateString($this->url, $styleName);
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
			return $this->interpolateString($url, $styleName);
		}
		
		return '';
	}

	/**
	 * Wrapper for laravel's base_path function.
	 * 
	 * @return mixed        
	 */
	protected function basePath()
	{
		return $this->realPath(base_path());
	}

	/**
	 * Wrapper for laravel's native public_path function.
	 * 
	 * @return mixed        
	 */
	protected function publicPath()
	{
		return $this->realPath(public_path());
	}

	/**
	 * Wrapper for php's native realpath function.
	 * 
	 * @param  string $value 
	 * @return mixed        
	 */
	protected function realPath($value)
	{
		return realpath($value);
	}

	/**
	 * Validate the attachment options for an attachment type.
	 * A url is required to have either an :id or an :id_partition interpolation.
	 * 
	 * @param  array $options
	 * @return void
	 */
	protected function validateOptions($options)
	{
		if (preg_match("/:id\b/", $options['url']) !== 1 && preg_match("/:id_partition\b/", $options['url']) !== 1) {
			throw new Exceptions\InvalidUrlOptionException('Invalid file url: an :id or :id_partition is required.', 1);
		}
	}

	/**
	 * Bootstrap the attachment.  
	 * This provides a mechanism for the attachment to access properties of the
	 * corresponding model it's attached to.
	 * 
	 * @param  Model $model      
	 * @return void             
	 */
	public function bootstrap($model)
	{
		$this->modelName = get_class($model);
		$this->recordId = $model->getKey();
 		$this->attributes = $model->getAttachmentAttributes($this->name);
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
		$callable = ['reset', 'remove', 'findDirectory', 'buildDirectory', 'cleanDirectory', 'emptyDirectory', 'move', 'setPermissions'];
		
		if (in_array($method, $callable))
		{
			$storage = App::make('Storage', $this);
			return call_user_func_array([$storage, $method], $parameters);
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
		$this->buildDirectory($style->name);
		$this->cleanDirectory($style->name);

		if ($style->value && $this->uploadedFile->isImage()) {
			$this->processImage($style);
		}
		else {
			$this->move($this->uploadedFile, $this->path($style->name), $this->mode);
		}
	}

	/**
	 * processImage method 
	 * 
	 * Parse the given style dimensions to extract out the file processing options,
	 * perform any necessary image resizing for a given style.
	 *
	 * @param  stdClass $style
	 * @return boolean
	 */
	public function processImage($style)
	{
		$filePath = $this->path($style->name);
		$resizer = App::make('Resizer', $this->uploadedFile);

		if (strpos($style->value, 'x') === false) 
		{
			// Width given, height automagically selected to preserve aspect ratio (landscape).
			$width = $style->value;
			$resizer->resize($width, null, 'landscape')->save($filePath);
			$this->setPermissions($filePath, $this->mode);

			return;
		}
		
		$dimensions = explode('x', $style->value);
		$width = $dimensions[0];
		$height = $dimensions[1];
		
		if (empty($width)) 
		{
			// Height given, width automagically selected to preserve aspect ratio (portrait).
			$resizer->resize(null, $height, 'portrait')->save($filePath);
			$this->setPermissions($filePath, $this->mode);

			return;
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

		$this->setPermissions($filePath, $this->mode);
	}
}
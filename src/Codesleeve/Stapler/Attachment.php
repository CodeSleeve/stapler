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
	 * The uploaded/resized files that have been queued up for deletion.
	 * 
	 * @var array
	 */
	protected $queuedForDeletion = [];

	/**
	 * The uploaded/resized files that have been queued up for deletion.
	 * 
	 * @var array
	 */
	protected $queuedForWrite = [];

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
		$this->clear();

		if ($uploadedFile == STAPLER_NULL) {
			return;
		}
		
		$this->uploadedFile = APP::make('UploadedFile', $uploadedFile);
		$this->instanceWrite('file_name', $this->uploadedFile->getClientOriginalName());
		$this->instanceWrite('file_size', $this->uploadedFile->getClientSize());
		$this->instanceWrite('content_type', $this->uploadedFile->getMimeType());
		$this->instanceWrite('updated_at', date('Y-m-d H:i:s'));
		$this->queueAllForWrite();
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
	 * Mutator method for the interpolator property.
	 * 
	 * @param Interpolator $value 
	 */
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
		$callable = ['reset', 'remove', 'buildDirectory', 'cleanDirectory', 'move'];
		
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
	 * Generates the url to a file upload.
	 *
	 * @param string $styleName
	 * @return string
	*/
	public function url($styleName = '')
	{
		if ($this->originalFilename()) {
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
	 * Process the write queue.
	 *
	 * @param  Eloquent $instance
	 * @return void
	*/
	public function afterSave($instance) 
	{
		$this->bootstrap($instance);
		$this->save();
	}

	/**
	 * Queue up this attachments files for deletion.
	 *
	 * @param  Eloquent $instance
	 * @return void
	 */
	public function beforeDelete($instance)
	{
		$this->bootstrap($instance);
		$this->queueAllForDeletion();
	}

	/**
	 * Process the delete queue.
	 *
	 * @param  Eloquent $instance
	 * @return void
	*/
	public function afterDelete($instance) 
	{
		$this->bootstrap($instance);
		$this->flushDeletes();
	}

	/**
	 * Destroys the attachment.  Has the same effect as previously assigning
	 * STAPLER_NULL to the attachment and then saving.
	 * 
	 * @param  array $stylesToClear 
	 * @return void  
	 */
	public function destroy($stylesToClear = [])
	{
		$this->clear($stylesToClear);
		$this->save();
	}

	/**
	 * Clears out the attachment.  Has the same effect as previously assigning
	 * STAPLER_NULL to the attachment.  Does not save the associated model.
	 * 
	 * @param  array $stylesToClear 
	 * @return void                   
	 */
	public function clear($stylesToClear = [])
	{
		if ($stylesToClear) {
			$this->queueSomeForDeletion($stylesToClear);
		}
		else {
			$this->queueAllForDeletion();
		}
	}

	/**
	 * Removes the old file upload (if necessary).
	 * Saves the new file upload.
	 *  
	 * @return void
	 */
	public function save()
	{
		if (!$this->keep_old_files) {
			$this->flushDeletes();
		}

		$this->flushWrites();
	}

	/**
	 * Process the queuedForWrite que.
	 * 
	 * @return void
	 */
	protected function flushWrites()
	{
		foreach ($this->queuedForWrite as $style) 
		{
			if ($style->value && $this->uploadedFile->isImage()) {
				$imageProcessor = App::make($this->image_processing_library);
				$resizer = new File\Image\Resizer($imageProcessor);
				$file = $resizer->resize($this->uploadedFile, $style);
			}
			else {
				$file = $this->uploadedFile->getRealPath();
				
			}

			$filePath = $this->path($style->name);
			$this->move($file, $filePath);
		}

		$this->queuedForWrite = [];
	}

	/**
	 * Process the queuedForDeletion que.
	 * 
	 * @return void
	 */
	protected function flushDeletes()
	{
		$this->remove($this->queuedForDeletion);
		$this->queuedForDeletion = [];
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
	 * Wrapper for laravel's native public_path function.
	 * 
	 * @return mixed        
	 */
	protected function publicPath()
	{
		return realPath(public_path());
	}

	/**
	 * Fill the queuedForWrite que with all of this attachment's styles.
	 * 
	 * @return void
	 */
	protected function queueAllForWrite()
	{
		$this->queuedForWrite = $this->styles;
	}

	/**
	 * Add a subset (filtered via style) of the uploaded files for this attachment
	 * to the queuedForDeletion queue.
	 * 
	 * @param  array $stylesToClear 
	 * @return void               
	 */
	protected function queueSomeForDeletion($stylesToClear)
	{
		$filePaths = array_map(function($styleToClear) 
		{
			if (array_key_exists($styleToClear, $this->options['styles'])){
				return $this->path($styleToClear);
			} 
		}, $stylesToClear);

		array_merge($this->queuedForDeletion, $filePaths);
    }

    /**
     * Add all uploaded files (across all image styles) to the queuedForDeletion queue.
     * 
     * @return void
     */
    protected function queueAllForDeletion()
    {
		if (!$this->originalFilename()) {
			return;
		}

		if (!$this->preserve_files) 
		{
			$filePaths = array_map(function($style) 
			{
				return $this->path($style->name);
			}, $this->styles);

			$this->queuedForDeletion = array_merge($this->queuedForDeletion, $filePaths);
		}

		$this->instanceWrite('file_name', NULL);
		$this->instanceWrite('file_size', NULL);
		$this->instanceWrite('content_type', NULL);
		$this->instanceWrite('updated_at', NULL);
    }

    /**
     * Set an attachment attribute on the underlying model instance.
     * 
     * @param  string $property 
     * @param  mixed $value    
     * @return void        
     */
    protected function instanceWrite($property, $value)
    {
    	$fieldName = "{$this->name}_{$property}";
    	$this->instance->setAttribute($fieldName, $value);
    }
}
<?php namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Storage\StorageInterface;
use Codesleeve\Stapler\File\Image\Resizer;

class Attachment
{
	/**
	 * The model the attachment belongs to.
	 *
	 * @var string
	 */
	protected $instance;

	/**
	 * An instance of the configuration class.
	 *
	 * @var Codesleeve\Stapler\Config
	 */
	protected $config;

	/**
	 * An instance of the underlying storage driver that is being used.
	 *
	 * @var Codesleeve\Stapler\Storage\StorageInterface.
	 */
	protected $storageDriver;

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
	 * An instance of the resizer library that's being used for image processing.
	 * 
	 * @var Codesleeve\Stapler\File\Image\Resizer
	 */
	protected $resizer;

	/**
	 * An IOWrapper instance for converting file input formats (symfony uploaded file object
	 * arrays, string, etc) into an instance of Codesleeve\Stapler\UploadedFile.
	 * 
	 * @var Codesleeve\Stapler\IOWrapper
	 */
	protected $IOWrapper;

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
	 * @param Codesleeve\Stapler\Config $config
	 * @param Codesleeve\Stapler\Interpolator $interpolator
	 * @param Codesleeve\Stapler\File\Image\Resizer $resizer
	 * @param Codesleeve\Stapler\IOWrapper $IOWrapper
	 */
	function __construct(Config $config, Interpolator $interpolator, Resizer $resizer, IOWrapper $IOWrapper)
	{
		$this->config = $config;
		$this->interpolator = $interpolator;
		$this->resizer = $resizer;
		$this->IOWrapper = $IOWrapper;
	}

	/**
	 * Handle the dynamic setting of attachment options.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
    {
        $this->config->$name = $value;
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
		return $this->config->$optionName;
    }

    /**
	 * Mutator method for the uploadedFile property.
	 * Accepts the following inputs: 
	 * - An absolute string url (for fetching remote files).
	 * - An array (data parsed from the $_FILES array),
	 * - A symfony uploaded file object.
	 *
	 * @param mixed $uploadedFile
	 * @return void
	 */
	public function setUploadedFile($uploadedFile)
	{
		$this->clear();

		if ($uploadedFile == STAPLER_NULL) {
			return;
		}

		$this->uploadedFile = $this->IOWrapper->make($uploadedFile);
		$this->instanceWrite('file_name', $this->uploadedFile->getFilename());
		$this->instanceWrite('file_size', $this->uploadedFile->getSize());
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
	 * @param Codesleeve\Stapler\Interpolator $interpolator
	 * @return void 
	 */
	public function setInterpolator(Interpolator $interpolator)
	{
		$this->interpolator = $interpolator;
	}

	/**
	 * Accessor method for the interpolator property.
	 *
	 * @return Codesleeve\Stapler\Interpolator
	 */
	public function getInterpolator()
	{
		return $this->interpolator;
	}

	/**
	 * Mutator method for the resizer property.
	 *
	 * @param Codesleeve\Stapler\File\Image\Resizer $resizer
	 * @return  void
	 */
	public function setResizer(Resizer $resizer)
	{
		$this->resizer = $resizer;
	}

	/**
	 * Accessor method for the uploadedFile property.
	 *
	 * @return Codesleeve\Stapler\File\Image\Resizer
	 */
	public function getResizer()
	{
		return $this->resizer;
	}

	/**
	 * Mutator method for the storageDriver property.
	 *
	 * @param  Codesleeve\Stapler\Storage\StorageInterface $storageDriver
	 * @return void
	 */
	public function setStorageDriver(StorageInterface $storageDriver)
	{
		$this->storageDriver = $storageDriver;
	}

	/**
	 * Mutator method for the instance property.
	 * This provides a mechanism for the attachment to access properties of the
	 * corresponding model instance it's attached to.
	 *
	 * @param  Eloquent $instance
	 * @return void
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
	}

	/**
	 * Accessore method for the underlying 
	 * instance (Eloquent model) object this attachment
	 * is defined on.
	 * 
	 * @return Eloquent 
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Mutator method for the config property.
	 *
	 * @param  Codesleeve\Stapler\Config $config
	 * @return void
	 */
	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * Accessor method for the Config property.
	 * 
	 * @return array 
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Mutator method for the IOWrapper property.
	 * 
	 * @param Codesleeve\Stapler\IOWrapper $IOWrapper 
	 */
	public function setIOWrapper($IOWrapper)
	{
		$this->IOWrapper = $IOWrapper;
	}

	/**
	 * Accessor method for the QueuedForDeletion property.
	 * 
	 * @return array 
	 */
	public function getQueuedForDeletion()
	{
		return $this->queuedForDeletion;
	}

	/**
	 * Mutator method for the QueuedForDeletion property.
	 * 
	 * @param array $array
	 */
	public function setQueuedForDeletion($array)
	{
		$this->queuedForDeletion = $array;
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
		$callable = ['remove', 'move'];

		if (in_array($method, $callable)) {
			return call_user_func_array([$this->storageDriver, $method], $parameters);
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
		$this->instance = $instance;
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
		$this->instance = $instance;
		$this->clear();
	}

	/**
	 * Process the delete queue.
	 *
	 * @param  Eloquent $instance
	 * @return void
	*/
	public function afterDelete($instance)
	{
		$this->instance = $instance;
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
	 * Rebuild the images for this attachment.
	 *
	 * @return void 
	 */
	public function reprocess()
	{
		if (!$this->originalFilename()) {
			return;
		}

		foreach ($this->styles as $style) 
		{
			$fileLocation = $this->storage == 'filesystem' ? $this->path() : $this->url();
			$file = $this->IOWrapper->make($fileLocation);

			if ($style->value && $file->isImage()) {
				$file = $this->resizer->resize($file, $style);
			}
			else {
				$file = $file->getRealPath();
			}

			$filePath = $this->path($style->name);
			$this->move($file, $filePath);
		}
	}

	/**
     * Return the class type of the attachment's underlying
     * model instance.
     * 
     * @return string
     */
    public function getInstanceClass()
    {
    	return get_class($this->instance);
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
				$file = $this->resizer->resize($this->uploadedFile, $style);
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
		return $this->public_path . $this->defaultUrl($styleName);
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
			return $this->path($styleToClear);
		}, $stylesToClear);

		$this->queuedForDeletion = array_merge($this->queuedForDeletion, $filePaths);
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

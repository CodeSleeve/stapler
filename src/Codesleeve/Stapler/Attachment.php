<?php namespace Codesleeve\Stapler;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\Storage\StorageableInterface;
use Codesleeve\Stapler\File\Image\Resizer;
use Codesleeve\Stapler\Factories\File as FileFactory;

class Attachment
{
	/**
	 * The model instance that the attachment belongs to.
	 *
	 * @var StaplerableInterface
	 */
	protected $instance;

	/**
	 * An instance of the configuration class.
	 *
	 * @var AttachmentConfig
	 */
	protected $config;

	/**
	 * An instance of the underlying storage driver that is being used.
	 *
	 * @var StorageableInterface.
	 */
	protected $storageDriver;

	/**
	 * An instance of the interpolator class for processing interpolations.
	 *
	 * @var Interpolator
	 */
	protected $interpolator;

	/**
	 * The uploaded file object for the attachment.
	 *
	 * @var \Codesleeve\Stapler\File\FileInterface
	 */
	protected $uploadedFile;

	/**
	 * An instance of the resizer library that's being used for image processing.
	 *
	 * @var \Codesleeve\Stapler\File\Image\Resizer
	 */
	protected $resizer;

	/**
	 * The uploaded/resized files that have been queued up for deletion.
	 *
	 * @var array
	 */
	protected $queuedForDeletion = [];

	/**
	 * The uploaded/resized files that have been queued up to be written to storage.
	 *
	 * @var array
	 */
	protected $queuedForWrite = [];

	/**
	 * Constructor method
	 *
	 * @param AttachmentConfig $config
	 * @param Interpolator $interpolator
	 * @param Resizer $resizer
	 */
	function __construct(AttachmentConfig $config, Interpolator $interpolator, Resizer $resizer)
	{
		$this->config = $config;
		$this->interpolator = $interpolator;
		$this->resizer = $resizer;
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
	 * - A Symfony uploaded file object.
	 *
	 * @param mixed $uploadedFile
	 */
	public function setUploadedFile($uploadedFile)
	{
		if (!$this->keep_old_files) {
			$this->clear();
		}

		if ($uploadedFile == STAPLER_NULL) {
			$this->clearAttributes();

			return;
		}

		$this->uploadedFile = FileFactory::create($uploadedFile);
		$this->instanceWrite('file_name', $this->uploadedFile->getFilename());
		$this->instanceWrite('file_size', $this->uploadedFile->getSize());
		$this->instanceWrite('content_type', $this->uploadedFile->getMimeType());
		$this->instanceWrite('updated_at', date('Y-m-d H:i:s'));
		$this->queueAllForWrite();
	}

	/**
	 * Accessor method for the uploadedFile property.
	 *
	 * @return \Codesleeve\Stapler\File\FileInterface
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}

	/**
	 * Mutator method for the interpolator property.
	 *
	 * @param Interpolator $interpolator
	 * @return void
	 */
	public function setInterpolator(Interpolator $interpolator)
	{
		$this->interpolator = $interpolator;
	}

	/**
	 * Accessor method for the interpolator property.
	 *
	 * @return Interpolator
	 */
	public function getInterpolator()
	{
		return $this->interpolator;
	}

	/**
	 * Mutator method for the resizer property.
	 *
	 * @param Resizer $resizer
	 */
	public function setResizer(Resizer $resizer)
	{
		$this->resizer = $resizer;
	}

	/**
	 * Accessor method for the uploadedFile property.
	 *
	 * @return Resizer
	 */
	public function getResizer()
	{
		return $this->resizer;
	}

	/**
	 * Mutator method for the storageDriver property.
	 *
	 * @param  StorageableInterface $storageDriver
	 */
	public function setStorageDriver(StorageableInterface $storageDriver)
	{
		$this->storageDriver = $storageDriver;
	}

	/**
	 * Accessor method for the storageDriver property.
	 *
	 * @return StorageableInterface
	 */
	public function getStorageDriver()
	{
		return $this->storageDriver;
	}

	/**
	 * Mutator method for the instance property.
	 * This provides a mechanism for the attachment to access properties of the
	 * corresponding model instance it's attached to.
	 *
	 * @param StaplerableInterface $instance
	 */
	public function setInstance(StaplerableInterface $instance)
	{
		$this->instance = $instance;
	}

	/**
	 * Accessor method for the underlying
	 * instance (model) object this attachment
	 * is defined on.
	 *
	 * @return StaplerableInterface
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Mutator method for the config property.
	 *
	 * @param  AttachmentConfig $config
	 */
	public function setConfig(AttachmentConfig $config)
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
	 * storage driver directly via the attachment.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$callable = ['remove', 'move'];

		if (in_array($method, $callable)) {
			return call_user_func_array([$this->storageDriver, $method], $parameters);
		}
	}

	/**
	 * Generates the url to an uploaded file (or a resized version of it).
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
	 * Generates the file system path to an uploaded file (or a resized version of it). 
	 * This is used for saving files, etc.
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
	 * @return string
	 */
	public function createdAt()
	{
		return $this->instance->getAttribute("{$this->name}_created_at");
	}

	/**
	 * Returns the last modified time of the file as originally assigned to this attachment's model.
	 * Lives in the <attachment>_updated_at attribute of the model.
     *
	 * @return string
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
     * Returns the class type of the attachment's underlying
     * model instance.
     *
     * @return string
     */
    public function getInstanceClass()
    {
    	return get_class($this->instance);
    }

    /**
	 * Rebuilds the images for this attachment.
	 */
	public function reprocess()
	{
		if (!$this->originalFilename()) {
			return;
		}

		foreach ($this->styles as $style)
		{
			$fileLocation = $this->storage == 'filesystem' ? $this->path('original') : $this->url('original');
			$file = FileFactory::create($fileLocation);

			if ($style->dimensions && $file->isImage()) {
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
	 * Process the write queue.
	 *
	 * @param  StaplerableInterface $instance
	*/
	public function afterSave(StaplerableInterface $instance)
	{
		$this->instance = $instance;
		$this->save();
	}

	/**
	 * Queue up this attachments files for deletion.
	 *
	 * @param  StaplerableInterface $instance
	 */
	public function beforeDelete(StaplerableInterface $instance)
	{
		$this->instance = $instance;
		
		if (!$this->preserve_files) {
			$this->clear();
		}
	}

	/**
	 * Process the delete queue.
	 *
	 * @param  StaplerableInterface $instance
	*/
	public function afterDelete(StaplerableInterface $instance)
	{
		$this->instance = $instance;
		$this->flushDeletes();
	}

	/**
	 * Removes all uploaded files (from storage) for this attachment.
	 * This method does not clear out attachment attributes on the model instance.
	 *
	 * @param  array $stylesToClear
	 */
	public function destroy(array $stylesToClear = [])
	{
		$this->clear($stylesToClear);
		$this->flushDeletes();
	}

	/**
	 * Queues up all or some of this attachments uploaded files/images for deletion.
	 *
	 * @param  array $stylesToClear
	 */
	public function clear(array $stylesToClear = [])
	{
		if ($stylesToClear) {
			$this->queueSomeForDeletion($stylesToClear);
		}
		else {
			$this->queueAllForDeletion();
		}
	}

	/**
	 * Flushes the queuedForDeletion and queuedForWrite arrays.
	 */
	public function save()
	{
		$this->flushDeletes();
		$this->flushWrites();
	}

	/**
     * Set an attachment attribute on the underlying model instance.
     *
     * @param  string $property
     * @param  mixed $value
     */
    public function instanceWrite($property, $value)
    {
    	$fieldName = "{$this->name}_{$property}";
    	$this->instance->setAttribute($fieldName, $value);
    }

	/**
	 * Clear (set to null) all attachment related model
	 * attributes.
	 */
	public function clearAttributes()
	{
		$this->instanceWrite('file_name', NULL);
		$this->instanceWrite('file_size', NULL);
		$this->instanceWrite('content_type', NULL);
		$this->instanceWrite('updated_at', NULL);
	}

	/**
	 * Process the queuedForWrite que.
	 */
	protected function flushWrites()
	{
		foreach ($this->queuedForWrite as $style)
		{
      		if ($style->dimensions && $this->uploadedFile->isImage()) {
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
	 */
	protected function flushDeletes()
	{
		$this->remove($this->queuedForDeletion);
		$this->queuedForDeletion = [];
	}

	/**
	 * Fill the queuedForWrite que with all of this attachment's styles.
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
	 */
	protected function queueSomeForDeletion(array $stylesToClear)
	{
		$filePaths = array_map(function($styleToClear)
		{
			return $this->path($styleToClear);
		}, $stylesToClear);

		$this->queuedForDeletion = array_merge($this->queuedForDeletion, $filePaths);
    }

    /**
     * Add all uploaded files (across all image styles) to the queuedForDeletion queue.
     */
    protected function queueAllForDeletion()
    {
		if (!$this->originalFilename()) {
			return;
		}
		
		$filePaths = array_map(function($style)
		{
			return $this->path($style->name);
		}, $this->styles);

		$this->queuedForDeletion = array_merge($this->queuedForDeletion, $filePaths);
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
}
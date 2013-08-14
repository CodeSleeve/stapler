<?php namespace Codesleeve\Stapler;

use Event;
use Illuminate\Support\Facades\Config as Config;
use App;

/**
 * Easy file attachment management for Eloquent (Laravel 4).
 * 
 * Credits to the guys at thoughtbot for creating the
 * papclip plugin (rails) from which this bundle is inspired.
 * https://github.com/thoughtbot/paperclip
 * 
 * 
 * @package tabennett/stapler
 * @version 1.1 Alpha
 * @author Travis Bennett <tandrewbennett@hotmail.com>
 * @link 	
 */

trait Stapler
{
	/**
	 * All of the model's current file attachments.
	 *
	 * @var array
	 */
	protected $attachedFiles = [];

	/**
	 * Temporary storage for uploaded files.
	 *
	 * @var array
	 */
	protected $staplerUploads = [];

	/**
     * Handle the dynamic retrieval of attachment objects.
     * 
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
		if (array_key_exists($property, $this->attachedFiles)) {
		    return $this->getAttachedFile($this->attachedFiles[$property]);
		}

		return parent::__get($property);
    }

	/**
	 * Add a new file attachment type to the list of available attachments.
	 * This function acts as a quasi constructor for this trait.
	 *
	 * @param string $name
	 * @param array $options
	 * @return void
	*/
	public function hasAttachedFile($name, $options = [])
	{
		// Register the attachment with stapler and setup event listeners.
		$this->registerAttachment($name, $options);
		$this->registerEvents();
	}

	/**
	 * Set values for the model's file attribute fields before it's saved.
	 *
	 * @param model $model - The instance of the model object that triggering the save event.
	 * @return void
	*/
	public function beforeSave($model) 
	{
		// Loop through each attachment type, if there's a corresponding model attribute
		// containing a file then we'll fill the model attributes for that attachment type.
		foreach($model->attachedFiles as $attachedFile) 
		{
			$attachmentName = $attachedFile->name;

			if (array_key_exists($attachmentName, $model->attributes))
			{
				$uploadedFile = $model->attributes[$attachmentName];
				
				if ($uploadedFile == STAPLER_NULL)
				{
					$attributes = [
						"{$attachmentName}_file_name" => '',
						"{$attachmentName}_file_size" => '',
						"{$attachmentName}_content_type" => '',
						"{$attachmentName}_updated_at" => ''
					];

					$model->fill($attributes, true);
					$attachedFile->setUploadedFile($uploadedFile);
				}
				elseif ($uploadedFile) 
				{
					if (!$uploadedFile->isValid()) {
						throw new Exceptions\FileException($uploadedFile->getErrorMessage($uploadedFile->getError()));
					}

					$attributes = [
						"{$attachmentName}_file_name" => $uploadedFile->getClientOriginalName(),
						"{$attachmentName}_file_size" => $uploadedFile->getClientSize(),
						"{$attachmentName}_content_type" => $uploadedFile->getMimeType(),
						"{$attachmentName}_updated_at" => date('Y-m-d H:i:s')
					];

					$model->fill($attributes, true);
					$attachedFile->setUploadedFile($uploadedFile);
				}
			
				unset($model->attributes[$attachmentName]);
			}
		}
	}

	/**
	 * Loop through each attachment type.
	 * If there's a corresponding model attribute containing a file then we'll attempt to process the file.  
	 * Images with styles will be resized accordingly before being moved to their destination folders.
	 *
	 * @param model $model - The instance of the model object that triggered the save event.
	 * @return void
	*/
	public function afterSave($model) 
	{
		foreach ($model->attachedFiles as $attachedFile)
		{
			$attachedFile->bootstrap($model);
			$uploadedFile = $attachedFile->getUploadedFile();

			if ($uploadedFile) 
			{
				if ($uploadedFile == STAPLER_NULL) {
					$attachedFile->reset($attachedFile);
					
					continue;
				}
				
				foreach ($attachedFile->styles as $style) {
					$attachedFile->process($style);
				}
			}
		}
	}

	/**
	 * Remove file uploads from the file system after record deletion.
	 *
	 * @param model $model - The instance of the model object that triggered the delete event.
	 * @return void
	*/
	public function afterDelete($model) 
	{
		foreach ($model->attachedFiles as $attachedFile) {
			$attachedFile->bootstrap($model);
			$attachedFile->remove();
		}
	}

	/**
	 * Pass through method to ensure that all attachedFile objects returned
	 * from the __get() method are bootstrapped before they're accessed.
	 * 
	 * @param  Attachment $attachedFile
	 * @return Attachemnt 
	 */
	protected function getAttachedFile($attachedFile)
	{
		if (!$attachedFile->instance) {
			$attachedFile->bootstrap($this);
		}

		return $attachedFile;
	}

	/**
	 * Register an attachment type.
	 * and add the attachment to the list of attachments to be processed during saving.
	 *
	 * @param  string $name
	 * @param  array $options
	 * @return mixed
	 */
	protected function registerAttachment($name, $options)
	{
		$options = $this->mergeOptions($options);
		$this->validateOptions($options);
		$interpolator = App::make('Interpolator');
		$this->attachedFiles[$name] = App::make('Attachment', ['name' => $name, 'options' => $options, 'interpolator' => $interpolator]);
	}

	/**
	 * Merge configuration options.
	 * Here we'll merge user defined options with the stapler defaults in a cascading manner.
	 * We start with overall stapler options.  Next we merge in storage driver specific options.
	 * Finally we'll merge in attachment specific options on top of that.
	 *
	 * @param  array $options
	 * @return array
	 */
	public function mergeOptions($options)
	{
		$defaultOptions = Config::get('stapler::stapler');
		$options = array_merge($defaultOptions, (array) $options);
		$storage = $options['storage'];
		$options = array_merge(Config::get("stapler::{$storage}"), $options);
		$options['styles'] = array_merge( (array) $options['styles'], ['original' => '']);

		return $options;
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
		$options['storage'] == 'filesystem' ? $this->validateFilesystemOptions($options) : $this->validateS3Options($options);
	}

	/**
	 * Validate the attachment optioins for an attachment type when the storage
	 * driver is set to 'filesystem'.
	 * 
	 * @param  array $options 
	 * @return void
	 */
	protected function validateFilesystemOptions($options)
	{
		if (preg_match("/:id\b/", $options['url']) !== 1 && preg_match("/:id_partition\b/", $options['url']) !== 1) {
			throw new Exceptions\InvalidUrlOptionException('Invalid file url: an :id or :id_partition is required.', 1);
		}
	}

	/**
	 * Validate the attachment optioins for an attachment type when the storage
	 * driver is set to 's3'.
	 * 
	 * @param  array $options 
	 * @return void
	 */
	protected function validateS3Options($options)
	{
		# code...
	}

	/**
	 * Register beforeSave, afterSave, and after Delete event handlers.
	 * 
	 * @return void 
	 */
	protected function registerEvents()
	{
		$currentClass = get_class();
		$beforeSave = "eloquent.saving: $currentClass";
		$afterSave = "eloquent.saved: $currentClass";
		$afterDelete = "eloquent.deleted: $currentClass";
        
		// To register the event listeners we'll call the Event::Listen method directly,
		// however it's worth mentioning that we could have alternatively used the new
		// L4 syntax: e.g $this->saving("$currentClass@beforeSave"),  $this->saved("$currentClass@afterSave"), etc.
        if (!Event::hasListeners($beforeSave)) {
        	Event::listen($beforeSave, "$currentClass@beforeSave");
        }
 
        if (!Event::hasListeners($afterSave)) {
        	Event::listen($afterSave, "$currentClass@afterSave");
        }

        if (!Event::hasListeners($afterDelete)) {
        	Event::listen($afterDelete, "$currentClass@afterDelete");
        }
	}
}
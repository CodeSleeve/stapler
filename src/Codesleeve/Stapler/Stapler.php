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
 * @version v1.0.0-Beta2
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
		$this->registerEvents($name);
	}

	/**
     * Handle the dynamic retrieval of attachment objects.
     * 
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
		if (array_key_exists($property, $this->attachedFiles)) {
		    return $this->attachedFiles[$property];
		}

		return parent::__get($property);
    }

	/**
     * Handle the dynamic setting of attachment objects.
     *
     * @param  string $property
     * @param  mixed $value
     * @return mixed
     */
	public function __set($property, $value) 
	{
		if (array_key_exists($property, $this->attachedFiles)) 
		{
			if ($value) {
				$attachedFile = $this->attachedFiles[$property];
				$attachedFile->setUploadedFile($value);
			}

			return;
		}
		
		parent::__set($property, $value);
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
		$attachment = App::make('Attachment', ['name' => $name, 'options' => $options, 'interpolator' => $interpolator]);
		$attachment->bootstrap($this);
		$this->attachedFiles[$name] = $attachment;
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
	protected function mergeOptions($options)
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
		if (preg_match("/:id\b/", $options['url']) !== 1 && preg_match("/:id_partition\b/", $options['url']) !== 1 && preg_match("/:hash\b/", $options['url']) !== 1) {
			throw new Exceptions\InvalidUrlOptionException('Invalid Url: an id, id_partition, or hash interpolation is required.', 1);
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
		if (!$options['bucket']) {
			throw new Exceptions\InvalidUrlOptionException('Invalid Path: a bucket interpolation is required for s3 storage.', 1);
		}
	}

	/**
	 * Register eloquent event handlers.
	 * For each event (after save, before delete, etc) we'll register a listener
	 * for it using a callback on the attachment.
	 *
	 * @param  $attachmentName
	 * @return void 
	 */
	protected function registerEvents($attachmentName)
	{
		$attachedFile = $this->attachedFiles[$attachmentName];
        
        $this->saved(function($instance) use ($attachedFile) {
        	$attachedFile->afterSave($instance);
        });

        $this->deleting(function($instance) use ($attachedFile) {
        	$attachedFile->beforeDelete($instance);
        });

        $this->deleted(function($instance) use ($attachedFile) {
        	$attachedFile->afterDelete($instance);
        });
	}
}
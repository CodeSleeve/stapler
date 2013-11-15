<?php namespace Codesleeve\Stapler;

use Event, Config, App;

/**
 * Easy file attachment management for Eloquent (Laravel 4).
 * 
 * Credits to the guys at thoughtbot for creating the
 * paperclip plugin (rails) from which this package is inspired.
 * https://github.com/thoughtbot/paperclip
 * 
 * 
 * @package tabennett/stapler
 * @version v1.0.0-Beta3
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
     * @param  string $key
     * @return mixed
     */
	public function getAttribute($key)
	{
		if (array_key_exists($key, $this->attachedFiles))
		{
		    return $this->attachedFiles[$key];
		}

		return parent::getAttribute($key);
    }

	/**
     * Handle the dynamic setting of attachment objects.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
	public function setAttribute($key, $value)
	{
		if (array_key_exists($key, $this->attachedFiles)) 
		{
			if ($value)
			{
				$attachedFile = $this->attachedFiles[$key];
				$attachedFile->setUploadedFile($value);
			}

			return;
		}
		
		parent::setAttribute($key, $value);
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
		App::make('AttachmentValidator')->validateOptions($options);
		
		$attachment = App::make('Attachment', ['name' => $name, 'options' => $options]);
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
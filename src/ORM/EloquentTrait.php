<?php namespace Codesleeve\Stapler\ORM;

use Codesleeve\Stapler\Factories\Attachment as AttachmentFactory;

trait EloquentTrait
{
    /**
     * All of the model's current file attachments.
     *
     * @var array
     */
    protected $attachedFiles = [];

    /**
     * Accessor method for the $attachedFiles property.
     *
     * @return array
     */
    public function getAttachedFiles()
    {
        return $this->attachedFiles;
    }

    /**
     * Add a new file attachment type to the list of available attachments.
     * This function acts as a quasi constructor for this trait.
     *
     * @param string $name
     * @param array $options
     */
    public function hasAttachedFile($name, array $options = [])
    {
        $attachment = AttachmentFactory::create($name, $options);
        $attachment->setInstance($this);
        $this->attachedFiles[$name] = $attachment;
    }

    /**
     * Register eloquent event handlers.
     * We'll spin through each of the attached files defined on this class
     * and register callbacks for the events we need to observe in order to
     * handle file uploads.
     */
    public static function bootEloquentTrait()
    {
        static::saved(function($instance) {
            foreach($instance->attachedFiles as $attachedFile) {
                $attachedFile->afterSave($instance);
            }
        });

        static::deleting(function($instance) {
            foreach($instance->attachedFiles as $attachedFile) {
                $attachedFile->beforeDelete($instance);
            }
        });

        static::deleted(function($instance) {
            foreach($instance->attachedFiles as $attachedFile) {
                $attachedFile->afterDelete($instance);
            }
        });
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
     * Get all of the current attributes and attachment objects on the model.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return array_merge($this->attachedFiles, parent::getAttributes());
    }
}

<?php namespace Codesleeve\Stapler\Traits;

use Codesleeve\Stapler\Factories\Attachment as AttachmentFactory;

trait Eloquent
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
     * @return void
     */
    public function hasAttachedFile($name, $options = [])
    {
        $attachment = AttachmentFactory::create($name, $options);
        $attachment->setInstance($this);
        $this->attachedFiles[$name] = $attachment;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::bootStapler();
    }

    /**
     * Register eloquent event handlers.
     * We'll spin through each of the attached files defined on this class
     * and register callbacks for the events we need to observe in order to
     * handle file uploads.
     *
     * @return void
     */
    public static function bootStapler()
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
}

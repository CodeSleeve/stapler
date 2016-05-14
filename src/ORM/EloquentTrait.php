<?php

namespace Codesleeve\Stapler\ORM;

use Codesleeve\Stapler\Factories\Attachment as AttachmentFactory;

trait EloquentTrait
{
    /**
     * All of the model's current file attachments.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Accessor method for the $attachments property.
     *
     * @return array
     */
    public function getAttachments() : array
    {
        return $this->attachments;
    }

    /**
     * Add a new file attachment type to the list of available attachments.
     * This function acts as a quasi constructor for this trait.
     *
     * @param string $name
     * @param array  $options
     */
    public function addAttachment(string $name, array $options = [])
    {
        $attachment = AttachmentFactory::create($name, $options);
        $attachment->setInstance($this);
        $this->attachments[$name] = $attachment;
    }

    /**
     * The "booting" method of the model.
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
     */
    public static function bootStapler()
    {
        static::saved(function ($instance) {
            foreach ($instance->attachments as $attachment) {
                $attachment->afterSave($instance);
            }
        });

        static::deleting(function ($instance) {
            foreach ($instance->attachments as $attachment) {
                $attachment->beforeDelete($instance);
            }
        });

        static::deleted(function ($instance) {
            foreach ($instance->attachments as $attachment) {
                $attachment->afterDelete($instance);
            }
        });
    }

    /**
     * Handle the dynamic retrieval of attachment objects.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attachments)) {
            return $this->attachments[$key];
        }

        return parent::getAttribute($key);
    }

    /**
     * Handle the dynamic setting of attachment objects.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->attachments)) {
            if ($value) {
                $attachment = $this->attachments[$key];
                $attachment->setUploadedFile($value);
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
        return array_merge($this->attachments, parent::getAttributes());
    }

    /**
     * Return the image paths (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function pathsForAttachment(string $attachmentName) : array
    {
        $paths = [];

        if (isset($this->attachments[$attachmentName])) {
            $attachment = $this->attachments[$attachmentName];

            foreach ($attachment->styles as $style) {
                $paths[$style->name] = $attachment->path($style->name);
            }
        }

        return $paths;
    }

    /**
     * Return the image urls (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function urlsForAttachment(string $attachmentName) : array
    {
        $urls = [];

        if (isset($this->attachments[$attachmentName])) {
            $attachment = $this->attachments[$attachmentName];

            foreach ($attachment->styles as $style) {
                $urls[$style->name] = $attachment->url($style->name);
            }
        }

        return $urls;
    }
}

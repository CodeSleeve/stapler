<?php

namespace Codesleeve\Stapler\ORM;

interface StaplerableInterface
{
    /**
     * Accessor method for the $attachedFiles property.
     *
     * @return array
     */
    public function getAttachedFiles();

    /**
     * Add a new file attachment type to the list of available attachments.
     * This function acts as a quasi constructor for this trait.
     *
     * @param string $name
     * @param array  $options
     */
    public function hasAttachedFile($name, array $options = []);

    /**
     * Handle the dynamic retrieval of attachment objects.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Handle the dynamic setting of attachment objects.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value);

    /**
     * Return the image paths (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function pathsForAttachment($attachmentName);

    /**
     * Return the image urls (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function urlsForAttachment($attachmentName);
}

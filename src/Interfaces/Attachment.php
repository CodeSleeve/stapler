<?php

namespace Codesleeve\Stapler\Interfaces;

use Codesleeve\Stapler\AttachmentConfig;
use Codesleeve\Stapler\Interfaces\Interpolator as InterpolatorInterface;
use Codesleeve\Stapler\Interfaces\Resizer as ResizerInterface;
use Codesleeve\Stapler\Interfaces\Storage as StorageInterface;
use Codesleeve\Stapler\ORM\StaplerableInterface;

interface Attachment
{
    /**
     * Constructor method.
     *
     * @param AttachmentConfig          $config
     * @param InterpolatorInterface     $interpolator
     * @param ResizerInterface          $resizer
     */
    public function __construct(AttachmentConfig $config, InterpolatorInterface $interpolator, ResizerInterface $resizer);

    /**
     * Mutator method for the uploadedFile property.
     * Accepts the following inputs:
     * - An absolute string url (for fetching remote files).
     * - An array (data parsed from the $_FILES array),
     * - A Symfony uploaded file object.
     *
     * @param mixed $uploadedFile
     */
    public function setUploadedFile($uploadedFile);

    /**
     * Accessor method for the uploadedFile property.
     *
     * @return \Codesleeve\Stapler\Interfaces\File
     */
    public function getUploadedFile();

    /**
     * Mutator method for the interpolator property.
     *
     * @param Interpolator $interpolator
     */
    public function setInterpolator(Interpolator $interpolator);

    /**
     * Accessor method for the interpolator property.
     *
     * @return Interpolator
     */
    public function getInterpolator();

    /**
     * Mutator method for the resizer property.
     *
     * @param Resizer $resizer
     */
    public function setResizer(Resizer $resizer);

    /**
     * Accessor method for the uploadedFile property.
     *
     * @return Resizer
     */
    public function getResizer();

    /**
     * Mutator method for the storageDriver property.
     *
     * @param StorageInterface $storageDriver
     */
    public function setStorageDriver(StorageInterface $storageDriver);

    /**
     * Accessor method for the storageDriver property.
     *
     * @return StorageInterface
     */
    public function getStorageDriver();

    /**
     * Mutator method for the instance property.
     * This provides a mechanism for the attachment to access properties of the
     * corresponding model instance it's attached to.
     *
     * @param StaplerableInterface $instance
     */
    public function setInstance(StaplerableInterface $instance);

    /**
     * Accessor method for the underlying
     * instance (model) object this attachment
     * is defined on.
     *
     * @return StaplerableInterface
     */
    public function getInstance();

    /**
     * Mutator method for the config property.
     *
     * @param AttachmentConfig $config
     */
    public function setConfig(AttachmentConfig $config);

    /**
     * Accessor method for the Config property.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Accessor method for the QueuedForDeletion property.
     *
     * @return array
     */
    public function getQueuedForDeletion();

    /**
     * Mutator method for the QueuedForDeletion property.
     *
     * @param array $array
     */
    public function setQueuedForDeletion($array);

    /**
     * Generates the url to an uploaded file (or a resized version of it).
     *
     * @param string $styleName
     *
     * @return string
     */
    public function url($styleName = '');

    /**
     * Generates the file system path to an uploaded file (or a resized version of it).
     * This is used for saving files, etc.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function path($styleName = '');

    /**
     * Returns the creation time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_created_at attribute of the model.
     * This attribute may conditionally exist on the model, it is not one of the four required fields.
     *
     * @return string
     */
    public function createdAt();

    /**
     * Returns the last modified time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_updated_at attribute of the model.
     *
     * @return string
     */
    public function updatedAt();

    /**
     * Returns the content type of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_content_type attribute of the model.
     *
     * @return string
     */
    public function contentType();

    /**
     * Returns the size of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_size attribute of the model.
     *
     * @return int
     */
    public function size();

    /**
     * Returns the name of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_name attribute of the model.
     *
     * @return string
     */
    public function originalFilename();

    /**
     * Returns the class type of the attachment's underlying
     * model instance.
     *
     * @return string
     */
    public function getInstanceClass();

    /**
     * Rebuilds the images for this attachment.
     */
    public function reprocess();

    /**
     * Process the write queue.
     *
     * @param StaplerableInterface $instance
     */
    public function afterSave(StaplerableInterface $instance);

    /**
     * Queue up this attachments files for deletion.
     *
     * @param StaplerableInterface $instance
     */
    public function beforeDelete(StaplerableInterface $instance);

    /**
     * Process the delete queue.
     *
     * @param StaplerableInterface $instance
     */
    public function afterDelete(StaplerableInterface $instance);

    /**
     * Removes all uploaded files (from storage) for this attachment.
     * This method does not clear out attachment attributes on the model instance.
     *
     * @param array $stylesToClear
     */
    public function destroy(array $stylesToClear = []);

    /**
     * Queues up all or some of this attachments uploaded files/images for deletion.
     *
     * @param array $stylesToClear
     */
    public function clear(array $stylesToClear = []);

    /**
     * Flushes the queuedForDeletion and queuedForWrite arrays.
     */
    public function save();

    /**
     * Set an attachment attribute on the underlying model instance.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function instanceWrite($property, $value);

    /**
     * Clear (set to null) all attachment related model
     * attributes.
     */
    public function clearAttributes();
}
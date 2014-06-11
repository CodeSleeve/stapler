## Attachments
Attachments are the bread and butter of Stapler.  When you define an attached file on a model, your model automatically gains a new property containing an attachment value object for representing uploaded files on that record.  Regardless or whether you've uploaded a file or not, this value object will exist.  This allows file uploads to be represented in a simple yet powerful object oriented fashion.

### Properties
* *Codesleeve\Stapler\ORM\StaplerableInterface* **instance**: The model instance that the attachment belongs to.
* *Codesleeve\Stapler\AttachmentConfig* **config**: The attachment's config value object.  
* *Codesleeve\Stapler\Storage\StorageableInterface* **storageDriver**: An instance of the underlying storage driver being used by the attachment.	
* *Codesleeve\Stapler\Interpolator* **interpolator**: An instance of the interpolator class for processing interpolations.
* *Codesleeve\Stapler\File\FileInterface* **uploadedFile**: The uploaded file object for the attachment.	
* *Codesleeve\Stapler\File\Image\Resizer* **resizer**: An instance of the resizer library that's being used for image processing.	
* *array* **queuedForDeletion**: An array of uploaded file objects queued up for deletion by Stapler.
* *array* **queuedForWrite**: An array of uploaded file objects queued up to be written to storage by Stapler.

### Methods
Attachments contain an assortment of methods for working with uploaded files and their properties:

* **setUploadedFile**: Mutator method for setting the uploadedFile property on the attachment.  When a model is using stapler and a property value is set for one of the attachments defined on that model, this method is called.  This allows allows files to be passed to stapler in multiple formats (strings, array, or symfony uploaded file objects) while ensuring that they're all converted to an instance of *Codesleeve\Stapler\File\FileInterface*.

* **getUploadedFile**: Accessor method for the uploadedFile property on the attachment.  Returns an instance of *Codesleeve\Stapler\File\FileInterface*.

* **setInterpolator**: Mutator method for setting the interpolator property on the attachment.

* **getInterpolator**: Accessor method for the interpolator property on the attachment.  Returns an instance of *Codesleeve\Stapler\Interpolator*.

* **setResizer**: Mutator method for setting the resizer property on the attachment.

* **getResizer**: Accessor method for the resizer property on the attachment.  Returns an instance of *Codesleeve\Stapler\File\Image\Resizer*.  The resizer object is responsible for all of the geometry calculations, auto-orient calculations, etc that are done when an image is processed.

* **setStorageDriver**: Mutator method for setting the storageDriver property on the attachment.

* **getStorageDriver**: Accessor method for the storageDriver property on the attachment.  Returns an instance of *Codesleeve\Stapler\Storage\StorageableInterface*.  The storageDriver object is responsible handling the underlying storage of an uploaded file across the various storage mediums (file system, S3, etc).

* **setInstance**: Mutator method for setting the instance property on the attachment.

* **getInstance**: Accessor method for the instance property on the attachment.  This is always the model/entity that the attachment was defined in and will vary depending upon which ORM/trait is currently being used.

* **setConfig**: Mutator method for setting the config property on the attachment.

* **getConfig**: Accessor method for the config property on the attachment.  Configuration for attachment objects are stored in a value object of type *Codesleeve\Stapler\AttachmentConfig*.

* **getQueuedForDeletion**: Accessor method for the queuedForDeletion property.

* **getQueuedForWrite**: Accessor method for the queuedForWrite property.

* **url**: Generates the url to an uploaded file (or a resized version of it).

* **path**: Generates the file system path to an uploaded file (or a resized version of it).

* **createdAt**: Returns the creation time of the file as originally assigned to this attachment's model. Lives in the <attachment>_created_at attribute of the model.  This attribute may conditionally exist on the model, it is not one of the four required fields.

* **updatedAt**: Returns the last modified time of the file as originally assigned to this attachment's model.  Lives in the <attachment>_updated_at attribute of the model.

* **contentType**: Returns the content type of the file as originally assigned to this attachment's model. Lives in the <attachment>_content_type attribute of the model.

* **size**: Returns the size of the file as originally assigned to this attachment's model. Lives in the <attachment>_file_size attribute of the model.

* **originalFilename**: Returns the name of the file as originally assigned to this attachment's model. Lives in the <attachment>_file_name attribute of the model.

* **getInstanceClass**: Returns the class type of the attachment's underlying model instance.

* **reprocess**: Rebuilds the images for an attachment.  This is an extremely powerful method; once called on an attachment object, it uses the original copy of the uploaded file to reprocess any styles defined on the attachment.  This is extremely useful when adding a new style to an attachment that has already had a file uploaded and processed.

* **afterSave**: This is the callback method triggered after an attachment's model instance has been saved.  Once triggered, it causes the queuedForWrite array to be flushed, which in turn triggers image processing, file storage, etc.

* **beforeDelete**:  This is the callback method triggered before a model instance is deleted.  Once triggered, all images/files for the attachment will be added to the queuedForDeletion array.

* **afterDelete**:  This is the callback method triggered after a model instance has been deleted.  Once triggered, it causes the queuedForDeletion array to be flushed/processed.

* **destroy**: Removes all uploaded files (from storage) for an attachment. This method does not clear out attachment attributes on the model instance.  An array of styles can be passed so that only those styles are removed from storage.

* **clear**: Queues up all or some of this attachments uploaded files/images for deletion.  An array of styles can be passed so that only those styles are queued up for deletion.

* **save**: Flushes the queuedForDeletion and queuedForWrite arrays.

* **instanceWrite**: Set an attachment attribute on the underlying model instance.  Accepts the name of an attachment property ('size', 'content_type', etc) as well as the value that should be set for the property.

* **clearAttributes**: Clear (set to null) all attachment related model attributes.
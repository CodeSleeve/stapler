## Examples
* [Eloquent](#eloquent)
  * [Defining Attachments](#defining-attachments)
  * [Saving Files](#saving-files)
  * [Retreiving Uploads](#retreiving-uploads)
  * [Deleting uploads](#deleting-uploads)

*These examples assume you have already booted Stapler (see [setup](setup.md) for more info on this).*

### Eloquent
#### Definining-Attachments
```php
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

Class Photo extends Eloquent implements StaplerableInterface
{
    // We'll need to use the Stapler Eloquent trait in our model (see setup for more info).
    use EloquentTrait;
    
    /**
     * We can add our attachments to the fillable array so that they're 
     * mass assignable on the model.
     *
     * @var array
     */
    protected $fillable = ['foo', 'bar', 'baz', 'qux', 'quux'];
    
    /**
     * Inside our model's constructor, we'll define some stapler attachments:
     *
     * @param attributes
     */
    public function __construct(array $attributes = array()) 
    {
        // Define an attachment named 'foo', with both thumbnail (100x100) and large (300x300) styles, 
        // using custom url and default_url configurations:
        $this->hasAttachedFile('foo', [
            'styles' => [
                'thumbnail' => '100x100',
                'large' => '300x300'
            ],
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'default_url' => '/:attachment/:style/missing.jpg'
        ]);
        
        // Define an attachment named 'bar', with both thumbnail (100x100) and large (300x300) styles, 
        // using custom url and default_url configurations, with the keep_old_files flag set to true 
        // (so that older file uploads aren't deleted from the file system) and image cropping turned on:
        $this->hasAttachedFile('bar', [
            'styles' => [
                'thumbnail' => '100x100#',
                'large' => '300x300#'
            ],
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'keep_old_files' => true
        ]);
        
        // Define an attachment named 'baz' that has a watermarked style.  Here, we define a style named 'watermarked'
        // that's a closure (so that we can do some complex watermarking stuff):
        $this->hasAttachedFile('baz', [
            'styles' => [
                'thumbnail' => ['dimensions' => '100x100', 'auto-orient' => true, 'convert_options' => ['quality' => 100]],
                'micro'     => '50X50',
                'watermarked' => function($file, $imagine) {
                    $watermark = $imagine->open('/path/to/images/watermark.png');   // Create an instance of ImageInterface for the watermark image.
                    $image     = $imagine->open($file->getRealPath());              // Create an instance of ImageInterface for the uploaded image.
                    $size      = $image->getSize();                                 // Get the size of the uploaded image.
                    $watermarkSize = $watermark->getSize();                         // Get the size of the watermark image.

                    // Calculate the placement of the watermark (we're aiming for the bottom right corner here).
                    $bottomRight = new Imagine\Image\Point($size->getWidth() - $watermarkSize->getWidth(), $size->getHeight() - $watermarkSize->getHeight());

                    // Paste the watermark onto the image.
                    $image->paste($watermark, $bottomRight);

                    // Return the Imagine\Image\ImageInterface instance.
                    return $image;
                }
            ],
            'url' => '/system/:attachment/:id_partition/:style/:filename'
        ]);
        
        // Define an attachment named 'qux'.  In this attachment, we'll use alternative style notation to define a slightly more
        // complex thumbnail style.  In this example, the thumbnail style will be a 100x100px auto-oriented image with 100% quality: 
        $this->hasAttachedFile('qux', [
            'styles' => [
                'thumbnail' => ['dimensions' => '100x100', 'auto-orient' => true, 'convert_options' => ['quality' => 100]],
                'micro'     => '50X50'
            ],
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'default_url' => '/defaults/:style/missing.png'
        ]);
        
        // Define an attachment named 'quux' that stores images remotely in an S3 bucket.
        $this->hasAttachedFile('quux', [
            'styles' => [
                'thumbnail' => '100x100#',
                'large' => '300x300#'
            ],
            'storage' => 's3',
            's3_client_config' => [
                'key' => 'yourPublicKey',
                'secret' => 'yourSecreteKey',
                'region' => 'yourBucketRegion'
            ],
            's3_object_config' => [
                'bucket' => 'your.s3.bucket'
            ],
            'default_url' => '/defaults/:style/missing.png',
            'keep_old_files' => true
        ]);

        // IMPORTANT:  the call to the parent constructor method
        // should always come after we define our attachments.
        parent::__construct($attributes);
    }
}
```

#### Saving-Files
Once an attachment is defined on a model, we can then assign values to it (as a property on the model) in order to save it as a file upload.  Assuming we had an instance of our Photo model from above, we can assign a value to any of our defined attachments before saving the model.  Upon a successful save of the record, Stapler will go in and handle all of the file uploading, image processing, etc for us.  In a controller somewhere, let's assume that we've fetched (or created) a photo model instance and we want to assign some file values to it (from a previously submitted form):

```php
// If we're using Laravel, we can assign the Symfony uploaded file object directly on the modeal:
$photo->foo = Input::file('foo');
$photos->save();

// In fact, because our attachments are listed in our fillable array, we can simple mass assign all input values on our photo:
$photo->fill(Input::all());
$photo->save();

// If we're not using Laravel, we can assign an array (from the $_FILES array) to the uploaded file:
$photo->foo = $_FILES['foo'];
$photo->save();

// Regardless of what framework we're using, we can always assign a remote url as an attachment value.
// This is very useful when working with third party API's such as facebook, twitter, etc.  
// Note that this feature requires that the CURL extension is included as part of your PHP installation.
$photo->foo = "http://foo.com/bar.jpg";
$photo->save();

// Or an existing file on the local filesystem:
$photo->foo = "/some/path/on/the/local/file/system/bar.jpg";
$photo->save();
```

#### Retreiving-Uploads
After we define an attachment on a model, we can access the attachment as a property on the model (regardless of whether or not an image has been uploaded or not).  When attempting to display images, the default image url will be displayed until an image is uploaded.  The attachment itself is an instance of Codesleeve\Stapler\Attachment (see [attachments](attachments.md) for more info on attachments).  An attachment is really just a value object; it provides methods for seamlessly accessing the properties, paths, and urls of the underlying uploaded file.  Continuing our example from above, lets assume we wanted to display the various styles of our previously defined foo attachment in an image tag.  Assuming we had an instance of the Photo model, we could do the following:
```html
Display a resized thumbnail style image belonging to a user record
<img src="<?= $photo->foo->url('thumbnail') ?>">

Display the original image style (unmodified image):
<img src="<?= $photo->foo->url('original') ?>">

This also displays the unmodified original image (unless the :default_style interpolation has been set to a different style):
<img src="<?= $photo->foo->url() ?>">
```

As you can see, we can display any of the defined styles for a given attachment. We can also retrieve the full file path (on disk) of a given style (this is very useful when providing file download functionality):
```php
$photo->foo->path('thumbnail');
```

We can also grab the size, original filename, laste updated timestamp, and content type of the original (unaltered) uploaded file (**NOTE**: *stapler will always store an unaltered version of the original file*):
```php
$photo->foo->size();
$photo->foo->originalFilename();
$photo->foo->updatedAt();
$photo->foo->contentType();
```

#### Deleting-Uploads
Unless you've set the 'keep_old_files' flag on the attachment to true, deleting a record will automatically remove all uploaded files, across all attachments, across all styles, for the a given model/record:
```php
$photo->delete();
```

If we need to remove the uploaded files only (the photo record itself will remain intact), we can assign the attachment a value of STAPLER_NULL and then save the record. This will remove all of the attachment's uploaded files from storage and clear out the attachment related file attributes on the model:
```php
// Remove all of the attachment's uploaded files and empty the attacment attributes on the model (does not save the record though).
$photo->foo = STAPLER_NULL;
$photo->save();
```

The destroy method is similar, however it doesn't clear out the attachment attributes on the model and doesn't require us to save the record in order to remove uploaded files.  It's also filterable; we can pass in array of the syles we want to clear:  
```php
// Remove all of the attachments's uploaded files (across all styles) from storage.
$photo->foo->destroy();

// Remove thumbnail files only.
$photo->foo->destroy(['thumbnail']);
```

You may also reprocess uploaded images on an attachment by calling the reprocess() command (this is very useful for adding new styles to an existing attachment type where records have already been uploaded).

```php
// Programmatically reprocess an attachment's uploaded images:
$photo->foo->reprocess();
```
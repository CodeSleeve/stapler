#Stapler

Be warned: this package is still very much in development.  I'm currently in the middle of refacting it to be more decoupled, solid, etc.  The features that are currently available should be equivalent to the L3 version of Stapler.  In the near future my goal is to have a completely decoupled file storage interface with drivers for various storage options (e.g local, AWS S3, Rackspace Cloud Files, etc).  I'm also shopping around for an image processing package on Packagist (currently looking at both Intervention Image and Imagine).  In general I want to make Stapler as powerful and fully featured as its Paperclip counterpart.  That being said....

Stapler can be used to generate file upload attachments for use with the wonderfully fabulous Laravel PHP Framework (>= 4.0), authored by Taylor Otwell.
If you have used ruby on rails' paperclip plugin then you will be familiar with its syntax.  This bundle is inspired entirely from the work done
by the guys at thoughtbot for the Rails Paperclip bundle: https://github.com/thoughtbot/paperclip.  While not an exact duplicate, if you've used Paperclip before then you should be 
somewhat familiar with how this bundle works.

Stapler was created by Travis Bennett.

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quickstart)
- [Configuration](#configuration)
- [Examples](#examples)

## Requirements

Stapler currently requires php >= 5.4 (Stapler is implemented via the use of traits).

## Installation
Stapler is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/stapler`.

```php
  "require": {
    "laravel/framework": "4.0.*",
    "codesleeve/stapler": "dev-master"
  }
```

Once this operation completes, the final step is to add the service provider. Open `app/config/app.php`, and add a new item to the providers array.

```php
    'Codesleeve\Stapler\StaplerServiceProvider'
```

## Quickstart
In the document root of your application (most likely the public folder), create a folder named system and 
grant your application write permissions to it.

In your model:

```php
class User extends Eloquent {
	use Codesleeve\Stapler\StaplerTrait;

    public function __construct($attributes = array(), $exists = false){
        parent::__construct($attributes, $exists);

        $this->hasAttachedFile('avatar', [
            'styles' => [
            	'medium' => '300x300',
                'thumb' => '100x100'
            ]
        ]);
    }
}
```

From the command line, use the migration generator:

```php
php artisan stapler:fasten users avatar
php artisan migrate
```

In your new view:

```php
<?= Form::open(['url' => action('UsersController@store'), 'method' => 'POST']) ?>
	<?= Form::file('avatar') ?>
    <?= Form::submit('save') ?>   
<?= Form::close() ?>
```
In your controller:

```php
public function store()
{
	$user = User::create(['avatar' => Input::file('avatar')]);	
}
```

In your show view:
```php
<img src="<?= $user->avatar->url() ?>" >
<img src="<?= $user->avatar->url('medium') ?>" >
<img src="<?= $user->avatar->url('thumb') ?>" >
```

To detach (reset) a file, simply set the attribute to the constant STAPLER_NULL:
```php
$user->avatar = STAPLER_NULL;
$user->save();
```

## Configuration

Stapler works by attaching file uploads to records stored within a database table (model).  Configuration is (currently) available on a per model basis only.  A model can have multiple attachments defined (avatar, photo, some_random_attachment, etc) and in turn each attachment can have multiple sizes (styles) defined.  When an image or file is uploaded, Stapler will handle all the file processing (moving, resizing, etc) and provide helper methods for retreiving the uploaded assets.  To accomplish this, four fields (named after the attachemnt) are created (via stapler:fasten) in the corresponding table for any model containing a file attachment (these should be included in the model's fillable array).  For example, an attachment named 'avatar' the following fields would be created:

*   avatar_file_name
*   avatar_file_size
*   avatar_content_type
*   avatar_updated_at

Stapler can be configured to store files in a variety of ways.  This is done by defining a url string which points to the uploaded file asset.  This is done via string interpolations.  Currently, the following interpolations are available for use:

*   **:attachment** - The name of the file attachment as declared in the hasAttachedFile function, e.g 'avatar'.
*   **:class**  - The classname of the model contaning the file attachment, e.g User.  Stapler can handle namespacing of classes.
*   **:extension** - The file extension type of the uploaded file, e.g '.jpg'
*   **:filename** - The name of the uploaded file, e.g 'some_file.jpg'
*   **:id** - The id of the corresponding database record for the uploaded file.
*   **:id_partition** - The partitioned id of the corresponding database record for the uploaded file, e.g an id = 1 is interpolated as 000/000/001.  This is the default and recommended setting for Stapler.  Partioned id's help overcome the 32k subfolder problem that occurs in nix-based systems using the EXT3 file system.
*   **:laravel_root** - The path to the root of the laravel project.
*   **:style** - The resizing style of the file (images only), e.g 'thumbnail' or 'orginal'.
*   **:url** - The resizing style of the file (images only), e.g 'thumbnail' or 'orginal'.

These interpolation can then be used to define a path, url, and default_url for the location of your uploaded files.
In a minimal configuration, the following settings are enabled by default:

*   **path**: ':laravel_root/public:url',
*   **url**: '/system/:class/:attachment/:id_partition/:style/:filename',
*   **default_url**: '/:attachment/:style/missing.png',
*   **default_style**: 'original',
*   **styles**: [],
*   **keep_old_files**: false

*   **path**: Similar to the url, the path option is the location where your files will be stored at on disk.  It should be noted that the path option should not conflict with the url option.  Stapler provides sensible defaults that take care of this for you.
*   **url**: The file system path to the file upload, relative to the public folder (document root) of the project.
*   **default_url**: The default file returned when no file upload is present for a record.
*   **default_style**: The default style returned from the Stapler file location helper methods.  An unaltered version of uploaded file
    is always stored within the 'original' style, however the default_style can be set to point to any of the defined syles within the styles array.
*   **styles**: An array of image sizes defined for the file attachment.  Stapler will attempt to use the Resizer bundle to format the file upload
    into the defined style.  To enable image cropping, insert a # symbol after the resizing options.  For example:

```php
'styles' => [
    'thumbnail' => '100x100#'
]
```

will create a copy of the file upload, resized and cropped to 100x100.

Currently, this verion of Stapler relies on a modified version of the Laravel3 Resizer bundle (as mentioned before, this is going to swapped out for one of the packagist image processing packages soon), which in turn makes use of the PHP GD library for image processing.  However, because Stapler is inspired by Rails paperclip plugin (which makes use of ImageMagick for image processing) the following ImageMagick processing directives will be recognized when defining Stapler styles:

*   **width**: A style that defines a width only (landscape).  Height will be automagically selected to preserve aspect ratio.  This works well for resizing
    images for display on mobile devices, etc.
*   **xheight**: A style that defines a heigh only (portrait).  Width automagically selected to preserve aspect ratio.
*   **widthxheight#**: Resize then crop.
*   **widthxheight!**: Resize by exacty width and height.  Width and height emphatically given, original aspect ratio will be ignored.
*   **widthxheight**: Auto determine both width and height when resizing.  This will resize as close as possible to the given dimensions while still preserving the original aspect ratio.
 

## Examples

Create an attachment named 'picture', with both thumbnail (100x100) and large (300x300) styles, using custom url and default_url configurations.

```php
public function __construct($attributes = array(), $exists = false){
    parent::__construct($attributes, $exists);

    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100',
            'thumbnail' => '300x300'
        ],
        'url' => '/system/:attachment/:id_partition/:style/:filename',
        'default_url' => '/:attachment/:style/missing.jpg'
    ]);
}
```

Create an attachment named 'picture', with both thumbnail (100x100) and large (300x300) styles, using custom url and default_url configurations, with the keep_old_files flag set to true (so that older file uploads aren't deleted from the file system) and image cropping turned on.

```php
public function __construct($attributes = array(), $exists = false){
    parent::__construct($attributes, $exists);

    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100#',
            'thumbnail' => '300x300#'
        ],
        'url' => '/system/:attachment/:id_partition/:style/:filename',
        'default_url' => '/:attachment/:style/missing.jpg',
        'keep_old_files' => true
    ]);
}
```

Stapler makes it easy to manage multiple file uploads as well.  Here's an example of how this might work:

In models/user.php:

```php
// A user has many photos.
public function photos(){
    return $this->has_many('Photo');
}
```

In models/photo.php:
```php
public function __construct($attributes = array(), $exists = false){
        parent::__construct($attributes, $exists);

        $this->hasAttachedFile('photo', [
            'styles' => [
                'thumbnail' => '100x100#'
            ]
        ]);
    }

// A photo belongs to a user.
public function user(){
    return $this->belongsTo('User');
}
```

In the user create view:

```php
<?= Form::open_for_files('/users', 'POST') ?>
    <?= Form::file('photos[]') ?>
    <?= Form::file('photos[]') ?>
    <?= Form::file('photos[]') ?>
<?= Form::close() ?>
```

In controllers/UsersController.php
```php
public function store()
{
    $user = new User;

    // Attach each photo to the user and save it.
    foreach($files as $file){
        $photo = new Photo();
        $photo->photo = $file;
        $user->photos()->insert($photo);
    }
}
```

Displaying uploaded files is also easy.  When working with a model instance, each attachment can be accessed as a property on the model.  An attachment object provides methods for seamlessly accessing the properties, paths, and urls of the underlying uploaded file object.  As an example, for an attachment named 'photo', the path(), url(), createdAt(), contentType(), size(), and originalFilename() methods would be available on the model to which the file was attached.  Assuming an attachment named photo that's attached to a User model, consider the following:

Display a resized thumbnail style image belonging to a user record:
```php
<img src="<?= $user->photo->url('thumbnail') ?>" >
```

Display the original image style (unmodified image):
```php
<img src="<?= $user->photo->url('original') ?>" >
```

This also displays the unmodified original image (unless the :default_url interpolation has been set to a different style):
```php
<img src="<?= $user->photo->url() ?>" >
```

We can also retrieve the file path, size, original filename, etc of an uploaded file.
This returns the physical file system path to the thumbnail style image:
```php
$user->photo->path('thumbnail');
```

This returns the original file size of the attachment's uploaded file:
```php
$user->photo->size();
```

This returns the original filename of the attachment's uploaded file:
```php
$user->photo->originalFilename();
```
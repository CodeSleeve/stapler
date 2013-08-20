#Stapler

Be warned: this package is still very much in development.  I'm currently in the middle of refacting it to be more decoupled, solid, etc.  The features that are currently available should be equivalent to the L3 version of Stapler.  I'm also shopping around for an image processing package on Packagist (currently looking at both Intervention Image and Imagine).  In general I want to make Stapler as powerful and fully featured as its Paperclip counterpart.  That being said....

Stapler can be used to generate file upload attachments for use with the wonderfully fabulous Laravel PHP Framework (>= 4.0), authored by Taylor Otwell.
If you have used ruby on rails' paperclip plugin then you will be familiar with its syntax.  This bundle is inspired entirely from the work done
by the guys at thoughtbot for the Rails Paperclip bundle: https://github.com/thoughtbot/paperclip.  While not an exact duplicate, if you've used Paperclip before then you should be 
somewhat familiar with how this bundle works.

Stapler was created by Travis Bennett.

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quickstart)
- [Overview](#overview)
- [Interpolations](#interpolations)
- [Filesystem Storage](#filesystemstorage)
- [S3 Storage](#s3storage)
- [Image Processing](#imageprocessing)
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

## Overview

Stapler works by attaching file uploads to records stored within a database table (model).  A model can have multiple attachments defined (avatar, photo, some_random_attachment, etc) and in turn each attachment can have multiple sizes (styles) defined.  When an image or file is uploaded, Stapler will handle all the file processing (moving, resizing, etc) and provide an attachment object (as a model property) with methods for workingw ith the uploaded assets.  To accomplish this, four fields (named after the attachemnt) are created (via stapler:fasten) in the corresponding table for any model containing a file attachment (these should be included in the model's fillable array).  For example, an attachment named 'avatar' the following fields would be created:

*   avatar_file_name
*   avatar_file_size
*   avatar_content_type
*   avatar_updated_at

## Configuration

Configuration is available on both a per attachment basis or globally through the config settings.  Stapler is very flexible about how it processes configuration; global configuration options can be overriden on a per attachment basis so tha you can easily cascade settings you would like to have on all attachments while still having the freedom to customize an individual attachment's configuration.  To get started, the first thing you'll probably want to do is publish the default configuration options to your app/config directory. 

 ```php
  php artisan config:publish codesleeve/stapler
``` 
Having done this, you should now be able to configure Stapler however you see fit wihout fear of future updates overriding your configuration files.

## Interpolations

With Stapler, uploaded files are accessed by defining path, url, and default_url strings which point to you uploaded file assets.  This is done via string interpolations.  Currently, the following interpolations are available for use:

*   **:attachment** - The name of the file attachment as declared in the hasAttachedFile function, e.g 'avatar'.
*   **:class**  - The classname of the model contaning the file attachment, e.g User.  Stapler can handle namespacing of classes.
*   **:extension** - The file extension type of the uploaded file, e.g '.jpg'
*   **:filename** - The name of the uploaded file, e.g 'some_file.jpg'
*   **:id** - The id of the corresponding database record for the uploaded file.
*   **:id_partition** - The partitioned id of the corresponding database record for the uploaded file, e.g an id = 1 is interpolated as 000/000/001.  This is the default and recommended setting for Stapler.  Partioned id's help overcome the 32k subfolder problem that occurs in nix-based systems using the EXT3 file system.
*   **:hash** - An sha256 hash of the corresponding database record id.
*   **:laravel_root** - The path to the root of the laravel project.
*   **:style** - The resizing style of the file (images only), e.g 'thumbnail' or 'orginal'.
*   **:url** - The url string pointing to your uploaded file.  This interpolation is actually an interpolation itself.  It can be composed of any of the above interpolations (except itself).  

## Filesystem Storage

Filesystem (local disk) is the default storage option for stapler.  When using it, the following configuration settings are available:

*   **path**: Similar to the url, the path option is the location where your files will be stored at on disk.  It should be noted that the path option should not conflict with the url option.  Stapler provides sensible defaults that take care of this for you.
*   **url**: The url (relative to your project document root) where files will be stored.  It is composed of 'interpolations' that will be replaced their corresponding values during runtime.  It's unique in that it functions as both a configuration option and an interpolation.
*   **default_url**: The default file returned when no file upload is present for a record.
*   **default_style**: The default style returned from the Stapler file location helper methods.  An unaltered version of uploaded file
    is always stored within the 'original' style, however the default_style can be set to point to any of the defined syles within the styles array.
*   **styles**: An array of image sizes defined for the file attachment.  Stapler will attempt to use to format the file upload
    into the defined style.
*   **override_file_permissions**: Override the default file permissions used by stapler when creating a new file in the file system.  Leaving this value as null will result in stapler chmod'ing files to 0666.  Set it to a specific octal value and stapler will chmod accordingly.  Set it to false to prevent chmod from occuring (useful for non unix-based environments).
*   **keep_old_files**: Set this to true in order to prevent older file uploads from being deleted from the file system.

Default values:
*   **path**: ':laravel_root/public:url',
*   **url**: '/system/:class/:attachment/:id_partition/:style/:filename',
*   **default_url**: '/:attachment/:style/missing.png',
*   **default_style**: 'original',
*   **styles**: [],
*   **override_file_permissions**: null,
*   **keep_old_files**: false
    
## S3 Storage

As your web application grows, you may find yourself in need of more robust file storage than what's provided by the local filesystem (e.g you're using multiple server instances and need a shared location for storing/accessing uploaded file assets).  Stapler provides a simple mechanism for easily storing and retreiving file objects with Amazon Simple Storage Service (Amazon S3).  In fact, aside from a few extra configuration settings, there's really no difference between s3 storage and filesystem storage when interacting with your attachments.  To get started with s3 storage you'll first need to change the storage setting in config/stapler.php from 'filesystem' to 's3' (keep in mind, this can be done per attachment if you want to use s3 for a specific attachment only).  After that's done, crack open config/s3.php for a list of s3 storage settings:

*   **path**: This is the key under the bucket in which the file will be stored. The URL will be constructed from the bucket and the path. This is what you will want to interpolate. Keys should be unique, like filenames, and despite the fact that S3 (strictly speaking) does not support directories, you can still use a / to separate parts of your file name.
*   **default_url**: The default file returned when no file upload is present for a record.  As with filesystem storage, this should be an image on your local filesystem.
*   **default_style**: The default style returned from the Stapler file location helper methods.  An unaltered version of uploaded file
    is always stored within the 'original' style, however the default_style can be set to point to any of the defined syles within the styles array.
*   **styles**: An array of image sizes defined for the file attachment.  Stapler will attempt to use to format the file upload
    into the defined style.
*   **key**: This is an alphanumeric text string that uniquely identifies the user who owns the account. No two accounts can have the same AWS Access Key.
*   **secret**: This key plays the role of a  password . It's called secret because it is assumed to be known by the owner only.  A Password with Access Key forms a secure information set that confirms the user's identity. You are advised to keep your Secret Key in a safe place.
*   **bucket**: The bucket where you wish to store your objects.  Every object in Amazon S3 is stored in a bucket.  If the specified bucket doesn't exist Stapler will attempt to create it.  The bucket name will not be interpolated. You can define the bucket as a closure if you want to determine it's name at runtime. Stapler will call that closure with attachment as the only argument.
*   **ACL**: This is a string/array that should be one of the canned access policies that S3 provides (private, public-read, public-read-write, authenticated-read, bucket-owner-read, bucket-owner-full-control). The default for Stapler is public-read.  An associative array (style => permission) may be passed to specify permissions on a per style basis.
*   **scheme**: The protocol for the URLs generated to your S3 assets. Can be either 'http' or 'https'.  Defaults to 'http' when your ACL is 'public-read' (the default) and 'https' when your ACL is anything else.
*   **region**: The region name of your bucket (e.g. 'us-east-1', 'us-west-1', 'us-west-2', 'eu-west-1').  Determines the base url where your objects are stored at (e.g a region of us-west-2 has a base url of s3-us-west-2.amazonaws.com).
*   **keep_old_files**: Set this to true in order to prevent older file uploads from being deleted from the bucket.

Default values:
*   **path**: ':attachment/:id/:style/:filename',
*   **default_url**: '/:attachment/:style/missing.png',
*   **default_style**: 'original',
*   **styles**: [],
*   **key**: ''
*   **secret**: ''
*   **bucket**: ''
*   **ACL**: 'public-read'
*   **scheme**: 'http'
*   **region**: 'us-west-2'
*   **keep_old_files**: false

## Image Processing

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

To store this on s3, you'll need to set a few s3 specific configuraiton options (the url interpolation will no longer be necessary when using s3 storage): 

```php
public function __construct($attributes = array(), $exists = false){
    parent::__construct($attributes, $exists);

    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100#',
            'thumbnail' => '300x300#'
        ],
        'default_url' => '/:attachment/:style/missing.jpg',
        'storage' => 's3',
        'key' => 'yourPublicKey',
        'secret' => 'yourSecreteKey',
        'bucket' => 'your.s3.bucket',
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
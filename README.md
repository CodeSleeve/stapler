#Stapler

[![Build Status](https://travis-ci.org/CodeSleeve/stapler.png?branch=master)](https://travis-ci.org/CodeSleeve/stapler)

## Changelog - v1.0.0-Beta4
- Added the ability to fetch and process remote files (requires the CURL extension).
- Added the ability to refresh/reprocess styles both programmatically and systematically (via artisan).
- Stapler core (attachment, interpolator, IOWrapper, Config, Validator, and Storage drivers) have now been completely decoupled from the Laravel framework.
- Created unit test suite.
- Various bug fixes.

## Roadmap - v1.0.0
- Add ability to assign attachment objects from model to model.
- Break stapler core into a separate, framework agnostic package that can be used to attach file uploads to various different ORM's.
- Create more complete unit test suite (more complete coverage, etc).
- Stapler 1.0 will support Laravel Framework 4.1.* (this will be the last release that maintains backwards compatibility with Laravel 4.0, due in part to changes in the version of Symfony HttpFoundation used by Laravel).

Stapler can be used to generate file upload attachments for use with the wonderfully fabulous Laravel PHP Framework (>= 4.0), authored by Taylor Otwell.  This package is inspired entirely from the work done by the guys at thoughtbot for the Rails Paperclip gem: https://github.com/thoughtbot/paperclip.  While not an exact duplicate, if you've used Paperclip before then you should feel quite comfortable using this package.

Stapler was created by Travis Bennett.

* [Requirements](#requirements)
* [Installation](#installation)
* [Quick Start](#quickstart)
* [Overview](#overview)
* [Configuration](#configuration)
  * [Stapler](#stapler-configuration)
  * [Filesystem](#filesystem-storage-configuration)
  * [S3](#s3-storage-configuration)
* [Interpolations](#interpolations)
* [Image Processing](#image-processing)
* [Examples](#examples)
* [Fetching Remote Images](#fetching-remote-images)
* [Advanced Usage](#advanced-usage)

## Requirements
Stapler currently requires php >= 5.4 (Stapler is implemented via the use of traits).

## Installation
Stapler is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/stapler`.

```js
  "require": {
    "laravel/framework": "4.*",
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
	use Codesleeve\Stapler\Stapler;

  public function __construct(array $attributes = array()) {
      $this->hasAttachedFile('avatar', [
          'styles' => [
            'medium' => '300x300',
            'thumb' => '100x100'
          ]
      ]);

      parent::__construct($attributes);
  }
}
```

> Make sure that the `hasAttachedFile()` method is called right before `parent::__construct()` of your model.

From the command line, use the migration generator:

```php
php artisan stapler:fasten users avatar
php artisan migrate
```

In your new view:

```php
<?= Form::open(['url' => action('UsersController@store'), 'method' => 'POST', 'files' => true]) ?>
	<?= Form::file('avatar') ?>
    <?= Form::submit('save') ?>   
<?= Form::close() ?>
```
In your controller:

```php
public function store()
{
	// Create a new user, assigning the uploaded file field ('named avatar in the form')
    // to the 'avatar' property of the user model.   
    $user = User::create(['avatar' => Input::file('avatar')]);	
}
```

In your show view:
```php
<img src="<?= $user->avatar->url() ?>" >
<img src="<?= $user->avatar->url('medium') ?>" >
<img src="<?= $user->avatar->url('thumb') ?>" >
```

To detach (reset) a file, simply call the clear() method of the attachment attribute before saving (you may also assign the constant STAPLER_NULL):

```php
$user->avatar->clear();
$user->save();
```
or

```php
$user->avatar = STAPLER_NULL;
$user->save();
```
This will ensure the the corresponding attachment fields in the database table record are cleared and the current file is removed from storage.  The database table record itself will not be destroyed and can be used normally (or even assigned a new file upload) as needed.

## Overview
Stapler works by attaching file uploads to database table records.  This is done by defining attachments inside the table's corresponding model and then assigning uploaded files (from your forms) as properties (named after the attachments) on the model before saving it.  In essence, this allows uloaded files to be treated just like any other property on the model; stapler will abstract away all of the file processing, storage, etc so you can focus on the rest of your project without having to worry about where your files are at or how to retrieve them.  

A model can have multiple attachments defined (avatar, photo, foo, etc) and in turn each attachment can have multiple sizes (styles) defined.  When an image or file is uploaded, Stapler will handle all the file processing (moving, resizing, etc) and provide an attachment object (as a model property) with methods for working with the uploaded file.  To accomplish this, four fields (named after the attachemnt) will need to be created (via stapler:fasten or manually) in the corresponding table for any model containing a file attachment.  For example, for an attachment named 'avatar' defined inside a model named 'User', the following fields would need to be added to the 'users' table:

*   (string) avatar_file_name
*   (integer) avatar_file_size
*   (string) avatar_content_type
*   (timestamp) avatar_updated_at

Inside your table migration file, something like this should suffice:

```php
$table->string("avatar_file_name")->nullable();
$table->integer("avatar_file_size")->nullable();
$table->string("avatar_content_type")->nullable();
$table->timestamp("avatar_updated_at")->nullable();
```

## Configuration
Configuration is available on both a per attachment basis or globally through the configuration file settings.  Stapler is very flexible about how it processes configuration; global configuration options can be overriden on a per attachment basis so tha you can easily cascade settings you would like to have on all attachments while still having the freedom to customize an individual attachment's configuration.  To get started, the first thing you'll probably want to do is publish the default configuration options to your app/config directory. 

 ```php
  php artisan config:publish codesleeve/stapler
``` 
Having done this, you should now be able to configure Stapler however you see fit wihout fear of future updates overriding your configuration files. 

### Stapler-Configuration
The following configuration settings apply to stapler in general.

*   **storage**: The underlying storage driver to uploaded files.  Defaults to filesystem (local storage) but can also be set to 's3' for use with AWS S3.
*   **image_processing_libarary**: The underlying image processing library being used.  Defaults to GD but can also be set to Imagick or Gmagick.
*   **default_url**: The default file returned when no file upload is present for a record.
*   **default_style**: The default style returned from the Stapler file location helper methods.  An unaltered version of uploaded file
    is always stored within the 'original' style, however the default_style can be set to point to any of the defined syles within the styles array.
*   **styles**: An array of image sizes defined for the file attachment.  Stapler will attempt to use to format the file upload
    into the defined style.
*   **keep_old_files**: Set this to true in order to prevent older file uploads from being deleted from the file system when a record is updated.
*   **preserve_old_files**: Set this to true in order to prevent an attachment's file uploads from being deleted from the file system when an the attachment object is destroyed (attachment's are destroyed when their corresponding mondels are deleted/destroyed from the database).

Default values:
*   **storage**: 'filesystem'
*   **image_processing_library**: 'GD'
*   **default_url**: '/:attachment/:style/missing.png'
*   **default_style**: 'original'
*   **styles**: []
*   **keep_old_files**: false
*   **preserve_old_files**: false

### Filesystem-Storage-Configuration
Filesystem (local disk) is the default storage option for stapler.  When using it, the following configuration settings are available:

*   **url**: The url (relative to your project document root) where files will be stored.  It is composed of 'interpolations' that will be replaced their corresponding values during runtime.  It's unique in that it functions as both a configuration option and an interpolation.
*   **path**: Similar to the url, the path option is the location where your files will be stored at on disk.  It should be noted that the path option should not conflict with the url option.  Stapler provides sensible defaults that take care of this for you.
*   **override_file_permissions**: Override the default file permissions used by stapler when creating a new file in the file system.  Leaving this value as null will result in stapler chmod'ing files to 0666.  Set it to a specific octal value and stapler will chmod accordingly.  Set it to false to prevent chmod from occuring (useful for non unix-based environments).

Default values:
*   **url**: '/system/:class/:attachment/:id_partition/:style/:filename'
*   **path**: ':laravel_root/public:url'
*   **override_file_permissions**: null
    
### S3-Storage-Configuration
As your web application grows, you may find yourself in need of more robust file storage than what's provided by the local filesystem (e.g you're using multiple server instances and need a shared location for storing/accessing uploaded file assets).  Stapler provides a simple mechanism for easily storing and retreiving file objects with Amazon Simple Storage Service (Amazon S3).  In fact, aside from a few extra configuration settings, there's really no difference between s3 storage and filesystem storage when interacting with your attachments.  To get started with s3 storage you'll first need to add the AWS SDK to your composer.json file:

```js
  "require": {
    "laravel/framework": "4.0.*",
    "codesleeve/stapler": "dev-master",
    "aws/aws-sdk-php": "2.4.*@dev"
  }
```

Next, change the storage setting in config/stapler.php from 'filesystem' to 's3' (keep in mind, this can be done per attachment if you want to use s3 for a specific attachment only).  After that's done, crack open config/s3.php for a list of s3 storage settings:

*   **path**: This is the key under the bucket in which the file will be stored. The URL will be constructed from the bucket and the path. This is what you will want to interpolate. Keys should be unique, like filenames, and despite the fact that S3 (strictly speaking) does not support directories, you can still use a / to separate parts of your file name.
*   **key**: This is an alphanumeric text string that uniquely identifies the user who owns the account. No two accounts can have the same AWS Access Key.
*   **secret**: This key plays the role of a  password . It's called secret because it is assumed to be known by the owner only.  A Password with Access Key forms a secure information set that confirms the user's identity. You are advised to keep your Secret Key in a safe place.
*   **bucket**: The bucket where you wish to store your objects.  Every object in Amazon S3 is stored in a bucket.  If the specified bucket doesn't exist Stapler will attempt to create it.  The bucket name will not be interpolated.
*   **ACL**: This is a string/array that should be one of the canned access policies that S3 provides (private, public-read, public-read-write, authenticated-read, bucket-owner-read, bucket-owner-full-control). The default for Stapler is public-read.  An associative array (style => permission) may be passed to specify permissions on a per style basis.
*   **scheme**: The protocol for the URLs generated to your S3 assets. Can be either 'http' or 'https'.  Defaults to 'http' when your ACL is 'public-read' (the default) and 'https' when your ACL is anything else.
*   **region**: The region name of your bucket (e.g. 'us-east-1', 'us-west-1', 'us-west-2', 'eu-west-1').  Determines the base url where your objects are stored at (e.g a region of us-west-2 has a base url of s3-us-west-2.amazonaws.com).  The default value for this field is an empty (US Standard *).  You can go [here](http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region) for a more complete list/explanation of regions.

Default values:
*   **path**: ':attachment/:id/:style/:filename'
*   **key**: ''
*   **secret**: ''
*   **bucket**: ''
*   **ACL**: 'public-read'
*   **scheme**: 'http'
*   **region**: ''

## Interpolations
With Stapler, uploaded files are accessed by configuring/defining path, url, and default_url strings which point to you uploaded file assets.  This is done via string interpolations.  Currently, the following interpolations are available for use:

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

## Image-Processing
Stapler makes use of the [imagine image](https://packagist.org/packages/imagine/imagine) library for all image processing.  Out of the box, the following image processing patterns/directives will be recognized when defining Stapler styles:

*   **width**: A style that defines a width only (landscape).  Height will be automagically selected to preserve aspect ratio.  This works well for resizing
    images for display on mobile devices, etc.
*   **xheight**: A style that defines a heigh only (portrait).  Width automagically selected to preserve aspect ratio.
*   **widthxheight#**: Resize then crop.
*   **widthxheight!**: Resize by exacty width and height.  Width and height emphatically given, original aspect ratio will be ignored.
*   **widthxheight**: Auto determine both width and height when resizing.  This will resize as close as possible to the given dimensions while still preserving the original aspect ratio.

To create styles for an attachment, simply define them (you may use any style name you like: foo, bar, baz, etc) inside the attachment's styles array using a combination of the directives defined above:

````php
'styles' => [
    'thumbnail' => '50x50',
    'large' => '150x150',
    'landscape' => '150',
    'portrait' => 'portrait' => 'x150',
    'foo' => '75x75',
    'fooCropped' => '75x75#'
]
````

For more customized image processing you may also pass a [callable](http://php.net/manual/en/language.types.callable.php) type as the value for a given style definition.  Stapler will automatically inject in the uploaded file object instance as well as the Imagine\Image\ImagineInterface object instance for you to work with.  When you're done with your processing, simply return an instance of Imagine\Image\ImageInterface from the callable.  Using a callable for a style definition provides an incredibly amount of flexibilty when it comes to image processing. As an example of this, let's create a watermarked image using a closure (we'll do a smidge of image processing with Imagine):

 ````php
 'styles' => [
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
]
```` 

## Examples
Create an attachment named 'picture', with both thumbnail (100x100) and large (300x300) styles, using custom url and default_url configurations.

```php
public function __construct(array $attributes = array()) {
    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100',
            'large' => '300x300'
        ],
        'url' => '/system/:attachment/:id_partition/:style/:filename',
        'default_url' => '/:attachment/:style/missing.jpg'
    ]);

    parent::__construct($attributes);
}
```

Create an attachment named 'picture', with both thumbnail (100x100) and large (300x300) styles, using custom url and default_url configurations, with the keep_old_files flag set to true (so that older file uploads aren't deleted from the file system) and image cropping turned on.

```php
public function __construct(array $attributes = array()) {
    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100#',
            'large' => '300x300#'
        ],
        'url' => '/system/:attachment/:id_partition/:style/:filename',
        'default_url' => '/:attachment/:style/missing.jpg',
        'keep_old_files' => true
    ]);

    parent::__construct($attributes);
}
```

To store this on s3, you'll need to set a few s3 specific configuraiton options (the url interpolation will no longer be necessary when using s3 storage): 

```php
public function __construct(array $attributes = array()) {
    $this->hasAttachedFile('picture', [
        'styles' => [
            'thumbnail' => '100x100#',
            'large' => '300x300#'
        ],
        'default_url' => '/:attachment/:style/missing.jpg',
        'storage' => 's3',
        'key' => 'yourPublicKey',
        'secret' => 'yourSecreteKey',
        'bucket' => 'your.s3.bucket',
        'keep_old_files' => true
    ]);

    parent::__construct($attributes);
}
```

Stapler makes it easy to manage multiple file uploads as well.  In stapler, attachments (and the uploaded file objects they represent) are tied directly to database records.  Because of this, processing multiple file uploades is simply a matter of defining the correct Eloquent relationships between models.  

As an example of how this works, let's assume that we have a system where users need to have multiple profile pictures (let's say 3).  Also, let's assume that users need to have the ability to upload all three of their photos from the user creation form. To do this, we'll need two tables (users and profile_pictures) and we'll need to set their relationships such that profile pictures belong to a user and a user has many profile pictures.  By doing this, uploaded images can be attached to the ProfilePicture model and instances of the User model can in turn access the uploaded files via their hasMany relationship to the ProfilePicture model.  Here's what this looks like:

In models/user.php:

```php
// A user has many profile pictures.
public function profilePictures(){
    return $this->hasMany('ProfilePicture');
}
```

In models/ProfilePicture.php:
```php
public function __construct(array $attributes = array()) {
    // Profile pictures have an attached file (we'll call it photo).
    $this->hasAttachedFile('photo', [
        'styles' => [
            'thumbnail' => '100x100#'
        ]
    ]);

    parent::__construct($attributes);
}

// A profile picture belongs to a user.
public function user(){
    return $this->belongsTo('User');
}
```

In the user create view:

```php
<?= Form::open(['url' => '/users', 'method' => 'post', 'files' => true]) ?>
    <?= Form::text('first_name') ?>
    <?= Form::text('last_name') ?>
    <?= Form::file('photos[]') ?>
    <?= Form::file('photos[]') ?>
    <?= Form::file('photos[]') ?>
<?= Form::close() ?>
```

In controllers/UsersController.php
```php
public function store()
{
    // Create the new user
    $user = new User(Input::get());
    $user->save();

    // Loop through each of the uploaded files:
    // 1. Create a new ProfilePicture instance. 
    // 2. Attach the file to the new instance (stapler will process it once it's saved).
    // 3. Attach the ProfilePicture instance to the user and save it.
    foreach(Input::file('photos') as $photo)
    {
        $profilePicture = new ProfilePicture();             // (1)
        $profilePicture->photo = $photo;                    // (2)
        $user->profilePictures()->save($profilePicture);    // (3)
    }
}
```

Displaying uploaded files is also easy.  When working with a model instance, each attachment can be accessed as a property on the model.  An attachment object provides methods for seamlessly accessing the properties, paths, and urls of the underlying uploaded file object.  As an example, for an attachment named 'photo', the path(), url(), createdAt(), contentType(), size(), and originalFilename() methods would be available on the model to which the file was attached.  Continuing our example from above, we can loop through a user's profile pictures display each of the uploaded files like this:

```html
// Display a resized thumbnail style image belonging to a user record:
<img src="<?= asset($profilePicture->photo->url('thumbnail')) ?>">

// Display the original image style (unmodified image):
<img src="<?=  asset($profilePicture->photo->url('original')) ?>">

// This also displays the unmodified original image (unless the :default_style interpolation has been set to a different style):
<img src="<?=  asset($profilePicture->photo->url()) ?>">
```

We can also retrieve the file path, size, original filename, etc of an uploaded file:
```php
$profilePicture->photo->path('thumbnail');
$profilePicture->photo->size();
$profilePicture->photo->originalFilename();
```

## Fetching-Remote-Images
As of Stapler v1.0.0-Beta4, remote images can now be fetched by assigning an absolute URL to an attachment property that's defined on a model: 

```php 
$profilePicture->photo = "http://foo.com/bar.jpg"; 
```

This is very useful when working with third party API's such as facebook, twitter, etc.  Note that this feature requires that the CURL extension is included as part of your PHP installation.

## Advanced-Usage
When working with attachments, there may come a point where you wish to do things outside of the normal workflow.  For example, suppose you wish to clear out an attachment (empty the attachment fields in the underlying table record and remove the uploaded file from storage) without having to destroy the record itself.  As mentioned above, you can always set the attachment attribute to STAPLER_NULL on the record before saving, however this only works if you save the record itself afterwards.  In situations where you wish to clear the uploaded file from storage without saving the record, you can use the attachment's destroy method:

```php
// Remove all of the attachment's uploaded files and empty the attacment attributes on the model:
$profilePicture->photo->destroy();

// For finer grained control, you can remove thumbnail files only (attachment attributes in the model will not be emptied).
$profilePicture->photo->destroy(['thumbnail']);
```

You may also reprocess uploaded images on an attachment by calling the reprocess() command (this is very useful for adding new styles to an existing attachment type where records have already been uploaded).

```php
// Programmatically reprocess an attachment's uploaded images:
$profilePicture->photo->reprocess();
```

This may also be achieved via a call to the stapler:refresh command.

Reprocess all attachments for the ProfilePicture model:
php artisan stapler:refresh ProfilePicture

Reprocess only the photo attachment on the ProfilePicture model:
php artisan stapler:refresh TestPhoto --attachments="photo"

Reprocess a list of attachments on the ProfilePicture model:
php artisan stapler:refresh TestPhoto --attachments="foo, bar, baz, etc"
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
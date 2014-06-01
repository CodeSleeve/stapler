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
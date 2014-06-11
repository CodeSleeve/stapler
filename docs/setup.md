## Setup
### Bootstrapping
Before you can begin using Stapler, there's a few things you're going to have to do.  In your application's bootstrap process (wherever that may be), you're going to need to run the following code snippet:

```php
// Boot stapler:
Stapler::boot();

// Set the configuration driver (we're using the default config driver here; if you choose to implement your own you'll need to implement Codesleeve\Stapler\Config\ConfigurableInterface):
$config = new Codesleeve\Stapler\Config\NativeConfig;
Stapler::setConfigInstance($config);

// Set the location to your application's document root:
$config->set('stapler.public_path', 'path/to/your/document/root');

// Set the location to your application's base folder.
$config->set('stapler.base_path', 'path/to/your/base/folder');
```

### Traits/Drivers
Stapler works via the use of traits.  In order to add file uploading capabilities to your models/entities, you'll have to first use the corresponding trait for your ORM and ensure that your entities implement `Codesleeve\Stapler\ORM\StaplerableInterface`.  Stapler currently supports the following ORMS (more coming soon):
* Eloquent: A trait for use within Laravel's Eloquent ORM.  Use this trait inside your Eloquent models in order to add file attachment abilities to them:
```php
	use Codesleeve\Stapler\ORM\StaplerableInterface;
	use Codesleeve\Stapler\ORM\EloquentTrait;
	
	class FooModel extends Eloquent implements StaplerableInterface{
		use EloquentTrait;
	}
```

### Database Tables
A model can have multiple attachments defined (avatar, photo, foo, etc) and in turn each attachment can have multiple sizes (styles) defined.  When an image or file is uploaded, Stapler will handle all the file processing (moving, resizing, etc) and provide an attachment object (as a model property) with methods for working with the uploaded file.  To accomplish this, four fields (named after the attachemnt) will need to be created in the corresponding table for any model/entity containing a file attachment.  For example, for an attachment named 'avatar' defined inside a model named 'User', the following fields would need to be added to the 'users' table:

*   (string) avatar_file_name
*   (integer) avatar_file_size
*   (string) avatar_content_type
*   (timestamp) avatar_updated_at
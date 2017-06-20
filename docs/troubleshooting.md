## Troubleshooting
> I get a Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException when attempting ot upload a file with stapler.

Check your form to ensure that the **enctype** attribute is set to 'multipart/form-data'.  If you're using Laravel's form helper to create your form, this can be done by adding 'files' => true to the form helper's open() method:

```php
<?= Form::open(['files' => true]) ?>
```

> I'm using Stapler with Eloquent to upload my files.  When I hit submit, the record gets saved, the attachment columns get set in the database, but no files are being uploaded.

This is most likely happening because you've created a static 'boot' method inside your model and it's overriding the boot method used by Stapler's Eloquent Trait.  In order to fix this, simply call the 'bootStapler' method from inside the boot method you defined:
```php
/**
 * The "booting" method of the model.
 */
public static function boot()
{
    parent::boot();

    static::bootStapler();
}
```

## mkdir(): Permission denied issue

In case when files can be uploaded either from the web (with `www-data` user for example) and through the CLI (with `dev` user for example), you can face the issue, when `dev` user cannot write to the directory created with `www-data` user.

Even though stapler is trying to create directory with `0777` permissions
```
    /**
     * Determine if a style directory needs to be built and if so create it.
     *
     * @param string $filePath
     */
    protected function buildDirectory($filePath)
    {
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
    ```

php's umask can override that anyways.

For this case you can override stapler's `Attachment` to disable `umask` temporary:

```
<?php

namespace App\Vendor\Codesleeve\Stapler;

use Codesleeve\Stapler\Attachment as BaseAttachment;

class Attachment extends BaseAttachment
{
    /**
     * Handle dynamic method calls on the attachment.
     * This allows us to call methods on the underlying
     * storage driver directly via the attachment.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = ['remove', 'move'];

        if (in_array($method, $callable)) {

            // we need to override umask to make 0777 permissions work
            // this will solve the issue with different permissions for web user and cli user
            if ($method == 'move') {
                $oldUmask = umask(0);
            }

            $result = call_user_func_array([$this->storageDriver, $method], $parameters);

            if ($method == 'move') {
                umask($oldUmask);
            }

            return $result;
        }
    }
}
```

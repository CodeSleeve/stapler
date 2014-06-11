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
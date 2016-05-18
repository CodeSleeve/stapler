## Interpolations
With Stapler, uploaded files are accessed by configuring/defining path, url, and default_url strings which point to your uploaded file assets.  This is done via string interpolations.  Currently, the following interpolations are available for use:

*   **:attachment** - The name of the file attachment as declared in the hasAttachedFile function, e.g 'avatar'.
*   **:class**  - The class name of the model containing the file attachment, e.g User.  This will include the class namespace.
*   **:class_name** - The class name of the model, without its namespace.
*   **:extension** - The file extension type of the uploaded file, e.g 'jpg'.
*   **:filename** - The name of the uploaded file, e.g 'some_file.jpg'.
*   **:id** - The id of the corresponding database record for the uploaded file.
*   **:id_partition** - The partitioned id of the corresponding database record for the uploaded file, e.g an id = 1 is interpolated as 000/000/001.  This is the default and recommended setting for Stapler.  Partioned id's help overcome the 32k subfolder problem that occurs in nix-based systems using the EXT3 file system.
*   **:secure_hash** - An sha256 hash of the corresponding database record id, the filesize, and the original file name.
*   **:hash** - An sha256 hash of the corresponding database record id.
*   **:app_root** - The path to the root of the project.
*   **:style** - The resizing style of the file (images only), e.g 'thumbnail' or 'original'.
*   **:url** - The url string pointing to your uploaded file.  This interpolation is actually an interpolation itself.  It can be composed of any of the above interpolations (except itself).

As of stapler 1.1.0, the interpolator class is now bound to an interface/contract that's configurable via the 'bindings' array of the config file.
If you need your own custom interpolations, you can easily swap out the default concrete implementation with your own.

```php

use Codesleeve\Stapler\Interpolator as BaseInterpolator;

class CustomerInterpolator extends BaseInterpolator
{
    /**
     * Returns a sorted list of all interpolations.
     * We can easily add to the list of interpolations provided by
     * the base interpolator class. Let's register the ':foo' interpolation:
     *
     * @return array
     */
    protected function interpolations()
    {
        $parentInterpolations = parent::interpolations();

        return array_merge($parentInterpolations, [':foo' => 'foo']);
    }

    /**
     * Now that we've registered the 'foo' interpolation, we need
     * to implement the 'foo' function that generates the interpolated value
     * for us. Let's just replace 'foo' with the string 'bar':
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function foo(AttachmentInterface $attachment, $styleName = '')
    {
        return 'bar';
    }
}
```
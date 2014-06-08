## Image-Processing
Stapler makes use of the [imagine image](https://packagist.org/packages/imagine/imagine) library for all image processing.  Out of the box, the following image processing patterns/directives will be recognized when defining Stapler styles:

*   **width**: A style that defines a width only (landscape).  Height will be automagically selected to preserve aspect ratio.  This works well for resizing images for display on mobile devices, etc.
*   **xheight**: A style that defines a heigh only (portrait).  Width automagically selected to preserve aspect ratio.
*   **widthxheight#**: Resize then crop.
*   **widthxheight!**: Resize by exacty width and height.  Width and height emphatically given, original aspect ratio will be ignored.
*   **widthxheight**: Auto determine both width and height when resizing.  This will resize as close as possible to the given dimensions while still preserving the original aspect ratio.

To create styles for an attachment, simply define them (you may use any style name you like: foo, bar, baz, etc) inside the attachment's styles array using a combination of the directives defined above:

```php
'styles' => [
    'thumbnail' => '50x50',
    'large' => '150x150',
    'landscape' => '150',
    'portrait' => 'portrait' => 'x150',
    'foo' => '75x75',
    'fooCropped' => '75x75#'
]
```

To control the quality of resized images, define your style as an array containing 'dimensions' and 'convert_options' keys (**NOTE**: *when defining a style as an array, you must include a dimensions key.  Stapler will throw an InvalidStyleConfigurationException otherwise*).  The values for the convert_options array can be any of the quality settings described [here](https://imagine.readthedocs.org/en/latest/usage/introduction.html) for the Imagine Image package.

```php
// Create a high quality jpeg thumbnail image.
'styles' => [
    'thumbnail' => [
        'dimensions' => '50x50',
        'convert_options' => ['jpeg_quality' => 100]
    ]
]
```

Using this syntax, we can also auto-orient images (**NOTE**: *this requires the exif extension as part or your php installation*).  This is very useful for handling mobile uploads, etc.
```php
// Create an auto-oriented jpeg thubmnail image.
'styles' => [
    'thumbnail' => [
        'dimensions' => '50x50',
        'auto_orient' => true
    ]
]
```

Of course we can combine these options:
```php
// Create an auto-oriented high quality jpeg thumbnail image.
'styles' => [
    'thumbnail' => [
        'dimensions' => '50x50',
        'convert_options' => ['jpeg_quality' => 100],
        'auto_orient' => true
    ]
]
```

For even more customized image processing you may also pass a [callable](http://php.net/manual/en/language.types.callable.php) type as the value for a given style definition.  Stapler will automatically inject in the uploaded file object instance as well as the Imagine\Image\ImagineInterface object instance for you to work with.  When you're done with your processing, simply return an instance of Imagine\Image\ImageInterface from the callable.  Using a callable for a style definition provides an incredible amount of flexibilty when it comes to image processing. As an example of this, let's create a watermarked image using a closure (we'll do a smidge of image processing with Imagine):

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
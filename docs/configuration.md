## Configuration
Configuration is available on both a per attachment basis or globally through the configuration file settings.  Stapler is very flexible about how it processes configuration; global configuration options can be overriden on a per attachment basis so that you can easily cascade settings you would like to have on all attachments while still having the freedom to customize an individual attachment's configuration.

* [Stapler](#stapler-configuration)
* [Filesystem](#filesystem-storage-configuration)
* [S3](#s3-storage-configuration)

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
*   **convert_options**:  An array of options for setting the quality and DPI of resized images.  Default values are 75 for Jpeg quality and 72 dpi for x/y-resolution.  Please see the Imagine\Image documentation for more details.

Default values:
*   **storage**: 'filesystem'
*   **image_processing_library**: 'GD'
*   **default_url**: '/:attachment/:style/missing.png'
*   **default_style**: 'original'
*   **styles**: []
*   **keep_old_files**: false
*   **preserve_old_files**: false
*   **convert_options**: []

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
    "codesleeve/stapler": "dev-master",
    "aws/aws-sdk-php": "2.4.*@dev"
  }
```

Next, change the storage setting in config/stapler.php from 'filesystem' to 's3' (keep in mind, this can be done per attachment if you want to use s3 for a specific attachment only).  As of Stapler 1.0.0, S3 storage configuration for the S3Client is broken down into two arrays:

* **s3_client_config**: An array of key/value pairs that will be passed directly into the S3Client::factory() method.  You can go [here](http://docs.aws.amazon.com/aws-sdk-php/guide/latest/configuration.html#client-configuration-options) for a complete list/explanation of these options.
* **s3_object_config**: An array of key/value pairs that will be passed directly to the S3Client::putObject() method.  You can go [here](http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_putObject) for a complete list/explanation of these options.

Description:
* **s3_client_config**
  * **key**: This is an alphanumeric text string that uniquely identifies the user who owns the account. No two accounts can have the same AWS Access Key.
  * **secret**: This key plays the role of a  password . It's called secret because it is assumed to be known by the owner only.  A Password with Access Key forms a secure information set that confirms the user's identity. You are advised to keep your Secret Key in a safe place.
  * **region**: The region name of your bucket (e.g. 'us-east-1', 'us-west-1', 'us-west-2', 'eu-west-1').  Determines the base url where your objects are stored at (e.g a region of us-west-2 has a base url of s3-us-west-2.amazonaws.com).  The default value for this field is an empty (US Standard *).  You can go [here](http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region) for a more complete list/explanation of regions.
  * **scheme**: The protocol for the URLs generated to your S3 assets. Can be either 'http' or 'https'.  Defaults to 'http' when your ACL is 'public-read' (the default) and 'https' when your ACL is anything else.
* **s3_object_config**
  * **bucket**: The bucket where you wish to store your objects.  Every object in Amazon S3 is stored in a bucket.  If the specified bucket doesn't exist Stapler will attempt to create it.  The bucket name will not be interpolated.
  * **ACL**: This is a string/array that should be one of the canned access policies that S3 provides (private, public-read, public-read-write, authenticated-read, bucket-owner-read, bucket-owner-full-control). The default for Stapler is public-read.  An associative array (style => permission) may be passed to specify permissions on a per style basis.
* **path**: This is the key under the bucket in which the file will be stored. The URL will be constructed from the bucket and the path. This is what you will want to interpolate. Keys should be unique, like filenames, and despite the fact that S3 (strictly speaking) does not support directories, you can still use a / to separate parts of your file name.

Default values:
* **s3_client_config**
  * **key**: ''
  * **secret**: ''
  * **region**: ''
  * **scheme**: 'http'
* **s3_object_config**
  *  **Bucket**: ''
  *  **ACL**: 'public-read'
* **path**: ':attachment/:id/:style/:filename'
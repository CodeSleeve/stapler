#Stapler

[![Build Status](https://travis-ci.org/CodeSleeve/stapler.png?branch=development)](https://travis-ci.org/CodeSleeve/stapler)
[![Latest Stable Version](https://poser.pugx.org/codesleeve/stapler/v/stable.svg)](https://packagist.org/packages/codesleeve/stapler) 
[![Total Downloads](https://poser.pugx.org/codesleeve/stapler/downloads.svg)](https://packagist.org/packages/codesleeve/stapler) 
[![Latest Unstable Version](https://poser.pugx.org/codesleeve/stapler/v/unstable.svg)](https://packagist.org/packages/codesleeve/stapler) 
[![License](https://poser.pugx.org/codesleeve/stapler/license.svg)](https://packagist.org/packages/codesleeve/stapler)

Stapler is a php-based, framework agnostic, file upload framework inspired by the [Ruby Paperclip](https://github.com/thoughtbot/paperclip) gem. It can be used to add file file uploads (as attachment objects) to your ORM records.  While not an exact duplicate, if you've used Paperclip before then you should feel quite comfortable using this package.

Stapler was created by [Travis Bennett](https://twitter.com/tandrewbennett).

## Requirements
Stapler currently requires php >= 5.4 (Stapler is implemented via the use of traits).

## Installation
Stapler is distributed as a composer package, which is how it should be used in your app. 

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/stapler`.

```js
  "require": {
    "codesleeve/stapler": "1.0.*"
  }
```

## About Stapler
Stapler works by attaching file uploads to database table records.  This is done by defining attachments inside the table's corresponding model and then assigning uploaded files (from your forms) as properties (named after the attachments) on the model before saving it.  In essence, this allows uloaded files to be treated just like any other property on the model; stapler will abstract away all of the file processing, storage, etc so you can focus on the rest of your project without having to worry about where your files are at or how to retrieve them.  

A model can have multiple attachments defined (avatar, photo, foo, etc) and in turn each attachment can have multiple sizes (styles) defined.  When an image or file is uploaded, Stapler will handle all the file processing (moving, resizing, etc) and provide an attachment object (as a model property) with methods for working with the uploaded file.  To accomplish this, four fields (named after the attachemnt) will need to be created in the corresponding table for any model containing a file attachment.  For example, for an attachment named 'avatar' defined inside a model named 'User', the following fields would need to be added to the 'users' table:

*   (string) avatar_file_name
*   (integer) avatar_file_size
*   (string) avatar_content_type
*   (timestamp) avatar_updated_at

### Key Benefits
* **Modern**: Stapler runs on top of php >= 54 and takes advantage of many of the new features provided by modern php (traits, callable typehinting, etc).
* **Simple**: Traditionally, file uploading has been known to be an arduous task; Stapler reduces much of the boilerplate required throughout this process.
* **Flexible**: Stapler provides an extremely flexible cascading configuration; files can be configured for storage locally or via AWS S3 (and much more).
* **Powerful**: Stapler makes use of modern object oriented programming patterns in order to provide a rock solid architecture for file uploading.  It's trait-based driver system provides the potential for it to work across multiple ORMS (both Active Record and Data Mapper implementations) that implement life cycle callbacks.

## Documentation
* [Quick Start](docs/quickstart.md): A quick example to get you up and running with Stapler.
* [Configuration](docs/configuration.md)
  * [Stapler](docs/configuration.md#stapler-configuration)
  * [Filesystem](docs/configuration.md#filesystem-storage-configuration)
  * [S3](docs/configuration.md#s3-storage-configuration)
* [Interpolations](docs/interpolations.md)
* [Image Processing](docs/imageprocessing.md)
* [Examples](docs/examples.md)
* [Advanced Usage](docs/advanced.md)
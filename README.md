#Stapler
[![Build Status](https://travis-ci.org/CodeSleeve/stapler.png?branch=master)](https://travis-ci.org/CodeSleeve/stapler)
[![Latest Stable Version](https://poser.pugx.org/codesleeve/stapler/v/stable.svg)](https://packagist.org/packages/codesleeve/stapler)
[![Total Downloads](https://poser.pugx.org/codesleeve/stapler/downloads.svg)](https://packagist.org/packages/codesleeve/stapler)
[![Latest Unstable Version](https://poser.pugx.org/codesleeve/stapler/v/unstable.svg)](https://packagist.org/packages/codesleeve/stapler)
[![License](https://poser.pugx.org/codesleeve/stapler/license.svg)](https://packagist.org/packages/codesleeve/stapler)

**Note**: *If you've previously been using this package, then you've been using it with Laravel.  This package is no longer directly coupled to the Laravel framework.  As of 1.0.0, Stapler is now framework agnostic.  In order to take advantage of the Laravel specific features provided by the previous Beta releases (service providers, IOC container, commands, migration generator, etc) , I've created a separate package specifically for the purpose of using Stapler within Laravel:  [Laravel-Stapler](https://github.com/CodeSleeve/laravel-stapler).  If you're using Stapler inside a Laravel application I strongly recommend you use this package (it will save you a bit of boilerplate).*

Stapler is a php-based framework agnostic file upload package inspired by the [Ruby Paperclip](https://github.com/thoughtbot/paperclip) gem. It can be used to add file file uploads (as attachment objects) to your ORM records.  While not an exact duplicate, if you've used Paperclip before then you should feel quite comfortable using this package.

Stapler was created by [Travis Bennett](https://twitter.com/tandrewbennett).

**2.0 Change Log**
- Bumped min php version to PHP >=7.0.
- Stapler now uses League\Flysystem under the hood to manage remove file systems.
- Added the ability to dynamically add interpolations to an existing interpolator ce22161bc189bfed1cb202d8d17d6f51b472a651.
- Added the ability to pass a callable as the value of the default_url aca4630f0537dd1ea605dae2cee911cae0842a72.
- Added the ability to rename files as they are saved (during creation and update) a77e575ab82c97b8e29379c398142bbe7ef7dcb5.
- Added a simple event dispatching system that can be used to tap into lifecycle events of an attachment as it's processed and uploaded.
- Removed the :secure_has interpolation.
- Replaced the :hash interpolation with a more secure hash that now requires the hash_secret option.
- The Eloquent trait now makes use of the `bootTraits()` method on the base Eloquent model (this will reduce errors due to the `boot()` method already being used on an Eloquent model).

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
Stapler works by attaching file uploads to database table records.  This is done by defining attachments inside the table's corresponding model and then assigning uploaded files (from your forms) as properties (named after the attachments) on the model before saving it.  Stapler will listen to the life cycle callbacks of the model (after save, before delete, and after delete) and handle the file accordingly.  In essence, this allows uploaded files to be treated just like any other property on the model; stapler will abstract away all of the file processing, storage, etc so you can focus on the rest of your project without having to worry about where your files are at or how to retrieve them.

### Key Benefits
* **Modern**: Stapler runs on top of php >= 5.4 and takes advantage of many of the new features provided by modern php (traits, callable typehinting, etc).
* **Simple**: Traditionally, file uploading has been known to be an arduous task; Stapler reduces much of the boilerplate required throughout this process.  Seriously, Stapler makes it dead simple to get up and running with file uploads (of any type).
* **Flexible**: Stapler provides an extremely flexible cascading configuration; files can be configured for storage locally or via AWS S3 by changing only a single configuration option.
* **Scalable**: Storing your assets in a central location (such as S3) allows your files to be accessable by multiple web instances from a single location.
* **Powerful**: Stapler makes use of modern object oriented programming patterns in order to provide a rock solid architecture for file uploading.  It's trait-based driver system provides the potential for it to work across multiple ORMS (both Active Record and Data Mapper implementations) that implement life cycle callbacks.

## Documentation
* [Setup](docs/setup.md)
* [Configuration](docs/configuration.md)
* [Interpolations](docs/interpolations.md)
* [Image Processing](docs/imageprocessing.md)
* [Working with Attachments](docs/attachments.md)
* [Examples](docs/examples.md)
* [Troubleshooting](docs/troubleshooting.md)
* [Contributing](docs/contributing.md)

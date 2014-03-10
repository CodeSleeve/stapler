<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Stapler Public Path Location
	|--------------------------------------------------------------------------
	|
	| The location of the web application's document root.  Defaults to Laravel's
	| public folder.
	|
	*/
	
	'public_path' => realPath(public_path()),

	/*
	|--------------------------------------------------------------------------
	| Stapler Storage Driver
	|--------------------------------------------------------------------------
	|
	| The default mechanism for handling file storage.  Currently Stapler supports
	| both file system and Amazon S3 as options.
	|
	*/
	
	'storage' => 'filesystem',

	/*
	|--------------------------------------------------------------------------
	| Stapler Image Processing Library
	|--------------------------------------------------------------------------
	|
	| The default library used for image processing.  Can be one GD, Imagick, or
	| Gmagick.
	|
	*/
	
	'image_processing_library' => 'GD',


	/*
	|--------------------------------------------------------------------------
	| Stapler Default Url
	|--------------------------------------------------------------------------
	|
	| The url (relative to your project document root) containing a default image
	| that will be used for attachments that don't currently have an uploaded image
	| attached to them.
	|
	*/

	'default_url' => '/:attachment/:style/missing.png',

	/*
	|--------------------------------------------------------------------------
	| Stapler Base Url
	|--------------------------------------------------------------------------
	| Base url for all the images.
	| Very handy for use with CDN
	| It also avoids unnecessary requests to Amazon S3
	| 
	| 'base_url' => 'http://my-host-cdn.dev.br/',
	*/

	'base_url' => false,

	/*
	|--------------------------------------------------------------------------
	| Stapler Default Style
	|--------------------------------------------------------------------------
	|
	| The default style returned from the Stapler file location helper methods. 
	| An unaltered version of uploaded file is always stored within the 'original' 
	| style, however the default_style can be set to point to any of the defined 
	| syles within the styles array.
	|
	*/

	'default_style' => 'original',

	/*
	|--------------------------------------------------------------------------
	| Stapler Styles
	|--------------------------------------------------------------------------
	|
	| An array of image sizes defined for the file attachment. 
	| Stapler will attempt to format the file upload into the defined style.
	|
	*/

	'styles' => [],

	/*
	|--------------------------------------------------------------------------
	| Before Styles Function
	|--------------------------------------------------------------------------
	|
	| Function to be performed on the image before resizing it. 
	| Very useful to put watermark on all images before resizes them.
	|
	|
	| 'before_styles' => function ($file, $imagine) {
	|
	|		$image = $imagine->open($file->getRealPath());
	|
	|		return $image;
	|	},
	*/

	'before_styles' => NULL,

	/*
	|--------------------------------------------------------------------------
	| Convert Options
	|--------------------------------------------------------------------------
	|
	| An array of options for setting the quality and DPI of resized images.
	| Default values are 75 for Jpeg quality and 72 dpi for x/y-resolution.
	| Please see the Imagine\Image documentation for more details.
	|
	*/

	'convert_options' => [],

	/*
	|--------------------------------------------------------------------------
	| Keep Old Files Flag
	|--------------------------------------------------------------------------
	|
	| Set this to true in order to prevent older file uploads from being deleted
	| from storage when a record is updated with a new upload.
	|
	*/

	'keep_old_files' => false,

	/*
	|--------------------------------------------------------------------------
	| Preserve Files Flag
	|--------------------------------------------------------------------------
	|
	| Set this to true in order to prevent file uploads from being deleted
	| from the file system when an attachment is destroyed.  Essentially this 
	| ensures the preservation of uploads event after their corresponding database
	| records have been removed.
	|
	*/
	'preserve_files' => false

];
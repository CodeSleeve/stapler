<?php namespace Codesleeve\Stapler;

use Illuminate\Support\ServiceProvider;
use Codesleeve\Stapler\File\UploadedFile;
use Codesleeve\Stapler\Storage\Local as Storage;

class StaplerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Holds the hash value for the current STAPLER_NULL constant.
	 * 
	 * @var string
	 */
	protected $staplerNull;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('tabennett/stapler');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->staplerNull = sha1(time());
		
		if (!defined('STAPLER_NULL')) {
			define('STAPLER_NULL', $this->staplerNull);
		}

		$this->app->bind('Attachment', function($app, $params)
        {
            return new Attachment($params['name'], $params['options']);
        });

		$this->app->bind('Resizer', function($app, $file)
        {
            return new Resizer($file);
        });

        $this->app->bind('Storage', function($app, $attachedFile)
        {
            return new Storage($attachedFile);
        });

        $this->app->bind('Utility', function($app, $arrayElements)
        {
            return new Utility($arrayElements);
        });

        $this->app->bind('UploadedFile', function($app, $uploadedFile)
        {
            $path = $uploadedFile->getPathname();
            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getClientMimeType();
            $size = $uploadedFile->getClientSize();
            $error = $uploadedFile->getError();
            
            return new UploadedFile($path, $originalName, $mimeType, $size, $error);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
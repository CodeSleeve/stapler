<?php namespace Codesleeve\Stapler;

use Illuminate\Support\ServiceProvider;
use Codesleeve\Stapler\File\UploadedFile;
use Codesleeve\Stapler\Storage\Filesystem;
use Codesleeve\Stapler\Storage\S3;

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
		
		$this->registerAttachment();
		$this->registerResizer();
		$this->registerFilesystemStorage();
		$this->registerS3Storage();
		$this->registerUtility();
		$this->registerUploadedFile();
		$this->registerStaplerFasten();

		$this->commands('stapler.fasten');
	}

	/**
	 * Register Codesleeve\Stapler\Attachment with the container.
	 * 
	 * @return void
	 */
	protected function registerAttachment()
	{
		$this->app->bind('Attachment', function($app, $params)
        {
            return new Attachment($params['name'], $params['options']);
        });
	}

	/**
	 * Register Codesleeve\Stapler\Resizer with the contaioner.
	 * 
	 * @return void 
	 */
	protected function registerResizer()
	{
		$this->app->bind('Resizer', function($app, $file)
        {
            return new Resizer($file);
        });
	}

	/**
	 * Register Codesleeve\Stapler\Filesystem with the contaioner.
	 * 
	 * @return void
	 */
	protected function registerFilesystemStorage()
	{
		$this->app->bind('filesystem', function($app, $attachedFile)
        {
            return new Filesystem($attachedFile);
        });
	}

	/**
	 * Register Codesleeve\Stapler\S3 with the contaioner.
	 * 
	 * @return void
	 */
	protected function registerS3Storage()
	{
		$this->app->bind('s3', function($app, $attachedFile)
        {
            return new S3($attachedFile);
        });
	}

	/**
	 * Register Codesleeve\Stapler\Utility with the container.
	 * 
	 * @return void
	 */
	protected function registerUtility()
	{
		$this->app->bind('Utility', function($app, $arrayElements)
        {
            return new Utility($arrayElements);
        });
	}

	/**
	 * Register Codesleeve\Stapler\UploadedFile with the container.
	 * 
	 * @return void
	 */
	protected function registerUploadedFile()
	{
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
	 * Register the stapler fasten command with the container.
	 * 
	 * @return void
	 */
	protected function registerStaplerFasten()
	{
		$this->app->bind('stapler.fasten', function($app) 
		{
			return new Commands\FastenCommand;
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
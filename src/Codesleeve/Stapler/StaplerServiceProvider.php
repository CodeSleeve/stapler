<?php namespace Codesleeve\Stapler;

use Illuminate\Support\ServiceProvider;
use Codesleeve\Stapler\File\UploadedFile;
use Codesleeve\Stapler\File\Image\Resizer;

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
		$this->package('codesleeve/stapler');
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
		
		$this->registerResizer();
		$this->registerIOWrapper();
		$this->registerConfig();
		$this->registerValidator();
		$this->registerInterpolator();
		$this->registerGD();
		$this->registerImagick();
		$this->registerGmagick();
		$this->registerFilesystemStorage();
		$this->registerS3Storage();
		$this->registerAttachment();
		$this->registerUtility();
		//$this->registerUploadedFile();
		$this->registerStaplerFasten();

		$this->commands('stapler.fasten');
	}

	/**
	 * Register Codesleeve\Stapler\File\Image\Resizer with the container.
	 * 
	 * @return void
	 */
	protected function registerResizer()
	{
		$this->app->bind('Resizer', function($app, $params)
        {
        	return new Resizer($params['imageProcessor']);
        });
	}

	/**
	 * Register Codesleeve\Stapler\IOWrapper with the container.
	 * 
	 * @return void
	 */
	protected function registerIOWrapper()
	{
		$this->app->singleton('IOWrapper', function($app, $params)
        {
        	return new IOWrapper();
        });
	}

	/**
	 * Register Codesleeve\Stapler\Config with the container.
	 * 
	 * @return void
	 */
	protected function registerConfig()
	{
		$this->app->bind('Config', function($app, $params)
        {
        	return new Config($params['name'], $params['options']);
        });
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
			$config = $app->make('Config', ['name' => $params['name'], 'options' => $params['options']]);
			$interpolator = $app->make('Interpolator');
			$imageProcessor = $app->make($params['options']['image_processing_library']);
			$resizer = $app->make('Resizer', ['imageProcessor' => $imageProcessor]);
			$IOWrapper = $app->make('IOWrapper');

            $attachment = new Attachment($config, $interpolator, $resizer, $IOWrapper);
            
            $storageDriver = $app->make($params['options']['storage'], ['attachment' => $attachment]);
            $attachment->setStorageDriver($storageDriver);

            return $attachment;
        });
	}

	/**
	 * Register Codesleeve\Stapler\Validator with the container.
	 * 
	 * @return void
	 */
	protected function registerValidator()
	{
		$this->app->singleton('AttachmentValidator', function($app)
        {
            return new Validator();
        });
	}

	/**
	 * Register Codesleeve\Stapler\Interpolator with the container.
	 * 
	 * @return void
	 */
	protected function registerInterpolator()
	{
		$this->app->singleton('Interpolator', function($app)
        {
            return new Interpolator();
        });
	}

	/**
	 * Register Imagine\Gd\Imagine with the container.
	 * 
	 * @return void
	 */
	public function registerGD()
	{
		$this->app->singleton('GD', function($app)
        {
            return new \Imagine\Gd\Imagine();
        });
	}

	/**
	 * Register Imagine\Imagick\Imagine with the container.
	 * 
	 * @return void
	 */
	public function registerImagick()
	{
		$this->app->singleton('Imagick', function($app)
        {
            return new \Imagine\Imagick\Imagine();
        });
	}

	/**
	 * Register Imagine\Gmagick\Imagine with the container.
	 * 
	 * @return void
	 */	
	public function registerGmagick()
	{
		$this->app->singleton('Gmagick', function($app)
        {
            return new \Imagine\Gmagick\Imagine();
        });
	}

	/**
	 * Register Storage\Filesystem with the contaioner.
	 * 
	 * @return void
	 */
	protected function registerFilesystemStorage()
	{
		$this->app->bind('filesystem', function($app, $params)
        {
            return new Storage\Filesystem($params['attachment']);
        });
	}

	/**
	 * Register Storage\S3 with the contaioner.
	 * 
	 * @return void
	 */
	protected function registerS3Storage()
	{
		$this->app->bind('s3', function($app, $params)
        {
            return new Storage\S3($params['attachment']);
        });
	}

	/**
	 * Register Codesleeve\Stapler\Utility with the container.
	 * 
	 * @return void
	 */
	protected function registerUtility()
	{
		$this->app->singleton('Utility', function($app, $arrayElements)
        {
            return new Utility($arrayElements);
        });
	}

	/**
	 * Register Codesleeve\Stapler\UploadedFile with the container.
	 * 
	 * @return void
	 */
	/*protected function registerUploadedFile()
	{
		$this->app->bind('UploadedFile', function($app, $uploadedFile)
	    {
	        $path = $uploadedFile->getPathname();
	        $originalName = $uploadedFile->getClientOriginalName();
	        $mimeType = $uploadedFile->getClientMimeType();
	        $size = $uploadedFile->getClientSize();
	        $error = $uploadedFile->getError();

	        $staplerUploadedFile = new UploadedFile($path, $originalName, $mimeType, $size, $error);

	        if (!$staplerUploadedFile->isValid()) {
				throw new Exceptions\FileException($staplerUploadedFile->getErrorMessage($staplerUploadedFile->getError()));
			}
	        
	        return $staplerUploadedFile;
	    });
	}*/

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

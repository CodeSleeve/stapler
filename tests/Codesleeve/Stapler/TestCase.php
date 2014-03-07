<?php namespace Codesleeve\Stapler;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;

class TestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * Bootstrap the test environemnt:
	 * - Create an application instance and register it within itself.
	 * - Register the package service provider with the app.
	 * - Set the APP facade.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$app = new Application;
		$app->instance('app', $app);
		$app->register('Codesleeve\Stapler\StaplerServiceProvider');
		Facade::setFacadeApplication($app);
	}

}

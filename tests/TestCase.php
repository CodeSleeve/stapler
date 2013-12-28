<?php

use Illuminate\Support\Facades\App;

class TestCase extends PHPUnit_Framework_TestCase {

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
		$app = new Illuminate\Foundation\Application;
		$app->instance('app', $app);
		$app->register('Codesleeve\Stapler\StaplerServiceProvider');
		Illuminate\Support\Facades\Facade::setFacadeApplication($app);
	}

}

<?php

use Illuminate\Support\Facades\App;

class TestCase extends PHPUnit_Framework_TestCase {

  public function setUp()
  {
    $app = new Illuminate\Foundation\Application;
    $app->instance('app', $app);
    $app->register('Codesleeve\Stapler\StaplerServiceProvider');
    Illuminate\Support\Facades\Facade::setFacadeApplication($app);
  }

}

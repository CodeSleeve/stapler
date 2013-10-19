<?php

use Illuminate\Support\Facades\App;

class StaplerServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $app = new Illuminate\Foundation\Application;
        $app->instance('app', $app);
        $app->register('Codesleeve\Stapler\StaplerServiceProvider');
        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
    }


    /**
     * @expectedException Codesleeve\Stapler\Exceptions\FileException
     */
    public function test_registerUploadedFile()
    {
        $uploadedFile = $this->getMock('Symfony\Component\HttpFoundation\File\UploadedFile',
          ['isValid'],
          [__DIR__.'/empty.gif', 'Test', null, null, null, true]
        );

        $uploadedFile->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));
        
        App::make('UploadedFile', $uploadedFile);
    }

}

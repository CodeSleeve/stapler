<?php

use Illuminate\Support\Facades\App;

class StaplerServiceProviderTest extends TestCase
{
    /**
     * @expectedException Codesleeve\Stapler\Exceptions\FileException
     */
    public function test_registerUploadedFile()
    {
        $uploadedFile = $this->getMock('Symfony\Component\HttpFoundation\File\UploadedFile',
          ['isValid'],
          [__DIR__.'/../fixtures/empty.gif', 'Test', null, null, null, true]
        );

        $uploadedFile->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));
        
        App::make('UploadedFile', $uploadedFile);
    }

}

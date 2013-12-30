<?php

use Codesleeve\Stapler\IOWrapper;
use Mockery as m;
	
class IOWrapperTest extends TestCase
{
	/**
	 * Test that you can work with test Symfony Uploaded Files
	 * 
	 * @return void
	 */
	public function testMakeWithSymfonyFile()
	{
		$path = __DIR__.'/../fixtures/empty.gif';
		$originalName = 'Test.gif';
    $file = new Symfony\Component\HttpFoundation\File\UploadedFile($path, $originalName, null, null, null, true);

    $io = new IOWrapper();
    $io->make($file);
  }
}

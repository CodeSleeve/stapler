<?php

use Mockery as m;

class UploadedFileTest extends TestCase
{
	/**
	 * Setup method.
	 * 
	 * @return void 
	 */
	public function setUp()
	{
		# code...
	}

	/**
	 * Teardown method.
	 * 
	 * @return void 
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Test that the validate method is working correctly when
	 * a valid file upload object is passed in.
	 * 
	 * @return void
	 */
	public function testValidate()
	{
		$staplerUploadedFile = $this->buildValidStaplerUploadedFile();
		
		$staplerUploadedFile->validate();
	}

	/**
	 * Test that the validate method will throw an exception
	 * when passed an invalid file upload object.
	 * 
	 * @expectedException Codesleeve\Stapler\Exceptions\FileException
	 * @return void
	 */
	public function testValidateThrowsExceptions()
	{
		$staplerUploadedFile = $this->buildInvalidStaplerUploadedFile();
		
		$staplerUploadedFile->validate();
	}

	/**
	 * Helper method to build an valid Codesleeve\Stapler\File\UploadedFile object.	
	 * 
	 * @return UploadedFile
	 */
	public function buildValidStaplerUploadedFile()
	{
		$symfonyUploadedFile = $this->buildSymfonyUploadedFile();
		
		return new Codesleeve\Stapler\File\UploadedFile($symfonyUploadedFile);
	}

	/**
	 * Helper method to build an invalid Codesleeve\Stapler\File\UploadedFile object.	
	 * 
	 * @return UploadedFile
	 */
	public function buildInvalidStaplerUploadedFile()
	{
		$symfonyUploadedFile = $this->buildSymfonyUploadedFile(false);
		
		return new Codesleeve\Stapler\File\UploadedFile($symfonyUploadedFile);
	}

	/**
	* Helper method to build a mock Symfony UploadedFile object.
	*
	* @param  boolean $test
	* @return UploadedFile 
	*/
	protected function buildSymfonyUploadedFile($test = true) 
	{
		$path = __DIR__.'/../../fixtures/empty.gif';
		$originalName = 'Test.gif';
		
		return new Symfony\Component\HttpFoundation\File\UploadedFile($path, $originalName, null, null, null, $test);
	}
}
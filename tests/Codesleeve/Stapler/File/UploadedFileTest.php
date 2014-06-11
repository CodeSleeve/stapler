<?php namespace Codesleeve\Stapler\Tests\File;

use PHPUnit_Framework_TestCase;
use Codesleeve\Stapler\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Mockery as m;

class UploadedFileTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup method.
	 *
	 * @return void
	 */
	public function setUp()
	{
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
	 * The validate method should throw an exception
	 * when passed an invalid file upload object.
	 *
	 * @test
	 * @expectedException Codesleeve\Stapler\Exceptions\FileException
	 * @return void
	 */
	public function it_should_throw_an_exception_if_the_file_upload_is_invalid()
	{
		$staplerUploadedFile = $this->buildInvalidStaplerUploadedFile();

		$staplerUploadedFile->validate();
	}

	/**
	 * An uploaded file shoudl be able to detect if the
	 * file type that has been uploaded is an image.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_detect_the_if_the_file_is_an_image()
	{
		$staplerUploadedFile = $this->buildValidStaplerUploadedFile();

		$isImage = $staplerUploadedFile->isImage();

		$this->assertEquals(true, $isImage);
	}

	/**
	 * An uploaded file object should be able to return the 
	 * name of the underlying uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_get_the_name_of_the_uploaded_file()
	{
		$staplerUploadedFile = $this->buildValidStaplerUploadedFile();

		$filename = $staplerUploadedFile->getFilename();

		$this->assertEquals('empty.gif', $filename);
	}

	/**
	 * An uploaded file object should be able to return the size of the
	 * underlying uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_get_the_size_of_the_uploaded_file()
	{
		$staplerUploadedFile = $this->buildValidStaplerUploadedFile();

		$size = $staplerUploadedFile->getSize();

		$this->assertEquals(null, $size);
	}

	/**
	 * An uploaded file object should be able to return the mime type 
	 * of the underlyjng uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_get_the_mime_type_of_the_uploaded_file()
	{
		$staplerUploadedFile = $this->buildValidStaplerUploadedFile();

		$mime = $staplerUploadedFile->getMimeType();

		$this->assertEquals('image/gif', $mime);
	}

	/**
	 * Helper method to build an valid Codesleeve\Stapler\File\UploadedFile object.
	 *
	 * @return UploadedFile
	 */
	protected function buildValidStaplerUploadedFile()
	{
		$symfonyUploadedFile = $this->buildSymfonyUploadedFile();

		return new UploadedFile($symfonyUploadedFile);
	}

	/**
	 * Helper method to build an invalid Codesleeve\Stapler\File\UploadedFile object.
	 *
	 * @return UploadedFile
	 */
	protected function buildInvalidStaplerUploadedFile()
	{
		$symfonyUploadedFile = $this->buildSymfonyUploadedFile(false);

		return new UploadedFile($symfonyUploadedFile);
	}

	/**
	* Helper method to build a mock Symfony UploadedFile object.
	*
	* @param  boolean $testing
	* @return UploadedFile
	*/
	protected function buildSymfonyUploadedFile($testing = true)
	{
		$path = __DIR__ . '/../Fixtures/empty.gif';
		$originalName = 'empty.gif';

		return new SymfonyUploadedFile($path, $originalName, null, null, null, $testing);
	}
}
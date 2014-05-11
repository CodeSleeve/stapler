<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Codesleeve\Stapler\Attachment;
use Codesleeve\Stapler\Interpolator;
use Codesleeve\Stapler\File\UploadedFile as StaplerUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class AttachmentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup method.
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
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
	 * When calling the setUploadedFile method with a value
	 * of STAPLER_NULL, setUploadedFile() should return null.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_return_null_when_setting_an_uploaded_file_that_is_equal_to_stapler_null()
	{
		$attachment = $this->build_mock_instance()->photo;
		
		$staplerUploadedFile = $attachment->setUploadedFile(STAPLER_NULL);

		$this->assertNull($staplerUploadedFile);
	}

	/**
	 * Calling the url method with a style parameter should
	 * return the url for that style.
	 * 
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_an_attachment_url_for_a_style()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);
		
		$url = $attachment->url('thumbnail');

		$this->assertEquals('/system/photos/000/000/001/thumbnail/empty.gif', $url);
	}

	/**
	 * Calling the url method without a style parameter should
	 * return the url for the default style.
	 * 
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_the_default_url_for_an_attachment_if_no_style_is_given()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);
		
		$url = $attachment->url();

		$this->assertEquals('/system/photos/000/000/001/original/empty.gif', $url);
	}

	/**
	 * Calling the path method with a style parameter should
	 * return the path for that style.
	 * 
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_an_attachment_path_for_a_style()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);
		
		$path = $attachment->path('thumbnail');

		$this->assertEquals('/public/system/photos/000/000/001/thumbnail/empty.gif', $path);
	}

	/**
	 * Calling the path method without a style parameter should
	 * return the path for the default style.
	 * 
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_the_default_path_for_an_attachment_if_no_style_is_given($value='')
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);
		
		$path = $attachment->path();

		$this->assertEquals('/public/system/photos/000/000/001/original/empty.gif', $path);
	}

	/**
	 * Calling the contentType method should return the
	 * content type of the original uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_the_content_type()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

		$contentType = $attachment->contentType();

		$this->assertEquals('image/gif', $contentType);
	}

	/**
	 * Calling the size method should return the size of the
	 * original uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_the_originaL_file_size()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

		$size = $attachment->size();

		$this->assertEquals(0, $size);
	}

	/**
	 * Calling the originalFilename method should return the name
	 * of the original uploaded file.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_return_the_original_file_name()
	{
		$attachment = $this->build_mock_instance()->photo;
		$symfonyUploadedFile = new SymfonyUploadedFile(__DIR__ . '/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
		$staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

		$filename = $attachment->originalFilename();

		$this->assertEquals('empty.gif', $filename);
	}

	public function it_should_be_able_to_register_an_after_save_observer()
	{
		# code...
	}

	public function it_should_be_able_to_register_a_before_delete_observer()
	{
		# code...
	}

	public function it_should_be_able_to_register_an_after_delete_observer()
	{
		# code...
	}

	/**
	 * Build a mock attachment instance.
	 * 
	 * @return Attachment
	 */
	protected function build_mock_instance()
	{
		Stapler::boot();

		return new \Codesleeve\Stapler\Fixtures\Models\Photo(['id' => 1]);
	}
}
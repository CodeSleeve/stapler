<?php namespace Codesleeve\Stapler\Factories;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Mockery as m;

class StorageTest extends PHPUnit_Framework_TestCase
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
	 * Test that the Storage factory can create an instance of the filesystem
	 * storage driver.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_filesystem_storeage_instance()
	{
		$attachment = $this->buildMockAttachment('filesystem');

		$storage = Storage::create($attachment);

		$this->assertInstanceOf('Codesleeve\Stapler\Storage\Filesystem', $storage);
	}

	/**
	 * Test that the Storage factory can create an instance of the s3
	 * storage driver.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_an_s3_storeage_instance()
	{
		$attachment = $this->buildMockAttachment('s3');

		$storage = Storage::create($attachment);

		$this->assertInstanceOf('Codesleeve\Stapler\Storage\S3', $storage);
	}

	/**
	 * Test that the Storage factory should create an instance of the filesystem
	 * storage driver by default.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_filesystem_storeage_instance_by_default()
	{
		$attachment = $this->buildMockAttachment();

		$storage = Storage::create($attachment);

		$this->assertInstanceOf('Codesleeve\Stapler\Storage\Filesystem', $storage);
	}

	/**
	 * Build a mock attachment object.
	 *
	 * @param  string $type
	 * @return \Codesleeve\Stapler\Attachment
	 */
	protected function buildMockAttachment($type = null)
	{
		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachmentConfig = new \Codesleeve\Stapler\AttachmentConfig('testAttachmentConfig', ['styles' => []]);
		$attachment->setConfig($attachmentConfig);
		$attachment->storage = $type;

		return $attachment;
	}
}
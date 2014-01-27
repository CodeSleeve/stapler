<?php

use Mockery as m;
	
class AttachmentTest extends TestCase
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
	 * Test that the setUploadedFile method is working correctly.
	 * 
	 * @return void
	 */
	public function testSetUploadedFile()
	{
		$IOWrapper = $this->buildMockIOWrapper();
		
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_name', '');
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_size', '');
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_content_type', '');
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_updated_at', date('Y-m-d H:i:s'));

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('clear')->once();
		$attachment->setIOWrapper($IOWrapper);
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$attachment->setUploadedFile('bar');
	}

	/**
	 * Test that the url method returns the url to a file that has been uploaded.
	 * 
	 * @return void
	 */
	public function testUrlReturnsAUrl()
	{
		$storageDriver = m::mock('Codesleeve\Stapler\Storage\StorageInterface');
		$storageDriver->shouldReceive('url')->once()->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(true);
		$attachment->shouldReceive('defaultUrl')->never();
		$attachment->setStorageDriver($storageDriver);

		$this->assertEquals('foo', $attachment->url());
	}

	/**
	 * Test that the url method returns the default url when no file has been uploaded.
	 * 
	 * @return void
	 */
	public function testUrlReturnsTheDefaultUrl()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(false);
		$attachment->setConfig($config);
		
		$this->assertEquals('', $attachment->url());
	}

	/**
	 * Test that the path method returns the path to a file that has been uploaded.
	 * 
	 * @return void
	 */
	public function testPathReturnsAPath()
	{
		$storageDriver = m::mock('Codesleeve\Stapler\Storage\StorageInterface');
		$storageDriver->shouldReceive('path')->once()->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(true);
		$attachment->shouldReceive('defaultPath')->never();
		$attachment->setStorageDriver($storageDriver);

		$this->assertEquals('foo', $attachment->path());
	}

	/**
	 * Test that the path method returns the default path when no file has been uploaded.
	 * 
	 * @return void
	 */
	public function testPathReturnsTheDefaultPath()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', ['public_path' => '/public']]);

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(false);
		$attachment->setConfig($config);
		
		$this->assertEquals('/public', $attachment->path());
	}

	/**
	 * Test that the createdAt method is working correctly.
	 * 
	 * @return void
	 */
	public function testCreatedAt()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('getAttribute')->once()->with('mockAttachment_created_at')->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$this->assertEquals('foo', $attachment->createdAt());
	}

	/**
	 * Test that the updatedAt method is working correctly.
	 * 
	 * @return void
	 */
	public function testUpdatedAt()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('getAttribute')->once()->with('mockAttachment_updated_at')->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$this->assertEquals('foo', $attachment->updatedAt());
	}

	/**
	 * Test that the contentType method is working correctly.
	 * 
	 * @return void
	 */
	public function testContentType()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('getAttribute')->once()->with('mockAttachment_content_type')->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$this->assertEquals('foo', $attachment->contentType());
	}

	/**
	 * Test that the size method is working correctly.
	 * 
	 * @return void
	 */
	public function testSize()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('getAttribute')->once()->with('mockAttachment_file_size')->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$this->assertEquals('foo', $attachment->size());
	}

	/**
	 * Test that the originalFilename method is working correctly.
	 * 
	 * @return void
	 */
	public function testOriginalFilename()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', []]);
		
		$instance = m::mock();
		$instance->shouldReceive('getAttribute')->once()->with('mockAttachment_file_name')->andReturn('foo');

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$this->assertEquals('foo', $attachment->originalFilename());
	}

	/**
	 * Test that the afterSave method is working correctly.
	 * 
	 * @return void
	 */
	public function testAfterSave()
	{
		$instance = m::mock();

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('save')->once();

		$attachment->afterSave($instance);

		$this->assertEquals($instance, $attachment->getInstance());
	}

	/**
	 * Test that the beforeDelelete method is functioning properly.
	 * 
	 * @return void
	 */
	public function testBeforeDelete()
	{
		$instance = m::mock();

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('clear')->once();

		$attachment->beforeDelete($instance);

		$this->assertEquals($instance, $attachment->getInstance());
	}

	/**
	 * Test the the afterDelete method is working correctly.
	 * 
	 * @return void
	 */
	/*public function testAfterDelete()
	{
		$instance = m::mock();

		$storageDriver = m::mock('Codesleeve\Stapler\Storage\StorageInterface');
		$storageDriver->shouldReceive('remove')->once()->with(['foo']);

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setStorageDriver($storageDriver);
		$attachment->setQueuedForDeletion(['foo']);

		$attachment->afterDelete($instance);

		$this->assertEquals($attachment->getQueuedForDeletion(), []);
		$this->assertEquals($instance, $attachment->getInstance());
	}*/

	/**
	 * Test that the destroy method is functioning correctly.
	 * 
	 * @return void 
	 */
	public function testDestroy()
	{
		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('clear')->once()->with(['foo']);
		$attachment->shouldReceive('save')->once();

		$attachment->destroy(['foo']);
	}

	/**
	 * Test that the clear method is working correctly when passed a
	 * specific set of styles to clear.
	 * 
	 * @return void
	 */
	public function testClearWithStyles()
	{
		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('path')->once()->with('foo')->andReturn('bar');
		
		$attachment->clear(['foo']);

		$this->assertEquals(['bar'], $attachment->getQueuedForDeletion());
	}

	/**
	 * Test that the clear method is working correctly when no styles are passed
	 * and a file has already been uploaded and preserve files is set to false.
	 * 
	 * @return void
	 */
	public function testClearWithoutStylesAndWithUploadedFile()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', ['preserve_files' => false, 'styles' => ['foo' => '']]])->makePartial();

		$instance = m::mock();
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_name', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_size', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_content_type', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_updated_at', null);

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(true);
		$attachment->shouldReceive('path')->once()->with('foo')->andReturn('bar');
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$attachment->clear();

		$this->assertEquals($attachment->getQueuedForDeletion(), ['bar']);
	}

	/**
	 * Test that the clear method is working correctly when no styles are passed
	 * and a file has already been uploaded and preserve files is set to true.
	 * 
	 * @return void
	 */
	public function testClearWithoutStylesAndWithUploadedFileAndPreserveFiles()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', ['preserve_files' => true]]);

		$instance = m::mock();
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_name', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_file_size', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_content_type', null);
		$instance->shouldReceive('setAttribute')->once()->with('mockAttachment_updated_at', null);

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->shouldReceive('originalFilename')->once()->andReturn(true);
		$attachment->setConfig($config);
		$attachment->setInstance($instance);

		$attachment->clear();

		$this->assertEquals($attachment->getQueuedForDeletion(), []);
	}

	/**
	 * Test that the getInstanceClass method is working correctly.
	 * 
	 * @return void
	 */
	public function testGetInstanceClass()
	{
		$instance = m::mock();

		$attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$attachment->setInstance($instance);

		$this->assertEquals(get_class($instance), $attachment->getInstanceClass());
	}

	/**
	 * Build a mock IOWrapper object.
	 * 
	 * @return IOWrapper
	 */
	protected function buildMockIOWrapper()
	{
		$mockUploadedFile = m::mock('Codesleeve\Stapler\File\UploadedFile');
		$mockUploadedFile->shouldReceive('getFilename')->once();
		$mockUploadedFile->shouldReceive('getSize')->once();
		$mockUploadedFile->shouldReceive('getMimeType')->once();
		
		$IOWrapper = m::mock('Codesleeve\Stapler\IOWrapper');
		$IOWrapper->shouldReceive('make')->once()->andReturn($mockUploadedFile);

		return $IOWrapper;
	}
}
<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Codesleeve\Stapler\AttachmentConfig;

class AttachmentConfigTest extends PHPUnit_Framework_TestCase
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
	 * Test that the AttachmentConfig class can store the
	 * name of it's correpsonding attachment.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_store_the_name_of_an_attachment()
	{
		$attachmentConfig = new AttachmentConfig('mockAttachment', ['styles' => []]);

		$this->assertEquals('mockAttachment', $attachmentConfig->name);
	}

	/**
	 * Test that the AttachmentConfig class can dynamically store attachment
	 * config options.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_dynamically_retrieve_config_values()
	{
		$attachmentConfig = new AttachmentConfig('mockAttachment', ['styles' => [], 'foo' => 'bar']);

		$this->assertEquals('bar', $attachmentConfig->foo);
		$this->assertNull($attachmentConfig->baz);
	}

	/**
	 * Test that the AttachmentConfig class can dynamically set new config options.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_dynamically_set_config_values()
	{
		$attachmentConfig = new AttachmentConfig('mockAttachment', ['styles' => []]);

		$attachmentConfig->foo = 'bar';

		$this->assertEquals('bar', $attachmentConfig->foo);
	}

	/**
	 * Test that the AttachmentConfig class can build an
	 * array of style objects if style options are passed in.
	 *
	 * @test
	 * @param string $value [description]
	 */
	public function it_should_be_able_build_an_array_of_style_objects()
	{
		$attachmentConfig = new AttachmentConfig('mockAttachment', ['styles' => ['baz' => '']]);

		$this->assertTrue(is_array($attachmentConfig->styles));
		$this->assertInstanceOf('Codesleeve\Stapler\Style', $attachmentConfig->styles[0]);
	}

	/**
	 * Test that the AttachmentConfig class will throw an exception
	 * if no styles key is present in the options array.
	 *
	 * @test
	 * @expectedException \Codesleeve\Stapler\Exceptions\InvalidAttachmentConfigurationException
	 * @return void
	 */
	public function it_should_throw_an_exception_if_no_styles_key_is_present()
	{
		$attachmentConfig = new AttachmentConfig('mockAttachment', []);
	}
}
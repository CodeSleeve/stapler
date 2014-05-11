<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;

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
	 * Test that the AttachmentConfig class will correctly merge 
	 * style options.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_merge_configuration_options()
	{
		$attachmentConfig = new \Codesleeve\Stapler\AttachmentConfig('mockAttachment', [
			'foo' => 'bar',
			'styles' => ['baz' => ''],
			'convert_options' => []
		]);

		$this->assertEquals('bar', $attachmentConfig->foo);
		$this->assertInstanceOf('Codesleeve\Stapler\Style', $attachmentConfig->styles[0]);
		$this->assertNull($attachmentConfig->baz);
	}
}
<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Codesleeve\Stapler\Interpolator;
use Codesleeve\Stapler\AttachmentConfig;
use Mockery as m;

class InterpolatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * An attachment instance.
	 * 
	 * @var \Codesleeve\Stapler\Attachment
	 */
	protected $attachment;

	/**
	 * An interpolator instance.
	 * 
	 * @var \Codesleeve\Stapler\Interpolator
	 */
	protected $interpolator;

	/**
	 * Setup method.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->attachment = $this->attachment ?: $this->build_mock_attachment();
		$this->interpolator = $this->interpolator ?: new Interpolator;
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
	 * Test that when no style is passed in, the interpolator 
	 * will correctly interpolate a string using the default style.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_interpolate_a_string_using_the_default_style()
	{
		$input = '/system/:class/:attachment/:id/:style/:filename';
		
		$interpolatedString = $this->interpolator->interpolate($input, $this->attachment);

		$this->assertEquals('/system/TestModel/photos/1/original/test.jpg', $interpolatedString);
	}

	/**
	 * Test the interpolator will correctly interpolate a string when
	 * using an injected style.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_interpolate_a_string_using_an_injected_style()
	{
		$input = '/system/:class/:attachment/:id/:style/:filename';
		
		$interpolatedString = $this->interpolator->interpolate($input, $this->attachment, 'thumbnail');

		$this->assertEquals('/system/TestModel/photos/1/thumbnail/test.jpg', $interpolatedString);
	}

	/**
	 * Test the interpolator will correctly interpolate a string when
	 * using an id partition
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_interpolate_a_string_using_an_id_partition()
	{
		$input = '/system/:class/:attachment/:id_partition/:style/:filename';
		
		$interpolatedString = $this->interpolator->interpolate($input, $this->attachment, 'thumbnail');

		$this->assertEquals('/system/TestModel/photos/000/000/001/thumbnail/test.jpg', $interpolatedString);
	}

	/**
	 * Build a mock attachment object.
	 *
	 * @return \Codesleeve\Stapler\Attachment
	 */
	protected function build_mock_attachment()
	{
		$instance = m::mock();
		$instance->shouldReceive('getKey')->andReturn(1);

		$attachmentConfig = new AttachmentConfig('photo', ['styles' => [], 'default_style' => 'original']);

		$attachment = m::mock('Codesleeve\Stapler\Attachment[originalFilename, getInstanceClass]');
		$attachment->shouldReceive('originalFilename')->andReturn('test.jpg');
		$attachment->shouldReceive('getInstanceClass')->andReturn('TestModel');
		$attachment->setInstance($instance);
		$attachment->setConfig($attachmentConfig);

		return $attachment;
	}
}
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
		$this->interpolator = $this->interpolator ?: new Interpolator;
		$this->attachment = $this->attachment ?: $this->build_mock_attachment($this->interpolator);
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
	 * @param  \Codesleeve\Stapler\Interpolator
	 * @return \Codesleeve\Stapler\Attachment
	 */
	protected function build_mock_attachment($interpolator)
	{
		$instance = $this->build_mock_instance();
		$attachmentConfig = new AttachmentConfig('photo', ['styles' => [], 'default_style' => 'original']);
		$imagine = m::mock('Imagine\Image\ImagineInterface');
		$resizer = new \Codesleeve\Stapler\File\Image\Resizer($imagine);
		
		$attachment = m::mock('Codesleeve\Stapler\Attachment[getInstanceClass]', [$attachmentConfig, $interpolator, $resizer]);
		$attachment->shouldReceive('getInstanceClass')->andReturn('TestModel');
		$attachment->setInstance($instance);

		return $attachment;
	}

	/**
	 * Build a mock model instance.
	 * 
	 * @return mixed
	 */
	protected function build_mock_instance()
	{
		$instance = m::mock('Codesleeve\Stapler\ORM\StaplerableInterface');
		$instance->shouldReceive('getKey')->andReturn(1);
		$instance->shouldReceive('getAttribute')->with('photo_file_name')->andReturn('test.jpg');
		$instance->shouldReceive('getAttribute')->with('photo_file_size')->andReturn(0);
		$instance->shouldReceive('getAttribute')->with('photo_content_type')->andReturn('image/jpeg');

		return $instance;
	}
}
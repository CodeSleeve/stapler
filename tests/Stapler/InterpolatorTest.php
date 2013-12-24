<?php

use Mockery as m;
	
class InterpolatorTest extends TestCase
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
	 * Test that the interpolate method will correctly interpolate a string.
	 * 
	 * @return void
	 */
	public function testInterpolate()
	{
		$string = m::mock(['plural' => 'bar']);
		$interpolator = m::mock('Codesleeve\Stapler\Interpolator', [$string])->makePartial();
		$attachment = $this->buildMockAttachment();
		$expected = '/system/foo/bar/000/000/001/original/test/jpg/test.jpg';
		$actual = $interpolator->interpolate('/system/:class/:attachment/:id_partition/:style/:basename/:extension/:filename', $attachment);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Build a mock attachment object.
	 * 
	 * @return Attachment
	 */
	protected function buildMockAttachment()
	{
		$instance = m::mock();
		$instance->shouldReceive('getKey')->twice()->andReturn(1);
		
		$config = m::mock('Codesleeve\Stapler\Config', ['bar', ['default_style' => 'original']]);

		$attachment = m::mock('Codesleeve\Stapler\Attachment[originalFilename, getInstanceClass]');
		$attachment->shouldReceive('originalFilename')->times(3)->andReturn('test.jpg');
		$attachment->shouldReceive('getInstanceClass')->once()->andReturn('foo');
		$attachment->setInstance($instance);
		$attachment->setConfig($config);

		return $attachment;
	}
}
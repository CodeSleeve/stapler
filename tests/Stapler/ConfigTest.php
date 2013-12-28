<?php

use Mockery as m;
	
class ConfigTest extends TestCase
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
	 * Test the __get magic method is working correctly.
	 * 
	 * @return void
	 */
	public function testGet()
	{
		$config = m::mock('Codesleeve\Stapler\Config', ['mockAttachment', ['foo' => 'bar', 'styles' => ['baz' => '']]])->makePartial();

		$this->assertEquals('bar', $config->foo);
		$this->assertInstanceOf('StdClass', $config->styles[0]);
		$this->assertNull($config->baz);
	}
}
<?php namespace Codesleeve\Stapler\Config;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class NativeConfigTest extends PHPUnit_Framework_TestCase
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
	 * Test that a NativeConfig object can get a single
	 * config item.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_get_a_single_item()
	{
		$data = ['group1' => ['item1' => 'foo']];
		$config = new NativeConfig($data);
		
		$item = $config->get('group1.item1');

		$this->assertEquals('foo', $item);
	}

	/**
	 * Test that a NativeConfig object can get a group of
	 * config item.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_get_an_item_group()
	{
		$data = ['group1' => ['item1' => 'foo']];
		$config = new NativeConfig($data);
		
		$item = $config->get('group1');

		$this->assertEquals(['item1' => 'foo'], $item);
	}

	/**
	 * Test that a NativeConfig object can get a single
	 * config item.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_set_a_single_item()
	{
		$config = new NativeConfig([]);

		$config->set('group1.item1', 'bar');

		$this->assertEquals('bar', $config->get('group1.item1'));
	}

	/**
	 * Test that a NativeConfig object can set a group of
	 * config item.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_set_an_item_group()
	{
		$config = new NativeConfig([]);
		
		$item = $config->set('group1', ['item1' => 'foo']);

		$this->assertEquals(['item1' => 'foo'], $config->get('group1'));
	}
}
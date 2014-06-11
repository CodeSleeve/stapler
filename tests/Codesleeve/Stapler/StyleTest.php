<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class StyleTest extends PHPUnit_Framework_TestCase
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

	public function it_should_be_able_to_set_accept_a_name_propert()
	{
		$styleValue = '50x50';

		$styleOjbect = new Style('foo', $styleValue);

		$this->assertEquals('foo', $styleOjbect->name);
	}

	/**
	 * Test that the style class can accept a simple string value
	 * of dimensions.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_accept_a_string_value()
	{
		$styleValue = '50x50';

		$styleOjbect = new Style('foo', $styleValue);

		$this->assertEquals('50x50', $styleOjbect->dimensions);
	}

	/**
	 * Test that the style class can accept an array of values to 
	 * parse.
	 *
	 * @test
	 * @param  string $value 
	 * @return void
	 */
	public function it_should_be_able_to_accept_an_array_of_values($value='')
	{
		$convertOptions = ['resolution-units' => 'ppi', 'resolution-x' => 300, 'resolution-y' => 300, 'jpeg_quality' => 100];
		$styleValue = ['dimensions' => '50x50', 'auto_orient' => true, 'convert_options' => $convertOptions];

		$styleOjbect = new Style('foo', $styleValue);

		$this->assertEquals('50x50', $styleOjbect->dimensions);
		$this->assertTrue($styleOjbect->autoOrient);
		$this->assertEquals($convertOptions, $styleOjbect->convertOptions);
	}

	/**
	 * Test that the style class will throw an exception if passed an array
	 * of values withou a 'dimensions' key.
	 *
	 * @test
	 * @expectedException \Codesleeve\Stapler\Exceptions\InvalidStyleConfigurationException
	 * @return void
	 */
	public function it_should_throw_an_exception_if_passed_an_array_of_values_withou_a_dimensions_key()
	{
		$convertOptions = ['resolution-units' => 'ppi', 'resolution-x' => 300, 'resolution-y' => 300, 'jpeg_quality' => 100];
		$styleValue = ['auto_orient' => true, 'convert_options' => $convertOptions];

		$styleOjbect = new Style('foo', $styleValue);
	}
}
<?php namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class StaplerTest extends PHPUnit_Framework_TestCase
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
	 * Test that the Stapler class can define an value for the STAPLER_NULL
	 * constant.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_define_a_stapler_null_constant()
	{
		Stapler::boot();

		$this->assertTrue(defined('STAPLER_NULL'));
	}

	/**
	 * Test that the Stapler class can build a single instance of
	 * the Interpolator class.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_interpolator_instance()
	{
		$interpolator1 = Stapler::getInterpolatorInstance();
		$interpolator2 = Stapler::getInterpolatorInstance();

		$this->assertInstanceOf('Codesleeve\Stapler\Interpolator', $interpolator1);
		$this->assertSame($interpolator1, $interpolator2);
	}

	/**
	 * Test that the Stapler class can build a single instance of
	 * the Validator class.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_validator_instance()
	{
		$validator1 = Stapler::getValidatorInstance();
		$validator2 = Stapler::getValidatorInstance();

		$this->assertInstanceOf('Codesleeve\Stapler\Validator', $validator1);
		$this->assertSame($validator1, $validator2);
	}

	/**
	 * Test that the Stapler class can build a single instance of
	 * the Resizer class.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_resizer_instance()
	{
		$resizer1 = Stapler::getResizerInstance('Imagine\Gd\Imagine');
		$resizer2 = Stapler::getResizerInstance('Imagine\Gd\Imagine');

		$this->assertInstanceOf('Codesleeve\Stapler\File\Image\Resizer', $resizer1);
		$this->assertSame($resizer1, $resizer2);
	}

	/**
	 * Test that the Stapler class can build a single instance of
	 * ImagineInterface.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_imagine_interface_instance()
	{
		$imagine1 = Stapler::getImagineInstance('Imagine\Gd\Imagine');
		$imagine2 = Stapler::getImagineInstance('Imagine\Gd\Imagine');

		$this->assertInstanceOf('Imagine\Image\ImagineInterface', $imagine1);
		$this->assertSame($imagine1, $imagine2);
	}

	/**
	 * Test that the Stapler class can build a single instance of
	 * Aws\S3\S3Client for each model/attachment combo.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_s3_client_instance_for_each_model_attachment_combo()
	{
		$dummyConfig = new AttachmentConfig('TestAttachment', [
			'styles' => [], 
			's3_client_config' => [
				'key' => '', 
				'secret' => '', 
				'region' => '', 
				'scheme' => 'http'
			]
		]);
		$mockAttachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
		$mockAttachment->shouldReceive('getInstanceClass')->twice()->andReturn('TestModel');
		$mockAttachment->setConfig($dummyConfig);

		$s3Client1 = Stapler::getS3ClientInstance($mockAttachment);
		$s3Client2 = Stapler::getS3ClientInstance($mockAttachment);

		$this->assertInstanceOf('Aws\S3\S3Client', $s3Client1);
		$this->assertSame($s3Client1, $s3Client2);
	}

	/**
	 * Test that the stapler class can build a single instance of
	 * Codesleeve\Stapler\Config\NativeConfig.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_a_singleton_native_config_instance()
	{
		$config1 = Stapler::getConfigInstance();
		$config2 = Stapler::getConfigInstance();

		$this->assertInstanceOf('Codesleeve\Stapler\Config\NativeConfig', $config1);
		$this->assertInstanceOf('Codesleeve\Stapler\Config\ConfigurableInterface', $config1);
		$this->assertSame($config1, $config2);
	}

	/**
	 * Test that the stapler class can build a single instance of
	 * Codesleeve\Stapler\Config\IlluminateConfig.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_create_set_singleton_config_instance()
	{
		$loaderInterface = m::mock('Illuminate\Config\loaderInterface');
		$illuminateConfig = new \Illuminate\Config\Repository($loaderInterface, 'testing');
		$config1 = new Config\IlluminateConfig($illuminateConfig, 'test');
		
		Stapler::setConfigInstance($config1);
		$config2 = Stapler::getConfigInstance();

		$this->assertInstanceOf('Codesleeve\Stapler\Config\IlluminateConfig', $config1);
		$this->assertInstanceOf('Codesleeve\Stapler\Config\ConfigurableInterface', $config1);
		$this->assertSame($config1, $config2);
	}
}
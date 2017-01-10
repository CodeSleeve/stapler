<?php

namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;

class StaplerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup method.
     */
    public function setUp()
    {
    }

    /**
     * Teardown method.
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
     */
    public function it_should_be_able_to_create_a_singleton_interpolator_instance()
    {
        $interpolator1 = Stapler::getInterpolatorInstance();
        $interpolator2 = Stapler::getInterpolatorInstance();

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\InterpolatorInterface', $interpolator1);
        $this->assertSame($interpolator1, $interpolator2);
    }

    /**
     * Test that the Stapler class can build a single instance of
     * the Validator class.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_singleton_validator_instance()
    {
        $validator1 = Stapler::getValidatorInstance();
        $validator2 = Stapler::getValidatorInstance();

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\ValidatorInterface', $validator1);
        $this->assertSame($validator1, $validator2);
    }

    /**
     * Test that the Stapler class can build a single instance of
     * the Resizer class.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_singleton_resizer_instance()
    {
        $resizer1 = Stapler::getResizerInstance('Imagine\Gd\Imagine');
        $resizer2 = Stapler::getResizerInstance('Imagine\Gd\Imagine');

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\ResizerInterface', $resizer1);
        $this->assertSame($resizer1, $resizer2);
    }

    /**
     * Test that the Stapler class can build a single instance of
     * ImagineInterface.
     *
     * @test
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
     * League\Flysystem\Filesyste for each model/attachment combo that's
     * using cloud storage.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_singleton_filesystem_client_instance_for_each_model_attachment_combo()
    {
        $dummyConfig = new AttachmentConfig('TestAttachment', [
            'storage' => 's3',
            'styles' => [],
            's3_client_config' => [
                'credentials' => [
                    'key' => '',
                    'secret' => '',
                ],
                'region' => '',
                'scheme' => 'http',
                'version' => 'latest'
            ],
        ]);
        $mockAttachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
        $mockAttachment->shouldReceive('getInstanceClass')->twice()->andReturn('TestModel');
        $mockAttachment->setConfig($dummyConfig);

        $filesystem1 = Stapler::filesystemForAttachment($mockAttachment);
        $filesystem2 = Stapler::filesystemForAttachment($mockAttachment);

        $this->assertInstanceOf('League\Flysystem\Filesystem', $filesystem1);
        $this->assertSame($filesystem1, $filesystem2);
    }

    /**
     * Test that the stapler class can build a single instance of
     * Codesleeve\Stapler\NativeConfig.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_singleton_native_config_instance()
    {
        $config1 = Stapler::getConfigInstance();
        $config2 = Stapler::getConfigInstance();

        $this->assertInstanceOf('Codesleeve\Stapler\NativeConfig', $config1);
        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\ConfigInterface', $config1);
        $this->assertSame($config1, $config2);
    }
}

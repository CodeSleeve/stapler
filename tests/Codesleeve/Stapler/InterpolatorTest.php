<?php

namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
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
     */
    public function setUp()
    {
        $this->interpolator = $this->interpolator ?: new Interpolator();
        $this->attachment = $this->attachment ?: $this->buildMockAttachment($this->interpolator);
    }

    /**
     * Teardown method.
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
     */
    public function it_should_be_able_to_interpolate_a_string_using_the_default_style()
    {
        $input = '/system/:class/:attachment/:id/:style/:filename';

        $interpolatedString = $this->interpolator->interpolate($input, $this->attachment);

        $this->assertEquals('/system/test_model/photos/1/original/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string when
     * using an injected style.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_using_an_injected_style()
    {
        $input = '/system/:class/:attachment/:id/:style/:filename';

        $interpolatedString = $this->interpolator->interpolate($input, $this->attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/1/thumbnail/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string containing an :id_partition interpolation.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_containing_an_id_partition()
    {
        $input = '/system/:class/:attachment/:id_partition/:style/:filename';

        $interpolatedString = $this->interpolator->interpolate($input, $this->attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/000/000/001/thumbnail/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string containing a :hash interpolation.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_containing_a_hash()
    {
        $attachmentConfig = new AttachmentConfig('photo', ['styles' => [], 'default_style' => 'original', 'hash_secret' => 'abfa04a42c94f58d17a509bccb2276d2f2e1718e23de5f0ff4bc93b4c922c2dbd23f81b31a7932fbf4424c95f14e055639d2376f8b3cb40ebf91ea4682197645']);
        $attachment = $this->buildMockAttachment($this->interpolator, 'Foo\\Faz\\Baz\\TestModel', $attachmentConfig);
        $input = '/system/:class_name/:attachment/:id_partition/:style/:hash';
        $interpolatedString = $this->interpolator->interpolate($input, $attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/000/000/001/thumbnail/fcab8328c8194101054eb52fb1e0189b26e0faccb68fe24fa73625ab4bc44c5e', $interpolatedString);
    }

    /**
     * Test that the interpolator will throw an exception if it attempts to interpolate a string with
     * a :hash interpolation but no hash_string option has been set.
     *
     * @expectedException \Codesleeve\Stapler\Exceptions\InvalidAttachmentConfigurationException
     * @test
     */
    public function it_should_throw_an_exception_if_it_interpolates_a_string_containing_a_hash()
    {
        $attachment = $this->buildMockAttachment($this->interpolator, 'Foo\\Faz\\Baz\\TestModel');
        $input = '/system/:class_name/:attachment/:id_partition/:style/:hash';
        $interpolatedString = $this->interpolator->interpolate($input, $attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/000/000/001/thumbnail/fcab8328c8194101054eb52fb1e0189b26e0faccb68fe24fa73625ab4bc44c5e', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string when
     * using a class name.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_using_a_class_name()
    {
        $attachment = $this->buildMockAttachment($this->interpolator, 'Foo\\Faz\\Baz\\TestModel');
        $input = '/system/:class_name/:attachment/:id_partition/:style/:filename';
        $interpolatedString = $this->interpolator->interpolate($input, $attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/000/000/001/thumbnail/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string when a :namespace interpolation.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_using_a_namespace()
    {
        $attachment = $this->buildMockAttachment($this->interpolator, 'Foo\\Faz\\Baz\\TestModel');
        $input = '/system/:namespace/:attachment/:id_partition/:style/:filename';
        $interpolatedString = $this->interpolator->interpolate($input, $attachment, 'thumbnail');

        $this->assertEquals('/system/foo/faz/baz/photos/000/000/001/thumbnail/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator will correctly interpolate a string with a :class_name interpolation.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_using_a_namespace_and_class_name()
    {
        $attachment = $this->buildMockAttachment($this->interpolator, 'Foo\\Bar\\Baz\\TestModel');
        $input = '/system/:namespace/:class_name/:attachment/:id_partition/:style/:filename';
        $interpolatedString = $this->interpolator->interpolate($input, $attachment, 'thumbnail');

        $this->assertEquals('/system/foo/bar/baz/test_model/photos/000/000/001/thumbnail/test.jpg', $interpolatedString);
    }

    /**
     * Test that the interpolator can interpolate strings that container interpolations that were
     * added dynamically.
     *
     * @test
     */
    public function it_should_be_able_to_interpolate_a_string_using_a_dynamic_interpolation()
    {
        $input = '/system/:class/:attachment/:id_partition/:style/:foo/:filename';

        Interpolator::interpolates(':foo', function($attachment, string $styleName = '') {
            return 'bar';
        });

        $interpolatedString = $this->interpolator->interpolate($input, $this->attachment, 'thumbnail');

        $this->assertEquals('/system/test_model/photos/000/000/001/thumbnail/bar/test.jpg', $interpolatedString);
    }

    /**
     * Build a mock attachment object.
     *
     * @param  Interpolator     $interpolator
     * @param  string           $className
     * @param  AttachmentConfig $attachmentConfig
     * @return void
     */
    protected function buildMockAttachment(Interpolator $interpolator, string $className = 'TestModel', AttachmentConfig $attachmentConfig = null)
    {
        if (!$attachmentConfig) {
            $attachmentConfig = new AttachmentConfig('photo', ['styles' => [], 'default_style' => 'original']);
        }

        $instance = $this->buildMockInstance();
        $imagine = m::mock('Imagine\Image\ImagineInterface');
        $resizer = new \Codesleeve\Stapler\File\Image\Resizer($imagine);
        $dispatcher = new \Codesleeve\Stapler\NativeEventDispatcher;

        $attachment = m::mock('Codesleeve\Stapler\Attachment[getInstanceClass]', [$attachmentConfig, $interpolator, $resizer, $dispatcher]);
        $attachment->shouldReceive('getInstanceClass')->andReturn($className);
        $attachment->setInstance($instance);

        return $attachment;
    }

    /**
     * Build a mock model instance.
     *
     * @return mixed
     */
    protected function buildMockInstance()
    {
        $instance = m::mock('Codesleeve\Stapler\ORM\StaplerableInterface');
        $instance->shouldReceive('getKey')->andReturn(1);
        $instance->shouldReceive('getAttribute')->with('photo_file_name')->andReturn('test.jpg');
        $instance->shouldReceive('getAttribute')->with('photo_file_size')->andReturn(0);
        $instance->shouldReceive('getAttribute')->with('photo_content_type')->andReturn('image/jpeg');

        return $instance;
    }
}

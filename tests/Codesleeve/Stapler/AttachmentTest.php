<?php

namespace Codesleeve\Stapler;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class AttachmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup method.
     */
    public function setUp()
    {
        parent::setUp();

        Stapler::boot();
    }

    /**
     * Teardown method.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * When calling the setUploadedFile method with a value
     * of STAPLER_NULL, setUploadedFile() should return null.
     *
     * @test
     */
    public function it_should_return_null_when_setting_an_uploaded_file_that_is_equal_to_stapler_null()
    {
        $attachment = $this->build_attachment();

        $staplerUploadedFile = $attachment->setUploadedFile(STAPLER_NULL);

        $this->assertNull($staplerUploadedFile);
    }

    /**
     * Calling the url method with a style parameter should
     * return the url for that style.
     *
     * @test
     */
    public function it_should_be_able_to_return_an_attachment_url_for_a_style()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $url = $attachment->url('thumbnail');

        $this->assertEquals('/system/photos/000/000/001/thumbnail/empty.gif', $url);
    }

    /**
     * Calling the url method without a style parameter should
     * return the url for the default style.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_default_style_for_an_attachment_if_no_style_is_given()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $url = $attachment->url();

        $this->assertEquals('/system/photos/000/000/001/original/empty.gif', $url);
    }

    /**
     * Calling the path method with a style parameter should
     * return the path for that style.
     *
     * @test
     */
    public function it_should_be_able_to_return_an_attachment_path_for_a_style()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $path = $attachment->path('thumbnail');

        $this->assertEquals('/public/system/photos/000/000/001/thumbnail/empty.gif', $path);
    }

    /**
     * Calling the path method without a style parameter should
     * return the path for the default style.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_default_path_for_an_attachment_if_no_style_is_given($value = '')
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $path = $attachment->path();

        $this->assertEquals('/public/system/photos/000/000/001/original/empty.gif', $path);
    }

    /**
     * Calling the contentType method should return the
     * content type of the original uploaded file.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_content_type()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $contentType = $attachment->contentType();

        $this->assertEquals('image/gif', $contentType);
    }

    /**
     * Calling the size method should return the size of the
     * original uploaded file.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_originaL_file_size()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $size = $attachment->size();

        $this->assertEquals(0, $size);
    }

    /**
     * Calling the originalFilename method should return the name
     * of the original uploaded file.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_original_file_name()
    {
        $attachment = $this->build_attachment();
        $symfonyUploadedFile = new SymfonyUploadedFile(__DIR__.'/Fixtures/empty.gif', 'empty.gif', null, null, null, true);
        $staplerUploadedFile = $attachment->setUploadedFile($symfonyUploadedFile);

        $filename = $attachment->originalFilename();

        $this->assertEquals('empty.gif', $filename);
    }

    /**
     * Calling url() or path() on a stapler attachment that doesn't have an uploaded file
     * should always return the url/path configured for the attachment via the 'default_url' setting.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_default_url_if_no_upload_exists_yet()
    {
        // given
        $attachment = $this->build_attachment_without_an_upload();

        // when/then
        $this->assertEquals('/photos/original/missing.png', $attachment->url());
        $this->assertEquals('/home/your-app/public/photos/original/missing.png', $attachment->path());
    }

    /**
     * Calling url() or path() on a stapler attachment that doesn't have an uploaded file
     * should always return the url/path configured for the attachment via the 'default_url' setting.
     *
     * @test
     */
    public function it_should_be_able_to_return_the_default_url_from_a_callable_if_no_upload_exists_yet()
    {
        // given
        $instance = $this->build_mock_instance_without_an_upload();
        $interpolator = new Interpolator();
        $attachmentConfig = new \Codesleeve\Stapler\AttachmentConfig('photo', [
            'styles' => ['original' => '', 'thumbnail' => '100x100'],
            'default_style' => 'original',
            'default_url' => function($style) {
                return 'https://foo.com/bar/' . $style->name . '.jpg';
            },
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'path' => ':app_root/public:url',
            'public_path' => '/home/your-app/public'
        ]);

        $imagine = m::mock('Imagine\Image\ImagineInterface');
        $dispatcher = new \Codesleeve\Stapler\NativeEventDispatcher;
        $resizer = new \Codesleeve\Stapler\File\Image\Resizer($imagine);

        $attachment = new \Codesleeve\Stapler\Attachment($attachmentConfig, $interpolator, $resizer, $dispatcher);
        $attachment->setInstance($instance);

        $storageDriver = new \Codesleeve\Stapler\Storage\Local($attachment);
        $attachment->setStorageDriver($storageDriver);

        // when/then
        $this->assertEquals('https://foo.com/bar/original.jpg', $attachment->url());
        $this->assertEquals('/bar/original.jpg', $attachment->path());
        $this->assertEquals('https://foo.com/bar/thumbnail.jpg', $attachment->url('thumbnail'));
        $this->assertEquals('/bar/thumbnail.jpg', $attachment->path('thumbnail'));
    }

    /**
     * Build an attachment object.
     *
     * @param  \Codesleeve\Stapler\Interpolator
     *
     * @return \Codesleeve\Stapler\Attachment
     */
    protected function build_attachment()
    {
        $instance = $this->build_mock_instance();
        $interpolator = new Interpolator();
        $attachmentConfig = new \Codesleeve\Stapler\AttachmentConfig('photo', [
            'styles' => [],
            'default_style' => 'original',
            'default_url' => '/:attachment/:style/missing.png',
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'path' => ':app_root/public:url',
            'public_path' => '/home/your-app/public'
        ]);

        $imagine = m::mock('Imagine\Image\ImagineInterface');
        $dispatcher = new \Codesleeve\Stapler\NativeEventDispatcher;
        $resizer = new \Codesleeve\Stapler\File\Image\Resizer($imagine);

        $attachment = new \Codesleeve\Stapler\Attachment($attachmentConfig, $interpolator, $resizer, $dispatcher);
        $attachment->setInstance($instance);

        $storageDriver = new \Codesleeve\Stapler\Storage\Local($attachment);
        $attachment->setStorageDriver($storageDriver);

        return $attachment;
    }

    /**
     * Build an attachment object that represents an attachment without an uploaded file.
     *
     * @param  \Codesleeve\Stapler\Interpolator
     *
     * @return \Codesleeve\Stapler\Attachment
     */
    protected function build_attachment_without_an_upload()
    {
        $instance = $this->build_mock_instance_without_an_upload();
        $interpolator = new Interpolator();
        $attachmentConfig = new \Codesleeve\Stapler\AttachmentConfig('photo', [
            'styles' => [],
            'default_style' => 'original',
            'default_url' => '/:attachment/:style/missing.png',
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'path' => ':app_root/public:url',
            'public_path' => '/home/your-app/public'
        ]);

        $imagine = m::mock('Imagine\Image\ImagineInterface');
        $dispatcher = new \Codesleeve\Stapler\NativeEventDispatcher;
        $resizer = new \Codesleeve\Stapler\File\Image\Resizer($imagine);

        $attachment = new \Codesleeve\Stapler\Attachment($attachmentConfig, $interpolator, $resizer, $dispatcher);
        $attachment->setInstance($instance);

        $storageDriver = new \Codesleeve\Stapler\Storage\Local($attachment);
        $attachment->setStorageDriver($storageDriver);

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
        $instance->shouldReceive('getAttribute')->with('photo_file_name')->andReturn('empty.gif');
        $instance->shouldReceive('getAttribute')->with('photo_file_size')->andReturn(0);
        $instance->shouldReceive('getAttribute')->with('photo_content_type')->andReturn('image/gif');
        $instance->shouldReceive('setAttribute');

        return $instance;
    }

    /**
     * Build a mock model instance that doesn't have a file uploaded yet.
     *
     * @return mixed
     */
    protected function build_mock_instance_without_an_upload()
    {
        $instance = m::mock('Codesleeve\Stapler\ORM\StaplerableInterface');
        $instance->shouldReceive('getKey')->andReturn(1);
        $instance->shouldReceive('getAttribute')->with('photo_file_name')->andReturn(null);
        $instance->shouldReceive('getAttribute')->with('photo_file_size')->andReturn(0);
        $instance->shouldReceive('getAttribute')->with('photo_content_type')->andReturn(null);
        $instance->shouldReceive('setAttribute');

        return $instance;
    }
}

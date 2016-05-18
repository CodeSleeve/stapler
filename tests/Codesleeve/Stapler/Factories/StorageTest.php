<?php

namespace Codesleeve\Stapler\Factories;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Codesleeve\Stapler\AttachmentConfig;

class StorageTest extends PHPUnit_Framework_TestCase
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
     * Test that the Storage factory can create an instance of the local
     * storage driver.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_local_storeage_instance()
    {
        $attachment = $this->buildMockAttachment('local');

        $storage = Storage::create($attachment);

        $this->assertInstanceOf('Codesleeve\Stapler\Storage\Local', $storage);
    }

    /**
     * Test that the Storage factory can create an instance of the s3
     * storage driver.
     *
     * @test
     */
    public function it_should_be_able_to_create_an_s3_storeage_instance()
    {
        $attachment = $this->buildMockS3Attachment();

        $storage = Storage::create($attachment);

        $this->assertInstanceOf('Codesleeve\Stapler\Storage\S3', $storage);
    }

    /**
     * Test that the Storage factory should create an instance of the local
     * storage driver by default.
     *
     * @test
     */
    public function it_should_be_able_to_create_a_local_storeage_instance_by_default()
    {
        $attachment = $this->buildMockAttachment();

        $storage = Storage::create($attachment);

        $this->assertInstanceOf('Codesleeve\Stapler\Storage\Local', $storage);
    }

    /**
     * Build a mock attachment object that uses local storage
     *
     * @param string $type
     *
     * @return \Codesleeve\Stapler\Attachment
     */
    protected function buildMockAttachment($type = null)
    {
        $attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
        $attachmentConfig = new AttachmentConfig('testAttachmentConfig', ['styles' => []]);
        $attachment->setConfig($attachmentConfig);
        $attachment->storage = $type;

        return $attachment;
    }

    /**
     * Build a mock attachment object that uses cloud storage.
     *
     * @param string $type
     *
     * @return \Codesleeve\Stapler\Attachment
     */
    protected function buildMockS3Attachment()
    {
        $attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
        $attachmentConfig = new AttachmentConfig('TestAttachment', [
            'storage' => 's3',
            'styles' => [],
            's3_client_config' => [
                'credentials' => [
                    'key' => '',
                    'secret' => ''
                ],
                'region' => '',
                'scheme' => 'http',
                'version' => 'latest'
            ],
        ]);
        $attachment->setConfig($attachmentConfig);

        return $attachment;
    }
}

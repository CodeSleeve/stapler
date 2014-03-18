<?php

use Mockery as m;

class StorageS3Test extends TestCase
{

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
   * Test that the default config options are forwarded to S3
   *
   * @return void
   */
  public function testMove()
  {
    $bucket = 'codename-production';
    $file = "/doesn't matter.jpg";
    $filePath = "s3://somewhere";
    $contentType = 'image/jpeg';
    $acl = 'public-read';

    $config = m::mock('Codesleeve\Stapler\Config', ['mock config', [ 'ACL' => $acl ]])->makePartial();
    $attachment = $this->getMockedAttachment($config, $contentType);

    $s3Client = m::mock();
    $s3Client->shouldReceive('putObject')->once()->with([
      'Bucket' => $bucket,
      'Key' => $filePath,
      'SourceFile' => $file,
      'ContentType' => $contentType,
      'ACL' => $acl
    ]);

    $storage = $this->getMockedStorage($attachment, $bucket, $s3Client);
    
    $storage->move($file, $filePath);
  }

  /**
   * Test that the config's CacheControl option is forwarded to S3
   *
   * @return void
   */
  public function testMoveWithOptions()
  {
    $bucket = 'codename-production';
    $file = "/doesn't matter.jpg";
    $filePath = "s3://somewhere";
    $contentType = 'image/jpeg';
    $acl = 'public-read';
    $cacheControl = "max-age=3123123";

    $config = m::mock('Codesleeve\Stapler\Config', ['mock config', [ 'ACL' => $acl, 'CacheControl' => $cacheControl ]])->makePartial();
    $attachment = $this->getMockedAttachment($config, $contentType);

    $s3Client = m::mock();
    $s3Client->shouldReceive('putObject')->once()->with([
      'Bucket' => $bucket,
      'Key' => $filePath,
      'SourceFile' => $file,
      'ContentType' => $contentType,
      'ACL' => $acl,
      'CacheControl' => $cacheControl
    ]);

    $storage = $this->getMockedStorage($attachment, $bucket, $s3Client);
    
    $storage->move($file, $filePath);
  }


  private function getMockedAttachment($config, $contentType) {
    $instance = m::mock();

    $attachment = m::mock('Codesleeve\Stapler\Attachment')->makePartial();
    $attachment->setConfig($config);
    $attachment->setInstance($instance);
    $attachment->shouldReceive('contentType')->andReturn($contentType);

    return $attachment;
  }

  private function getMockedStorage($attachment, $bucket, $s3Client) {
    $s3ClientManager = $this->app->make('S3ClientManager');

    $storage = m::mock('Codesleeve\Stapler\Storage\S3', [$attachment, $s3ClientManager])->makePartial();
    $storage->shouldReceive('getBucket')->andReturn($bucket);
    $storage->shouldReceive('getS3Client')->andReturn($s3Client);

    return $storage;
  }

}

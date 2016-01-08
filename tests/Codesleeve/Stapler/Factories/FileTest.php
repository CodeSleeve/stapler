<?php

namespace Codesleeve\Stapler\Factories;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class FileTest extends PHPUnit_Framework_TestCase
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
    }

    /**
     * Test that the file factory can create a Codesleeve\Stapler\UploadedFile
     * object from a symfony object.
     *
     * @test
     */
    public function it_should_be_able_to_build_a_stapler_uploaded_file_object_from_a_symfony_file_object()
    {
        $symfonyUploadedFile = $this->buildSymfonyUploadedFile(true);

        $uploadedFile = File::create($symfonyUploadedFile);

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\File', $uploadedFile);
    }

    /**
     * Test that the file factory can create a Codesleeve\Stapler\UploadedFile
     * object from an array.
     *
     * @test
     */
    public function it_should_be_able_to_build_a_stapler_uploaded_file_object_from_an_array()
    {
        $fileData = [
            'tmp_name' => __DIR__.'/../Fixtures/empty.gif',
            'name' => 'empty.gif',
            'type' => null,
            'size' => null,
            'error' => null,
        ];

        $uploadedFile = File::create($fileData, true);

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\File', $uploadedFile);
    }

    /**
     * Test that the file factory can create a Codesleeve\Stapler\UploadedFile
     * object from a url.
     *
     * @test
     */
    public function it_should_be_able_to_build_a_stapler_uploaded_file_object_from_a_url()
    {
        $uploadedFile = File::create('https://www.google.com/images/srpr/logo11w.png');

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\File', $uploadedFile);
    }

    /**
     * Test that the file factory can create a Codesleeve\Stapler\UploadedFile
     * object from a redirect url.
     *
     * @test
     */
    public function it_should_be_able_to_build_a_stapler_uploaded_file_object_from_a_redirect_url()
    {
        $uploadedFile = File::create('https://graph.facebook.com/zuck/picture?type=large');

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\File', $uploadedFile);
    }

  /**
   * Test that file created by file factory is not containing unnecessary quer string.
   *
   * @test
   */
  public function it_should_be_able_to_build_a_stapler_uploaded_file_object_without_following_querystring_in_basename()
  {
      $url = 'https://graph.facebook.com/zuck/picture?type=large';
      $uploadedFile = File::create($url);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_exec($ch);
      $info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      curl_close($ch);

        // To make sure that the exact image URL has query string
        $this->assertGreaterThanOrEqual(0, strpos($info, '?'));
      $this->assertFalse(strpos($uploadedFile->getFileName(), '?'));
  }

    /**
     * Test that the file factory can create a Codesleeve\Stapler\UploadedFile
     * object from a string filepath.
     *
     * @test
     */
    public function it_should_be_able_to_build_a_stapler_uploaded_file_object_from_a_string_file_path()
    {
        $uploadedFile = File::create(__DIR__.'/../Fixtures/empty.gif');

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\File', $uploadedFile);
    }

    /**
     * Helper method to build a mock Symfony UploadedFile object.
     *
     * @param bool $testing
     *
     * @return UploadedFile
     */
    protected function buildSymfonyUploadedFile($testing = true)
    {
        $path = __DIR__.'/../Fixtures/empty.gif';
        $originalName = 'empty.gif';

        return new SymfonyUploadedFile($path, $originalName, null, null, null, $testing);
    }
}

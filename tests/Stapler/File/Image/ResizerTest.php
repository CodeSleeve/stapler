<?php

use Imagine\Image\Box;
use Imagine\Image\Point;
use Illuminate\Support\Facades\App;

class ResizerTest extends TestCase
{

  public function test_resizeCrop() {
    $uploadedFile = $this->uploadedFile();

    $originalSize = new Box(600, 400);

    $expectedResize = new Box(768, 512);
    $expectedCropPoint = new Point(128, 0);
    $expectedCropBox = new Box(512, 512);

    $image = $this->mockImage($originalSize, $expectedResize, $expectedCropPoint, $expectedCropBox);

    $imageProcessor = $this->mockImageProcessor($image);
    $resizer = new Codesleeve\Stapler\File\Image\Resizer($imageProcessor);

    $style = $this->styleObject('512x512#');

    $file = $resizer->resize($uploadedFile, $style);
  }

  public function test_resizeCrop_edgecase() {
    $uploadedFile = $this->uploadedFile();

    $originalSize = new Box(1000, 653);

    $expectedResize = new Box(440, 287.32);
    $expectedCropPoint = new Point(0, 21.66);
    $expectedCropBox = new Box(440, 244);

    $image = $this->mockImage($originalSize, $expectedResize, $expectedCropPoint, $expectedCropBox);

    $imageProcessor = $this->mockImageProcessor($image);
    $resizer = new Codesleeve\Stapler\File\Image\Resizer($imageProcessor);

    $style = $this->styleObject('440x244#');

    $file = $resizer->resize($uploadedFile, $style);
  }


  //
  // Testing Helpers
  //

  private function uploadedFile() {
    $symfonyUploadedFile = $this->getMock('Symfony\Component\HttpFoundation\File\UploadedFile',
      null,
      [__DIR__.'/../../../fixtures/empty.gif', 'Test.gif', null, null, null, true]
    );

    return App::make('UploadedFile', $symfonyUploadedFile);
  }

  private function mockImage( $originalSize, $expectedResize,
                              $expectedCropPoint = null, $expectedCropBox = null) {

    $image = $this->getMock('Image', ['getSize', 'resize', 'crop', 'save']);

    $image->expects($this->once())
      ->method('getSize')
      ->will($this->returnValue($originalSize));

    $image->expects($this->once())
      ->method('resize')
      ->with($expectedResize)
      ->will($this->returnValue($image));

    $image->expects($this->once())
      ->method('crop')
      ->with($expectedCropPoint, $expectedCropBox)
      ->will($this->returnValue($image));

    $image->expects($this->once())
      ->method('save');

    return $image;
  }

  private function mockImageProcessor($image) {
    $imageProcessor = $this->getMock('Imagine', ['open']);

    $imageProcessor->expects($this->once())
      ->method('open')
      ->will($this->returnValue($image));

    return $imageProcessor;
  }

  private function styleObject($style) {
    $utility = App::make('Utility');
    $styles = $utility->convertToObject(['thumbnail' => $style]);
    return $styles[0];
  }

}

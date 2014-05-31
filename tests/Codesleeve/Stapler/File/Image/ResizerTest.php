<?php namespace Codesleeve\Stapler\File\Image;

use PHPUnit_Framework_TestCase;
use Codesleeve\Stapler\File\UploadedFile;
use Codesleeve\Stapler\Style;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Mockery as m;

class ResizerTest extends PHPUnit_Framework_TestCase
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
	 * Test the resize crop method.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_resize_and_crop_an_image()
	{
		$uploadedFile = $this->buildUploadedFile();
		$originalSize = new Box(600, 400);
		$expectedResize = new Box(768, 512);
		$expectedCropPoint = new Point(128, 0);
		$expectedCropBox = new Box(512, 512);

		$image = $this->buildMockImage($originalSize, $expectedResize, $expectedCropPoint, $expectedCropBox);
		$imageProcessor = $this->buildMockImageProcessor($image);
		$resizer = new Resizer($imageProcessor);

		$style = $this->buildMockStyleObject('thumbnail', '512x512#');
		$file = $resizer->resize($uploadedFile, $style);
	}

	/**
	 * Test resize cropping edge case.
	 *
	 * @test
	 * @return void
	 */
	public function it_should_be_able_to_resize_and_crop_an_edge_case()
	{
		$uploadedFile = $this->buildUploadedFile();
		$originalSize = new Box(1000, 653);
		$expectedResize = new Box(440, 287.32);
		$expectedCropPoint = new Point(0, 21.66);
		$expectedCropBox = new Box(440, 244);

		$image = $this->buildMockImage($originalSize, $expectedResize, $expectedCropPoint, $expectedCropBox);
		$imageProcessor = $this->buildMockImageProcessor($image);
		$resizer = new Resizer($imageProcessor);

		$style = $this->buildMockStyleObject('thumbnail', '440x244#');
		$file = $resizer->resize($uploadedFile, $style);
	}

	/**
	* Helper method to build a mock Stapler UploadedFile object.
	*
	* @return UploadedFile
	*/
	protected function buildUploadedFile()
	{
		$path = realpath(__DIR__ . '/../../Fixtures/empty.gif');
		$originalName = 'Test.gif';
		$symfonyUploadedFile = new SymfonyUploadedFile($path, $originalName, null, null, null, true);

		return new UploadedFile($symfonyUploadedFile);
	}

	/**
	* Helper method to build a mock Image object.
	*
	* @param  integer $originalSize
	* @param  integer $expectedResize
	* @param  integer $expectedCropPoint
	* @param  integer $expectedCropBox
	* @return Image
	*/
	protected function buildMockImage($originalSize, $expectedResize, $expectedCropPoint = null, $expectedCropBox = null)
	{
		$image = $this->getMock('Imagine\Image\ImageInterface');
		$image->expects($this->once())->method('getSize')->will($this->returnValue($originalSize));
		$image->expects($this->once())->method('resize')->with($expectedResize)->will($this->returnValue($image));
		$image->expects($this->once())->method('crop')->with($expectedCropPoint, $expectedCropBox)->will($this->returnValue($image));
		$image->expects($this->once())->method('save');
		
		return $image;
	}

	/**
	 * Helper method to build a mock Imagine instance.
	 *
	 * @param  Image $image
	 * @return Imagine
	 */
	protected function buildMockImageProcessor($image)
	{
		$imageProcessor = m::mock('Imagine\Image\ImagineInterface');
		$imageProcessor->shouldReceive('open')->once()->andReturn($image);

		return $imageProcessor;
	}

	/**
	 * Helper method to build a mock style object.
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  array $convertOptions
	 * @return Object
	 */
	protected function buildMockStyleObject($name, $value, $convertOptions = [])
	{
		return new Style($name, $value, $convertOptions);
	}
}

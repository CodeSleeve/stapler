<?php namespace Codesleeve\Stapler\File\Image;

use Imagine\Image\Box;
use Imagine\Image\Point;

class Resizer
{
	
	/**
	 * Instance of Imagine Interface.
	 * 
	 * @var mixed
	 */
	protected $imagine;

	/**
	 * Constructor method
	 * 
	 * @param mixed $imagine
	 */
	function __construct($imagine) {
		$this->imagine = $imagine;
	}

	/**
	 * Resize an image using the computed settings.
	 *
	 * @param  UploadedFile $file
	 * @param  stdClass $style
	 * @param  int $defaultQuality
	 * @return void           
	 */
	public function resize($file, $style, $defaultQuality = null)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'STP') . '.' . $file->getClientOriginalName();
		$defaultQuality = is_numeric($defaultQuality) ? $defaultQuality : 75;
		list($width, $height, $option, $quality) = $this->parseStyleDimensions($style, $defaultQuality);
		$method = "resize" . ucfirst($option);
		
		if ($method == 'resizeCustom') {
			$this->resizeCustom($file, $style->value)
				->save($filePath, array('quality' => $quality));
		}
		else {
			$this->$method($file, $width, $height)
				->save($filePath, array('quality' => $quality));
		}
		
		return $filePath;
	}

	/**
	 * parseStyleDimensions method 
	 * 
	 * Parse the given style dimensions to extract out the file processing options,
	 * perform any necessary image resizing for a given style.
	 *
	 * @param  stdClass $style
	 * @param  int $defaultQuality
	 * @return array
	 */
	protected function parseStyleDimensions($style, $defaultQuality)
	{
		if (is_callable($style->value)) {
			return [null, null, 'custom', $defaultQuality];
		}

		preg_match('/^(?<width>\d+)?(?:x(?<height>\d+))?(?<resizing>[#!])?(?:@(?<quality>\d+))?$/i', $style->value, $matches);

		$width = empty($matches['width']) ? null : $matches['width'];
		$height = empty($matches['height']) ? null : $matches['height'];

		$qualityOption = empty($matches['quality']) ? $defaultQuality : $matches['quality'];

		if (!$height)
		{
			// Width given, height automagically selected to preserve aspect ratio (landscape).
			return [$width, null, 'landscape', $qualityOption];
		}

		if (!$width)
		{
			// Height given, width automagically selected to preserve aspect ratio (portrait).
			return [null, $height, 'portrait', $qualityOption];
		}

		$resizingOption = empty($matches['resizing']) ? false : $matches['resizing'];

		if ($resizingOption == '#') 
		{
			// Resize, then crop.
			return [$width, $height, 'crop', $qualityOption];
		}

		if ($resizingOption == '!')
		{
			// Resize by exact width/height (does not preserve aspect ratio).
			return [$width, $height, 'exact', $qualityOption];
		}

		// Let the script decide the best way to resize.
		return [$width, $height, 'auto', $qualityOption];
	}

	/**
	 * Resize an image as a landscape (width only)
	 *
	 * @param  UploadedFile $file
	 * @param  string $width
	 * @param  string $height
	 * @return Imagine\Image
	 */
	protected function resizeLandscape($file, $width, $height)
	{
		$image = $this->imagine
			->open($file->getRealPath());

		$dimensions = $image->getSize()
			->widen($width);

		$image = $image->resize($dimensions);

		return $image;
	}

	/**
	 * Resize an image as a portrait (height only)
	 *
	 * @param  UploadedFile $file
	 * @param  string $width
	 * @param  string $height
	 * @return Imagine\Image
	 */
	protected function resizePortrait($file, $width, $height)
	{
		$image = $this->imagine
			->open($file->getRealPath());

		$dimensions = $image->getSize()
			->heighten($height);

		$image = $image->resize($dimensions);

		return $image;
	}

	/**
	 * Resize an image and then center crop it.
	 *
	 * @param  UploadedFile $file
	 * @param  string $width
	 * @param  string $height
	 * @return Imagine\Image
	 */
	protected function resizeCrop($file, $width, $height)
	{
		$image = $this->imagine->open($file->getRealPath());
		list($optimalWidth, $optimalHeight) = $this->getOptimalCrop($image->getSize(), $width, $height);

		// Find center - this will be used for the crop
		$centerX = ($optimalWidth  / 2) - ($width  / 2);
		$centerY = ($optimalHeight / 2) - ($height / 2);
		
		return $image->resize(new Box($optimalWidth, $optimalHeight))
			->crop(new Point($centerX, $centerY), new Box($width, $height));
	}

	/**
	 * Resize an image to an exact width and height.
	 *
	 * @param  UploadedFile $file
	 * @param  string $width
	 * @param  string $height
	 * @return Imagine\Image
	 */
	protected function resizeExact($file, $width, $height)
	{
		return $this->imagine
			->open($file->getRealPath())
			->resize(new Box($width, $height));
	}

	/**
	 * Resize an image as closely as possible to a given
	 * width and height while still maintaining aspect ratio.
	 *
	 * @param  UploadedFile $file
	 * @param  string $width
	 * @param  string $height
	 * @return Imagine\Image
	 */
	protected function resizeAuto($file, $width, $height)
	{
		// Image to be resized is wider (landscape)
		if ($height < $width) {
			return $this->resizeLandscape($file, $width, $height);

		}

		// Image to be resized is taller (portrait)
		if ($height > $width){
			return $this->resizePortrait($file, $width, $height);
		}

		// Image to be resizerd is a square
		return $this->resizeExact($file, $width, $height);
	}

	/**
	 * Resize an image using a user defined callback.
	 *
	 * @param  UploadedFile $file
	 * @param  $callable
	 * @return Imagine\Image
	 */
	protected function resizeCustom($file, $callable)
	{
		return call_user_func_array($callable, [$file, $this->imagine]);
	}

	/**
	 * Attempts to find the best way to crop. 
	 * Takes into account the image being a portrait or landscape.
	 *
	 * @param  Imagine\Image\Box $size - The image's current size.
	 * @param  int $width - The image's new width.
	 * @param  int $height - The image's new height.
	 * @return array
	 */
	protected function getOptimalCrop($size, $width, $height)
	{
		$heightRatio = $size->getHeight() / $height;
		$widthRatio  = $size->getWidth() /  $width;
		
		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} 
		else {
			$optimalRatio = $widthRatio;
		}
		
		$optimalHeight = $size->getHeight() / $optimalRatio;
		$optimalWidth  = $size->getWidth()  / $optimalRatio;
		
		return [$optimalWidth, $optimalHeight];
	}

}
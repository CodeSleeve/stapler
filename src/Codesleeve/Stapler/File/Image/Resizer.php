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
	 * @return void
	 */
	public function resize($file, $style)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'STP') . '.' . $file->getFilename();
		list($width, $height, $option) = $this->parseStyleDimensions($style);
		$method = "resize" . ucfirst($option);

		if ($method == 'resizeCustom') {
			$this->resizeCustom($file, $style->value)
				->save($filePath);
		}
    	else {
      		$this->$method($file, $width, $height)
		       ->save($filePath);
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
	 * @return array
	 */
	protected function parseStyleDimensions($style)
  	{
		if (is_callable($style->value)) {
			return [null, null, 'custom'];
		}

		if (strpos($style->value, 'x') === false)
		{
			// Width given, height automagically selected to preserve aspect ratio (landscape).
			$width = $style->value;

			return [$width, null, 'landscape'];
		}

		$dimensions = explode('x', $style->value);
		$width = $dimensions[0];
		$height = $dimensions[1];

		if (empty($width))
    	{
			// Height given, width automagically selected to preserve aspect ratio (portrait).
			return [null, $height, 'portrait'];
		}

		$resizingOption = substr($height, -1, 1);

		if ($resizingOption == '#')
		{
			// Resize, then crop.
      		$height = rtrim($height, '#');

			return [$width, $height, 'crop'];
		}

		if ($resizingOption == '!')
		{
			// Resize by exact width/height (does not preserve aspect ratio).
			$height = rtrim($height, '!');

			return [$width, $height, 'exact'];
		}

		// Let the script decide the best way to resize.
		return [$width, $height, 'auto'];
	}

	/**
	 * Resize an image as a landscape (width only)
	 *
	 * @param  UploadedFile $file
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
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
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
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
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return Imagine\Image
	 */
	protected function resizeCrop($file, $width, $height)
  	{
		$image = $this->imagine->open($file->getRealPath());
		list($optimalWidth, $optimalHeight) = $this->getOptimalCrop($image->getSize(), $width, $height);

    	// Find center - this will be used for the crop
		$centerX = ($optimalWidth / 2) - ($width / 2);
    	$centerY = ($optimalHeight / 2) - ($height / 2);

		return $image->resize(new Box($optimalWidth, $optimalHeight))
			->crop(new Point($centerX, $centerY), new Box($width, $height));
	}

	/**
	 * Resize an image to an exact width and height.
	 *
	 * @param  UploadedFile $file
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
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
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
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
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return array
	 */
	protected function getOptimalCrop($size, $width, $height)
	{
		$heightRatio = $size->getHeight() / $height;
		$widthRatio  = $size->getWidth() / $width;

		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		}
		else {
			$optimalRatio = $widthRatio;
		}

		$optimalHeight = round($size->getHeight() / $optimalRatio, 2);
		$optimalWidth  = round($size->getWidth() / $optimalRatio, 2);

		return [$optimalWidth, $optimalHeight];
	}

}

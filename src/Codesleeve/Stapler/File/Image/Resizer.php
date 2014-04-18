<?php namespace Codesleeve\Stapler\File\Image;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;

class Resizer
{
	/**
	 * Instance of Imagine Interface.
	 *
	 * @var \Imagine\Image\ImagineInterface
	 */
	protected $imagine;

	/**
	 * Constructor method
	 *
	 * @param \Imagine\Image\ImagineInterface $imagine
	 */
	function __construct(ImagineInterface $imagine) {
		$this->imagine = $imagine;
	}

	/**
	 * Resize an image using the computed settings.
	 *
	 * @param  \Codesleeve\Stapler\File\UploadedFile $file
	 * @param  \Codesleeve\Stapler\Style $style
	 * @return void
	 */
	public function resize($file, $style)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'STP') . '.' . $file->getFilename();
		list($width, $height, $option) = $this->parseStyleDimensions($style);
		$method = "resize" . ucfirst($option);

		if ($method == 'resizeCustom')
		{
			$this->resizeCustom($file, $style->value)
				->save($filePath, $style->convertOptions);

			return $filePath;
		}

  		$image = $this->imagine->open($file->getRealPath());

		if ($style->autoOrient) {
			$image = $this->autoOrient($file->getRealPath(), $image);
		}

  		$this->$method($image, $width, $height)
	       ->save($filePath, $style->convertOptions);

		return $filePath;
	}

    /**
     * Accessor method for the $imagine property.
     *
     * @param ImagineInterface $imagine
     */
    public function setImagine(ImagineInterface $imagine){
        $this->imagine = $imagine;
    }

	/**
	 * parseStyleDimensions method
	 *
	 * Parse the given style dimensions to extract out the file processing options,
	 * perform any necessary image resizing for a given style.
	 *
	 * @param  \Codesleeve\Stapler\Style $style
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
	 * @param  \Imagine\Image\ImageInterface $image
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeLandscape($image, $width, $height)
	{
		$dimensions = $image->getSize()
			->widen($width);

		$image = $image->resize($dimensions);

		return $image;
	}

	/**
	 * Resize an image as a portrait (height only)
	 *
	 * @param  \Imagine\Image\ImageInterface $image
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizePortrait($image, $width, $height)
	{
		$dimensions = $image->getSize()
			->heighten($height);

		$image = $image->resize($dimensions);

		return $image;
	}

	/**
	 * Resize an image and then center crop it.
	 *
	 * @param  \Imagine\Image\ImageInterface $image
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeCrop($image, $width, $height)
  	{
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
	 * @param  \Imagine\Image\ImageInterface $image
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeExact($image, $width, $height)
	{
		return $image->resize(new Box($width, $height));
	}

	/**
	 * Resize an image as closely as possible to a given
	 * width and height while still maintaining aspect ratio.
	 * This method is really just a proxy to other resize methods:
	 *
	 * If the current image is wider than it is tall, we'll resize landscape.
	 * If the current image is taller than it is wide, we'll resize portrait.
	 * If the image is as tall as it is wide (it's a squarey) then we'll
	 * apply the same process using the new dimensions (we'll resize exact if
	 * the new dimensions are both equal since at this point we'll have a square
	 * image being resized to a square).
	 *
	 * @param  \Imagine\Image\ImageInterface $image
	 * @param  string $width - The image's new width.
	 * @param  string $height - The image's new height.
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeAuto($image, $width, $height)
	{
		$size = $image->getSize();
		$originalWidth = $size->getWidth();
		$originalHeight = $size->getHeight();

		if ($originalHeight < $originalWidth) {
			return $this->resizeLandscape($image, $width, $height);
		}

		if ($originalHeight > $originalWidth){
			return $this->resizePortrait($image, $width, $height);
		}

		if ($height < $width) {
			return $this->resizeLandscape($image, $width, $height);
		}

		if ($height > $width){
			return $this->resizePortrait($image, $width, $height);
		}

		return $this->resizeExact($image, $width, $height);
	}

	/**
	 * Resize an image using a user defined callback.
	 *
	 * @param  UploadedFile $file
	 * @param  $callable
	 * @return \Imagine\Image\ImageInterface
	 */
	protected function resizeCustom($file, $callable)
	{
		return call_user_func_array($callable, [$file, $this->imagine]);
	}

	/**
	 * Attempts to find the best way to crop.
	 * Takes into account the image being a portrait or landscape.
	 *
	 * @param  \Imagine\Image\Box $size - The image's current size.
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

	/**
	 * Re-orient an image using its embedded Exif profile orientation:
	 * 1. Read the embedded exif data inside the image to determine it's orientation.
	 * 2. Rotate and flip the image accordingly to re-orient it.
	 * 3. Strip the Exif data from the image so that there can be no attempt to 'correct' it again.
	 *
	 * @param  string $path
	 * @param  \Imagine\Image\ImageInterface $image
	 * @return \Imagine\Image\ImageInterface $image
	 */
	protected function autoOrient($path, $image)
	{
		$exif = exif_read_data($path);

		if (isset($exif['Orientation']))
		{
		    switch($exif['Orientation']) {
		        case 2:
		            $image->flipHorizontally();
		            break;
		        case 3:
		            $image->rotate(180);
		            break;
		        case 4:
		            $image->flipVertically();
		            break;
		        case 5:
		            $image->flipVertically()
		            	->rotate(90);
		            break;
		        case 6:
		            $image->rotate(90);
		            break;
		        case 7:
		        	$image->flipHorizontally()
		        		->rotate(90);
		        	break;
		        case 8:
		            $image->rotate(-90);
		            break;
		    }
		}

		return $image->strip();
	}

}

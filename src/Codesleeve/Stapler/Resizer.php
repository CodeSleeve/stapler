<?php namespace Codesleeve\Stapler;

use Symfony\Component\HttpFoundation\File\File;
/**
 * Provides a very simple way to resize an image.
 * 
 * Credits to Jarrod Oberto.
 * Jarrod wrote a tutorial on NetTuts.
 * http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
 * 
 * I only turned it into a Laravel bundle.
 * 
 * @package Resizer
 * @version 1.0
 * @author Maikel D (original author Jarrod Oberto)
 * @link 
 * @example
 * 		Resizer::open(mixed $file)
 *			->resize(int $width , int $height , string 'exact, portrait, landscape, auto or crop')
 *			->save(string 'path/to/file.jpg' , int $quality);
 *		
 *		// Resize and save an image.
 * 		Resizer::open(Input::file('field_name'))
 *			->resize(800 , 600 , 'crop')
 *			->save('path/to/file.jpg' , 100);
 *		
 *		// Recompress an image.
 *		Resizer::open('path/to/image.jpg')
 *			->save('path/to/new_image.jpg' , 60);
 */
class Resizer {
	
	/**
	 * The symfony uploaded file object.
	 * 
	 * @var Resource
	 */
	private $file;

	/**
	 * The extension of the uploaded file.
	 * 
	 * @var Resource
	 */
	private $extension;

	/**
	 * The image (on disk) that's being resized.
	 * 
	 * @var Resource
	 */
	private $image;
	
	/**
	 * Original width of the image being resized.
	 * 
	 * @var int
	 */
	private $width;
	
	/**
	 * Original height of the image being resized.
	 * 
	 * @var int
	 */
	private $height;
	
	/**
	 * The cached, resized image.
	 * 
	 * @var Resource
	 */
	private $imageResized;
	
	/**
	 * Instantiates the Resizer and receives the path to an image we're working with
	 * 
	 * @param mixed $file The file array provided by Laravel's Input::file('field_name') or a path to a file
	 */
	function __construct($file)
	{
		// Store the file extension
		$this->extension = $file->guessExtension();

		// Open up the file
		$this->image = $this->openImage($file);
		
		// Get width and height of our image
		$this->width  = imagesx($this->image);
		$this->height = imagesy($this->image);
	}
	
	/**
	 * Static call, Laravel style.
	 * 
	 * Returns a new Resizer object, allowing for chainable calls
	 * @param  mixed $file The file array provided by Laravel's Input::file('field_name') or a path to a file
	 * @return Resizer
	 */
	public static function open($file)
	{
		return new Resizer($file);
	}
	
	/**
	 * Resizes and/or crops an image
	 * 
	 * @param  int    $newWidth  The width of the image
	 * @param  int    $newHeight The height of the image
	 * @param  string $option     Either exact, portrait, landscape, auto or crop.
	 * @return [type]
	 */
	public function resize($newWidth , $newHeight , $option = 'auto')
	{
		// Get optimal width and height - based on $option.
		$optionsArray = $this->getDimensions($newWidth , $newHeight , $option);
		
		$optimalWidth	= $optionsArray['optimalWidth'];
		$optimalHeight	= $optionsArray['optimalHeight'];
		
		// Resample - create image canvas of x, y size.
		$this->imageResized = imagecreatetruecolor( $optimalWidth  , $optimalHeight );
		
		// Retain transparency for PNG and GIF files.
		imagecolortransparent($this->imageResized , imagecolorallocatealpha($this->imageResized , 0 , 0 , 0 , 127));
		imagealphablending($this->imageResized , false);
		imagesavealpha($this->imageResized , true);
		
		// Create the new image.
		imagecopyresampled($this->imageResized , $this->image , 0 , 0 , 0 , 0 , $optimalWidth , $optimalHeight , $this->width , $this->height);
		
		// if option is 'crop', then crop too
		if ($option == 'crop') {
			$this->crop($optimalWidth , $optimalHeight , $newWidth , $newHeight);
		}
		
		// Return $this to allow calls to be chained
		return $this;
	}
	
	/**
	 * Save the image based on its file type.
	 * 
	 * @param  string $savePath     Where to save the image
	 * @param  int    $image_quality The output quality of the image
	 * @return boolean
	 */
	public function save($savePath , $image_quality = 95)
	{
		// If the image wasn't resized, fetch original image.
		if (!$this->imageResized) {
			$this->imageResized = $this->image;
		}

		// Create and save an image based on it's extension
		switch($this->extension)
		{
			case 'jpg':
			case 'jpeg':
				if (imagetypes() & IMG_JPG) {
					imagejpeg($this->imageResized , $savePath , $image_quality);
				}
				break;
				
			case 'gif':
				if (imagetypes() & IMG_GIF) {
					imagegif($this->imageResized , $savePath);
				}
				break;
				
			case 'png':
				// Scale quality from 0-100 to 0-9
				$scaleQuality = round(($image_quality/100) * 9);
				
				// Invert quality setting as 0 is best, not 9
				$invertScaleQuality = 9 - $scaleQuality;
				
				if (imagetypes() & IMG_PNG) {
					imagepng($this->imageResized , $savePath , $invertScaleQuality);
				}
				break;
				
			default:
				throw new Exception('Invalid image type', 1);
				break;
		}
		
		// Remove the resource for the resized image
		imagedestroy($this->imageResized);
	}
	
	/**
	 * Open a file, detect its mime-type and create an image resrource from it.
	 * 
	 * @param  array $file Attributes of file from the $_FILES array
	 * @return mixed
	 */
	private function openImage($file)
	{
		$mime = $file->getMimeType();
		$filePath = $file->getPathname();
		switch ($mime)
		{
			case 'image/pjpeg': // IE6
			case 'image/jpeg':	$img = @imagecreatefromjpeg($filePath);		break;
			case 'image/gif':	$img = @imagecreatefromgif($filePath);		break;
			case 'image/png':	$img = @imagecreatefrompng($filePath);		break;
			default:			$img = false;								break;
		}
		
		return $img;
	}
	
	/**
	 * Return the image dimensions based on the option that was chosen.
	 * 
	 * @param  int    $newWidth  The width of the image
	 * @param  int    $newHeight The height of the image
	 * @param  string $option     Either exact, portrait, landscape, auto or crop.
	 * @return array
	 */
	private function getDimensions($newWidth , $newHeight , $option)
	{
		switch ($option)
		{
			case 'exact':
				$optimalWidth	= $newWidth;
				$optimalHeight	= $newHeight;
				break;
			case 'portrait':
				$optimalWidth	= $this->getSizeByFixedHeight($newHeight);
				$optimalHeight	= $newHeight;
				break;
			case 'landscape':
				$optimalWidth	= $newWidth;
				$optimalHeight	= $this->getSizeByFixedWidth($newWidth);
				break;
			case 'auto':
				$optionsArray	= $this->getSizeByAuto($newWidth , $newHeight);
				$optimalWidth	= $optionsArray['optimalWidth'];
				$optimalHeight	= $optionsArray['optimalHeight'];
				break;
			case 'crop':
				$optionsArray	= $this->getOptimalCrop($newWidth , $newHeight);
				$optimalWidth	= $optionsArray['optimalWidth'];
				$optimalHeight	= $optionsArray['optimalHeight'];
				break;
		}
		
		return array(
			'optimalWidth' => $optimalWidth,
			'optimalHeight' => $optimalHeight
		);
	}
	
	/**
	 * Returns the width based on the image height
	 * 
	 * @param  int    $newHeight The height of the image
	 * @return int
	 */
	private function getSizeByFixedHeight($newHeight)
	{
		$ratio = $this->width / $this->height;
		$newWidth = $newHeight * $ratio;
		
		return $newWidth;
	}
	
	/**
	 * Returns the height based on the image width
	 * 
	 * @param  int    $newWidth The width of the image
	 * @return int
	 */
	private function getSizeByFixedWidth($newWidth)
	{
		$ratio = $this->height / $this->width;
		$newHeight = $newWidth * $ratio;
		
		return $newHeight;
	}
	
	/**
	 * Checks to see if an image is portrait or landscape and resizes accordingly.
	 * 
	 * @param  int    $newWidth  The width of the image
	 * @param  int    $newHeight The height of the image
	 * @return array
	 */
	private function getSizeByAuto($newWidth , $newHeight)
	{
		// Image to be resized is wider (landscape)
		if ($this->height < $this->width)
		{
			$optimalWidth = $newWidth;
			$optimalHeight= $this->getSizeByFixedWidth($newWidth);
		}
		// Image to be resized is taller (portrait)
		elseif ($this->height > $this->width)
		{
			$optimalWidth = $this->getSizeByFixedHeight($newHeight);
			$optimalHeight= $newHeight;
		}
		// Image to be resizerd is a square
		else
		{
			if ($newHeight < $newWidth) {
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);
			} else if ($newHeight > $newWidth) {
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
			} else {
				// Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
			}
		}
		
		return array(
			'optimalWidth' => $optimalWidth,
			'optimalHeight' => $optimalHeight
		);
	}
	
	/**
	 * Attempts to find the best way to crop. Whether crop is based on the
	 * image being portrait or landscape.
	 * 
	 * @param  int    $newWidth  The width of the image
	 * @param  int    $newHeight The height of the image
	 * @return array
	 */
	private function getOptimalCrop($newWidth , $newHeight)
	{
		$heightRatio = $this->height / $newHeight;
		$widthRatio  = $this->width /  $newWidth;
		
		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} else {
			$optimalRatio = $widthRatio;
		}
		
		$optimalHeight = $this->height / $optimalRatio;
		$optimalWidth  = $this->width  / $optimalRatio;
		
		return array(
			'optimalWidth' => $optimalWidth,
			'optimalHeight' => $optimalHeight
		);
	}
	
	/**
	 * Crops an image from its center
	 * 
	 * @param  int    $optimalWidth  The width of the image
	 * @param  int    $optimalHeight The height of the image
	 * @param  int    $newWidth      The new width
	 * @param  int    $newHeight     The new height
	 * @return true
	 */
	private function crop($optimalWidth , $optimalHeight , $newWidth , $newHeight)
	{
		// Find center - this will be used for the crop
		$cropStartX = ($optimalWidth  / 2) - ($newWidth  / 2);
		$cropStartY = ($optimalHeight / 2) - ($newHeight / 2);
		
		$crop = $this->imageResized;
		
		// Now crop from center to exact requested size
		$this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
		imagecopyresampled($this->imageResized , $crop , 0 , 0 , $cropStartX , $cropStartY , $newWidth , $newHeight  , $newWidth , $newHeight);
		
		return true;
	}
	
}
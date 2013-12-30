<?php namespace Codesleeve\Stapler\File;

class UploadedFile extends \Symfony\Component\HttpFoundation\File\UploadedFile
{
	/**
	 * An array of key value pairs for valid image
	 * extensions and their associated MIME types.
	 * 
	 * @var array
	 */
	protected $imageMimes = [
		'bmp'   => 'image/bmp',
		'gif'   => 'image/gif',
		'jpeg'  => array('image/jpeg', 'image/pjpeg'),
		'jpg'   => array('image/jpeg', 'image/pjpeg'),
		'jpe'   => array('image/jpeg', 'image/pjpeg'),
		'png'   => 'image/png',
		'tiff'  => 'image/tiff',
		'tif'   => 'image/tiff',
	];

	/**
	 * Utility method for detecing whether a given file upload is an image.
	 *
	 * @return bool
	 */
	public function isImage()
	{
		$mime = $this->getMimeType();
		
		// The $imageMimes property contains an array of file extensions and
		// their associated MIME types. We will loop through them and look for 
		// the MIME type of the current UploadedFile.
		foreach ($this->imageMimes as $imageMime)
		{
			if (in_array($mime, (array) $imageMime))
			{
				return true;
			}
		}

		return false;
	}

}

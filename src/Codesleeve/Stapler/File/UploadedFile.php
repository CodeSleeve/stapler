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

	/**
     * Returns an informative upload error message.
     *
     * @param int $code The error code returned by an upload attempt
     * @return string The error message regarding the specified error code
     */
    public function getErrorMessage($errorCode = null)
    {
		$errorCode = $errorCode ?: $this->$error;

		static $errors = [
			UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
			UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
			UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
			UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
			UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
			UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
			UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a php extension.',
		];

		$maxFilesize = $errorCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
		$message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

		return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }

}
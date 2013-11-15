<?php namespace Codesleeve\Stapler\File;

use Illuminate\Support\Facades\Config as Config;

class UploadedFile extends \Symfony\Component\HttpFoundation\File\UploadedFile
{
	/**
	 * Utility method for detecing whether a given file upload is an image.
	 *
	 * @return bool
	 */
	public function isImage()
	{
		$extensions = ['jpg', 'jpeg', 'gif', 'png'];
		$mimes = Config::get('stapler::mimes');
		$mime = $this->getMimeType();
		
		// The MIME configuration file contains an array of file extensions and
		// their associated MIME types. We will loop through each extension and look for the MIME type.
		foreach ($extensions as $extension)
		{
			if (isset($mimes[$extension]) and in_array($mime, (array) $mimes[$extension]))
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
     *
     * @return string The error message regarding the specified error code
     */
    public function getErrorMessage($errorCode)
    {
        static $errors = array(
            UPLOAD_ERR_INI_SIZE   => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
            UPLOAD_ERR_FORM_SIZE  => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL    => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION  => 'File upload was stopped by a php extension.',
        );

        $maxFilesize = $errorCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

       return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }

}
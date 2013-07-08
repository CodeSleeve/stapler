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

}
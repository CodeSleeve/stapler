<?php namespace Codesleeve\Stapler\File\Mime;

use Codesleeve\Stapler\Exceptions\UnknownFileExtensionException;

class MimeType
{
	/**
     * @var array
     */
    static $mimeTypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // Images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // Audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // Adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // MS Office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // Open Office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    /**
	 * Return the mime type of a file.
	 * 
	 * @param  string $fileName 
	 * @return string
	 */
	public static function guess($fileName) 
	{
		$pathInfo = pathinfo($filename);
		if (!array_key_exists('extension', $pathinfo)) {
			return static::guessWithoutExtension($filename);
		}

        $extension = strtolower($pathInfo['extension']);
        if (array_key_exists($extension, self::$mimeTypes)) {
            return self::$mimeTypes[$extension];
        }

        return static::guessWithoutExtension($filename);
	}

	/**
	 * Return the mime type of a file without relying upon the 
	 * filename extension.
	 * 
	 * @param  string $filename 
	 * @return string
	 */
	public static function guessWithoutExtension($filename) 
	{
		if (function_exists('finfo_open') && is_file($filename)) 
        {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        } 

    	return 'application/octet-stream';
	}

	/**
	 * Return the file extension for a give mime type.
	 * 
	 * @param  string $mimeType
	 * @throws \Codesleeve\Stapler\Exceptions\UnknownFileExtensionException
	 * @return string
	 */
	public static function getExtensionFromMime($mimeType) 
	{
		$extension = array_search($mimeType, static::$mimeTypes);

		if (!$extension) {
			throw new UnknownFileExtensionException("Unable to find a file extension for MIME type $mimeType");
		}

		return $extension;
	}
}
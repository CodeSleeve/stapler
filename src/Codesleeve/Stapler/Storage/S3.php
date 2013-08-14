<?php namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\Exceptions;
use Aws\S3\S3Client;
use Config;

class S3 implements StorageInterface
{
	/**
	 * The currenty attachedFile object being processed.
	 * 
	 * @var Codesleeve\Stapler\Attachment
	 */
	protected $attachedFile;

	/**
	 * An AWS S3Client instance.
	 * 
	 * @var S3Client
	 */
	protected $s3Client;

	/**
	 * Constructor method
	 * 
	 * @param Codesleeve\Stapler\Attachment $attachedFile
	 */
	function __construct($attachedFile)
	{
		$this->attachedFile = $attachedFile;
		$this->s3Client = S3Client::factory([
			'key' => $attachedFile->key, 
			'secret' => $attachedFile->secret, 
			'region' => $attachedFile->region, 
			'scheme' => $attachedFile->scheme
		]);
	}

	/**
	 * Return the url for a file upload.
	 * 
	 * @param  string $styleName 
	 * @return string          
	 */
	public function url($styleName)
	{
		return $this->s3Client->getObjectUrl('stapler.test.bucket', $this->path($styleName));
	}

	/**
	 * Return the key the uploaded file object is stored under within a bucket.
	 * 
	 * @param  string $styleName 
	 * @return string          
	 */
	public function path($styleName)
	{
		return $this->attachedFile->getInterpolator()->interpolate($this->attachedFile->path, $this->attachedFile, $styleName);
	}

	/**
	 * Reset an attached file
	 *
	 * @return void
	 */
	public function reset()
	{
		
	}

	/**
	 * Remove an attached file.
	 * 
	 * @param  Codesleeve\Stapler\Attachment $attachedFile
	 * @return void
	 */
	public function remove()
	{
		
	}

	/**
	 * Utility function to return the base directory of the uploaded file for 
	 * a file attachment.
	 * 
	 * @return string               
	 */
	public function findDirectory()
	{
		
	}

	/**
	 * Determine if a style directory needs to be built and if so create it.
	 *
	 * @param  string $styleName
	 * @return void
	 */
	public function buildDirectory($styleName)
	{
		
	}

	/**
	 * Determine if a style directory needs to be cleaned (emptied) and if so empty it.
	 *
	 * @param  string $styleName
	 * @return void
	 */
	public function cleanDirectory($styleName)
	{
		
	}

	/**
	 * Recursively delete the files in a directory.
	 *
	 * @desc Recursively loops through each file in the directory and deletes it.
	 * @param string $directory
	 * @param boolean $deleteDirectory
	 * @return void
	 */
	public function emptyDirectory($directory, $deleteDirectory = false)
	{
		
	}

	/**
	 * Move an uploaded file to it's intended destination
	 *
	 * @param  Symfony\Component\HttpFoundation\File\UploadedFile $file 
	 * @param  string $filePath 
	 * @return void 
	 */
	public function move($file, $filePath, $overrideFilePermissions)
	{
		
	}
}
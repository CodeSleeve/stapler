<?php namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\File\UploadedFile;
use Aws\S3\S3Client;
use Config;

class S3 implements StorageInterface
{
	/**
	 * The current attachedFile object being processed.
	 * 
	 * @var Codesleeve\Stapler\Attachment
	 */
	public $attachedFile;

	/**
	 * An AWS S3Client instance.
	 * 
	 * @var S3Client
	 */
	protected $s3Client;

	/**
	 * Boolean flag indicating if this attachment's bucket currently exists.
	 * 
	 * @var array
	 */
	protected $bucketExists = false;

	/**
	 * Constructor method
	 * 
	 * @param Codesleeve\Stapler\Attachment $attachedFile
	 */
	function __construct($attachedFile)
	{
		$this->attachedFile = $attachedFile;
	}

	/**
	 * Return the url for a file upload.
	 * 
	 * @param  string $styleName 
	 * @return string          
	 */
	public function url($styleName)
	{
		return $this->getS3Client()->getObjectUrl($this->getBucket(), $this->path($styleName));
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
	 * Remove an attached file.
	 * 
	 * @param  array $filePaths
	 * @return void
	 */
	public function remove($filePaths)
	{
		if ($filePaths) {
			$this->getS3Client()->deleteObjects(['Bucket' => $this->getBucket(), 'Objects' => $this->getKeys($filePaths)]);
		}
	}

	/**
	 * Move an uploaded file to it's intended destination.
	 * The file can be an actual uploaded file object or the path to
	 * a resized image file on disk.
	 *
	 * @param  UploadedFile $file 
	 * @param  string $filePath
	 * @return void 
	 */
	public function move($file, $filePath)
	{
 		$this->getS3Client()->putObject(['Bucket' => $this->getBucket(), 'Key' => $filePath, 'SourceFile' => $file, 'ContentType' => $this->attachedFile->contentType(), 'ACL' => $this->attachedFile->ACL]);
	}

	/**
	 * Return an array of paths (bucket keys) for an attachment.
	 * There will be one path for each of the attachmetn's styles.
	 *
	 * @param  $filePaths
	 * @return array
	 */
	protected function getKeys($filePaths)
	{
		$keys = [];

		foreach ($filePaths as $filePath) {
			$keys[] = ['Key' => $filePath];
		}

		return $keys;
	}

	/**
	 * This is a wrapper method for returning the name of an attachment's bucket.
	 * If the bucket doesn't exist we'll build it first before returning it's name.
	 * 
	 * @return string
	 */
	protected function getBucket()
	{
		$bucketName = $this->attachedFile->bucket;
		if (!$this->bucketExists) {
			$this->buildBucket($bucketName);
		}

		return $bucketName;
	}

	/**
	 * Attempt to build a bucket (if it doesn't already exist).
	 * 
	 * @param  string $bucketName
	 * @return void
	 */
	protected function buildBucket($bucketName)
	{
		if (!$this->getS3Client()->doesBucketExist($bucketName, true)) {
			$this->getS3Client()->createBucket(['ACL' => $this->attachedFile->ACL, 'Bucket' => $bucketName, 'LocationConstraint' => $this->attachedFile->region]);
		}

		$this->bucketExists = true;
	}

	/**
	 * Return the S3Client object this class is using.
	 * If no instance has been defined yet we'll buld one and then
	 * cache it on the S3Client property.  This allows us to lazy load
	 * the S3Client instance only when it's being used.
	 * 	
	 * @return SS3Client
	 */
	protected function getS3Client()
	{
		if ($this->s3Client) {
			return $this->s3Client;
		}

		return $this->s3Client = $this->buildS3Client();
	}

	/**
	 * Build an S3Client instance using the information defined in
	 * this class's attachedFile object.
	 * 
	 * @return S3Client
	 */
	protected function buildS3Client()
	{
		return S3Client::factory([
			'key' => $this->attachedFile->key, 
			'secret' => $this->attachedFile->secret, 
			'region' => $this->attachedFile->region, 
			'scheme' => $this->attachedFile->scheme
		]);
	}
}

<?php namespace Codesleeve\Stapler\Storage;

use Aws\S3\S3Client;
use Codesleeve\Stapler\Attachment;

class S3 implements StorageInterface
{
	/**
	 * The current attachedFile object being processed.
	 *
	 * @var \Codesleeve\Stapler\Attachment
	 */
	public $attachedFile;

    /**
     * The AWS S3Client instance.
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
	 * @param \Codesleeve\Stapler\Attachment $attachedFile
     * @param S3Client $s3Client
	 */
	function __construct(Attachment $attachedFile, S3Client $s3Client)
	{
		$this->attachedFile = $attachedFile;
        $this->s3Client = $s3Client;
	}

	/**
	 * Return the url for a file upload.
	 *
	 * @param  string $styleName
	 * @return string
	 */
	public function url($styleName)
	{
		return $this->s3Client->getObjectUrl($this->getBucket(), $this->path($styleName));
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
			$this->s3Client->deleteObjects(['Bucket' => $this->getBucket(), 'Objects' => $this->getKeys($filePaths)]);
		}
	}

	/**
	 * Move an uploaded file to it's intended destination.
	 * The file can be an actual uploaded file object or the path to
	 * a resized image file on disk.
	 *
	 * @param  \Codesleeve\Stapler\File\UploadedFile $file
	 * @param  string $filePath
	 * @return void
	 */
	public function move($file, $filePath)
	{
 		$this->s3Client->putObject(['Bucket' => $this->getBucket(), 'Key' => $filePath, 'SourceFile' => $file, 'ContentType' => $this->attachedFile->contentType(), 'ACL' => $this->attachedFile->ACL]);
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
		if (!$this->s3Client->doesBucketExist($bucketName, true)) {
			$this->s3Client->createBucket(['ACL' => $this->attachedFile->ACL, 'Bucket' => $bucketName, 'LocationConstraint' => $this->attachedFile->region]);
		}

		$this->bucketExists = true;
	}
}

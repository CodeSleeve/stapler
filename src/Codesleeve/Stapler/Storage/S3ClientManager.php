<?php namespace Codesleeve\Stapler\Storage;

use Aws\S3\S3Client;

class S3ClientManager
{
	/**
	 * A key value store of S3 clients.
	 * 
	 * @var array
	 */
	protected $s3Clients = [];

	/**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
	 * Return an S3Client object for a specific attachment type.
	 * If no instance has been defined yet we'll buld one and then
	 * cache it on the s3Clients property (for the current request only).
	 *
	 * @param  Codesleeve\Stapler\Attachment $attachedFile
	 * @return SS3Client
	 */
	public function getS3Client($attachedFile)
	{
		$modelName = $attachedFile->getInstanceClass();
		$attachmentName = $attachedFile->getConfig()->attachmentName;
		$key = "$modelName.$attachmentName";

		if (array_key_exists($key, $this->s3Clients)) {
			return $this->s3Clients[$key];
		}

		$this->s3Clients[$key] = $this->buildS3Client($attachedFile);

		return $this->s3Clients[$key];
	}

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     *
     * @return void
     */
    protected function __construct()
    {
        
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

	/**
	 * Build an S3Client instance using the information defined in
	 * this class's attachedFile object.
	 * 
	 * @return S3Client
	 */
	protected function buildS3Client($attachedFile)
	{
		return S3Client::factory([
			'key' => $attachedFile->key, 
			'secret' => $attachedFile->secret, 
			'region' => $attachedFile->region, 
			'scheme' => $attachedFile->scheme
		]);
	}
}
<?php namespace Codesleeve\Stapler\Storage;

interface StorageableInterface
{
    /**
	 * Return the url for a file upload.
	 *
	 * @param  string $styleName
	 * @return string
	 */
	public function url($styleName);

	/**
	 * For filesystem storage this method returns the path (on disk) of a file upload.
	 * For s3 storage this method returns the key an uploaded object is stored under in a bucket.
	 *
	 * @param  string $styleName
	 * @return string
	 */
	public function path($styleName);

    /**
	 * Remove an attached file.
	 *
	 * @param array $filePaths
	 * @return void
	 */
    public function remove(array $filePaths);

    /**
	 * Move an uploaded file to it's intended destination.
	 * The file can be an actual uploaded file object or the path to
	 * a resized image file on disk.
	 *
	 * @param  string $file
	 * @param  string $filePath
	 * @return void
	 */
	public function move($file, $filePath);
}
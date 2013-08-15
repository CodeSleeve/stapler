<?php namespace Codesleeve\Stapler\Storage;

interface StorageInterface
{
    /**
	 * Return the url for a file upload.
	 * 
	 * @param  string $styleName 
	 * @return string          
	 */
	public function url($styleName);

	/**
	 * For filesystem storge this method returns the path (on disk) of a file upload.
	 * For s3 storage this method returns the key an uploaded object is stored under in a bucket.
	 * 
	 * @param  string $styleName 
	 * @return string          
	 */
	public function path($styleName);

    /**
	 * Reset an attached file
	 *
	 * @return void
	 */
    public function reset();

    /**
	 * Remove an attached file.
	 * 
	 * @return void
	 */
    public function remove();

    /**
	 * Move an uploaded file to it's intended destination.
	 * The file can be an actual uploaded file object or the path to
	 * a resized image file on disk.
	 *
	 * @param  UploadedFile $file 
	 * @param  string $style
	 * @param  mixed $overrideFilePermissions
	 * @return void 
	 */
	public function move($file, $style, $overrideFilePermissions);
}
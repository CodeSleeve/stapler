<?php namespace Codesleeve\Stapler\Storage;

use Codesleeve\Stapler\Exceptions;
use Codesleeve\Stapler\Attachment;

class Filesystem implements StorageableInterface
{
	/**
	 * The current attachedFile object being processed.
	 *
	 * @var \Codesleeve\Stapler\Attachment
	 */
	public $attachedFile;

	/**
	 * Constructor method
	 *
	 * @param Attachment $attachedFile
	 */
	function __construct(Attachment $attachedFile)
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
		return $this->attachedFile->getInterpolator()->interpolate($this->attachedFile->url, $this->attachedFile, $styleName);
	}

	/**
	 * Return the path (on disk) of a file upload.
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
	 * @param array $filePaths
	 */
	public function remove(array $filePaths)
	{
		foreach ($filePaths as $filePath) {
			$directory = dirname($filePath);
			if (file_exists($filePath)) {
				unlink($filePath);
				$this->cleanEmptyDirectory($directory);
			}
		}
	}

	/**
	 * Move an uploaded file to it's intended destination.
	 * The file can be an actual uploaded file object or the path to
	 * a resized image file on disk.
	 *
	 * @param  string $file
	 * @param  string $filePath
	 */
	public function move($file, $filePath)
	{
 		$this->buildDirectory($filePath);
 		$this->moveFile($file, $filePath);
        $this->setPermissions($filePath, $this->attachedFile->override_file_permissions);
	}

	/**
	 * Determine if a style directory needs to be built and if so create it.
	 *
	 * @param  string $filePath
	 */
	protected function buildDirectory($filePath)
	{
		$directory = dirname($filePath);

		if (!is_dir($directory)) {
			mkdir($directory, 0777, true);
		}
	}

	/**
	 * Set the file permissions of a file upload
	 * Does not ignore umask.
	 *
	 * @param string $filePath
	 * @param integer $overrideFilePermissions
	 */
	protected function setPermissions($filePath, $overrideFilePermissions)
	{
		if ($overrideFilePermissions) {
			chmod($filePath, $overrideFilePermissions & ~umask());
		}
		elseif (is_null($overrideFilePermissions)) {
			chmod($filePath, 0666 & ~umask());
		}
	}

	/**
	 * Attempt to move and uploaded file to it's intended location on disk.
	 *
	 * @param  string $file
	 * @param  string $filePath
     * @throws Exceptions\FileException
	 */
	protected function moveFile($file, $filePath)
	{
		if (!rename($file, $filePath))
        {
            $error = error_get_last();
            throw new Exceptions\FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $file, $filePath, strip_tags($error['message'])));
        }
	}

	/**
	 * Recursively delete empty directories.
	 *
	 * @desc Recursively loops through each file in the directory and deletes it.
	 * @param string $directory
	 */
	protected function cleanEmptyDirectory($directory)
	{
		$iterator = new \FilesystemIterator($directory);
		$isDirEmpty = !$iterator->valid();
		if ($isDirEmpty) {
			rmdir($directory);
			$directory = dirname($directory);
			return $this->cleanEmptyDirectory($directory);
		} else {
			return true;
		}
	}
}
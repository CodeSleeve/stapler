<?php namespace Codesleeve\Stapler\Storage;

interface StorageInterface
{
    /**
	 * Reset an attached file
	 *
	 * @return void
	 */
    public function reset();

    /**
	 * Remove an attached file.
	 * 
	 * @param  Codesleeve\Stapler\Attachment $attachedFile
	 * @return void
	 */
    public function remove();

    /**
	 * Utility function to return the base directory of the uploaded file for 
	 * a file attachment.
	 * 
	 * @return string               
	 */
    public function findDirectory();

    /**
	 * Determine if a style directory needs to be built and if so create it.
	 *
	 * @param  string $styleName
	 * @return void
	 */
    public function buildDirectory($styleName);

    /**
	 * Determine if a style directory needs to be cleaned (emptied) and if so empty it.
	 *
	 * @param  string $styleName
	 * @return void
	 */
    public function cleanDirectory($styleName);

    /**
	 * Function to recursively delete the files in a directory.
	 *
	 * @desc Recursively loops through each file in the directory and deletes it.
	 * @param string $directory
	 * @param boolean $deleteDirectory
	 * @return void
	 */
    public function emptyDirectory($directory, $deleteDirectory = false);

    /**
	 * Move an uploaded file to it's intended destination
	 *
	 * @param  Symfony\Component\HttpFoundation\File\UploadedFile $file 
	 * @param  string $filePath 
	 * @return void 
	 */
	public function move($file, $filePath, $mode);
}
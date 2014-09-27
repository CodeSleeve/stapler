<?php namespace Codesleeve\Stapler\File;

interface FileInterface
{
    /**
	 * Return the name of the file.
	 *
	 * @return string
	 */
	public function getFilename();

	/**
	 * Return the size of the file.
	 *
	 * @return string
	 */
	public function getSize();

	/**
	 * Return the mime type of the file.
	 *
	 * @return string
	 */
	public function getMimeType();

	/**
	 * Method for determining whether the uploaded file is
	 * an image type.
	 *
	 * @return boolean
	 */
	public function isImage();
}
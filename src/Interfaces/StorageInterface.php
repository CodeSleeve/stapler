<?php

namespace Codesleeve\Stapler\Interfaces;

interface StorageInterface
{
    /**
     * Return the url for a file upload.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function url(string $styleName) : string;

    /**
     * For filesystem storage this method returns the path (on disk) of a file upload.
     * For s3 storage this method returns the key an uploaded object is stored under in a bucket.
     *
     * @param string $styleName
     *
     * @return string
     */
    public function path(string $styleName) : string;

    /**
     * Remove an attached file.
     *
     * @param array $filePaths
     */
    public function remove(array $filePaths);

    /**
     * Move an uploaded file to it's intended destination.
     * The file can be an actual uploaded file object or the path to
     * a resized image file on disk.
     *
     * @param string $file
     * @param string $filePath
     */
    public function move(string $file, string $filePath);

    /**
     * Rename and uploaded file.
     *
     * @param  string $oldName
     * @param  string $newName
     *
     * @return void
     */
    public function rename(string $oldName, string $newName);

    /**
     * Determine if an uploaded file exists.
     *
     * @param  string  $filePath
     * @return boolean
     */
    public function has(string $filePath) : bool;
}
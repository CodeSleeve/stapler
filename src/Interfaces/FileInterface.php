<?php

namespace Codesleeve\Stapler\Interfaces;

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
     * @return int|null
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
     * @return bool
     */
    public function isImage() : bool;
}

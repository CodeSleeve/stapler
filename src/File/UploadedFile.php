<?php

namespace Codesleeve\Stapler\File;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Codesleeve\Stapler\Exceptions\FileException;
use Codesleeve\Stapler\Interfaces\File as FileInterface;

class UploadedFile implements FileInterface
{
    /**
     * The underlying uploaded file object that acts
     * as part of this class's composition.
     *
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $uploadedFile;

    /**
     * An array of key value pairs for valid image
     * extensions and their associated MIME types.
     *
     * @var array
     */
    protected $imageMimes = [
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpe' => ['image/jpeg', 'image/pjpeg'],
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
    ];

    /**
     * Constructor method.
     *
     * @param SymfonyUploadedFile $uploadedFile
     */
    public function __construct(SymfonyUploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    /**
     * Handle dynamic method calls on this class.
     * This method allows this class to act as a 'composite' object
     * by delegating method calls to the underlying SymfonyUploadedFile object.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->uploadedFile, $method], $parameters);
    }

    /**
     * Method for determining whether the uploaded file is
     * an image type.
     *
     * @return bool
     */
    public function isImage()
    {
        $mime = $this->getMimeType();

        // The $imageMimes property contains an array of file extensions and
        // their associated MIME types. We will loop through them and look for
        // the MIME type of the current SymfonyUploadedFile.
        foreach ($this->imageMimes as $imageMime) {
            if (in_array($mime, (array) $imageMime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->uploadedFile->getClientOriginalName();
    }

    /**
     * Return the size of the file.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->uploadedFile->getClientSize();
    }

    /**
     * Return the mime type of the file.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->uploadedFile->getMimeType();
    }

    /**
     * Validate the uploaded file object.
     *
     * @throws FileException
     */
    public function validate()
    {
        if (!$this->isValid()) {
            throw new FileException($this->getErrorMessage());
        }
    }

    /**
     * Returns an informative upload error message.
     *
     * @return string
     */
    protected function getErrorMessage()
    {
        $errorCode = $this->getError();

        static $errors = [
            UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d kb).',
            UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            UPLOAD_ERR_EXTENSION => 'File upload was stopped by a php extension.',
        ];

        $maxFilesize = $errorCode === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
    }
}

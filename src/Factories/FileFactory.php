<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\File\{File as StaplerFile, UploadedFile as StaplerUploadedFile};
use Codesleeve\Stapler\Interfaces\{ConfigInterface, FileInterface};
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\{MimeTypeGuesser, MimeTypeExtensionGuesser};

class FileFactory
{
    /**
     * A instance of Symfony's MIME type extension guesser interface.
     *
     * @var \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    protected static $mimeTypeExtensionGuesser;

    /**
     * Return an instance of the Symfony MIME type extension guesser.
     *
     * @return \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesserInterface
     */
    public static function getMimeTypeExtensionGuesserInstance()
    {
        if (!static::$mimeTypeExtensionGuesser) {
            static::$mimeTypeExtensionGuesser = new MimeTypeExtensionGuesser();
        }

        return static::$mimeTypeExtensionGuesser;
    }

    /**
     * Set the configuration object instance.
     *
     * @param ConfigInterface $config
     */
    public static function setConfigInstance(ConfigInterface $config)
    {
        static::$config = $config;
    }

    /**
     * Build a Codesleeve\Stapler\UploadedFile object using various file input types.
     *
     * @param mixed $file
     * @param bool  $testing
     *
     * @return FileInterface
     */
    public static function create($file, bool $testing = false) : FileInterface
    {
        if ($file instanceof SymfonyUploadedFile) {
            return static::createFromObject($file);
        }

        if (is_array($file)) {
            return static::createFromArray($file, $testing);
        }

        if (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://') {
            return static::createFromUrl($file);
        }

        if (preg_match('#^data:[-\w]+/[-\w\+\.]+;base64#', $file)) {
            return static::createFromDataURI($file);
        }

        return static::createFromString($file);
    }

    /**
     * Compose a \Codesleeve\Stapler\File\UploadedFile object from
     * a \Symfony\Component\HttpFoundation\File\UploadedFile object.
     *
     * @param SymfonyUploadedFile $file
     *
     * @return StaplerUploadedFile
     */
    protected static function createFromObject(SymfonyUploadedFile $file) : StaplerUploadedFile
    {
        $staplerFile = new StaplerUploadedFile($file);
        $staplerFile->validate();

        return $staplerFile;
    }

    /**
     * Compose a \Codesleeve\Stapler\File\UploadedFile object from a
     * data uri.
     *
     * @param  mixed $file
     * @return StaplerFile
     */
    protected static function createFromDataURI($file) : StaplerFile
    {
        $fp = @fopen($file, 'r');

        if (!$fp) {
            throw new \Codesleeve\Stapler\Exceptions\FileException('Invalid data URI');
        }

        $meta = stream_get_meta_data($fp);
        $extension = static::getMimeTypeExtensionGuesserInstance()->guess($meta['mediatype']);
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($meta['uri']) . '.' . $extension;

        file_put_contents($filePath, stream_get_contents($fp));

        return new StaplerFile($filePath);
    }

    /**
     * Build a Codesleeve\Stapler\File\UploadedFile object from the
     * raw php $_FILES array date.
     *
     * @param array $file
     * @param bool  $testing
     *
     * @return StaplerUploadedFile
     */
    protected static function createFromArray(array $file, bool $testing) : StaplerUploadedFile
    {
        $file = new SymfonyUploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error'], $testing);

        return static::createFromObject($file);
    }

    /**
     * Fetch a remote file using a string URL and convert it into
     * an instance of Codesleeve\Stapler\File\File.
     *
     * @param string $file
     *
     * @return StaplerFile
     */
    protected static function createFromUrl($file) : StaplerFile
    {
        $ch = curl_init($file);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $rawFile = curl_exec($ch);
        curl_close($ch);

        // Remove the query string and hash if they exist
        $file = preg_replace('/[&#\?].*/', '', $file);

        // Get the original name of the file
        $pathinfo = pathinfo($file);
        $name = $pathinfo['basename'];
        $extension = isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '';

        // Create a temporary file with a unique name.
        $tempFile = tempnam(sys_get_temp_dir(), 'stapler-');

        if ($extension) {
            $filePath = $tempFile."{$extension}";
        } else {
            // Since we don't have an extension for the file, we'll have to go ahead and write
            // the contents of the rawfile to disk (using the tempFile path) in order to use
            // symfony's mime type guesser to generate an extension for the file.
            file_put_contents($tempFile, $rawFile);
            $mimeType = MimeTypeGuesser::getInstance()->guess($tempFile);
            $extension = static::getMimeTypeExtensionGuesserInstance()->guess($mimeType);

            $filePath = $tempFile.'.'.$extension;
        }

        file_put_contents($filePath, $rawFile);
        unlink($tempFile);

        return new StaplerFile($filePath);
    }

    /**
     * Fetch a local file using a string location and convert it into
     * an instance of \Codesleeve\Stapler\File\File.
     *
     * @param string $file
     *
     * @return StaplerFile
     */
    protected static function createFromString(string $file) : StaplerFile
    {
        return new StaplerFile($file, pathinfo($file)['basename']);
    }
}

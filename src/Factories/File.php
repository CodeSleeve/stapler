<?php

namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\File\File as StaplerFile;
use Codesleeve\Stapler\File\UploadedFile as StaplerUploadedFile;
use Codesleeve\Stapler\Interfaces\Config as ConfigInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;

class File
{
    /**
     * A instance of Symfony's MIME type extension guesser interface.
     *
     * @var \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    protected static $mimeTypeExtensionGuesser;

    /**
     * Build a Codesleeve\Stapler\UploadedFile object using various file input types.
     *
     * @param mixed $file
     * @param bool  $testing
     *
     * @return \Codesleeve\Stapler\File\UploadedFile
     */
    public static function create($file, $testing = false)
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
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Codesleeve\Stapler\File\UploadedFile
     */
    protected static function createFromObject(SymfonyUploadedFile $file)
    {
        $staplerFile = new StaplerUploadedFile($file);
        $staplerFile->validate();

        return $staplerFile;
    }

    /**
     * Compose a \Codesleeve\Stapler\File\UploadedFile object from a
     * data uri.
     *
     * @param  string $file
     * @return \Codesleeve\Stapler\File\File
     */
    protected static function createFromDataURI($file)
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
     * Build a Codesleeve\Stapler\File\File object from the
     * raw php $_FILES array date.  We assume here that the $_FILES array
     * has been formated using the Stapler::arrangeFiles utility method.
     *
     * @param array $file
     * @param bool  $testing
     *
     * @return \Codesleeve\Stapler\File\File
     */
    protected static function createFromArray(array $file, $testing)
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
     * @return \Codesleeve\Stapler\File\File
     */
    protected static function createFromUrl($file)
    {
        $ch = curl_init($file);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $rawFile = curl_exec($ch);
        curl_close($ch);

        // Remove the query string if it exists
        // We should do this before fetching the pathinfo() so that the extension is valid
        if (strpos($file, '?') !== false) {
            list($file, $queryString) = explode('?', $file);
        }

        // Get the original name of the file
        $pathinfo = pathinfo($file);
        $name = $pathinfo['basename'];

        // Create a filepath for the file by storing it on disk.
        $filePath = sys_get_temp_dir()."/$name";
        file_put_contents($filePath, $rawFile);

        if (empty($pathinfo['extension'])) {
            $mimeType = MimeTypeGuesser::getInstance()->guess($filePath);
            $extension = static::getMimeTypeExtensionGuesserInstance()->guess($mimeType);

            unlink($filePath);
            $filePath = sys_get_temp_dir()."/$name".'.'.$extension;
            file_put_contents($filePath, $rawFile);
        }

        return new StaplerFile($filePath);
    }

    /**
     * Fetch a local file using a string location and convert it into
     * an instance of \Codesleeve\Stapler\File\File.
     *
     * @param string $file
     *
     * @return \Codesleeve\Stapler\File\File
     */
    protected static function createFromString($file)
    {
        return new StaplerFile($file, pathinfo($file)['basename']);
    }

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
}

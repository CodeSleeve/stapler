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
    protected static function createFromUrl($url)
    {
        // Remove the query string and hash if they exist
        $file = preg_replace('/[&#\?].*/', '', $url);

        // Get the original name of the file
        $pathinfo = pathinfo($file);
        $name = $pathinfo['basename'];
        $extension = isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '';

        try
        {
            // Create a temporary file with a unique name.
            $lockFile = tempnam(sys_get_temp_dir(), 'stapler-');
            
            $c = new \GuzzleHttp\Client();
            $response = $c->request('GET', $url, [
                'sink'=>$lockFile,
            ]);
            
            if($response->getStatusCode()!=200)
            {
                throw new \Codesleeve\Stapler\Exceptions\FileException('Invalid URI returned HTTP code ', $response->getStatusCode());
            }
            
            $extension = isset($pathinfo['extension']) ? '.'.$pathinfo['extension'] : '';
            if(count($response->getHeader('Content-Type'))>0) {
              $mimeType = $response->getHeader('Content-Type')[0];
              $extension = '.' . static::getMimeTypeExtensionGuesserInstance()->guess($mimeType);
            }
            
            if(!$extension) {
              $mimeType = MimeTypeGuesser::getInstance()->guess($lockFile);
              $extension = '.' . static::getMimeTypeExtensionGuesserInstance()->guess($mimeType);
            }
            
            $filePath = $lockFile."{$extension}";
            rename($lockFile, $filePath);
            
            return new StaplerFile($filePath);
        } catch (\Exception $e) {
            @unlink($lockFile);
            @unlink($filePath);
            throw($e);
        }
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

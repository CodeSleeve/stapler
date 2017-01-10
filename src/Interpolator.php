<?php

namespace Codesleeve\Stapler;

use Doctrine\Common\Inflector\Inflector;
use Codesleeve\Stapler\Exceptions\InvalidAttachmentConfigurationException;
use Codesleeve\Stapler\Interfaces\{InterpolatorInterface, AttachmentInterface};

class Interpolator implements InterpolatorInterface
{
    /**
     * An string inflector object for pluralizing words.
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * @var array
     */
    protected static $interpolations = [];

    /**
     * Dynamically add a new interpolation this this interpolator.
     *
     * @param Callable $interpolation
     */
    public static function interpolates(string $key, Callable $value)
    {
        static::$interpolations[$key] = $value;
    }

    /**
     * Interpolate a string.
     *
     * @param string     $string
     * @param AttachmentInterface $attachment
     * @param string     $styleName
     *
     * @return string
     */
    public function interpolate($string, AttachmentInterface $attachment, string $styleName = '') : string
    {
        foreach ($this->interpolations() as $key => $value) {
            if (strpos($string, $key) !== false) {
                if (is_callable([$this, $value])) {
                    $interpolatedValue = call_user_func([$this, $value], $attachment, $styleName);
                } else {
                    $interpolatedValue = call_user_func($value, $attachment, $styleName);
                }

                $string = preg_replace("/$key\b/", $interpolatedValue, $string);
            }
        }

        return $string;
    }

    /**
     * Returns a sorted list of all interpolations.  This list is currently hard coded
     * (unlike its paperclip counterpart) but can be changed in the future so that
     * all interpolation methods are broken off into their own class and returned automatically.
     *
     * @return array
     */
    protected function interpolations()
    {
        return array_merge([
            ':filename'     => 'filename',
            ':url'          => 'url',
            ':app_root'     => 'appRoot',
            ':class'        => 'getClass',
            ':class_name'   => 'getClassName',
            ':namespace'    => 'getNamespace',
            ':basename'     => 'basename',
            ':extension'    => 'extension',
            ':id'           => 'id',
            ':hash'         => 'hash',
            ':secure_hash'  => 'secureHash',
            ':id_partition' => 'idPartition',
            ':attachment'   => 'attachment',
            ':style'        => 'style',
        ], static::$interpolations);
    }

    /**
     * Returns the file name.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function filename(AttachmentInterface $attachment, string $styleName = '')
    {
        return $attachment->originalFilename();
    }

    /**
     * Generates the url to a file upload.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function url(AttachmentInterface $attachment, string $styleName = '')
    {
        return $this->interpolate($attachment->url, $attachment, $styleName);
    }

    /**
     * Returns the application root of the project.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function appRoot(AttachmentInterface $attachment, string $styleName = '')
    {
        return $attachment->base_path;
    }

    /**
     * Returns the current class name, taking into account namespaces, e.g
     * 'Swingline\Stapler' will become swing_line/stapler.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function getClass(AttachmentInterface $attachment, string $styleName = '')
    {
        return Inflector::tableize($this->handleBackslashes($attachment->getInstanceClass()));
    }

    /**
     * Returns the snake cased current class name, not taking into account namespaces, e.g
     * 'Swingline\Stapler' will become stapler.
     *
     * @param AttachmentInterface $attachment
     * @param string     $styleName
     *
     * @return string
     */
    protected function getClassName(AttachmentInterface $attachment, string $styleName = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());
        $className = end($classComponents);

        return Inflector::tableize($className);
    }

    /**
     * Returns the current class name, exclusively taking into account namespaces, e.g
     * 'Swingline\Stapler' will become swing_line.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function getNamespace(AttachmentInterface $attachment, string $styleName = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());
        $namespace = implode('/', array_slice($classComponents, 0, count($classComponents) - 1));

        return Inflector::tableize($namespace);
    }

    /**
     * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function basename(AttachmentInterface $attachment, string $styleName = '')
    {
        return pathinfo($attachment->originalFilename(), PATHINFO_FILENAME);
    }

    /**
     * Returns the extension of the attached file, e.g 'jpg' for file.jpg.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function extension(AttachmentInterface $attachment, string $styleName = '')
    {
        return pathinfo($attachment->originalFilename(), PATHINFO_EXTENSION);
    }

    /**
     * Returns the id of the current object instance.
     *
     * @param AttachmentInterface $attachment
     * @param string     $styleName
     *
     * @return string
     */
    protected function id(AttachmentInterface $attachment, string $styleName = '')
    {
        return $this->ensurePrintable($attachment->getInstance()->getKey());
    }

    /**
     * Return a secure hash of the attachment's corresponding instance id.
     *
     * @param  AttachmentInterface $attachment
     * @param  string              $styleName
     * @throws InvalidAttachmentConfigurationException
     */
    protected function hash(AttachmentInterface $attachment, string $styleName = '')
    {
        if (!$attachment->hash_secret) {
            throw new InvalidAttachmentConfigurationException('Unable to generate hash without :hash_secret', 1);
        }

        return hash('sha256', $this->id($attachment, $styleName).$attachment->size().$attachment->originalFilename().$attachment->hash_secret);
    }

    /**
     * Generates the id partition of a record, e.g
     * return /000/001/234 for an id of 1234.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return mixed
     */
    protected function idPartition(AttachmentInterface $attachment, string $styleName = '')
    {
        $id = $this->ensurePrintable($attachment->getInstance()->getKey());

        if (is_numeric($id)) {
            return implode('/', str_split(sprintf('%09d', $id), 3));
        } elseif (is_string($id)) {
            return implode('/', array_slice(str_split($id, 3), 0, 3));
        } else {
            return;
        }
    }

    /**
     * Returns the pluralized form of the attachment name. e.g.
     * "avatars" for an attachment of :avatar.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function attachment(AttachmentInterface $attachment, string $styleName = '')
    {
        return Inflector::pluralize($attachment->name);
    }

    /**
     * Returns the style, or the default style if an empty style is supplied.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function style(AttachmentInterface $attachment, string $styleName = '')
    {
        return $styleName ?: $attachment->default_style;
    }

    /**
     * Utitlity function to turn a backslashed string into a string
     * suitable for use in a file path, e.g '\foo\bar' becomes 'foo/bar'.
     *
     * @param string $string
     *
     * @return string
     */
    protected function handleBackslashes(string $string) : string
    {
        return str_replace('\\', '/', ltrim($string, '\\'));
    }

    /**
     * Utility method to ensure the input data only contains
     * printable characters. This is especially important when
     * handling non-printable ID's such as binary UUID's.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    protected function ensurePrintable($input)
    {
        if (!is_numeric($input) && !ctype_print($input)) {
            // Hash the input data with SHA-256 to represent
            // as printable characters, with minimum chances
            // of the uniqueness being lost.
            return hash('sha256', $input);
        }

        return $input;
    }
}

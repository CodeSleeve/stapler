<?php

namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\Interpolator as InterpolatorInterface;
use Codesleeve\Stapler\Interfaces\Attachment as AttachmentInterface;
use Doctrine\Common\Inflector\Inflector;

class Interpolator implements InterpolatorInterface
{
    /**
     * An string inflector object for pluralizing words.
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * Interpolate a string.
     *
     * @param string     $string
     * @param AttachmentInterface $attachment
     * @param string     $styleName
     *
     * @return string
     */
    public function interpolate($string, AttachmentInterface $attachment, $styleName = '')
    {
        foreach ($this->interpolations() as $key => $value) {
            if (strpos($string, $key) !== false) {
                $string = preg_replace("/$key\b/", $this->$value($attachment, $styleName), $string);
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
        return [
            ':filename' => 'filename',
            ':url' => 'url',
            ':app_root' => 'appRoot',
            ':class' => 'getClass',
            ':class_name' => 'getClassName',
            ':namespace' => 'getNamespace',
            ':basename' => 'basename',
            ':extension' => 'extension',
            ':id' => 'id',
            ':hash' => 'hash',
            ':secure_hash' => 'secureHash',
            ':id_partition' => 'idPartition',
            ':attachment' => 'attachment',
            ':style' => 'style',
        ];
    }

    /**
     * Returns the file name.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function filename(AttachmentInterface $attachment, $styleName = '')
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
    protected function url(AttachmentInterface $attachment, $styleName = '')
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
    protected function appRoot(AttachmentInterface $attachment, $styleName = '')
    {
        return $attachment->base_path;
    }

    /**
     * Returns the current class name, taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline/Stapler.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function getClass(AttachmentInterface $attachment, $styleName = '')
    {
        return $this->handleBackslashes($attachment->getInstanceClass());
    }

    /**
     * Returns the current class name, not taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Stapler.
     *
     * @param AttachmentInterface $attachment
     * @param string     $styleName
     *
     * @return string
     */
    protected function getClassName(AttachmentInterface $attachment, $styleName = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return end($classComponents);
    }

    /**
     * Returns the current class name, exclusively taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function getNamespace(AttachmentInterface $attachment, $styleName = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return implode('/', array_slice($classComponents, 0, count($classComponents) - 1));
    }

    /**
     * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    protected function basename(AttachmentInterface $attachment, $styleName = '')
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
    protected function extension(AttachmentInterface $attachment, $styleName = '')
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
    protected function id(AttachmentInterface $attachment, $styleName = '')
    {
        return $this->ensurePrintable($attachment->getInstance()->getKey());
    }

    /**
     * Return a secure Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     */
    protected function secureHash(AttachmentInterface $attachment, $styleName = '')
    {
        return hash('sha256', $this->id($attachment, $styleName).$attachment->size().$attachment->originalFilename());
    }

    /**
     * Return a Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     */
    protected function hash(AttachmentInterface $attachment, $styleName = '')
    {
        return hash('sha256', $this->id($attachment, $styleName));
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
    protected function idPartition(AttachmentInterface $attachment, $styleName = '')
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
    protected function attachment(AttachmentInterface $attachment, $styleName = '')
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
    protected function style(AttachmentInterface $attachment, $styleName = '')
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
    protected function handleBackslashes($string)
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

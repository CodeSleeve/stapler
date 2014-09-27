<?php namespace Codesleeve\Stapler;

use Doctrine\Common\Inflector\Inflector;

class Interpolator
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
	 * @param  string $string
	 * @param  Attachment $attachment
     * @param  string $styleName
	 * @return string
	*/
	public function interpolate($string, Attachment $attachment, $styleName = '')
	{
		foreach ($this->interpolations() as $key => $value)
		{
			if (strpos($string, $key) !== false) {
				$string = preg_replace("/$key\b/", $this->$value($attachment, $styleName), $string);
			}
		}

		return $string;
	}

	/**
	 * Returns a sorted list of all interpolations.  This list is currently hard coded
	 * (unlike its paperclip counterpart) but can be changed in the future so that
	 * all interpolation methods are broken off into their own class and returned automatically
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
			':basename' => 'basename',
			':extension' => 'extension',
			':id' => 'id',
			':hash' => 'hash',
			':id_partition' => 'idPartition',
			':attachment' => 'attachment',
			':style' => 'style'
		];
	}

	/**
	 * Returns the file name.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function filename(Attachment $attachment, $styleName = '')
	{
		return $attachment->originalFilename();
	}

	/**
	 * Generates the url to a file upload.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function url(Attachment $attachment, $styleName = '')
	{
		return $this->interpolate($attachment->url, $attachment, $styleName);
	}

	/**
	 * Returns the application root of the project.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function appRoot(Attachment $attachment, $styleName = '')
	{
		return $attachment->base_path;
	}

	/**
	 * Returns the current class name, taking into account namespaces, e.g
	 * 'Swingline\Stapler' will become Swingline/Stapler.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
    protected function getClass(Attachment $attachment, $styleName = '')
    {
    	return $this->handleBackslashes($attachment->getInstanceClass());
    }

    /**
	 * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function basename(Attachment $attachment, $styleName = '')
	{
		return pathinfo($attachment->originalFilename(), PATHINFO_FILENAME);
	}

    /**
	 * Returns the extension of the attached file, e.g 'jpg' for file.jpg.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function extension(Attachment $attachment, $styleName = '')
	{
		return pathinfo($attachment->originalFilename(), PATHINFO_EXTENSION);
	}

	/**
	 * Returns the id of the current object instance.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
    protected function id(Attachment $attachment, $styleName = '')
    {
     	return $attachment->getInstance()->getKey();
    }

	/**
	 * Return a Bcrypt hash of the attachment's corresponding instance id.
	 *
	 * @param Attachment $attachment
	 * @param  string $styleName
	 * @return void
	 */
	protected function hash(Attachment $attachment, $styleName = '')
	{
		return hash('sha256', $this->id($attachment, $styleName));
	}

	/**
	* Generates the id partition of a record, e.g
	* return /000/001/234 for an id of 1234.
	*
	* @param Attachment $attachment
	* @param string $styleName
	* @return mixed
	*/
	protected function idPartition(Attachment $attachment, $styleName = '')
	{
		$id = $attachment->getInstance()->getKey();

		if (is_numeric($id))
		{
			return implode('/', str_split(sprintf('%09d', $id), 3));
		}
		elseif (is_string($id))
		{
			return implode('/', array_slice(str_split($id, 3), 0, 3));
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns the pluralized form of the attachment name. e.g.
     * "avatars" for an attachment of :avatar.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function attachment(Attachment $attachment, $styleName = '')
	{
		return Inflector::pluralize($attachment->name);
	}

	/**
	 * Returns the style, or the default style if an empty style is supplied.
	 *
	 * @param Attachment $attachment
	 * @param string $styleName
	 * @return string
	*/
	protected function style(Attachment $attachment, $styleName = '')
	{
		return $styleName ?: $attachment->default_style;
	}

	/**
	 * Utitlity function to turn a backslashed string into a string
	 * suitable for use in a file path, e.g '\foo\bar' becomes 'foo/bar'.
	 *
	 * @param string $string
	 * @return string
	 */
	protected function handleBackslashes($string)
	{
		return str_replace('\\', '/', ltrim($string, '\\'));
	}
}
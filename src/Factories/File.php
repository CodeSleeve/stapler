<?php namespace Codesleeve\Stapler\Factories;

use Codesleeve\Stapler\File\Mime\MimeType;
use Codesleeve\Stapler\File\File as StaplerFile;
use Codesleeve\Stapler\File\UploadedFile as StaplerUploadedFile;
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
	 * @param  mixed $file
	 * @param  boolean $testing
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

		if (substr($file, 0, 7) == "http://" || substr($file, 0, 8) == "https://") {
			return static::createFromUrl($file);
		}

		return static::createFromString($file);
	}

	/**
	 * Compose a \Codesleeve\Stapler\File\UploadedFile object from
	 * a \Symfony\Component\HttpFoundation\File\UploadedFile object.
	 *
	 * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @return \Codesleeve\Stapler\File\UploadedFile
	 */
	protected static function createFromObject(SymfonyUploadedFile $file)
	{
		$staplerFile = new StaplerUploadedFile($file);
		$staplerFile->validate();

        return $staplerFile;
	}

	/**
	 * Build a Codesleeve\Stapler\File\File object from the
	 * raw php $_FILES array date.	We assume here that the $_FILES array
	 * has been formated using the Stapler::arrangeFiles utility method.
	 *
	 * @param  array $file
	 * @param  boolean $testing
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
	 * @param  string $file
	 * @return \Codesleeve\Stapler\File\File
	 */
	protected static function createFromUrl($file)
	{
		$ch = curl_init($file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$rawFile = self::curl_exec_follow($ch);
		curl_close($ch);

		// Get the original name of the file
		$pathinfo = pathinfo($file); 
		$name = urlencode($pathinfo['basename']);

		// Create a filepath for the file by storing it on disk.
		$filePath = sys_get_temp_dir() . "/$name";
		file_put_contents($filePath, $rawFile);

		if (empty($pathinfo['extension'])) 
		{
			$mimeType = MimeTypeGuesser::getInstance()->guess($filePath);
			$extension = static::getMimeTypeExtensionGuesserInstance()->guess($mimeType);

			unlink($filePath);
			$filePath = sys_get_temp_dir() . "/$name" . "." . $extension;
			file_put_contents($filePath, $rawFile);
		}

		return new StaplerFile($filePath);
	}
	
	static function curl_exec_follow($ch, &$maxredirect = null) {
  
	  // we emulate a browser here since some websites detect
	  // us as a bot and don't let us do our job
	  $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
	                " Gecko/20041107 Firefox/1.0";
	  curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );
	
	  $mr = $maxredirect === null ? 5 : intval($maxredirect);
	  if (!ini_get('open_basedir') && !ini_get('safe_mode')) {
	
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	  } else {
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	
	    if ($mr > 0)
	    {
	      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	      $newurl = $original_url;
	      
	      $rch = curl_copy_handle($ch);
	      
	      curl_setopt($rch, CURLOPT_HEADER, true);
	      curl_setopt($rch, CURLOPT_NOBODY, true);
	      curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
	      do
	      {
	        curl_setopt($rch, CURLOPT_URL, $newurl);
	        $header = curl_exec($rch);
	        if (curl_errno($rch)) {
	          $code = 0;
	        } else {
	          $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
	          if ($code == 301 || $code == 302) {
	            preg_match('/Location:(.*?)\n/i', $header, $matches);
	            $newurl = trim(array_pop($matches));
	            
	            // if no scheme is present then the new url is a
	            // relative path and thus needs some extra care
	            if(!preg_match("/^https?:/i", $newurl)){
	              $newurl = $original_url . $newurl;
	            }   
	          } else {
	            $code = 0;
	          }
	        }
	      } while ($code && --$mr);
	      
	      curl_close($rch);
	      
	      if (!$mr)
	      {
	        if ($maxredirect === null)
	        trigger_error('Too many redirects.', E_USER_WARNING);
	        else
	        $maxredirect = 0;
	        
	        return false;
	      }
	      curl_setopt($ch, CURLOPT_URL, $newurl);
	    }
	  }
	  return curl_exec($ch);
	}

	/**
	 * Fetch a local file using a string location and convert it into
	 * an instance of \Codesleeve\Stapler\File\File.
	 *
	 * @param  string $file
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
            static::$mimeTypeExtensionGuesser = new MimeTypeExtensionGuesser;
        }

        return static::$mimeTypeExtensionGuesser;
    }

    /**
     * Set the configuration object instance.
     *
     * @param ConfigurableInterface $config
     */
    public static function setConfigInstance(ConfigurableInterface $config){
        static::$config = $config;
    }
}

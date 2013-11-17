<?php namespace Codesleeve\Stapler;

use Codesleeve\Stapler\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class IOWrapper
{
	/**
	 * Build an UploadedFile object using various file input types.
	 *  
	 * @param  mixed $file 
	 * @return Codesleeve\Stapler\File\UploadedFile
	 */
	public function make($file)
	{
		if ($file instanceof SymfonyUploadedFile) {
			return $this->createFromObject($file);
		}

		if (is_array($file)) {
			return $this->createFromArray($file);
		}

		return $this->createFromString($file);
	}

	/**
	 * Build a Codesleeve\Stapler\File\UploadedFile object from
	 * a symfony\Component\HttpFoundation\File\UploadedFile object.
	 * 
	 * @param  symfony\Component\HttpFoundation\File\UploadedFile $file 
	 * @return Codesleeve\Stapler\File\UploadedFile
	 */
	protected function createFromObject(SymfonyUploadedFile $file)
	{
		$path = $file->getPathname();
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getClientSize();
        $error = $file->getError();

        $staplerFile = new UploadedFile($path, $originalName, $mimeType, $size, $error);

        if (!$staplerFile->isValid()) {
			throw new Exceptions\FileException($staplerFile->getErrorMessage($staplerFile->getError()));
		}
        
        return $staplerFile;
	}

	/**
	 * Build a Codesleeve\Stapler\File\UploadedFile object from the
	 * raw php $_FILES array date.	We assume here that the $_FILES array
	 * has been formated using the Stapler::arrangeFiles utility method.
	 * 
	 * @param  array $file 
	 * @return Codesleeve\Stapler\File\UploadedFile      
	 */
	protected function createFromArray($file)
	{
		return new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
	}

	/**
	 * Fetch a remote file using a string URL and convert it into
	 * an instance of Codesleeve\Stapler\File\UploadedFile.
	 * 
	 * @param  string $file 
	 * @return Codesleeve\Stapler\File\UploadedFile   
	 */
	protected function createFromString($file)
	{
		
	}
}
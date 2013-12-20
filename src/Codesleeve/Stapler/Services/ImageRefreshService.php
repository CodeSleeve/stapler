<?php namespace Codesleeve\Stapler\Services;

use Codesleeve\Stapler\File\UploadedFile;
use Codesleeve\Stapler\IOWrapper;
use Codesleeve\Stapler\Exceptions\InvalidClassException;
use App;

class ImageRefreshService
{
	/**
	 * Attempt to refresh the defined attachments on a particular model.
	 *
	 * @param  string $class
	 * @param  array $attachments
	 * @return void 
	 */
	public function refresh($class, $attachments)
	{
		if (!method_exists($class, 'hasAttachedFile')) {
			throw new InvalidClassException("Invalid class: the $class class is not currently using Stapler.", 1);
		}

		$models = App::make($class)->all();

		if ($attachments) 
		{
			$attachments = explode(', ', $this->option('attachments'));
			$this->processSomeAttachments($models, $attachments);

			return;
		}
		
		$this->processAllAttachments($models);
	}

	/**
	 * Process a only a specified subset of stapler attachments.
	 * 
	 * @param  array $attachments 
	 * @return void              
	 */
	protected  function processSomeAttachments($models, $attachments)
	{
		foreach ($models as $model) 
		{
			foreach ($model->getAttachedFiles() as $attachedFile) 
			{
				if (in_array($attachedFile->name, $attachments)) {
					$this->rebuildImages($attachedFile);
				}
			}
		}
	}

	/**
	 * Process all stapler attachments defined on a class.
	 * 
	 * @return void
	 */
	protected function processAllAttachments($models)
	{
		foreach ($models as $model) 
		{
			foreach ($model->getAttachedFiles() as $attachedFile) 
			{
				$this->rebuildImages($attachedFile);
			}
		}
	}

	/**
	 * Rebuild the images for a specific attachment.
	 *
	 * @param  AttachedFile $attachedFile
	 * @return void 
	 */
	protected function rebuildImages($attachedFile)
	{
		if (!$attachedFile->originalFilename()) {
			return;
		}

		foreach ($attachedFile->styles as $style) 
		{
			$ioWrapper = new IOWrapper;
			$fileLocation = $attachedFile->storage == 'filesystem' ? $attachedFile->path() : $attachedFile->url();
			$file = $ioWrapper->make($fileLocation);

			if ($style->value && $file->isImage()) {
				$file = $attachedFile->getResizer()->resize($file, $style);
			}
			else {
				$file = $file->getRealPath();
			}

			$filePath = $attachedFile->path($style->name);
			$attachedFile->move($file, $filePath);
		}
	}
}
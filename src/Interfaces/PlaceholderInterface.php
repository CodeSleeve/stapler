<?php namespace Codesleeve\Stapler\Interfaces;

use Codesleeve\Stapler\Attachment;

interface PlaceholderInterface
{
    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     * @return mixed
     */
    public function placehold($url, Attachment $attachment, $styleName = '');
}
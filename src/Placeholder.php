<?php namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\PlaceholderInterface;

class Placeholder implements PlaceholderInterface
{
    /**
     * Interpolate a string.
     *
     * @param  string $string
     * @param  Attachment $attachment
     * @param  string $styleName
     * @return string
    */
    public function placehold($url, Attachment $attachment, $styleName = '')
    {
        if ($url) {
            return $attachment->getInterpolator()->interpolate($url, $attachment, $styleName);
        }

        return '';
    }
}
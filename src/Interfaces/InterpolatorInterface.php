<?php

namespace Codesleeve\Stapler\Interfaces;

interface InterpolatorInterface
{
    /**
     * Interpolate a string.
     *
     * @param string              $string
     * @param AttachmentInterface $attachment
     * @param string              $styleName
     *
     * @return string
     */
    public function interpolate($string, AttachmentInterface $attachment, string $styleName = '') : string;
}
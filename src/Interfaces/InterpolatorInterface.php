<?php

namespace Codesleeve\Stapler\Interfaces;

interface InterpolatorInterface
{
    /**
     * Dynamically add a new interpolation this this interpolator.
     *
     * @param Callable $interpolation
     */
    public static function interpolates(string $key, Callable $value);

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
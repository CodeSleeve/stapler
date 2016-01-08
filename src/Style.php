<?php

namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\Style as StyleInterface;

class Style implements StyleInterface
{
    /**
     * The name of the style.
     *
     * @var string
     */
    public $name;

    /**
     * The style dimensions.
     * This can be either a string or a callable type.
     *
     * @var mixed
     */
    public $dimensions;

    /**
     * Whether or not the image should be auto-oriented
     * using embedded EXIF data.
     *
     * @var bool
     */
    public $autoOrient = false;

    /**
     * An array of values used by Imagine Image to control
     * image quality, DPI, etc when saving an image.
     *
     * @var array
     */
    public $convertOptions = [];

    /**
     * Constructor method.
     *
     * @throws Exceptions\InvalidStyleConfigurationException
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;

        if (is_array($value)) {
            if (!array_key_exists('dimensions', $value)) {
                throw new Exceptions\InvalidStyleConfigurationException('Error Processing Request', 1);
            }

            $this->dimensions = $value['dimensions'];

            if (array_key_exists('auto_orient', $value)) {
                $this->autoOrient = $value['auto_orient'];
            }

            if (array_key_exists('convert_options', $value)) {
                $this->convertOptions = $value['convert_options'];
            }

            return;
        }

        $this->dimensions = $value;
    }
}

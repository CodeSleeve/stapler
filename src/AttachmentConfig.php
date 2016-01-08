<?php

namespace Codesleeve\Stapler;

class AttachmentConfig
{
    /**
     * The name of the attachment.
     *
     * @var string
     */
    public $name;

    /**
     * An array of attachment configuration options.
     *
     * @var array
     */
    protected $options;

    /**
     * An array of Codesleeve\Stapler\Style objects.
     *
     * @var array
     */
    protected $styles;

    /**
     * Constructor method.
     *
     * @throws Exceptions\InvalidAttachmentConfigurationException
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options)
    {
        if (!array_key_exists('styles', $options)) {
            throw new Exceptions\InvalidAttachmentConfigurationException("Attachment configuration options must contain a 'styles' key", 1);
        }

        $this->name = $name;
        $this->options = $options;
        $this->styles = $this->buildStyleObjects($options['styles']);
    }

    /**
     * Handle the dynamic setting of attachment options.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Handle the dynamic retrieval of attachment options.
     * Style options will be converted into a php stcClass.
     *
     * @param string $optionName
     *
     * @return mixed
     */
    public function __get($optionName)
    {
        if (array_key_exists($optionName, $this->options)) {
            if ($optionName == 'styles') {
                return $this->styles;
            }

            return $this->options[$optionName];
        }

        return;
    }

    /**
     * Convert the styles array into an array of Style objects.
     * Both array keys and array values will be converted to object properties.
     *
     * @param mixed $styles
     *
     * @return array
     */
    protected function buildStyleObjects($styles)
    {
        $config = Stapler::getConfigInstance();
        $className = $config->get('bindings.style');
        $styleObjects = [];

        foreach ($styles as $styleName => $styleValue) {
            $styleObjects[] = new $className($styleName, $styleValue);
        }

        return $styleObjects;
    }
}

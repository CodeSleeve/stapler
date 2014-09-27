<?php namespace Codesleeve\Stapler\Config;

interface ConfigurableInterface
{
    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * Set a configuration value.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value);
}
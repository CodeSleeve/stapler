<?php

namespace Codesleeve\Stapler\Interfaces;

interface Config
{
    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name);

    /**
     * Set a configuration value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set(string $name, $value);
}

<?php

namespace Codesleeve\Stapler\Interfaces;

interface StyleInterface
{
    /**
     * Constructor method.
     *
     * @throws Exceptions\InvalidStyleConfigurationException
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __construct($name, $value);
}

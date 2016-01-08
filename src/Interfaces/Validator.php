<?php

namespace Codesleeve\Stapler\Interfaces;

interface Validator
{
    /**
     * Validate the attachment options for an attachment type.
     * A url is required to have either an :id or an :id_partition interpolation.
     *
     * @param array $options
     */
    public function validateOptions(array $options);
}
<?php

namespace Codesleeve\Stapler\Interfaces;

interface EventDispatcher
{
    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function fire($event, $payload = []);
}

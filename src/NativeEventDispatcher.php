<?php

namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\EventDispatcher as DispatcherInterface;

class NativeEventDispatcher implements DispatcherInterface
{
    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function fire($event, $payload = [])
    {
        return true;
    }
}
<?php

namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\EventDispatcherInterface;

class NativeEventDispatcher implements EventDispatcherInterface
{
    /**
     * Fire an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed         $payload
     *
     * @return array|null
     */
    public function fire($event, $payload = [])
    {
        return true;
    }
}

<?php

namespace Rompetomp\InertiaBundle\Ssr;

interface GatewayInterface
{
    /**
     * Dispatch the Inertia page to the Server Side Rendering engine.
     */
    public function dispatch(array $page): ?Response;
}

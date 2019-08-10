<?php

namespace Rompetomp\InertiaBundle\EventListener;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class InertiaListener.
 */
class InertiaListener
{
    /**
     * @var \Rompetomp\InertiaBundle\Service\InertiaInterface
     */
    protected $inertia;

    public function __construct(InertiaInterface $inertia)
    {
        $this->inertia = $inertia;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->headers->get('X-Inertia')) {
            return;
        }

        if ($request->getMethod() === 'GET'
            && $request->headers->get('X-Inertia-Version') !== $this->inertia->getVersion()
        ) {
            $response = new Response('', 409, ['X-Inertia-Location' => $request->getUri()]);
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->getRequest()->headers->get('X-Inertia')) {
            return;
        }
        if ($event->getResponse()->isRedirect()
            && $event->getResponse()->getStatusCode() === 302
            && in_array($event->getRequest()->getMethod(), ['PUT', 'PATCH', 'DELETE'])
        ) {
            $event->getResponse()->setStatusCode(303);
        }
    }
}

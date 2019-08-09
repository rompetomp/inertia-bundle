<?php

namespace Rompetomp\InertiaBundle;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class Inertia
{
    /** @var string */
    protected $rootView;
    /** @var \Twig\Environment */
    protected $engine;
    /** @var array */
    protected $sharedProps = [];
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    protected $requestStack;
    /** @var string */
    private $version = null;

    /**
     * Inertia constructor.
     *
     * @param string                                         $rootView
     * @param \Twig\Environment                              $engine
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(string $rootView, Environment $engine, RequestStack $requestStack)
    {
        $this->engine = $engine;
        $this->rootView = $rootView;
        $this->requestStack = $requestStack;
    }

    public function share(string $key, $value = null): void
    {
        $this->sharedProps[$key] = $value;
    }

    public function getShared(string $key = null)
    {
        if ($key) {
            return $this->sharedProps[$key] ?? null;
        }
        return $this->sharedProps;
    }

    public function version(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function render($component, $props = [])
    {
        $props = array_merge($this->sharedProps, $props);
        $request = $this->requestStack->getCurrentRequest();
        $url = $request->getRequestUri();
        $version = $this->version;
        $page = compact('component', 'props', 'url', 'version');

        if ($request->headers->get('X-Inertia')) {
            return new JsonResponse($page, 200, [
                'Vary' => 'Accept',
                'X-Inertia' => true
            ]);
        }

        $response = new Response();
        $response->setContent($this->engine->render($this->rootView, compact('page')));
        return $response;
    }
}

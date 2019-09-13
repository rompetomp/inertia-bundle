<?php

namespace Rompetomp\InertiaBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class Inertia implements InertiaInterface
{
    /** @var string */
    protected $rootView;

    /** @var \Twig\Environment */
    protected $engine;

    /** @var array */
    protected $sharedProps = [];

    /** @var array */
    protected $sharedViewData = [];

    /** @var array */
    protected $sharedContext = [];

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    protected $requestStack;

    /** @var string */
    protected $version = null;

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

    public function viewData(string $key, $value = null): void
    {
        $this->sharedViewData[$key] = $value;
    }

    public function getViewData(string $key = null)
    {
        if ($key) {
            return $this->sharedViewData[$key] ?? null;
        }

        return $this->sharedViewData;
    }

    public function context(string $key, $value = null): void
    {
        $this->sharedContext[$key] = $value;
    }

    public function getContext(string $key = null)
    {
        if ($key) {
            return $this->sharedContext[$key] ?? null;
        }

        return $this->sharedContext;
    }

    public function version(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getRootView(): string
    {
        return $this->rootView;
    }

    public function render($component, $props = [], $viewData = [], $context = []): Response
    {
        $context = array_merge($this->sharedContext, $context);
        $viewData = array_merge($this->sharedViewData, $viewData);
        $props = array_merge($this->sharedProps, $props);
        $request = $this->requestStack->getCurrentRequest();
        $url = $request->getRequestUri();

        $only = array_filter(explode(',', $request->headers->get('X-Inertia-Partial-Data')));
        $props = ($only && $request->headers->get('X-Inertia-Partial-Component') === $component)
            ? self::array_only($props, $only) : $props;

        array_walk_recursive($props, function (&$prop) {
            if ($prop instanceof \Closure) {
                $prop = $prop();
            }
        });

        $version = $this->version;
        $page = compact('component', 'props', 'url', 'version');

        if ($request->headers->get('X-Inertia')) {
            return new JsonResponse($page, 200, [
                'Vary' => 'Accept',
                'X-Inertia' => true,
            ]);
        }

        $response = new Response();
        $response->setContent($this->engine->render($this->rootView, compact('page', 'viewData', 'context')));

        return $response;
    }

    private static function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

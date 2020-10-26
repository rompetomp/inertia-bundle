<?php

namespace Rompetomp\InertiaBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

class Inertia implements InertiaInterface
{
    /** @var string */
    protected $rootView;

    /** @var \Twig\Environment */
    protected $engine;

    /** @var SerializerInterface */
    protected $serializer;

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
    public function __construct(string $rootView, Environment $engine, RequestStack $requestStack, ?SerializerInterface $serializer = null)
    {
        $this->engine = $engine;
        $this->rootView = $rootView;
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
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
        $page = $this->serialize(compact('component', 'props', 'url', 'version'), $context);

        if ($request->headers->get('X-Inertia')) {
            return new JsonResponse($page, 200, [
                'Vary' => 'Accept',
                'X-Inertia' => true,
            ]);
        }

        $response = new Response();
        $response->setContent($this->engine->render($this->rootView, compact('page', 'viewData')));

        return $response;
    }

    /**
     * Serializes the given objects with the given context if the Symfony Serializer is available. If not, uses `json_encode`.
     *
     * @see https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/AJAX_Security_Cheat_Sheet.md#always-return-json-with-an-object-on-the-outside
     *
     * @param array $page
     * @param array $context
     *
     * @return array @return array returns a decoded array of the previously JSON-encoded data, so it can safely be given to {@see JsonResponse}
     */
    private function serialize(array $page, $context = []): array
    {
        if (null !== $this->serializer) {
            $json = $this->serializer->serialize($page, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function() { return null; },
            ], $context));
        } else {
            $json = json_encode($page);
        }

        return json_decode($json, true) ?? [];
    }

    private static function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

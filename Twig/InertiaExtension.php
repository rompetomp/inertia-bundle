<?php

namespace Rompetomp\InertiaBundle\Twig;

use Twig\Markup;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InertiaExtension.
 *
 * @author  Hannes Vermeire <hannes@codedor.be>²²
 *
 * @since   2019-08-09
 */
class InertiaExtension extends AbstractExtension
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [new TwigFunction('inertia', [$this, 'inertiaFunction'])];
    }

    public function inertiaFunction($page, $context = [])
    {
        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($page, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], $context));
        } else {
            $json = json_encode($page);
        }

        return new Markup('<div id="app" data-page="'.htmlspecialchars($json).'"></div>', 'UTF-8');
    }
}

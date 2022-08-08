<?php

namespace Rompetomp\InertiaBundle\Twig;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Rompetomp\InertiaBundle\Ssr\GatewayInterface;
use Rompetomp\InertiaBundle\Ssr\Response;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Class InertiaExtension.
 *
 * @author  Hannes Vermeire <hannes@codedor.be>
 *
 * @since   2019-08-09
 */
class InertiaExtension extends AbstractExtension
{
    /**
     * @var InertiaInterface
     */
    private $inertia;

    /**
     * @var GatewayInterface
     */
    private $gateway;

    public function __construct(InertiaInterface $inertia, GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
        $this->inertia = $inertia;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('inertia', [$this, 'inertiaFunction']),
            new TwigFunction('inertiaHead', [$this, 'inertiaHeadFunction']),
        ];
    }

    public function inertiaFunction($page)
    {
        if ($this->inertia->isSsr()) {
            $response = $this->gateway->dispatch($page);
            if ($response instanceof Response) {
                return new Markup($response->body, 'UTF-8');
            }
        }

        return new Markup('<div id="app" data-page="'.htmlspecialchars(json_encode($page)).'"></div>', 'UTF-8');
    }

    public function inertiaHeadFunction($page)
    {
        if ($this->inertia->isSsr()) {
            $response = $this->gateway->dispatch($page);
            if ($response instanceof Response) {
                return new Markup($response->head, 'UTF-8');
            }
        }

        return new Markup('', 'UTF-8');
    }
}

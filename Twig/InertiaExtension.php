<?php

namespace Rompetomp\InertiaBundle\Twig;

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
    public function getFunctions(): array
    {
        return [new TwigFunction('inertia', [$this, 'inertiaFunction'])];
    }

    public function inertiaFunction($page)
    {
        return new Markup('<div id="app" data-page="'.htmlspecialchars(json_encode($page)).'"></div>', 'UTF-8');
    }
}

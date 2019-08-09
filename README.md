# Inertia.js Symfony Adapter
This is a Inertia.js server-side adapter based on [inertia-laravel](https://github.com/inertiajs/inertia-laravel), but
for Symfony.

## Installation
Install using Composer:
```console
composer require rompetomp/inertia-bundle
```

## Setup root template
The first step to using Inertia is creating a root template. We recommend using `app.html.twig`. This template should
include your assets, as well as the `inertia(page)` function

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{% block title %}Welcome!{% endblock %}</title>
    {% block stylesheets %}
        {{ encore_entry_link_tags('app') }}
    {% endblock %}
</head>
<body>
{{ inertia(page) }}
{% block javascripts %}
    {{ encore_entry_script_tags('app') }}
{% endblock %}
</body>
</html>
```

The `inertia(page)` function is a helper function for creating our base `div`. It includes a `data-page` attribute which
contains the initial page information. This is what it looks like:
```php
<div id="app" data-page="<?php echo htmlspecialchars(json_encode($page)); ?>"></div>
```

If you'd like a different root view, you can change it by creating a `packages/rompetomp_inertia.yaml` file, and including
this config:
```yaml
rompetomp_inertia:
  root_view: 'name.twig.html'
```

## Making Inertia responses
To make an Inertia response, inject the `Rompetomp\InertiaBundle\Service\InertiaInterface $inertia` typehint in your 
controller, and use the render function on that Service:
```php
namespace App\Controller;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    public function index(InertiaInterface $inertia)
    {
        return $inertia->render('Dashboard');
    }
}
```

## Sharing data
To share data with all your components, use `$inertia->share($key, $data)`. This can be done in an EventSubscriber:
```php
<?php

namespace App\EventSubscriber;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class InertiaSubscriber implements EventSubscriberInterface
{
    /** @var \Rompetomp\InertiaBundle\Service\InertiaInterface */
    protected $inertia;

    /**
     * AppSubscriber constructor.
     *
     * @param \Rompetomp\InertiaBundle\Service\InertiaInterface $inertia
     */
    public function __construct(InertiaInterface $inertia)
    {
        $this->inertia = $inertia;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerEvent',
        ];
    }

    public function onControllerEvent($event)
    {
        $this->inertia->share('Auth::user', ['name' => 'Hannes']);
    }
}
```

## TODO
- Investigate if we need a inertia-laravel Middleware-like functionality: https://github.com/inertiajs/inertia-laravel/blob/master/src/Middleware.php
- Lazy loading of props/shared proprs.


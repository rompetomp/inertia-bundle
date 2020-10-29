<?php

namespace Rompetomp\InertiaBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface InertiaInterface.
 *
 * @author  Hannes Vermeire <hannes@codedor.be>
 *
 * @since   2019-08-09
 */
interface InertiaInterface
{
    /**
     * Adds global component properties for the templating system.
     *
     * @param mixed $value
     */
    public function share(string $key, $value = null): void;

    /**
     * @return mixed
     */
    public function getShared(string $key = null);

    /**
     * Adds global view data for the templating system.
     *
     * @param mixed $value
     */
    public function viewData(string $key, $value = null): void;

    /**
     * @return mixed
     */
    public function getViewData(string $key = null);

    public function version(string $version): void;

    /**
     * Adds a context for the serializer.
     *
     * @param mixed $value
     */
    public function context(string $key, $value = null): void;

    /**
     * @return mixed
     */
    public function getContext(string $key = null);

    /**
     * @return string
     */
    public function getVersion(): ?string;

    public function setRootView(string $rootView): void;

    public function getRootView(): string;

    /**
     * @param string $component component name
     * @param array  $props     component properties
     * @param array  $viewData  templating view data
     * @param array  $context   serialization context
     */
    public function render($component, $props = [], $viewData = [], $context = []): Response;
}

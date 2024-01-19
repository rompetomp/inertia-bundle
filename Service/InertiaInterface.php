<?php

namespace Rompetomp\InertiaBundle\Service;

use Rompetomp\InertiaBundle\LazyProp;
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

    public function getVersion(): ?string;

    public function setRootView(string $rootView): void;

    public function getRootView(): string;

    /**
     * Set if it will use ssr.
     */
    public function useSsr(bool $useSsr): void;

    /**
     * Check if it using ssr.
     */
    public function isSsr(): bool;

    /**
     * Set the ssr url where it will fetch its content.
     */
    public function setSsrUrl(string $url): void;

    /**
     * Get the ssr url where it will fetch its content.
     */
    public function getSsrUrl(): string;

    /**
     * @param callable|string|array $callback
     */
    public function lazy($callback): LazyProp;

    /**
     * @param string      $component component name
     * @param array       $props     component properties
     * @param array       $viewData  templating view data
     * @param array       $context   serialization context
     * @param string|null $url       custom url
     */
    public function render($component, $props = [], $viewData = [], $context = [], $url = null): Response;
}

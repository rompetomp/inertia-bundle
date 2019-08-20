<?php

namespace Rompetomp\InertiaBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface InertiaInterface.
 *
 * @author Hannes Vermeire <hannes@codedor.be>
 *
 * @since   2019-08-09
 */
interface InertiaInterface
{
    /**
     * Adds global component properties for the templating system.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function share(string $key, $value = null): void;

    /**
     * @param string|null $key
     *
     * @return mixed
     */
    public function getShared(string $key = null);

    /**
     * Adds global view data for the templating system.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setViewData(string $key, $value = null): void;

    /**
     * @param string|null $key
     *
     * @return mixed
     */
    public function getViewData(string $key = null);

    /**
     * @param string $version
     */
    public function version(string $version): void;

    /**
     * @return string
     */
    public function getVersion(): ?string;

    /**
     * @return string
     */
    public function getRootView(): string;

    /**
     * @param       $component component name
     * @param array $props     component properties
     * @param array $view      templating view data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($component, $props = [], $view = []): Response;
}

<?php
namespace Rompetomp\InertiaBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface InertiaInterface
 *
 * @package Rompetomp\InertiaBundle\Service
 * @author Hannes Vermeire <hannes@codedor.be>
 * @since   2019-08-09
 */
interface InertiaInterface
{
    /**
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
     * @param       $component
     * @param array $props
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($component, $props = []): Response;
}

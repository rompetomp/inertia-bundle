<?php

namespace Rompetomp\InertiaBundle\Ssr;

use Exception;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpGateway implements GatewayInterface
{
    private $interia;
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient, InertiaInterface $inertia)
    {
        $this->interia = $inertia;
        $this->httpClient = $httpClient;
    }

    /**
     * Dispatch the Inertia page to the Server Side Rendering engine.
     */
    public function dispatch(array $page): ?Response
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                $this->interia->getSsrUrl(),
                [
                    'headers' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ],
                    'body' => json_encode($page),
                ]
            );
        } catch (Exception $e) {
            return null;
        }

        if (is_null($response)) {
            return null;
        }

        $content = $response->toArray();

        return new Response(
            implode("\n", $content['head']),
            $content['body']
        );
    }
}

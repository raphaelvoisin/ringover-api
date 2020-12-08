<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RaphaelVoisin\Ringover\Api\Api;
use RaphaelVoisin\Ringover\Api\Push\Push;
use RaphaelVoisin\Ringover\Exception\SendRequestException;

/**
 * @property Push $pushApi
 */
class Client
{
    private const REQUEST_TIMEOUT = 5;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @var HttpClient|null
     */
    private $httpClient;

    /**
     * @var array
     */
    private $apis = [];

    public function __construct(
        string $apiKey,
        HttpClient $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? new HttpClient();
    }

    /**
     * @return Api
     */
    public function __get(string $name)
    {
        if ('pushApi' === $name) {
            return $this->getApi(Push::class);
        }
    }

    public function buildRequest(string $endpoint, array $payload): RequestInterface
    {
        $uri = new Uri($endpoint);

        return new Request(
            'POST',
            $uri,
            [
                'content-type' => 'application/json',
                'Authorization' => $this->apiKey
            ],
            \GuzzleHttp\json_encode($payload)
        );
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->send($request, [
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT
            ]);
        } catch (GuzzleException $e) {
            throw new SendRequestException(
                'Error when sending request : ' . $e->getMessage(),
                $request,
                null,
                $e
            );
        }
    }

    private function getApi(string $apiClass): Api
    {
        if (!isset($this->apis[$apiClass])) {
            $this->apis[$apiClass] = new $apiClass($this);
        }

        return $this->apis[$apiClass];
    }
}

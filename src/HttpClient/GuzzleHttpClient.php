<?php

declare(strict_types=1);

namespace Bobospay\HttpClient;

use Bobospay\Config;
use Bobospay\Exceptions\ApiException;
use Bobospay\Exceptions\AuthenticationException;
use Bobospay\Exceptions\NotFoundException;
use Bobospay\Exceptions\NotAcceptableException;
use Bobospay\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Guzzle-based HTTP client for the Bobospay API.
 *
 * Automatically injects authentication headers on every request and maps
 * HTTP error responses to typed SDK exceptions.
 */
class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(private readonly Config $config)
    {
        $this->client = new Client([
            'base_uri' => $this->config->baseUrl . '/v2/',
            'timeout' => $this->config->timeout,
            'verify' => $this->config->verifySsl,
            'headers' => [
                'X-Bobospay-Client-Id' => $this->config->clientId,
                'Authorization' => 'Bearer ' . $this->config->clientSecret,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client->get($uri, [
                'query' => $query,
            ]);

            return (array) json_decode((string) $response->getBody(), true);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $uri, array $body = [], array $headers = []): array
    {
        try {
            $response = $this->client->post($uri, [
                'json' => $body,
                'headers' => $headers,
            ]);

            return (array) json_decode((string) $response->getBody(), true);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Map a Guzzle request exception to a typed SDK exception.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws NotAcceptableException
     * @throws ValidationException
     * @throws ApiException
     */
    private function handleException(RequestException $e): never
    {
        $response = $e->getResponse();
        $status = $response?->getStatusCode() ?? 500;
        $body = [];

        if ($response !== null) {
            $body = (array) json_decode((string) $response->getBody(), true);
        }

        $message = $body['message'] ?? $e->getMessage();
        $errors = $body['errors'] ?? [];

        throw match ($status) {
            401 => new AuthenticationException($message, $status),
            404 => new NotFoundException($message, $status),
            406 => new NotAcceptableException($message, $status),
            422 => new ValidationException($message, $errors, $status),
            default => new ApiException($message, $status),
        };
    }
}


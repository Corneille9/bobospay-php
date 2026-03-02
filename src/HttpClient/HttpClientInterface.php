<?php

declare(strict_types=1);

namespace Bobospay\HttpClient;

/**
 * Contract for HTTP communication with the Bobospay API.
 */
interface HttpClientInterface
{
    /**
     * Send a GET request.
     *
     * @param string               $uri   Relative URI (e.g. "transactions").
     * @param array<string, mixed> $query Query parameters.
     *
     * @return array<string, mixed> Decoded JSON response.
     */
    public function get(string $uri, array $query = []): array;

    /**
     * Send a POST request.
     *
     * @param string               $uri     Relative URI.
     * @param array<string, mixed> $body    JSON body payload.
     * @param array<string, string> $headers Extra headers (e.g. Idempotency-Key).
     *
     * @return array<string, mixed> Decoded JSON response.
     */
    public function post(string $uri, array $body = [], array $headers = []): array;
}


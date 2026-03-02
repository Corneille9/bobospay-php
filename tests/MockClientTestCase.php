<?php

declare(strict_types=1);

namespace Bobospay\Tests;

use Bobospay\Config;
use Bobospay\HttpClient\GuzzleHttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

/**
 * Base test case providing helpers for HTTP mock setup.
 */
abstract class MockClientTestCase extends TestCase
{
    /**
     * Build a GuzzleHttpClient that uses a MockHandler instead of real HTTP.
     *
     * @param MockHandler $mockHandler Pre-configured mock handler with queued responses.
     *
     * @return GuzzleHttpClient
     */
    protected function buildHttpClient(MockHandler $mockHandler): GuzzleHttpClient
    {
        $config = new Config('ci_test_fake_client_id', 'test_client_secret');

        $handlerStack = HandlerStack::create($mockHandler);

        // Use reflection to inject the mocked Guzzle client.
        $httpClient = new GuzzleHttpClient($config);
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setValue($httpClient, new Client([
            'handler' => $handlerStack,
            'base_uri' => $config->baseUrl . '/v2/',
            'headers' => [
                'X-Bobospay-Client-Id' => $config->clientId,
                'Authorization' => 'Bearer ' . $config->clientSecret,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]));

        return $httpClient;
    }

    /**
     * Load a JSON fixture file and return its contents as a string.
     */
    protected function fixture(string $filename): string
    {
        $path = __DIR__ . '/Fixtures/' . $filename;

        if (!file_exists($path)) {
            $this->fail("Fixture file not found: {$path}");
        }

        return (string) file_get_contents($path);
    }
}


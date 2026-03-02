<?php

declare(strict_types=1);

namespace Bobospay;

use Bobospay\HttpClient\GuzzleHttpClient;
use Bobospay\HttpClient\HttpClientInterface;
use Bobospay\Services\AccountService;
use Bobospay\Services\CurrencyService;
use Bobospay\Services\CustomerService;
use Bobospay\Services\TransactionService;

/**
 * Main entry point for the Bobospay PHP SDK.
 *
 * Usage:
 *
 *     $bobospay = new BobospayClient('client_id', 'client_secret');
 *     $account  = $bobospay->account()->get();
 *     $tx       = $bobospay->transactions()->create($dto);
 */
class BobospayClient
{
    private Config $config;

    private HttpClientInterface $http;

    /**
     * @param string $clientId     Merchant application client ID.
     * @param string $clientSecret Merchant application client secret.
     * @param array{
     *     timeout?: int,
     *     verify_ssl?: bool,
     * } $options Additional configuration options.
     */
    public function __construct(string $clientId, string $clientSecret, array $options = [])
    {
        $this->config = new Config($clientId, $clientSecret, $options);
        $this->http = new GuzzleHttpClient($this->config);
    }

    /**
     * Create a client instance with a custom HTTP client implementation.
     *
     * Useful for testing or replacing the default Guzzle transport.
     */
    public static function withHttpClient(HttpClientInterface $httpClient): self
    {
        $instance = new self('ci_test_stub', 'stub_secret');
        $instance->http = $httpClient;

        return $instance;
    }

    /**
     * Account operations (profile, balances, currencies, payment methods).
     */
    public function account(): AccountService
    {
        return new AccountService($this->http);
    }

    /**
     * Transaction operations (list, create, find, generate token).
     */
    public function transactions(): TransactionService
    {
        return new TransactionService($this->http);
    }

    /**
     * Customer operations (list, create/upsert, find).
     */
    public function customers(): CustomerService
    {
        return new CustomerService($this->http);
    }

    /**
     * Currency operations (list active currencies).
     */
    public function currencies(): CurrencyService
    {
        return new CurrencyService($this->http);
    }
}


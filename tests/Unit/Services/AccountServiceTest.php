<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\Services;

use Bobospay\Services\AccountService;
use Bobospay\Tests\MockClientTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \Bobospay\Services\AccountService
 */
class AccountServiceTest extends MockClientTestCase
{
    public function test_get_returns_merchant_profile(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('account_response.json')),
        ]);

        $service = new AccountService($this->buildHttpClient($mock));
        $result = $service->get();

        $this->assertSame('ACME Ltd', $result['data']['business_name']);
        $this->assertSame('Active', $result['data']['status']);
    }

    public function test_balances_returns_wallet_balances(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('balances_response.json')),
        ]);

        $service = new AccountService($this->buildHttpClient($mock));
        $result = $service->balances();

        $this->assertCount(2, $result['data']);
        $this->assertSame('NGN', $result['data'][0]['currency']);
    }

    public function test_currencies_returns_active_currencies(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('currencies_response.json')),
        ]);

        $service = new AccountService($this->buildHttpClient($mock));
        $result = $service->currencies();

        $this->assertContains('NGN', $result['data']);
    }

    public function test_payment_methods_returns_enabled_methods(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('payment_methods_response.json')),
        ]);

        $service = new AccountService($this->buildHttpClient($mock));
        $result = $service->paymentMethods();

        $this->assertContains('card', $result['data']);
    }
}


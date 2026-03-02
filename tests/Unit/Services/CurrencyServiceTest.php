<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\Services;

use Bobospay\Services\CurrencyService;
use Bobospay\Tests\MockClientTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \Bobospay\Services\CurrencyService
 */
class CurrencyServiceTest extends MockClientTestCase
{
    public function test_list_returns_currencies(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('currencies_response.json')),
        ]);

        $service = new CurrencyService($this->buildHttpClient($mock));
        $result = $service->list();

        $this->assertContains('NGN', $result['data']);
        $this->assertContains('GHS', $result['data']);
        $this->assertContains('USD', $result['data']);
    }
}


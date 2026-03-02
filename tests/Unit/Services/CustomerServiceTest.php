<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\Services;

use Bobospay\DTOs\CreateCustomerDTO;
use Bobospay\Exceptions\NotFoundException;
use Bobospay\Services\CustomerService;
use Bobospay\Tests\MockClientTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \Bobospay\Services\CustomerService
 */
class CustomerServiceTest extends MockClientTestCase
{
    public function test_list_returns_paginated_customers(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('customers_list_response.json')),
        ]);

        $service = new CustomerService($this->buildHttpClient($mock));
        $result = $service->list();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertSame('Jane', $result['data'][0]['firstname']);
    }

    public function test_create_returns_customer(): void
    {
        $mock = new MockHandler([
            new Response(201, [], $this->fixture('customer_response.json')),
        ]);

        $service = new CustomerService($this->buildHttpClient($mock));

        $dto = new CreateCustomerDTO(
            firstname: 'Jane',
            lastname: 'Doe',
            email: 'jane@example.com',
            phone: '08012345678',
        );

        $result = $service->create($dto);

        $this->assertSame(12, $result['data']['id']);
        $this->assertSame('Jane', $result['data']['firstname']);
    }

    public function test_find_returns_single_customer(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('customer_response.json')),
        ]);

        $service = new CustomerService($this->buildHttpClient($mock));
        $result = $service->find(12);

        $this->assertSame(12, $result['data']['id']);
        $this->assertSame('jane@example.com', $result['data']['email']);
    }

    public function test_find_throws_not_found_exception_on_404(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode([
                'status' => 'error',
                'message' => 'Customer not found',
            ])),
        ]);

        $service = new CustomerService($this->buildHttpClient($mock));

        $this->expectException(NotFoundException::class);
        $service->find(99999);
    }
}


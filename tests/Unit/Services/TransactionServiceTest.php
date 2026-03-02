<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\Services;

use Bobospay\DTOs\CreateTransactionDTO;
use Bobospay\Exceptions\NotAcceptableException;
use Bobospay\Exceptions\NotFoundException;
use Bobospay\Exceptions\ValidationException;
use Bobospay\Services\TransactionService;
use Bobospay\Tests\MockClientTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \Bobospay\Services\TransactionService
 */
class TransactionServiceTest extends MockClientTestCase
{
    public function test_list_returns_paginated_transactions(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('transactions_list_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));
        $result = $service->list();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertSame(1, $result['meta']['current_page']);
    }

    public function test_create_returns_transaction(): void
    {
        $mock = new MockHandler([
            new Response(201, [], $this->fixture('transaction_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));

        $dto = new CreateTransactionDTO(
            amount: 1500.00,
            currency: 'NGN',
            callbackUrl: 'https://merchant.example/callback',
            note: 'Order #1234',
            channels: ['card'],
        );

        $result = $service->create($dto);

        $this->assertSame(124, $result['data']['id']);
        $this->assertSame('Pending', $result['data']['status']);
        $this->assertSame(1500.00, $result['data']['amount']);
    }

    public function test_create_with_idempotency_key(): void
    {
        $mock = new MockHandler([
            new Response(201, [], $this->fixture('transaction_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));

        $dto = new CreateTransactionDTO(
            amount: 1500.00,
            currency: 'NGN',
            callbackUrl: 'https://merchant.example/callback',
        );

        $result = $service->create($dto, 'unique-key-123');

        $this->assertSame(124, $result['data']['id']);
    }

    public function test_find_returns_single_transaction(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('transaction_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));
        $result = $service->find(124);

        $this->assertSame(124, $result['data']['id']);
        $this->assertSame('tx_xyz', $result['data']['uuid']);
    }

    public function test_generate_token_returns_token_and_url(): void
    {
        $mock = new MockHandler([
            new Response(200, [], $this->fixture('token_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));
        $result = $service->generateToken(124);

        $this->assertSame('abcd1234', $result['data']['token']);
        $this->assertStringContainsString('checkout', $result['data']['url']);
    }

    public function test_create_throws_validation_exception_on_422(): void
    {
        $mock = new MockHandler([
            new Response(422, [], $this->fixture('validation_error_response.json')),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));

        $dto = new CreateTransactionDTO(
            amount: 0,
            currency: '',
            callbackUrl: '',
        );

        $this->expectException(ValidationException::class);
        $service->create($dto);
    }

    public function test_find_throws_not_found_exception_on_404(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode([
                'status' => 'error',
                'message' => 'Transaction not found',
            ])),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));

        $this->expectException(NotFoundException::class);
        $service->find(99999);
    }

    public function test_generate_token_throws_not_acceptable_on_406(): void
    {
        $mock = new MockHandler([
            new Response(406, [], json_encode([
                'status' => 'error',
                'message' => 'Transaction is not in a valid state for token generation',
            ])),
        ]);

        $service = new TransactionService($this->buildHttpClient($mock));

        $this->expectException(NotAcceptableException::class);
        $service->generateToken(124);
    }
}


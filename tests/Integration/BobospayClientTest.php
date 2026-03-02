<?php

declare(strict_types=1);

namespace Bobospay\Tests\Integration;

use Bobospay\BobospayClient;
use Bobospay\DTOs\CreateCustomerDTO;
use Bobospay\DTOs\CreateTransactionDTO;
use Bobospay\HttpClient\HttpClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests that exercise the full client -> service flow
 * using a fake HTTP client implementation.
 *
 * @covers \Bobospay\BobospayClient
 */
class BobospayClientTest extends TestCase
{
    private BobospayClient $client;

    /** @var array<string, array<string, mixed>> */
    private array $responses;

    protected function setUp(): void
    {
        $this->responses = [
            'GET:account' => [
                'data' => ['business_name' => 'Test Merchant', 'status' => 'Active'],
            ],
            'GET:account/balances' => [
                'data' => [['balance' => '5000.00', 'currency' => 'NGN']],
            ],
            'GET:transactions' => [
                'data' => [['id' => 1, 'status' => 'Pending']],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1],
            ],
            'POST:transactions' => [
                'data' => ['id' => 2, 'uuid' => 'tx_new', 'status' => 'Pending', 'amount' => 2000.00],
            ],
            'GET:transactions/2' => [
                'data' => ['id' => 2, 'uuid' => 'tx_new', 'status' => 'Pending'],
            ],
            'GET:transactions/2/token' => [
                'data' => ['token' => 'tok_abc', 'url' => 'https://checkout.bobospay.com/checkout?tx_token=tok_abc'],
            ],
            'GET:customers' => [
                'data' => [['id' => 1, 'firstname' => 'Jane']],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1],
            ],
            'POST:customers' => [
                'data' => ['id' => 1, 'firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@test.com'],
            ],
            'GET:customers/1' => [
                'data' => ['id' => 1, 'firstname' => 'Jane', 'email' => 'jane@test.com'],
            ],
            'GET:currencies' => [
                'data' => ['NGN', 'GHS'],
            ],
        ];

        $fake = new FakeHttpClient($this->responses);
        $this->client = BobospayClient::withHttpClient($fake);
    }

    public function test_account_get(): void
    {
        $result = $this->client->account()->get();
        $this->assertSame('Test Merchant', $result['data']['business_name']);
    }

    public function test_account_balances(): void
    {
        $result = $this->client->account()->balances();
        $this->assertSame('5000.00', $result['data'][0]['balance']);
    }

    public function test_transactions_list(): void
    {
        $result = $this->client->transactions()->list();
        $this->assertCount(1, $result['data']);
    }

    public function test_transactions_create(): void
    {
        $dto = new CreateTransactionDTO(
            amount: 2000.00,
            currency: 'NGN',
            callbackUrl: 'https://test.com/cb',
        );

        $result = $this->client->transactions()->create($dto);
        $this->assertSame(2, $result['data']['id']);
        $this->assertSame('Pending', $result['data']['status']);
    }

    public function test_transactions_find(): void
    {
        $result = $this->client->transactions()->find(2);
        $this->assertSame('tx_new', $result['data']['uuid']);
    }

    public function test_transactions_generate_token(): void
    {
        $result = $this->client->transactions()->generateToken(2);
        $this->assertSame('tok_abc', $result['data']['token']);
        $this->assertStringContainsString('checkout', $result['data']['url']);
    }

    public function test_customers_list(): void
    {
        $result = $this->client->customers()->list();
        $this->assertSame('Jane', $result['data'][0]['firstname']);
    }

    public function test_customers_create(): void
    {
        $dto = new CreateCustomerDTO(
            firstname: 'Jane',
            lastname: 'Doe',
            email: 'jane@test.com',
        );

        $result = $this->client->customers()->create($dto);
        $this->assertSame(1, $result['data']['id']);
    }

    public function test_customers_find(): void
    {
        $result = $this->client->customers()->find(1);
        $this->assertSame('jane@test.com', $result['data']['email']);
    }

    public function test_currencies_list(): void
    {
        $result = $this->client->currencies()->list();
        $this->assertContains('NGN', $result['data']);
    }
}

/**
 * Minimal fake HTTP client for integration tests.
 *
 * @internal
 */
class FakeHttpClient implements HttpClientInterface
{
    /** @param array<string, array<string, mixed>> $responses */
    public function __construct(private readonly array $responses)
    {
    }

    public function get(string $uri, array $query = []): array
    {
        return $this->resolve('GET', $uri);
    }

    public function post(string $uri, array $body = [], array $headers = []): array
    {
        return $this->resolve('POST', $uri);
    }

    /** @return array<string, mixed> */
    private function resolve(string $method, string $uri): array
    {
        $key = "{$method}:{$uri}";

        if (isset($this->responses[$key])) {
            return $this->responses[$key];
        }

        throw new \RuntimeException("No fake response registered for {$key}");
    }
}


<?php

declare(strict_types=1);

namespace Bobospay\Services;

use Bobospay\DTOs\CreateTransactionDTO;
use Bobospay\HttpClient\HttpClientInterface;

/**
 * Transaction-related API operations.
 *
 * Covers listing, creating, retrieving transactions, and generating
 * checkout tokens.
 */
class TransactionService
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    /**
     * List transactions for the merchant (paginated).
     *
     * @param int $page    Page number.
     * @param int $perPage Items per page.
     *
     * @return array<string, mixed>
     */
    public function list(int $page = 1, int $perPage = 15): array
    {
        return $this->http->get('transactions', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Create a new transaction.
     *
     * @param CreateTransactionDTO $dto            Transaction data.
     * @param string|null          $idempotencyKey Optional idempotency key to prevent duplicates.
     *
     * @return array<string, mixed>
     */
    public function create(CreateTransactionDTO $dto, ?string $idempotencyKey = null): array
    {
        $headers = [];

        if ($idempotencyKey !== null) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return $this->http->post('transactions', $dto->toArray(), $headers);
    }

    /**
     * Retrieve a single transaction by ID.
     *
     * @param int $id Transaction ID.
     *
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->http->get("transactions/{$id}");
    }

    /**
     * Generate a short-lived checkout token for a pending transaction.
     *
     * @param int $id Transaction ID.
     *
     * @return array<string, mixed> Contains "token" and "url" keys.
     */
    public function generateToken(int $id): array
    {
        return $this->http->get("transactions/{$id}/token");
    }
}


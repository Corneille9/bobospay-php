<?php

declare(strict_types=1);

namespace Bobospay\Services;

use Bobospay\DTOs\CreateCustomerDTO;
use Bobospay\HttpClient\HttpClientInterface;

/**
 * Customer-related API operations.
 *
 * Covers listing, creating/updating (upsert), and retrieving customers.
 */
class CustomerService
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    /**
     * List customers for the merchant (paginated).
     *
     * @param int $page    Page number.
     * @param int $perPage Items per page.
     *
     * @return array<string, mixed>
     */
    public function list(int $page = 1, int $perPage = 15): array
    {
        return $this->http->get('customers', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Create or update a customer (upsert by email within the merchant).
     *
     * @param CreateCustomerDTO $dto Customer data.
     *
     * @return array<string, mixed>
     */
    public function create(CreateCustomerDTO $dto): array
    {
        return $this->http->post('customers', $dto->toArray());
    }

    /**
     * Retrieve a single customer by ID.
     *
     * @param int $id Customer ID.
     *
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->http->get("customers/{$id}");
    }
}


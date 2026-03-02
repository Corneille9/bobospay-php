<?php

declare(strict_types=1);

namespace Bobospay\Services;

use Bobospay\HttpClient\HttpClientInterface;

/**
 * Currency-related API operations.
 *
 * Retrieves the list of active fiat currencies available for merchant payments.
 */
class CurrencyService
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    /**
     * List all active currencies.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->http->get('currencies');
    }
}


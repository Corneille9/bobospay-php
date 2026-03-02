<?php

declare(strict_types=1);

namespace Bobospay\Services;

use Bobospay\HttpClient\HttpClientInterface;

/**
 * Account-related API operations.
 *
 * Covers merchant profile, balances, currencies, and payment methods.
 */
class AccountService
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    /**
     * Retrieve the merchant profile.
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->http->get('account');
    }

    /**
     * Retrieve wallet balances for the merchant.
     *
     * @return array<string, mixed>
     */
    public function balances(): array
    {
        return $this->http->get('account/balances');
    }

    /**
     * Retrieve active currencies for this merchant application.
     *
     * @return array<string, mixed>
     */
    public function currencies(): array
    {
        return $this->http->get('account/currencies');
    }

    /**
     * Retrieve enabled payment methods for this merchant application.
     *
     * @return array<string, mixed>
     */
    public function paymentMethods(): array
    {
        return $this->http->get('account/payment-methods');
    }
}


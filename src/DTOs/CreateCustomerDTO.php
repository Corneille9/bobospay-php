<?php

declare(strict_types=1);

namespace Bobospay\DTOs;

/**
 * Data transfer object for creating or updating a customer via POST /v2/customers.
 */
class CreateCustomerDTO
{
    /**
     * @param string      $firstname First name (max 100 characters).
     * @param string      $lastname  Last name (max 100 characters).
     * @param string      $email     Valid email address (used for upsert).
     * @param string|null $phone     Phone number (numeric, min 8 digits).
     */
    public function __construct(
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $email,
        public readonly ?string $phone = null,
    ) {
    }

    /**
     * Convert to an array suitable for the API request body.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
        ], fn ($value) => $value !== null);
    }
}


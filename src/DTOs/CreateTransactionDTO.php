<?php

declare(strict_types=1);

namespace Bobospay\DTOs;

/**
 * Data transfer object for creating a transaction via POST /v2/transactions.
 */
class CreateTransactionDTO
{
    /**
     * @param float       $amount         Transaction amount.
     * @param string      $currency       ISO currency code (e.g. "NGN", "XOF").
     * @param string      $callbackUrl    URL called by Bobospay on status change.
     * @param string|null $note           Optional description/note.
     * @param array<string>|null $channels       Payment channel categories (mobile_money, card, bank).
     * @param array<string>|null $mobileChannels Mobile money providers (mtn, moov, orange, wave, free).
     * @param array<string, mixed>|null $customer  Customer data (id, firstname, lastname, email, phone, country).
     * @param array<string, mixed>|null $customData Arbitrary key-value data attached to the transaction.
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $callbackUrl,
        public readonly ?string $note = null,
        public readonly ?array $channels = null,
        public readonly ?array $mobileChannels = null,
        public readonly ?array $customer = null,
        public readonly ?array $customData = null,
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'callback_url' => $this->callbackUrl,
            'note' => $this->note,
            'channels' => $this->channels,
            'mobile_channels' => $this->mobileChannels,
            'customer' => $this->customer,
            'custom_data' => $this->customData,
        ], fn ($value) => $value !== null);
    }
}


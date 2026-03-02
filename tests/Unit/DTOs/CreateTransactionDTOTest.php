<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\DTOs;

use Bobospay\DTOs\CreateTransactionDTO;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bobospay\DTOs\CreateTransactionDTO
 */
class CreateTransactionDTOTest extends TestCase
{
    public function test_to_array_with_all_fields(): void
    {
        $dto = new CreateTransactionDTO(
            amount: 1500.00,
            currency: 'NGN',
            callbackUrl: 'https://example.com/callback',
            note: 'Order #123',
            channels: ['card', 'mobile_money'],
            mobileChannels: ['mtn', 'moov'],
            customer: ['email' => 'jane@example.com'],
            customData: ['order_id' => '123'],
        );

        $array = $dto->toArray();

        $this->assertSame(1500.00, $array['amount']);
        $this->assertSame('NGN', $array['currency']);
        $this->assertSame('https://example.com/callback', $array['callback_url']);
        $this->assertSame('Order #123', $array['note']);
        $this->assertSame(['card', 'mobile_money'], $array['channels']);
        $this->assertSame(['mtn', 'moov'], $array['mobile_channels']);
        $this->assertSame(['email' => 'jane@example.com'], $array['customer']);
        $this->assertSame(['order_id' => '123'], $array['custom_data']);
    }

    public function test_to_array_omits_null_fields(): void
    {
        $dto = new CreateTransactionDTO(
            amount: 500.00,
            currency: 'XOF',
            callbackUrl: 'https://example.com/cb',
        );

        $array = $dto->toArray();

        $this->assertCount(3, $array);
        $this->assertArrayNotHasKey('note', $array);
        $this->assertArrayNotHasKey('channels', $array);
        $this->assertArrayNotHasKey('mobile_channels', $array);
        $this->assertArrayNotHasKey('customer', $array);
        $this->assertArrayNotHasKey('custom_data', $array);
    }
}


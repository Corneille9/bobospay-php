<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\DTOs;

use Bobospay\DTOs\CreateCustomerDTO;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bobospay\DTOs\CreateCustomerDTO
 */
class CreateCustomerDTOTest extends TestCase
{
    public function test_to_array_with_all_fields(): void
    {
        $dto = new CreateCustomerDTO(
            firstname: 'Jane',
            lastname: 'Doe',
            email: 'jane@example.com',
            phone: '08012345678',
        );

        $array = $dto->toArray();

        $this->assertSame('Jane', $array['firstname']);
        $this->assertSame('Doe', $array['lastname']);
        $this->assertSame('jane@example.com', $array['email']);
        $this->assertSame('08012345678', $array['phone']);
    }

    public function test_to_array_omits_null_phone(): void
    {
        $dto = new CreateCustomerDTO(
            firstname: 'John',
            lastname: 'Smith',
            email: 'john@example.com',
        );

        $array = $dto->toArray();

        $this->assertCount(3, $array);
        $this->assertArrayNotHasKey('phone', $array);
    }
}


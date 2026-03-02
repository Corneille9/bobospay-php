<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit;

use Bobospay\Config;
use Bobospay\Exceptions\BobospayException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bobospay\Config
 */
class ConfigTest extends TestCase
{
    public function test_live_prefix_resolves_to_production_url(): void
    {
        $config = new Config('ci_live_abc123', 'secret');

        $this->assertSame('ci_live_abc123', $config->clientId);
        $this->assertSame('secret', $config->clientSecret);
        $this->assertSame('https://bobospay.com/api', $config->baseUrl);
        $this->assertSame(30, $config->timeout);
        $this->assertTrue($config->verifySsl);
    }

    public function test_test_prefix_resolves_to_sandbox_url(): void
    {
        $config = new Config('ci_test_abc123', 'secret');

        $this->assertSame('https://sandbox.bobospay.com/api', $config->baseUrl);
    }

    public function test_dev_prefix_resolves_to_local_url(): void
    {
        $config = new Config('ci_dev_abc123', 'secret');

        $this->assertSame('http://127.0.0.1:8000/api', $config->baseUrl);
    }

    public function test_invalid_prefix_throws_exception(): void
    {
        $this->expectException(BobospayException::class);
        $this->expectExceptionMessage('Invalid client_id prefix');

        new Config('invalid_key', 'secret');
    }

    public function test_custom_timeout_and_verify_ssl(): void
    {
        $config = new Config('ci_test_abc123', 'secret', [
            'timeout' => 60,
            'verify_ssl' => false,
        ]);

        $this->assertSame(60, $config->timeout);
        $this->assertFalse($config->verifySsl);
    }

    public function test_environment_method(): void
    {
        $this->assertSame('dev', (new Config('ci_dev_x', 's'))->environment());
        $this->assertSame('test', (new Config('ci_test_x', 's'))->environment());
        $this->assertSame('live', (new Config('ci_live_x', 's'))->environment());
    }

    public function test_debug_info_masks_secret(): void
    {
        $config = new Config('ci_live_abc', 'super_secret_key');
        $debug = $config->__debugInfo();

        $this->assertSame('********', $debug['clientSecret']);
        $this->assertSame('ci_live_abc', $debug['clientId']);
        $this->assertSame('live', $debug['environment']);
    }
}


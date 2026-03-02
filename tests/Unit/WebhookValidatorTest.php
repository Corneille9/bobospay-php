<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit;

use Bobospay\Exceptions\BobospayException;
use Bobospay\Webhook\WebhookValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bobospay\Webhook\WebhookValidator
 */
class WebhookValidatorTest extends TestCase
{
    private string $secret = 'test_webhook_secret';

    public function test_is_valid_returns_true_for_correct_signature(): void
    {
        $validator = new WebhookValidator($this->secret);
        $payload = '{"event":"transaction.completed","data":{"id":1}}';
        $signature = hash_hmac('sha256', $payload, $this->secret);

        $this->assertTrue($validator->isValid($payload, $signature));
    }

    public function test_is_valid_returns_false_for_wrong_signature(): void
    {
        $validator = new WebhookValidator($this->secret);
        $payload = '{"event":"transaction.completed"}';

        $this->assertFalse($validator->isValid($payload, 'invalid_signature'));
    }

    public function test_is_valid_returns_false_for_empty_signature(): void
    {
        $validator = new WebhookValidator($this->secret);

        $this->assertFalse($validator->isValid('{}', ''));
    }

    public function test_validate_returns_decoded_payload(): void
    {
        $validator = new WebhookValidator($this->secret);
        $payload = '{"event":"transaction.completed","data":{"id":42}}';
        $signature = hash_hmac('sha256', $payload, $this->secret);

        $result = $validator->validate($payload, $signature);

        $this->assertSame('transaction.completed', $result['event']);
        $this->assertSame(42, $result['data']['id']);
    }

    public function test_validate_throws_on_invalid_signature(): void
    {
        $validator = new WebhookValidator($this->secret);

        $this->expectException(BobospayException::class);
        $this->expectExceptionMessage('Invalid webhook signature');
        $validator->validate('{}', 'bad');
    }

    public function test_validate_throws_on_malformed_json(): void
    {
        $validator = new WebhookValidator($this->secret);
        $payload = 'not json at all';
        $signature = hash_hmac('sha256', $payload, $this->secret);

        $this->expectException(BobospayException::class);
        $this->expectExceptionMessage('malformed JSON');
        $validator->validate($payload, $signature);
    }
}


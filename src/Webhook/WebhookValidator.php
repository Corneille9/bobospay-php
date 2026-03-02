<?php

declare(strict_types=1);

namespace Bobospay\Webhook;

use Bobospay\Exceptions\BobospayException;

/**
 * Validates incoming webhook payloads from Bobospay.
 *
 * Bobospay signs webhooks with an HMAC-SHA256 signature using the
 * merchant's client_secret. This helper verifies the signature to
 * ensure the payload has not been tampered with.
 *
 * Usage:
 *
 *     $validator = new WebhookValidator('your_client_secret');
 *     $payload   = file_get_contents('php://input');
 *     $signature = $_SERVER['HTTP_X_BOBOSPAY_SIGNATURE'] ?? '';
 *
 *     if ($validator->isValid($payload, $signature)) {
 *         // process webhook
 *     }
 */
class WebhookValidator
{
    /**
     * @param string $secret The merchant's client_secret used to compute HMAC.
     */
    public function __construct(private readonly string $secret)
    {
    }

    /**
     * Verify that a webhook payload matches the provided HMAC-SHA256 signature.
     *
     * @param string $payload   Raw request body.
     * @param string $signature Signature from the X-Bobospay-Signature header.
     *
     * @return bool True if the signature is valid.
     */
    public function isValid(string $payload, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Verify and decode a webhook payload. Throws on invalid signature.
     *
     * @param string $payload   Raw request body.
     * @param string $signature Signature from the X-Bobospay-Signature header.
     *
     * @return array<string, mixed> Decoded JSON payload.
     *
     * @throws BobospayException If the signature is invalid or the body is not valid JSON.
     */
    public function validate(string $payload, string $signature): array
    {
        if (!$this->isValid($payload, $signature)) {
            throw new BobospayException('Invalid webhook signature.', 400);
        }

        $data = json_decode($payload, true);

        if (!is_array($data)) {
            throw new BobospayException('Invalid webhook payload: malformed JSON.', 400);
        }

        return $data;
    }
}


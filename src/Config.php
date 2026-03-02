<?php

declare(strict_types=1);

namespace Bobospay;

use Bobospay\Exceptions\BobospayException;

/**
 * SDK configuration holder.
 * Stores credentials and HTTP settings. The base URL is resolved
 */
class Config
{
    /**
     * Base URL map keyed by client_id prefix.
     */
    private const ENVIRONMENT_URLS = [
        'ci_dev'  => 'http://127.0.0.1:8000/api',
        'ci_test' => 'https://sandbox.bobospay.com/api',
        'ci_live' => 'https://bobospay.com/api',
    ];

    public readonly string $clientId;

    public readonly string $clientSecret;

    public readonly string $baseUrl;

    public readonly int $timeout;

    public readonly bool $verifySsl;

    /**
     * @param string $clientId     Merchant application client ID (must start with ci_test_, or ci_live_).
     * @param string $clientSecret Merchant application client secret (used as Bearer token).
     * @param array{
     *     timeout?: int,
     *     verify_ssl?: bool,
     * } $options Additional options.
     *
     * @throws BobospayException If the client_id prefix is not recognized.
     */
    public function __construct(string $clientId, string $clientSecret, array $options = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = self::resolveBaseUrl($clientId);
        $this->timeout = $options['timeout'] ?? 30;
        $this->verifySsl = $options['verify_ssl'] ?? true;
    }

    /**
     * Determine the base URL from the client_id prefix.
     *
     * @throws BobospayException If the prefix is not recognized.
     */
    private static function resolveBaseUrl(string $clientId): string
    {
        foreach (self::ENVIRONMENT_URLS as $prefix => $url) {
            if (str_starts_with($clientId, $prefix . '_')) {
                return $url;
            }
        }

        throw new BobospayException(
            'Invalid client_id prefix. Expected one of: ci_test_*, ci_live_*.'
        );
    }

    /**
     * Get the environment name derived from the client_id prefix.
     *
     * @return string One of "test", or "live".
     */
    public function environment(): string
    {
        foreach (array_keys(self::ENVIRONMENT_URLS) as $prefix) {
            if (str_starts_with($this->clientId, $prefix . '_')) {
                return substr($prefix, 3); // strip "ci_"
            }
        }

        return 'unknown';
    }

    /**
     * Prevent credentials from leaking in debug output.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => '********',
            'baseUrl' => $this->baseUrl,
            'environment' => $this->environment(),
            'timeout' => $this->timeout,
            'verifySsl' => $this->verifySsl,
        ];
    }
}


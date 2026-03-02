<?php

/**
 * Manual autoloader for the Bobospay PHP SDK.
 *
 * Use this file only when Composer is not available.
 * Guzzle (guzzlehttp/guzzle ^7.0) must still be loaded separately
 * before requiring this file -- or use Composer for a fully managed setup.
 *
 * Usage:
 *
 *     // 1. Load Guzzle (if not using Composer)
 *     require_once '/path/to/guzzle/autoload.php';
 *
 *     // 2. Load the Bobospay SDK
 *     require_once '/path/to/bobospay-php/autoload.php';
 *
 *     // 3. Use the SDK
 *     $bobospay = new Bobospay\BobospayClient('ci_live_xxx', 'secret');
 */

spl_autoload_register(function (string $class): void {
    // Only handle classes in the Bobospay\ namespace.
    $prefix = 'Bobospay\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    // Skip framework-specific classes that require external packages.
    $skip = [
        'Bobospay\\Integrations\\Laravel\\',
    ];

    foreach ($skip as $skipPrefix) {
        if (strncmp($class, $skipPrefix, strlen($skipPrefix)) === 0) {
            return;
        }
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});


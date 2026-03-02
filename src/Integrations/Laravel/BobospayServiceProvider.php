<?php

declare(strict_types=1);

namespace Bobospay\Integrations\Laravel;

use Bobospay\BobospayClient;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel service provider for the Bobospay SDK.
 *
 * Registers the BobospayClient as a singleton in the container
 * and publishes the configuration file.
 */
class BobospayServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/bobospay.php',
            'bobospay'
        );

        $this->app->singleton(BobospayClient::class, function ($app) {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app['config'];

            return new BobospayClient(
                (string) $config->get('bobospay.client_id', ''),
                (string) $config->get('bobospay.client_secret', ''),
                [
                    'timeout' => (int) $config->get('bobospay.timeout', 30),
                    'verify_ssl' => (bool) $config->get('bobospay.verify_ssl', true),
                ]
            );
        });

        $this->app->alias(BobospayClient::class, 'bobospay');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/bobospay.php' => $this->app->configPath('bobospay.php'),
            ], 'bobospay-config');
        }
    }
}


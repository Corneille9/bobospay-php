<?php

declare(strict_types=1);

namespace Bobospay\Integrations\Laravel;

use Bobospay\BobospayClient;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for the Bobospay SDK.
 *
 * Provides static access to the BobospayClient singleton.
 *
 * @method static \Bobospay\Services\AccountService account()
 * @method static \Bobospay\Services\TransactionService transactions()
 * @method static \Bobospay\Services\CustomerService customers()
 * @method static \Bobospay\Services\CurrencyService currencies()
 *
 * @see \Bobospay\BobospayClient
 */
class BobospayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BobospayClient::class;
    }
}


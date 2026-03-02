# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-02

### Changed (breaking)

- Complete rewrite targeting the Merchant API v2.
- Entry point changed from static facade `Bobospay::setClientId()` to instantiable `BobospayClient`.
- Environment is now detected automatically from the `client_id` prefix (`ci_dev_*`, `ci_test_*`, `ci_live_*`). No need to call `setEnvironment()`.
- Minimum PHP version raised to 8.1.

### Added

- `BobospayClient` -- main SDK entry point.
- `Config` -- holds credentials and resolves base URL from client_id prefix.
- `TransactionService` -- list, create, find, generateToken.
- `CustomerService` -- list, create (upsert), find.
- `AccountService` -- get, balances, currencies, paymentMethods.
- `CurrencyService` -- list.
- `CreateTransactionDTO` / `CreateCustomerDTO` -- typed request objects.
- `WebhookValidator` -- HMAC-SHA256 signature verification for incoming webhooks.
- Typed exceptions: `AuthenticationException`, `NotFoundException`, `NotAcceptableException`, `ValidationException`, `ApiException`.
- Laravel service provider and facade with auto-discovery.
- Manual `autoload.php` for use without Composer.
- `BobospayClient::withHttpClient()` factory for testing with a custom HTTP transport.
- GitHub Actions workflows for CI (matrix PHP 8.1-8.4) and automated releases.

### Removed

- Static `Bobospay` facade and all v1 classes (`Customer`, `Transaction`, `Currency`, `init.php`).

## [1.1.0] - 2024-01-01

Last release of the v1 series. See the v1 branch for its history.


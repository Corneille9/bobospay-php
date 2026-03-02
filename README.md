# Bobospay PHP SDK

Official PHP SDK for the **Bobospay Merchant API v2**. Provides a clean, typed interface for managing transactions, customers, currencies, and account data. Framework-agnostic with first-class Laravel support.

Sign up for a Bobospay merchant account at [https://bobospay.com](https://bobospay.com).

---

## Requirements

- PHP 8.1 or later
- `json` extension
- `openssl` extension

## Installation

```bash
composer require bobospay/bobospay-php
```

## Without Composer

If you cannot use Composer, download or clone the repository and use the
provided `autoload.php` file. The SDK still requires **Guzzle 7** as an HTTP
client, so you need to load it first.

```
your-project/
    guzzlehttp/          <-- guzzle and its dependencies
    bobospay-php/        <-- this SDK
        autoload.php
        src/
    index.php
```

```php
<?php

// 1. Load Guzzle (adjust the path to match your setup)
require_once __DIR__ . '/guzzlehttp/autoload.php';

// 2. Load the Bobospay SDK
require_once __DIR__ . '/bobospay-php/autoload.php';

use Bobospay\BobospayClient;
use Bobospay\DTOs\CreateTransactionDTO;
use Bobospay\Exceptions\ApiException;

$bobospay = new BobospayClient('ci_live_your_client_id', 'your_client_secret');

try {
    $response = $bobospay->transactions()->create(new CreateTransactionDTO(
        amount: 1500.00,
        currency: 'NGN',
        callbackUrl: 'https://yoursite.com/payment/callback',
        note: 'Order #1234',
    ));

    $token = $bobospay->transactions()->generateToken($response['data']['id']);
    header('Location: ' . $token['data']['url']);
    exit;
} catch (ApiException $e) {
    echo 'Payment error: ' . $e->getMessage();
}
```

> The `autoload.php` loader skips framework-specific classes
> (`Bobospay\Integrations\Laravel\*`) since they require Laravel packages
> that would not be available in a plain PHP setup.

---

## Quick start

```php
use Bobospay\BobospayClient;
use Bobospay\DTOs\CreateTransactionDTO;

$bobospay = new BobospayClient(
    'ci_live_your_client_id',
    'your_client_secret',
);

// Create a transaction
$response = $bobospay->transactions()->create(new CreateTransactionDTO(
    amount: 1500.00,
    currency: 'NGN',
    callbackUrl: 'https://yoursite.com/payment/callback',
    note: 'Order #1234',
));

$transactionId = $response['data']['id'];

// Generate a checkout URL
$token = $bobospay->transactions()->generateToken($transactionId);
$checkoutUrl = $token['data']['url'];

// Redirect the customer to $checkoutUrl
```

## Environment detection

The SDK **automatically** determines the API endpoint from the `client_id` prefix.

| Prefix      | Environment | Base URL                           |
|-------------|-------------|------------------------------------|
| `ci_test_*` | Sandbox     | `https://sandbox.bobospay.com/api` |
| `ci_live_*` | Production  | `https://bobospay.com/api`         |

```php
// Sandbox -- automatically hits sandbox.bobospay.com
$bobospay = new BobospayClient('ci_test_abc123', 'your_test_secret');

// Production -- automatically hits bobospay.com
$bobospay = new BobospayClient('ci_live_abc123', 'your_live_secret');
```

## Configuration options

The constructor accepts an optional third argument for HTTP settings:

```php
$bobospay = new BobospayClient('ci_live_abc123', 'secret', [
    'timeout'    => 60,    // Request timeout in seconds (default: 30)
    'verify_ssl' => false, // Disable SSL verification (default: true)
]);
```

## Usage

### Account

```php
// Merchant profile
$profile = $bobospay->account()->get();
echo $profile['data']['business_name'];

// Wallet balances
$balances = $bobospay->account()->balances();

// Active currencies for this app
$currencies = $bobospay->account()->currencies();

// Enabled payment methods
$methods = $bobospay->account()->paymentMethods();
```

### Transactions

```php
use Bobospay\DTOs\CreateTransactionDTO;

// List transactions (paginated)
$list = $bobospay->transactions()->list(page: 1, perPage: 25);

// Create a transaction
$response = $bobospay->transactions()->create(new CreateTransactionDTO(
    amount: 2000.00,
    currency: 'XOF',
    callbackUrl: 'https://yoursite.com/callback',
    note: 'Invoice #5678',
    channels: ['mobile_money', 'card'],
    mobileChannels: ['mtn', 'moov'],
    customer: [
        'firstname' => 'Jane',
        'lastname'  => 'Doe',
        'email'     => 'jane@example.com',
    ],
    customData: ['invoice_id' => '5678'],
));

// Create with an idempotency key (prevents duplicate transactions on retries)
$response = $bobospay->transactions()->create($dto, idempotencyKey: 'unique-key-123');

// Retrieve a transaction
$tx = $bobospay->transactions()->find(124);
echo $tx['data']['status']; // "Pending", "Successful", etc.

// Generate a checkout token and URL
$token = $bobospay->transactions()->generateToken(124);
$checkoutUrl = $token['data']['url'];
```

### Customers

```php
use Bobospay\DTOs\CreateCustomerDTO;

// List customers (paginated)
$list = $bobospay->customers()->list(page: 1, perPage: 15);

// Create or update a customer (upsert by email)
$customer = $bobospay->customers()->create(new CreateCustomerDTO(
    firstname: 'Jane',
    lastname: 'Doe',
    email: 'jane@example.com',
    phone: '08012345678',
));

// Retrieve a customer
$customer = $bobospay->customers()->find(12);
```

### Currencies

```php
// List all active currencies
$currencies = $bobospay->currencies()->list();
// $currencies['data'] => ["NGN", "GHS", "USD", ...]
```

## Webhook verification

Bobospay signs webhook payloads with an HMAC-SHA256 signature using your `client_secret`. The SDK provides a helper to verify incoming webhooks:

```php
use Bobospay\Webhook\WebhookValidator;

$validator = new WebhookValidator('your_client_secret');

$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_BOBOSPAY_SIGNATURE'] ?? '';

// Option 1: boolean check
if ($validator->isValid($payload, $signature)) {
    $data = json_decode($payload, true);
    // process the webhook...
}

// Option 2: validate and decode in one step (throws on invalid signature)
try {
    $data = $validator->validate($payload, $signature);
    // process $data...
} catch (\Bobospay\Exceptions\BobospayException $e) {
    http_response_code(400);
    echo 'Invalid signature';
}
```

## Error handling

The SDK throws typed exceptions so you can handle each error case individually:

```php
use Bobospay\Exceptions\AuthenticationException;
use Bobospay\Exceptions\ValidationException;
use Bobospay\Exceptions\NotFoundException;
use Bobospay\Exceptions\NotAcceptableException;
use Bobospay\Exceptions\ApiException;

try {
    $tx = $bobospay->transactions()->find(99999);
} catch (AuthenticationException $e) {
    // 401 -- Invalid credentials
} catch (NotFoundException $e) {
    // 404 -- Resource not found
} catch (ValidationException $e) {
    // 422 -- Validation failed
    $fieldErrors = $e->getErrors();
    // ['amount' => ['The amount field is required.']]
} catch (NotAcceptableException $e) {
    // 406 -- Operation not allowed for current resource state
} catch (ApiException $e) {
    // Any other API error
    $status = $e->getStatusCode();
}
```

All exceptions extend `Bobospay\Exceptions\BobospayException`, so you can also catch that as a catch-all.

## Laravel integration

The SDK ships with a Laravel service provider that auto-registers via package discovery.

### 1. Publish the configuration

```bash
php artisan vendor:publish --tag=bobospay-config
```

### 2. Set your environment variables

```env
BOBOSPAY_CLIENT_ID=ci_live_your_client_id
BOBOSPAY_CLIENT_SECRET=your_client_secret
```

### 3. Use via dependency injection

```php
use Bobospay\BobospayClient;
use Bobospay\DTOs\CreateTransactionDTO;

class PaymentController extends Controller
{
    public function __construct(private BobospayClient $bobospay) {}

    public function pay(Request $request)
    {
        $response = $this->bobospay->transactions()->create(new CreateTransactionDTO(
            amount: $request->input('amount'),
            currency: 'NGN',
            callbackUrl: route('payment.callback'),
        ));

        $token = $this->bobospay->transactions()->generateToken($response['data']['id']);

        return redirect($token['data']['url']);
    }
}
```

### 4. Or use the Facade

```php
use Bobospay\Integrations\Laravel\BobospayFacade as Bobospay;

$profile = Bobospay::account()->get();
$tx = Bobospay::transactions()->create($dto);
```

## Testing

The SDK provides an `HttpClientInterface` that you can swap out in tests:

```php
use Bobospay\BobospayClient;

// Create a client with a custom/mock HTTP implementation
$client = BobospayClient::withHttpClient($mockHttpClient);
```

### Running the SDK test suite

```bash
composer install
vendor/bin/phpunit
```

## Security

- **Never expose your `client_secret` in client-side code.** It is used directly as the Bearer token.
- Always validate webhook signatures before processing payloads.
- Use environment variables to store credentials.

## License

MIT License. See [LICENSE](LICENSE) for details.

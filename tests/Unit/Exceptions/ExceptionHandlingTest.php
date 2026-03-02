<?php

declare(strict_types=1);

namespace Bobospay\Tests\Unit\Exceptions;

use Bobospay\Exceptions\ApiException;
use Bobospay\Exceptions\AuthenticationException;
use Bobospay\Exceptions\BobospayException;
use Bobospay\Exceptions\NotAcceptableException;
use Bobospay\Exceptions\NotFoundException;
use Bobospay\Exceptions\ValidationException;
use Bobospay\Tests\MockClientTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \Bobospay\Exceptions\BobospayException
 * @covers \Bobospay\Exceptions\AuthenticationException
 * @covers \Bobospay\Exceptions\NotFoundException
 * @covers \Bobospay\Exceptions\NotAcceptableException
 * @covers \Bobospay\Exceptions\ValidationException
 * @covers \Bobospay\Exceptions\ApiException
 */
class ExceptionHandlingTest extends MockClientTestCase
{
    public function test_401_throws_authentication_exception(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ])),
        ]);

        $http = $this->buildHttpClient($mock);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');
        $http->get('account');
    }

    public function test_404_throws_not_found_exception(): void
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode([
                'status' => 'error',
                'message' => 'Resource not found',
            ])),
        ]);

        $http = $this->buildHttpClient($mock);

        $this->expectException(NotFoundException::class);
        $http->get('transactions/99999');
    }

    public function test_406_throws_not_acceptable_exception(): void
    {
        $mock = new MockHandler([
            new Response(406, [], json_encode([
                'status' => 'error',
                'message' => 'Not acceptable',
            ])),
        ]);

        $http = $this->buildHttpClient($mock);

        $this->expectException(NotAcceptableException::class);
        $http->get('transactions/1/token');
    }

    public function test_422_throws_validation_exception_with_errors(): void
    {
        $mock = new MockHandler([
            new Response(422, [], $this->fixture('validation_error_response.json')),
        ]);

        $http = $this->buildHttpClient($mock);

        try {
            $http->post('transactions', []);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertArrayHasKey('amount', $e->getErrors());
            $this->assertArrayHasKey('currency', $e->getErrors());
        }
    }

    public function test_500_throws_api_exception(): void
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode([
                'status' => 'error',
                'message' => 'Internal server error',
            ])),
        ]);

        $http = $this->buildHttpClient($mock);

        $this->expectException(ApiException::class);
        $http->get('account');
    }

    public function test_all_exceptions_extend_bobospay_exception(): void
    {
        $this->assertInstanceOf(BobospayException::class, new AuthenticationException('test'));
        $this->assertInstanceOf(BobospayException::class, new NotFoundException('test'));
        $this->assertInstanceOf(BobospayException::class, new NotAcceptableException('test'));
        $this->assertInstanceOf(BobospayException::class, new ValidationException('test'));
        $this->assertInstanceOf(BobospayException::class, new ApiException('test'));
    }

    public function test_exception_provides_status_code(): void
    {
        $e = new ApiException('Server error', 503);
        $this->assertSame(503, $e->getStatusCode());
    }
}


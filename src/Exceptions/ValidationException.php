<?php

declare(strict_types=1);

namespace Bobospay\Exceptions;

/**
 * Thrown when the API returns a 422 Unprocessable Entity response.
 */
class ValidationException extends BobospayException
{
    /**
     * @var array<string, array<string>> Field-level validation errors.
     */
    private array $errors;

    /**
     * @param string                       $message    Error message.
     * @param array<string, array<string>> $errors     Field-level errors.
     * @param int                          $statusCode HTTP status code.
     */
    public function __construct(string $message, array $errors = [], int $statusCode = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<string, array<string>> Field-level validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}


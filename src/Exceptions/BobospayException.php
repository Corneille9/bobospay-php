<?php

declare(strict_types=1);

namespace Bobospay\Exceptions;

/**
 * Base exception for all Bobospay SDK errors.
 */
class BobospayException extends \RuntimeException
{
    /**
     * @param string $message    Error message.
     * @param int    $statusCode HTTP status code (0 if not HTTP-related).
     */
    public function __construct(string $message = '', int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * @return int The HTTP status code associated with this error.
     */
    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}


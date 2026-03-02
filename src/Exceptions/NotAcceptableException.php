<?php

declare(strict_types=1);

namespace Bobospay\Exceptions;

/**
 * Thrown when the API returns a 406 Not Acceptable response.
 *
 * Typically indicates that the requested operation is not allowed
 * for the current state of the resource (e.g. generating a token
 * for a non-pending transaction).
 */
class NotAcceptableException extends BobospayException
{
}


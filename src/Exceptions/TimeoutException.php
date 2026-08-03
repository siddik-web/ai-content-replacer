<?php
namespace App\Exceptions;

use Exception;
use Throwable;

class TimeoutException extends Exception
{
    private int $timeout;

    public function __construct(
        string $message = "",
        int $timeout = 0,
        int $code = 504, // HTTP 504 Gateway Timeout
        ?Throwable $previous = null
    ) {
        $this->timeout = $timeout;
        $message = $message ?: "API request timed out after {$timeout} seconds";
        parent::__construct($message, $code, $previous);
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
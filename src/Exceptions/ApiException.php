<?php
namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    /**
     * @var array Additional context or metadata about the error.
     */
    private array $context;

    /**
     * Constructor for ApiException.
     *
     * @param string $message The error message
     * @param int $code The error code (optional)
     * @param array $context Additional context or metadata (optional)
     * @param Exception|null $previous The previous exception (optional)
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        array $context = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the additional context or metadata associated with the exception.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Convert the exception to a string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        $contextString = !empty($this->context) ? json_encode($this->context) : 'No context';
        return sprintf(
            "ApiException: [Code %d]: %s\nContext: %s",
            $this->getCode(),
            $this->getMessage(),
            $contextString
        );
    }
}
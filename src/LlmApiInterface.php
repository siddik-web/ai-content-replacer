<?php

namespace App;

/**
 * Interface LlmApiInterface
 * 
 * Contract for LLM translation API service providers (Gemini, Ollama, etc.).
 * 
 * @package App
 */
interface LlmApiInterface
{
    /**
     * Get a translation response from the LLM API.
     *
     * @param string $text The text to translate
     * @param string $locale The target locale (e.g. 'fr-FR', 'nl-NL')
     * @param string $model The model to use
     * @param float $temperature The temperature for generation
     * @param int $maxTokens Maximum tokens to generate
     * @param int $timeout Timeout in seconds
     * @param int $maxRetries Maximum number of retry attempts
     * @param int $initialBackoff Initial backoff time in seconds
     * @return string|null The translated text or null on failure
     */
    public function getResponse(
        string $text,
        string $locale,
        string $model = "gemini-flash-lite-latest",
        float $temperature = 0.0,
        int $maxTokens = 300,
        int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 1
    ): ?string;

    /**
     * Get batch translation response from the LLM API for multiple key-value items.
     *
     * @param array $items Key-value pairs where value is English text
     * @param string $locale Target locale
     * @param string $model Model name
     * @param float $temperature Generation temperature
     * @param int $maxTokens Max response tokens
     * @param int $timeout Request timeout
     * @param int $maxRetries Max retries
     * @param int $initialBackoff Initial backoff in seconds
     * @return array Translated key-value pairs
     */
    public function getBatchResponse(
        array $items,
        string $locale,
        string $model = "gemini-flash-lite-latest",
        float $temperature = 0.0,
        int $maxTokens = 2000,
        int $timeout = 60,
        int $maxRetries = 2,
        int $initialBackoff = 1
    ): array;
}

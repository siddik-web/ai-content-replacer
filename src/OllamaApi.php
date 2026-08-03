<?php
namespace App;

use App\Exceptions\TimeoutException;
use ArdaGnsrn\Ollama\Ollama;
use Psr\Log\LoggerInterface;

class OllamaApi
{
    private object $client;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ?object $client = null)
    {
        $this->client = $client ?? Ollama::client();
        $this->logger = $logger;
    }

    /**
     * Get a translation response from the Ollama API with retries and exponential backoff.
     *
     * @param string $text The text to translate
     * @param string $locale The target locale
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
        string $model = "gemma3:1b",
        float $temperature = 0.0,
        int $maxTokens = 300,
        int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 1
    ): ?string {
        $prompt = $this->buildTranslationPrompt($text, $locale);

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->completions()->create([
                    'model'       => $model,
                    'prompt'      => $prompt,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                    'timeout'     => $timeout,
                    'stream'      => false,
                ]);

                $translation = trim($response->response);

                if (! empty($translation)) {
                    return $translation;
                }

                $this->logger->warning("Empty translation response received", [
                    'text'   => $text,
                    'locale' => $locale,
                    'model'  => $model,
                ]);

                return null;

            } catch (TimeoutException $e) {
                $this->handleTimeoutException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        return null;
    }

    /**
     * Get batch translation response from Ollama API for multiple key-value items.
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
        string $model = "gemma3:1b",
        float $temperature = 0.0,
        int $maxTokens = 2000,
        int $timeout = 60,
        int $maxRetries = 2,
        int $initialBackoff = 1
    ): array {
        if (empty($items)) {
            return [];
        }

        $prompt = $this->buildBatchTranslationPrompt($items, $locale);

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->completions()->create([
                    'model'       => $model,
                    'prompt'      => $prompt,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                    'timeout'     => $timeout,
                    'stream'      => false,
                ]);

                $rawResponse = trim($response->response);

                // Strip markdown code block wrappers if present (e.g. ```json ... ```)
                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawResponse);
                $cleanJson = trim($cleanJson);

                $decoded = json_decode($cleanJson, true);
                if (is_array($decoded) && ! empty($decoded)) {
                    $results = [];
                    foreach ($items as $key => $origValue) {
                        if (isset($decoded[$key]) && is_string($decoded[$key]) && ! empty(trim($decoded[$key]))) {
                            $results[$key] = trim($decoded[$key]);
                        }
                    }
                    if (! empty($results)) {
                        return $results;
                    }
                }

                $this->logger->warning("Batch translation JSON parsing failed on attempt " . ($attempt + 1), [
                    'raw' => $rawResponse
                ]);

            } catch (TimeoutException $e) {
                $this->handleTimeoutException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        // Fallback: Translate individually if batch LLM response fails
        $this->logger->warning("Batch translation failed, falling back to individual translations", ['count' => count($items)]);
        $results = [];
        foreach ($items as $key => $value) {
            $trans = $this->getResponse($value, $locale, $model, $temperature, 300, $timeout, $maxRetries, $initialBackoff);
            if ($trans !== null) {
                $results[$key] = $trans;
            }
        }
        return $results;
    }

    /**
     * Build the translation prompt for the LLM.
     *
     * @param string $text The text to translate
     * @param string $locale The target locale
     * @return string The formatted prompt
     */
    private function buildTranslationPrompt(string $text, string $locale): string
    {
        return <<<PROMPT
        Translate the following English text: "$text" into $locale and return only the translation in exact format not additional text, formatting, explanations or notes, also don't ask me for any additional questions just give me the exact answer only. Ensure technical terms like 'None', 'CSS', 'Remove', 'No Repeat', 'ID', Single Alphabet remain in English where appropriate, as they are commonly used in $locale technical contexts. don't add any quotation.
        PROMPT;
    }

    /**
     * Build prompt for batch key-value translations.
     */
    private function buildBatchTranslationPrompt(array $items, string $locale): string
    {
        $jsonItems = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return <<<PROMPT
Translate each value in the following JSON object into $locale.
Ensure technical terms like 'None', 'CSS', 'Remove', 'No Repeat', 'ID', single letters, and code placeholders remain in English where appropriate for $locale technical context.
IMPORTANT: Return ONLY a valid JSON object with the exact same keys, where each value is its translated string. Do not include markdown code block formatting (like ```json), explanations, notes, or additional text.

Input JSON:
$jsonItems
PROMPT;
    }

    /**
     * Handle timeout exception with logging and backoff.
     *
     * @param TimeoutException $e The exception
     * @param int $attempt Current attempt number
     * @param int $maxRetries Maximum retries
     * @param int $initialBackoff Initial backoff time
     */
    private function handleTimeoutException(
        TimeoutException $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->warning("API Timeout: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'timeout' => $e->getTimeout(),
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }

    /**
     * Handle generic exception with logging and backoff.
     *
     * @param \Exception $e The exception
     * @param int $attempt Current attempt number
     * @param int $maxRetries Maximum retries
     * @param int $initialBackoff Initial backoff time
     */
    private function handleGenericException(
        \Exception $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->error("API Error: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }
}

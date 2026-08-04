<?php

namespace App;

use App\Exceptions\TimeoutException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class GeminiApi implements LlmApiInterface
{
    private LoggerInterface $logger;
    private ?string $apiKey;
    private ClientInterface $httpClient;

    public function __construct(LoggerInterface $logger, ?string $apiKey = null, ?ClientInterface $httpClient = null)
    {
        $this->logger = $logger;
        $this->apiKey = $apiKey 
            ?? $_ENV['GEMINI_API_KEY'] 
            ?? $_SERVER['GEMINI_API_KEY'] 
            ?? getenv('GEMINI_API_KEY') 
            ?? null;
        
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Get the API Key or throw exception if not configured.
     *
     * @return string
     * @throws \RuntimeException
     */
    private function getApiKey(): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException("Gemini API Key is missing. Please set GEMINI_API_KEY in your .env file or UI settings.");
        }
        return $this->apiKey;
    }

    /**
     * Set API Key dynamically.
     *
     * @param string $apiKey
     * @return self
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Get a translation response from the Gemini API with retries and exponential backoff.
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
        string $model = "gemini-flash-lite-latest",
        float $temperature = 0.0,
        int $maxTokens = 300,
        int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 1
    ): ?string {
        try {
            $apiKey = $this->getApiKey();
        } catch (\RuntimeException $e) {
            $this->logger->error("Gemini API configuration error: " . $e->getMessage());
            return null;
        }

        $prompt = $this->buildTranslationPrompt($text, $locale);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($model) . ":generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
                'responseMimeType' => 'application/json'
            ]
        ];

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->httpClient->request('POST', $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $apiKey,
                    ],
                    'json'    => $payload,
                    'timeout' => $timeout,
                ]);

                $body = json_decode((string)$response->getBody(), true);
                $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

                $translation = '';
                $decodedJson = json_decode(trim($rawText), true);
                if (is_array($decodedJson) && isset($decodedJson['translation']) && is_string($decodedJson['translation'])) {
                    $translation = trim($decodedJson['translation']);
                } else {
                    $translation = trim($rawText);
                }

                if (! empty($translation)) {
                    $cleaned = $this->cleanTranslationOutput($text, $translation);
                    $cleaned = $this->sanitizeJoomlaStringValue($text, $cleaned, $locale);
                    if (! empty($cleaned)) {
                        return $cleaned;
                    }
                }

                $this->logger->warning("Empty translation response received from Gemini API", [
                    'text'   => $text,
                    'locale' => $locale,
                    'model'  => $model,
                ]);

                return null;

            } catch (GuzzleException $e) {
                $this->handleGuzzleException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        return null;
    }

    /**
     * Get batch translation response from Gemini API for multiple key-value items.
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
    ): array {
        if (empty($items)) {
            return [];
        }

        try {
            $apiKey = $this->getApiKey();
        } catch (\RuntimeException $e) {
            $this->logger->error("Gemini API configuration error: " . $e->getMessage());
            return [];
        }

        $prompt = $this->buildBatchTranslationPrompt($items, $locale);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($model) . ":generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
                'responseMimeType' => 'application/json'
            ]
        ];

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->httpClient->request('POST', $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $apiKey,
                    ],
                    'json'    => $payload,
                    'timeout' => $timeout,
                ]);

                $body = json_decode((string)$response->getBody(), true);
                $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
                $cleanJson = trim($cleanJson);

                $decoded = json_decode($cleanJson, true);
                if (is_array($decoded) && ! empty($decoded)) {
                    $results = [];
                    foreach ($items as $key => $origValue) {
                        if (isset($decoded[$key]) && is_string($decoded[$key]) && ! empty(trim($decoded[$key]))) {
                            $val = $this->cleanTranslationOutput($origValue, $decoded[$key]);
                            $results[$key] = $this->sanitizeJoomlaStringValue($origValue, $val, $locale);
                        }
                    }
                    if (! empty($results)) {
                        return $results;
                    }
                }

                $this->logger->warning("Batch translation JSON parsing failed on Gemini attempt " . ($attempt + 1), [
                    'raw' => $rawText
                ]);

            } catch (GuzzleException $e) {
                $this->handleGuzzleException($e, $attempt, $maxRetries, $initialBackoff);
            } catch (\Exception $e) {
                $this->handleGenericException($e, $attempt, $maxRetries, $initialBackoff);
            }
        }

        // Fallback: Translate individually if batch response fails
        $this->logger->warning("Gemini batch translation failed, falling back to individual translations", ['count' => count($items)]);
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
     * Build translation prompt for single text.
     */
    private function buildTranslationPrompt(string $text, string $locale): string
    {
        return <<<PROMPT
You are a professional translator for Joomla software language files.
Translate the following English source text into the target locale: {$locale}.

STRICT REQUIREMENTS:
1. Preserve all placeholders exactly as written (e.g. %s, %d, %1\$s, %2\$s, {placeholder}).
2. Preserve all HTML tags, entity references, and backslashes verbatim.
3. Return a JSON object with a single key "translation" containing the translated text value.

Text to translate:
"{$text}"
PROMPT;
    }

    /**
     * Build batch translation prompt for multiple key-value items.
     */
    private function buildBatchTranslationPrompt(array $items, string $locale): string
    {
        $jsonInput = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are a professional translator for Joomla software language files.
Translate the following JSON object's values from English into the target locale: {$locale}.

STRICT REQUIREMENTS:
1. Keep the JSON keys EXACTLY identical to the input.
2. Preserve all placeholders like %s, %d, %1\$s, %2\$s, HTML tags, and HTML entities verbatim.
3. Output MUST be a valid JSON object mapping each original key to its translated string value.

Input JSON:
{$jsonInput}
PROMPT;
    }

    /**
     * Clean and sanitize LLM translation output.
     */
    public function cleanTranslationOutput(string $originalText, string $rawResponse): string
    {
        $text = trim($rawResponse);

        // 1. Strip markdown code fences
        $text = preg_replace('/^```[a-z]*\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // 2. Extract text after "translates to:" / "translated as:"
        if (preg_match('/(?:translates? to|translated as)\s*:\s*(.+)$/isu', $text, $m)) {
            $text = trim($m[1]);
        }

        // 3. Strip leading preamble labels
        $text = preg_replace('/^(?:translation|output|result|here is|translated text)\s*:\s*/iu', '', $text);
        $text = trim($text);

        // 4. If BCP-47 locale label appears (e.g. "nl-NL:"), take text after it
        if (preg_match('/\b[a-z]{2}-[A-Z]{2}\s*:\s*(.+)$/u', $text, $m)) {
            $text = trim($m[1]);
        }

        // 5. If response is surrounded by double quotes while original wasn't, unwrap outer quotes
        if (preg_match('/^"([^"]+)"$/u', $text, $m) && ! (str_starts_with($originalText, '"') && str_ends_with($originalText, '"'))) {
            $text = $m[1];
        }

        // 6. Ensure single-line output
        $lines = explode("\n", $text);
        return trim($lines[0]);
    }

    /**
     * Sanitize translated string for Joomla .ini file output.
     */
    public function sanitizeJoomlaStringValue(string $originalText, string $translatedText, string $locale): string
    {
        $clean = trim($translatedText);

        // Ensure percent placeholders match original count
        preg_match_all('/%[sd]/', $originalText, $origPlaceholders);
        preg_match_all('/%[sd]/', $clean, $transPlaceholders);

        if (count($origPlaceholders[0]) !== count($transPlaceholders[0])) {
            // Restore missing placeholders if original had them
            foreach ($origPlaceholders[0] as $ph) {
                if (strpos($clean, $ph) === false) {
                    $clean .= " " . $ph;
                }
            }
        }

        return trim($clean);
    }

    private function handleGuzzleException(GuzzleException $e, int $attempt, int $maxRetries, int $initialBackoff): void
    {
        $statusCode = $e->getCode();
        $this->logger->warning("Gemini API request failed on attempt " . ($attempt + 1) . " of {$maxRetries}: HTTP status {$statusCode} - " . $e->getMessage());

        if ($attempt < $maxRetries - 1) {
            $backoff = $initialBackoff * (2 ** $attempt);
            if ($backoff > 0) {
                usleep((int)($backoff * 1000000));
            }
        }
    }

    private function handleGenericException(\Exception $e, int $attempt, int $maxRetries, int $initialBackoff): void
    {
        $this->logger->error("Gemini API generic error on attempt " . ($attempt + 1) . ": " . $e->getMessage());

        if ($attempt < $maxRetries - 1) {
            $backoff = $initialBackoff * (2 ** $attempt);
            if ($backoff > 0) {
                usleep((int)($backoff * 1000000));
            }
        }
    }
}

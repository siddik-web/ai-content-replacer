<?php

namespace App;

use ArdaGnsrn\Ollama\Ollama;
use Psr\Log\LoggerInterface;

class OllamaApi
{
    private Ollama $client;
    private static ?self $instance = null;
    private LoggerInterface $logger;

    // In-memory cache for storing translations
    private array $translationCache = [];

    private function __construct(LoggerInterface $logger)
    {
        $this->client = Ollama::client();
        $this->logger = $logger;
    }

    /**
     * Singleton pattern to get a single instance of OllamaApi.
     */
    public static function getInstance(LoggerInterface $logger): self
    {
        if (self::$instance === null) {
            self::$instance = new self($logger);
        }
        
        return self::$instance;
    }

    /**
     * Get a translation response from the Ollama API, using cache if available.
     *
     * @param string $text Text to translate.
     * @param string $locale Target locale for translation.
     * @param string $model Model name to use for translation.
     * @param float $temperature Control randomness in generation (default: 0.3).
     * @param int $maxTokens Limit for response tokens (default: 100).
     * @param int|null $timeout Request timeout in seconds (default: 10).
     * @return string|null The translated text or null in case of an error.
     */
    public function getResponse(
        string $text,
        string $locale,
        string $model = "gemma2",
        float $temperature = 0.3,
        int $maxTokens = 100,
        ?int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 2 // Initial wait time in seconds
    ): ?string {
        // Generate a unique cache key for the translation
        $cacheKey = md5($text . $locale . $model);

        // Check if translation is already cached
        if (isset($this->translationCache[$cacheKey])) {
            return $this->translationCache[$cacheKey];
        }

        // Construct prompt for translation
        $prompt = "Translate this text to $locale and return only the translation in exact format not additional text, formatting, explanations or notes, also don't ask me for any additional questions just give me the exact answer only, don't add any quotation: $text";

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $completions = $this->client->completions()->create([
                    'model' => $model,
                    'prompt' => $prompt,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'timeout' => $timeout,
                    'stream' => false,
                ]);

                $translation = trim($completions->response);

                // Cache the result
                $this->translationCache[$cacheKey] = $translation;

                return $translation;
            } catch (\Throwable $e) {
                $this->logger->error("Ollama API Error: " . $e->getMessage(), ['exception' => $e]);
                // Apply exponential backoff if retries remain
                if ($attempt < $maxRetries - 1) {
                    $waitTime = $initialBackoff * pow(2, $attempt);
                    sleep($waitTime);
                } else {
                    return null;
                }
                
            }
        }
    }

    /**
     * Reset the singleton instance (useful for testing or reinitialization).
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}

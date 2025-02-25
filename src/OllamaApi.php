<?php
namespace App;

use ArdaGnsrn\Ollama\Ollama;
use Psr\Log\LoggerInterface;
use App\Exceptions\TimeoutException;

class OllamaApi
{
    private Ollama $client;
    private static ?self $instance = null;
    private LoggerInterface $logger;

    private function __construct(LoggerInterface $logger)
    {
        $this->client = Ollama::client();
        $this->logger = $logger;
    }

    public static function getInstance(LoggerInterface $logger): self
    {
        if (self::$instance === null) {
            self::$instance = new self($logger);
        }
        return self::$instance;
    }

    public function getResponse(
        string $text,
        string $locale,
        string $model = "gemma2",
        float $temperature = 0.0,
        int $maxTokens = 300,
        ?int $timeout = 20,
        int $maxRetries = 3,
        int $initialBackoff = 1
    ): ?string {
        $prompt = $this->buildTranslationPrompt($text, $locale);
        
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->completions()->create([
                    'model' => $model,
                    'prompt' => $prompt,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                    'timeout' => $timeout ?? 20, // Ensure default if null
                    'stream' => false,
                ]);

                $translation = trim($response->response);

                if (!empty($translation)) {
                    return $translation;
                }
                
                // Handle empty response case
                $this->logger->warning("Empty translation response received", [
                    'text' => $text,
                    'locale' => $locale,
                    'model' => $model
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

    private function buildTranslationPrompt(string $text, string $locale): string
    {
        return <<<PROMPT
        Translate the following English text: "$text" into $locale and return only the translation in exact format not additional text, formatting, explanations or notes, also don't ask me for any additional questions just give me the exact answer only. Ensure technical terms like 'None', 'CSS', 'Remove', 'No Repeat', 'ID', Single Alphabet remain in English where appropriate, as they are commonly used in $locale technical contexts. don't add any quotation.
        PROMPT;
    }

    private function handleTimeoutException(
        TimeoutException $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->warning("API Timeout: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'timeout' => $e->getTimeout()
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }

    private function handleGenericException(
        \Exception $e,
        int $attempt,
        int $maxRetries,
        int $initialBackoff
    ): void {
        $this->logger->error("API Error: Attempt {$attempt}/{$maxRetries}", [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ]);

        if ($attempt < $maxRetries - 1) {
            $waitTime = $initialBackoff * (2 ** $attempt);
            sleep($waitTime);
        }
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
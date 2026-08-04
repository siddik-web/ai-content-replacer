<?php

namespace App;

use Psr\Log\LoggerInterface;

class LlmApiFactory
{
    /**
     * Create an instance of LlmApiInterface based on provider preference and environment configuration.
     *
     * @param string|null $provider Provider name ('gemini' or 'ollama')
     * @param LoggerInterface|null $logger Logger instance
     * @param string|null $apiKey Optional Gemini API key override
     * @return LlmApiInterface
     */
    public static function create(?string $provider = null, ?LoggerInterface $logger = null, ?string $apiKey = null): LlmApiInterface
    {
        $logger = $logger ?? Util::createLogger();

        $envProvider = $_ENV['LLM_PROVIDER'] 
            ?? $_SERVER['LLM_PROVIDER'] 
            ?? getenv('LLM_PROVIDER') 
            ?? null;

        $envGeminiKey = $apiKey 
            ?? $_ENV['GEMINI_API_KEY'] 
            ?? $_SERVER['GEMINI_API_KEY'] 
            ?? getenv('GEMINI_API_KEY') 
            ?? null;

        $targetProvider = strtolower($provider ?? $envProvider ?? '');

        if ($targetProvider === 'gemini') {
            return new GeminiApi($logger, $envGeminiKey);
        }

        if ($targetProvider === 'ollama') {
            return new OllamaApi($logger);
        }

        // Auto-detect provider: if Gemini key is available, default to Gemini, otherwise Ollama
        if (! empty($envGeminiKey)) {
            return new GeminiApi($logger, $envGeminiKey);
        }

        return new OllamaApi($logger);
    }
}

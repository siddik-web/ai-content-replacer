<?php

namespace Tests;

use App\GeminiApi;
use App\LlmApiFactory;
use App\OllamaApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LlmApiFactoryTest extends TestCase
{
    public function testCreatesGeminiApiWhenProviderSpecified(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = LlmApiFactory::create('gemini', $logger, 'test-api-key');

        $this->assertInstanceOf(GeminiApi::class, $api);
    }

    public function testCreatesOllamaApiWhenProviderSpecified(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = LlmApiFactory::create('ollama', $logger);

        $this->assertInstanceOf(OllamaApi::class, $api);
    }

    public function testAutoDetectsGeminiKey(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = LlmApiFactory::create(null, $logger, 'some-key');

        $this->assertInstanceOf(GeminiApi::class, $api);
    }
}

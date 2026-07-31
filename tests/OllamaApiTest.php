<?php

namespace Tests;

use App\OllamaApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ArdaGnsrn\Ollama\Ollama;
use ArdaGnsrn\Ollama\Resources\Completions;
use ArdaGnsrn\Ollama\Resources\Responses\CompletionResponse;

class OllamaApiTest extends TestCase
{
    public function testInstantiationWithMockClient(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMock(Ollama::class);
        
        $api = new OllamaApi($logger, $client);
        
        $this->assertInstanceOf(OllamaApi::class, $api);
    }

    public function testGetResponseReturnsTranslation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        
        // Create mock completion response
        $completionResponse = $this->createMock(CompletionResponse::class);
        $completionResponse->response = 'Bonjour';
        
        // Create mock completions resource
        $completions = $this->createMock(Completions::class);
        $completions->expects($this->once())
            ->method('create')
            ->willReturn($completionResponse);
        
        // Create mock client
        $client = $this->createMock(Ollama::class);
        $client->expects($this->once())
            ->method('completions')
            ->willReturn($completions);
        
        $api = new OllamaApi($logger, $client);
        $result = $api->getResponse('Hello', 'fr-FR');
        
        $this->assertEquals('Bonjour', $result);
    }

    public function testGetResponseReturnsNullOnEmptyResponse(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Empty translation response received');
        
        // Create mock completion response with empty string
        $completionResponse = $this->createMock(CompletionResponse::class);
        $completionResponse->response = '';
        
        // Create mock completions resource
        $completions = $this->createMock(Completions::class);
        $completions->expects($this->once())
            ->method('create')
            ->willReturn($completionResponse);
        
        // Create mock client
        $client = $this->createMock(Ollama::class);
        $client->expects($this->once())
            ->method('completions')
            ->willReturn($completions);
        
        $api = new OllamaApi($logger, $client);
        $result = $api->getResponse('Hello', 'fr-FR');
        
        $this->assertNull($result);
    }

    public function testGetResponseRetriesOnFailure(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
            ->method('error');
        
        // Create mock completions resource that throws exception
        $completions = $this->createMock(Completions::class);
        $completions->expects($this->exactly(3))
            ->method('create')
            ->willThrowException(new \Exception('API Error'));
        
        // Create mock client
        $client = $this->createMock(Ollama::class);
        $client->expects($this->exactly(3))
            ->method('completions')
            ->willReturn($completions);
        
        $api = new OllamaApi($logger, $client);
        $result = $api->getResponse('Hello', 'fr-FR', 'gemma3', 0.0, 300, 20, 3, 0);
        
        $this->assertNull($result);
    }
}

<?php

namespace Tests;

use App\GeminiApi;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GeminiApiTest extends TestCase
{
    public function testGetResponseReturnsTranslation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $jsonResponseBody = json_encode([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '{"translation": "Bonjour"}']
                        ]
                    ]
                ]
            ]
        ]);

        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $jsonResponseBody));

        $api = new GeminiApi($logger, 'test-key', $client);
        $result = $api->getResponse('Hello', 'fr-FR');

        $this->assertEquals('Bonjour', $result);
    }

    public function testGetBatchResponseReturnsParsedJson(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $jsonResponseBody = json_encode([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode(['KEY1' => 'Hola', 'KEY2' => 'Mundo'])]
                        ]
                    ]
                ]
            ]
        ]);

        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $jsonResponseBody));

        $api = new GeminiApi($logger, 'test-key', $client);
        $result = $api->getBatchResponse(['KEY1' => 'Hello', 'KEY2' => 'World'], 'es-ES');

        $this->assertEquals(['KEY1' => 'Hola', 'KEY2' => 'Mundo'], $result);
    }

    public function testCleanTranslationOutputStripsPreamblesAndFences(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new GeminiApi($logger, 'test-key');

        $cleaned = $api->cleanTranslationOutput('Hello', "```json\nTranslation: Bonjour\n```");
        $this->assertEquals('Bonjour', $cleaned);
    }

    public function testSanitizeJoomlaStringValuePreservesPlaceholders(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new GeminiApi($logger, 'test-key');

        $sanitized = $api->sanitizeJoomlaStringValue('Hello %s items', 'Bonjour items', 'fr-FR');
        $this->assertEquals('Bonjour items %s', $sanitized);
    }
}

<?php

namespace Tests;

use App\OllamaApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OllamaApiTest extends TestCase
{
    private function createDummyClient(?string $responseText = 'Bonjour', bool $shouldThrow = false, ?object $tracker = null)
    {
        $completions = new class($responseText, $shouldThrow, $tracker) {
            private ?string $responseText;
            private bool $shouldThrow;
            private ?object $tracker;

            public function __construct(?string $responseText, bool $shouldThrow, ?object $tracker)
            {
                $this->responseText = $responseText;
                $this->shouldThrow = $shouldThrow;
                $this->tracker = $tracker;
            }

            public function create(array $params)
            {
                if ($this->tracker) {
                    $this->tracker->calls++;
                }
                if ($this->shouldThrow) {
                    throw new \Exception('API Error');
                }
                $res = new \stdClass();
                $res->response = $this->responseText;
                return $res;
            }
        };

        return new class($completions) {
            private $completions;
            public function __construct($completions) { $this->completions = $completions; }
            public function completions() { return $this->completions; }
        };
    }

    public function testInstantiationWithMockClient(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createDummyClient();
        
        $api = new OllamaApi($logger, $client);
        $this->assertInstanceOf(OllamaApi::class, $api);
    }

    public function testGetResponseReturnsTranslation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createDummyClient('Bonjour');
        
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
        
        $client = $this->createDummyClient('');
        
        $api = new OllamaApi($logger, $client);
        $result = $api->getResponse('Hello', 'fr-FR');
        
        $this->assertNull($result);
    }

    public function testGetResponseRetriesOnFailure(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
            ->method('error');
        
        $tracker = (object)['calls' => 0];
        $client = $this->createDummyClient(null, true, $tracker);
        
        $api = new OllamaApi($logger, $client);
        $result = $api->getResponse('Hello', 'fr-FR', 'gemma3:1b', 0.0, 300, 20, 3, 0);
        
        $this->assertNull($result);
        $this->assertEquals(3, $tracker->calls);
    }

    public function testGetBatchResponseReturnsParsedJson(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $jsonPayload = json_encode(['K1' => 'Hola', 'K2' => 'Mundo']);
        $client = $this->createDummyClient($jsonPayload);

        $api = new OllamaApi($logger, $client);
        $results = $api->getBatchResponse(['K1' => 'Hello', 'K2' => 'World'], 'es-ES');

        $this->assertEquals(['K1' => 'Hola', 'K2' => 'Mundo'], $results);
    }
}

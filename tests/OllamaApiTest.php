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
        $result = $api->getBatchResponse(['K1' => 'Hello', 'K2' => 'World'], 'es-ES');
        
        $this->assertEquals(['K1' => 'Hola', 'K2' => 'Mundo'], $result);
    }

    public function testCleanTranslationOutputStripsPreamblesAndMarkdown(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $dirty1 = '"Min" translates to:' . "\n\n" . 'min"';
        $this->assertEquals('min', $api->cleanTranslationOutput('Min', $dirty1));

        $dirty2 = '"%d Product" translates to: **Productnummer** or **ProductID**';
        $this->assertEquals('Productnummer or ProductID', $api->cleanTranslationOutput('%d Product', $dirty2));
    }

    /** Pattern: LLM echoes original + "translates to:" anywhere in string */
    public function testCleanOutputHandlesTranslatesToMidString(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $raw = '%s plugin is disabled" translates to: "Het plugins-plugin is uitgeschakelde.';
        $result = $api->cleanTranslationOutput('%s plugin is disabled', $raw);
        $this->assertStringNotContainsString('translates to', $result);
        $this->assertStringContainsString('uitgeschakelde', $result);
    }

    /** Pattern: LLM prepends locale label mid-string */
    public function testCleanOutputStripsLocaleLabel(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $raw = 'Order summisatie (%s items) nl-NL: Bestellingssummantie (%s items)';
        $result = $api->cleanTranslationOutput('Order summary (%s items)', $raw);
        // Must not contain the first (wrong) attempt
        $this->assertStringNotContainsString('summisatie', $result);
        // Must contain the correct translation after the locale label
        $this->assertStringContainsString('Bestellingssummantie', $result);
    }

    /** Pattern: LLM appends "followed by 'original'" */
    public function testCleanOutputStripsFollowedByAppendage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $raw = "Wijkeldens Uw Profil In Schrijven followed by 'Edit Your Profile'";
        $result = $api->cleanTranslationOutput('Edit Your Profile', $raw);
        $this->assertStringNotContainsString('followed by', $result);
        $this->assertStringNotContainsString('Edit Your Profile', $result);
        $this->assertStringContainsString('Wijkeldens', $result);
    }

    /** Pattern: LLM emits locale label + trailing asterisk markdown residue */
    public function testCleanOutputStripsTrailingAsterisks(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $raw = 'Voer uw bericht in.** nl-NL: Voewerk uw bericht.*';
        $result = $api->cleanTranslationOutput('Enter your message', $raw);
        $this->assertStringNotContainsString('nl-NL', $result);
        $this->assertStringNotContainsString('*', $result);
        $this->assertStringContainsString('Voewerk', $result);
    }


    public function testSanitizeJoomlaStringValueRestoresMissingPlaceholders(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        // LLM dropped the %d placeholder
        $result = $api->sanitizeJoomlaStringValue('%d producten gevonden', 'producten gevonden');
        $this->assertStringContainsString('%d', $result);
    }

    public function testSanitizeJoomlaStringValueStripsHtmlTags(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $result = $api->sanitizeJoomlaStringValue('Add to cart', '<b>Toevoegen</b> aan winkelwagen');
        $this->assertEquals('Toevoegen aan winkelwagen', $result);
    }

    public function testSanitizeJoomlaStringValuePreservesNamedToken(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        // Properly translated with token in place
        $result = $api->sanitizeJoomlaStringValue('Order {ORDER_LINK} placed', 'Bestelling {ORDER_LINK} geplaatst');
        $this->assertStringContainsString('{ORDER_LINK}', $result);

        // Token dropped by LLM — must be restored
        $dropped = $api->sanitizeJoomlaStringValue('Order {ORDER_LINK} placed', 'Bestelling geplaatst');
        $this->assertStringContainsString('{ORDER_LINK}', $dropped);
    }

    public function testSanitizeJoomlaStringValueFallsBackToOriginalWhenEmpty(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $result = $api->sanitizeJoomlaStringValue('Add to cart', '');
        $this->assertEquals('Add to cart', $result);
    }

    public function testDetectScriptContaminationRejectsArabicInDutch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        // "Verbvوجد" — Arabic chars mixed into nl-NL output (the exact screenshot bug)
        $this->assertTrue($api->detectScriptContamination('nl-NL', 'Verbvوجد'));
    }

    public function testDetectScriptContaminationAcceptsCleanDutch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $this->assertFalse($api->detectScriptContamination('nl-NL', 'Verwijderd'));
        $this->assertFalse($api->detectScriptContamination('nl-NL', '%d producten gevonden'));
    }

    public function testDetectScriptContaminationRejectsCjkInFrench(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $this->assertTrue($api->detectScriptContamination('fr-FR', 'Ajouter 购物车'));
    }

    public function testDetectScriptContaminationAcceptsCyrillicInRussian(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $api = new OllamaApi($logger);

        $this->assertFalse($api->detectScriptContamination('ru-RU', 'Добавить в корзину'));
    }

    public function testSanitizeJoomlaStringValueFallsBackOnScriptContamination(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');
        $api = new OllamaApi($logger);

        // Arabic contamination in nl-NL: sanitizer must return original
        $result = $api->sanitizeJoomlaStringValue('Trashed', 'Verbvوجد', 'nl-NL');
        $this->assertEquals('Trashed', $result);
    }
}

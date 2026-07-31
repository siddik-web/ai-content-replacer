<?php

namespace Tests;

use App\TranslationService;
use PHPUnit\Framework\TestCase;

class TranslationServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/translation_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testSetAndGetComponentName(): void
    {
        $service = new TranslationService();
        $service->setComponentName('com_test');

        $this->assertEquals('com_test', $service->getComponentName());
    }

    public function testSetSiteLanguagePath(): void
    {
        $service = new TranslationService();
        $result = $service->setSiteLanguagePath('/tmp/site');

        $this->assertInstanceOf(TranslationService::class, $result);
    }

    public function testSetAdminLanguagePath(): void
    {
        $service = new TranslationService();
        $result = $service->setAdminLanguagePath('/tmp/admin');

        $this->assertInstanceOf(TranslationService::class, $result);
    }

    public function testLoadTranslationsFromFile(): void
    {
        $locale = 'fr-FR';
        $componentName = 'com_test';
        
        // Create directory structure
        $langDir = $this->tempDir . '/language/fr-FR';
        mkdir($langDir, 0755, true);
        
        // Create INI file
        $iniContent = <<<INI
; French translations
COM_TEST_HELLO="Bonjour"
COM_TEST_GOODBYE="Au revoir"
COM_TEST_THANKS="Merci"
INI;
        
        file_put_contents($langDir . '/fr-FR.com_test.ini', $iniContent);

        $service = new TranslationService();
        $service->setComponentName($componentName)
                ->setSiteLanguagePath($this->tempDir . '/language');

        $translations = $service->loadTranslations($locale, false, false);

        $this->assertIsArray($translations);
        $this->assertCount(3, $translations);
        $this->assertEquals('Bonjour', $translations['COM_TEST_HELLO']);
        $this->assertEquals('Au revoir', $translations['COM_TEST_GOODBYE']);
        $this->assertEquals('Merci', $translations['COM_TEST_THANKS']);
    }

    public function testLoadTranslationsSkipsComments(): void
    {
        $locale = 'de-DE';
        $componentName = 'com_test';
        
        $langDir = $this->tempDir . '/language/de-DE';
        mkdir($langDir, 0755, true);
        
        $iniContent = <<<INI
; This is a comment
COM_TEST_HELLO="Hallo"
; Another comment
COM_TEST_WORLD="Welt"
INI;
        
        file_put_contents($langDir . '/de-DE.com_test.ini', $iniContent);

        $service = new TranslationService();
        $service->setComponentName($componentName)
                ->setSiteLanguagePath($this->tempDir . '/language');

        $translations = $service->loadTranslations($locale, false, false);

        $this->assertCount(2, $translations);
        $this->assertEquals('Hallo', $translations['COM_TEST_HELLO']);
        $this->assertEquals('Welt', $translations['COM_TEST_WORLD']);
    }

    public function testLoadTranslationsCaches(): void
    {
        $locale = 'es-ES';
        $componentName = 'com_test';
        
        $langDir = $this->tempDir . '/language/es-ES';
        mkdir($langDir, 0755, true);
        
        $iniContent = 'COM_TEST_KEY="Valor"';
        file_put_contents($langDir . '/es-ES.com_test.ini', $iniContent);

        $service = new TranslationService();
        $service->setComponentName($componentName)
                ->setSiteLanguagePath($this->tempDir . '/language');

        $translations1 = $service->loadTranslations($locale, false, false);
        
        // Modify file after first load
        file_put_contents($langDir . '/es-ES.com_test.ini', $iniContent . "\nCOM_TEST_KEY2=\"Valor2\"");
        
        $translations2 = $service->loadTranslations($locale, false, false);

        // Should return cached version (1 key, not 2)
        $this->assertCount(1, $translations2);
        $this->assertEquals($translations1, $translations2);
    }

    public function testLoadTranslationsDifferentCacheKeysForSystemFile(): void
    {
        $locale = 'it-IT';
        $componentName = 'com_test';
        
        $adminLangDir = $this->tempDir . '/administrator/language/it-IT';
        mkdir($adminLangDir, 0755, true);
        
        // Create regular admin INI file
        $iniContent = 'COM_TEST_ADMIN="Amministratore"';
        file_put_contents($adminLangDir . '/it-IT.com_test.ini', $iniContent);
        
        // Create sys INI file
        $sysIniContent = 'COM_TEST_SYS="Sistema"';
        file_put_contents($adminLangDir . '/it-IT.com_test.sys.ini', $sysIniContent);

        $service = new TranslationService();
        $service->setComponentName($componentName)
                ->setSiteLanguagePath($this->tempDir . '/language')
                ->setAdminLanguagePath($this->tempDir . '/administrator/language');

        $adminTranslations = $service->loadTranslations($locale, true, false);
        $sysTranslations = $service->loadTranslations($locale, true, true);

        $this->assertCount(1, $adminTranslations);
        $this->assertCount(1, $sysTranslations);
        $this->assertEquals('Amministratore', $adminTranslations['COM_TEST_ADMIN']);
        $this->assertEquals('Sistema', $sysTranslations['COM_TEST_SYS']);
    }

    public function testLoadTranslationsThrowsOnMissingFile(): void
    {
        $service = new TranslationService();
        $service->setComponentName('com_test')
                ->setSiteLanguagePath($this->tempDir . '/language');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Translation file not found');
        
        $service->loadTranslations('xx-XX', false, false);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

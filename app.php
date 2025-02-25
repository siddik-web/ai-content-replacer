<?php

ini_set('max_execution_time', 3000);
ini_set('memory_limit', '512M'); // Set appropriate memory limit
require_once './vendor/autoload.php';

use App\ContentReplacer;
use App\TranslationService;
use App\Util;
use Monolog\Logger;

// Constants moved to a separate config file for better maintainability
require_once 'config.php';

/**
 * TranslationApp Class
 * 
 * This class is responsible for loading and processing translations.
 */
class TranslationApp {
    private string $rootPath;
    private string $componentName;
    private string $langCode;
    private TranslationService $translationService;
    private ContentReplacer $contentReplacer;
    
    public function __construct(string $rootPath, string $componentName = 'com_sppagebuilder', string $langCode = 'en-GB') {
        $this->validatePaths($rootPath);
        
        $this->rootPath = $rootPath;
        $this->componentName = $componentName;
        $this->langCode = $langCode;
        
        $this->initializeServices();
    }
    
    private function validatePaths(string $rootPath): void {
        if (empty($rootPath) || !is_dir($rootPath)) {
            throw new InvalidArgumentException('Invalid root path provided.');
        }
    }
    
    private function initializeServices(): void {
        $siteLanguagePath = $this->buildPath(LANG_FOLDER);
        $adminLanguagePath = $this->buildPath(ADMIN_FOLDER, LANG_FOLDER);
        
        $this->translationService = new TranslationService();
        $this->translationService
            ->setComponentName($this->componentName)
            ->setSiteLanguagePath($siteLanguagePath)
            ->setAdminLanguagePath($adminLanguagePath);
            
        $this->contentReplacer = new ContentReplacer(
            $this->translationService,
            new Logger('ollama_api')
        );
    }
    
    private function buildPath(string ...$segments): string {
        return implode(DIRECTORY_SEPARATOR, array_merge([$this->rootPath], $segments));
    }
    
    public function processTranslation(string $locale, ?string $requestFileName = null): bool {
        if (empty($locale)) {
            throw new InvalidArgumentException('Invalid request! code parameter missing');
        }

        $paths = $this->constructPaths();
        
        if ($requestFileName === null) {
            return $this->processAllFiles($locale, $paths);
        } else {
            return$this->processSingleFile($locale, $requestFileName, $paths);
        }
    }
    
    private function constructPaths(): array {
        $langPath = $this->buildPath(LANG_FOLDER, $this->langCode);
        $langAdminPath = $this->buildPath(ADMIN_FOLDER, LANG_FOLDER, $this->langCode);
        
        return [
            'site' => [
                'input' => Util::constructFilePath($langPath, $this->langCode, $this->componentName, INI_EXTENSION),
                'output' => $this->buildPath(LANG_FOLDER)
            ],
            'admin' => [
                'input' => Util::constructFilePath($langAdminPath, $this->langCode, $this->componentName, INI_EXTENSION),
                'output' => $this->buildPath(ADMIN_FOLDER, LANG_FOLDER)
            ],
            'sys' => [
                'input' => Util::constructFilePath($langAdminPath, $this->langCode, $this->componentName, SYS_INI_EXTENSION),
                'output' => $this->buildPath(ADMIN_FOLDER, LANG_FOLDER)
            ]
        ];
    }
    
    private function processAllFiles(string $locale, array $paths): bool {
        foreach ($paths as $type => $path) {
            return $this->processFile($locale, $type, $path);
        }
    }
    
    private function processSingleFile(string $locale, string $requestFileName, array $paths): bool {
        if (!isset($paths[$requestFileName])) {
            throw new InvalidArgumentException('Invalid file type specified');
        }
        
        return $this->processFile($locale, $requestFileName, $paths[$requestFileName]);
    }
    
    private function processFile(string $locale, string $type, array $path): bool {
        $outputFileName = $locale . '.com_sppagebuilder' . ($type === 'sys' ? '.sys' : '') . '.ini';
        return $this->contentReplacer
            ->setOutputFileName($outputFileName)
            ->replaceContent(
                $path['input'],
                $locale,
                $path['output'],
                $type !== 'site'
            );
    }
}
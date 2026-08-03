<?php
namespace App;

use App\OllamaApi;
use Psr\Log\LoggerInterface;

class ContentReplacer
{
    private TranslationService $translationService;
    private OllamaApi $ollamaApi;
    private LoggerInterface $logger;

    public function __construct(TranslationService $translationService, OllamaApi $ollamaApi, LoggerInterface $logger)
    {
        $this->translationService = $translationService;
        $this->ollamaApi          = $ollamaApi;
        $this->logger             = $logger;
    }

    /**
     * Replace content in translation files based on the given locale.
     *
     * @param string $inputFilePath Path to the input file
     * @param string $locale Locale to translate into
     * @param string $outputBaseDir Base directory for output files
     * @param bool $isAdmin Whether the file is for admin use
     * @param string $outputFileName The name of the output file
     * @return bool True if successful, false otherwise
     */
    public function replaceContent(string $inputFilePath, string $locale, string $outputBaseDir, bool $isAdmin, string $outputFileName): bool
    {
        try {
            $localeOutputDir = $outputBaseDir . "/$locale";
            if (! is_dir($localeOutputDir)) {
                mkdir($localeOutputDir, 0755, true);
                $this->logger->info("Created directory: '$localeOutputDir'");
            }

            $outputFilePath = $localeOutputDir . "/" . $outputFileName;

            $isSystemFile = $isAdmin && strpos($inputFilePath, '.sys.ini') !== false;

            try {
                $translations = $this->translationService->loadTranslations($locale, $isAdmin, $isSystemFile);
            } catch (\RuntimeException $e) {
                $this->logger->info("Target translation file not found for locale '$locale'. A new translation file will be created.");
                $translations = [];
            }

            $baseTranslations = $this->translationService->loadTranslations('en-GB', $isAdmin, $isSystemFile);

            $missingTranslations = array_diff_key($baseTranslations, $translations);

            if (empty($missingTranslations)) {
                $this->logger->info("No missing translations found for locale '$locale'. Skipping file update.");
                return true;
            }

            $translatedMissingKeys = [];

            foreach ($missingTranslations as $key => $value) {
                $translatedValue = $this->ollamaApi->getResponse($value, $locale);
                if ($translatedValue !== null) {
                    $translatedMissingKeys[$key] = $translatedValue;
                    $this->logger->info("Translated key '$key' for locale '$locale': '$translatedValue'");
                } else {
                    $this->logger->warning("Failed to translate key '$key' for locale '$locale'");
                }
            }

            if (empty($translatedMissingKeys)) {
                $this->logger->warning("No translations were successfully produced for locale '$locale'");
                return false;
            }

            if (! $this->appendToFile($translatedMissingKeys, $outputFilePath)) {
                $this->logger->error("Failed to append translations to file: '$outputFilePath'");
                return false;
            }

            $this->logger->info("Successfully appended " . count($translatedMissingKeys) . " translations to file: '$outputFilePath'");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error during translation process: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Append content to a file, avoiding duplicate keys.
     *
     * @param array $content Array of key-value pairs to append
     * @param string $filePath Path to the output file
     * @return bool True if successful, false otherwise
     */
    private function appendToFile(array $content, string $filePath): bool
    {
        try {
            $existingContent = [];
            if (file_exists($filePath)) {
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (! empty($line)) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) === 2) {
                            $key   = trim($parts[0]);
                            $value = trim($parts[1]);
                            if (! empty($key) && ! empty($value)) {
                                $existingContent[$key] = $value;
                            }
                        }
                    }
                }
            }

            $file = fopen($filePath, 'a');
            if (! $file) {
                throw new \RuntimeException("Unable to open file for appending: '$filePath'");
            }

            $appendedCount = 0;
            foreach ($content as $key => $value) {
                if (! isset($existingContent[$key])) {
                    fwrite($file, "$key=\"$value\"" . PHP_EOL);
                    $appendedCount++;
                }
            }

            fclose($file);
            $this->logger->info("Appended $appendedCount new translations to file: '$filePath'");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error appending to file '$filePath': " . $e->getMessage());
            return false;
        }
    }
}

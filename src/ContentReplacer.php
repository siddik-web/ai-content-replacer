<?php
namespace App;

use App\OllamaApi;
use Psr\Log\LoggerInterface;

class ContentReplacer {
    private $translationService;
    private $ollamaApi;
    private $outputFileName;
    private $logger;

    public function __construct(TranslationService $translationService, LoggerInterface $logger) {
        $this->translationService = $translationService;
        $this->ollamaApi = OllamaApi::getInstance($logger);
        $this->logger = $logger;
    }

    /**
     * Replace content in translation files based on the given locale.
     *
     * @param string $inputFilePath Path to the input file
     * @param string $locale Locale to translate into
     * @param string $outputBaseDir Base directory for output files
     * @param bool $isAdmin Whether the file is for admin use
     * @return bool True if successful, false otherwise
     */
    public function replaceContent(string $inputFilePath, string $locale, string $outputBaseDir, bool $isAdmin = false): bool {
        try {

            // Define output directory and file paths
            $localeOutputDir = $outputBaseDir . "/$locale";
            if (!is_dir($localeOutputDir)) {
                mkdir($localeOutputDir, 0777, true);
                $this->logger->info("Created directory: '$localeOutputDir'");
            }

            $outputFilePath = $localeOutputDir . "/" . $this->getOutputFileName();

            $isSystemFile = $isAdmin && strpos($inputFilePath, '.sys.ini') !== false;

            // Load translations for the target locale and base locale (en-GB)
            $translations = $this->translationService->loadTranslations($locale, $isAdmin, $isSystemFile);
            $baseTranslations = $this->translationService->loadTranslations('en-GB', $isAdmin, $isSystemFile);

            // Find missing translations in the target locale
            $missingTranslations = array_diff_key($baseTranslations, $translations);

            // Translate missing keys in batches
            $translatedMissingKeys = [];
            $replacementsMade = false;
            
            foreach ($missingTranslations as $key => $value) {
                $translatedValue = $this->getTranslatedValueWithRetry($value, $locale);
                if ($translatedValue !== null) {
                    $translatedMissingKeys[$key] = $translatedValue;
                    $replacementsMade = true;

                    if (!$this->appendToFile($translatedMissingKeys, $outputFilePath)) {
                        $this->logger->error("Failed to append translations to file: '$outputFilePath'");
                        continue;
                    }
                    $this->logger->info("Translated key '$key' for locale '$locale': '$translatedValue'");
                } else {
                    $this->logger->warning("Failed to translate key '$key' for locale '$locale'");
                }
            }

            // If no replacements were made, exit early
            if (!$replacementsMade) {
                $this->logger->info("No missing translations found for locale '$locale'. Skipping file update.");
                return true; // No updates needed, but no errors occurred
            }

            $this->logger->info("Successfully appended translations to file: '$outputFilePath'");
            return true; // Indicate success
        } catch (\Exception $e) {
            $this->logger->error("Error during translation process: " . $e->getMessage());
            return false; // Indicate failure
        }
    }

    /**
     * Get the translated value for a given key using the Ollama API with retries.
     *
     * @param string $value The value to translate
     * @param string $locale The target locale
     * @return string|null The translated value or null if translation fails
     */
    private function getTranslatedValueWithRetry(string $value, string $locale): ?string {
        $maxRetries = 3;
        $retryDelay = 2; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Set a timeout for the API call (e.g., 10 seconds)
                return $this->ollamaApi->getResponse($value, $locale);
            } catch (\Exception $e) {
                $this->logger->warning("Attempt $attempt failed for value '$value' in locale '$locale': " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep($retryDelay); // Wait before retrying
                }
            }
        }

        $this->logger->error("All attempts failed for value '$value' in locale '$locale'");
        return null; // Return null if all retries fail
    }

    /**
     * Append content to a file, avoiding duplicate keys.
     *
     * @param array $content Array of key-value pairs to append
     * @param string $filePath Path to the output file
     * @return bool True if successful, false otherwise
     */
    private function appendToFile(array|string $content, string $filePath): bool {
        try {
            // Read existing content from the file if it exists
            $existingContent = [];
            if (file_exists($filePath)) {
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) === 2) {
                            $key = trim($parts[0]);
                            $value = trim($parts[1]);
                            if (!empty($key) && !empty($value)) {
                                $existingContent[$key] = $value;
                            }
                        }
                    }
                }
            }

            // Open the file in append mode
            $file = fopen($filePath, 'a');
            if (!$file) {
                throw new \RuntimeException("Unable to open file for appending: '$filePath'");
            }

            // Append only the new translations to the file
            foreach ($content as $key => $value) {
                if (!isset($existingContent[$key])) {
                    fwrite($file, "$key=\"$value\"" . PHP_EOL);
                    $this->logger->info("Appended new translation to file: '$key=$value'");
                }
            }

            fclose($file);
            return true; // Indicate success
        } catch (\Exception $e) {
            $this->logger->error("Error appending to file '$filePath': " . $e->getMessage());
            return false; // Indicate failure
        }
    }

    /**
     * Set the output file name.
     *
     * @param string $outputFileName The name of the output file
     * @return self
     */
    public function setOutputFileName(string $outputFileName): self {
        $this->outputFileName = $outputFileName;
        return $this;
    }

    /**
     * Get the output file name.
     *
     * @return string
     */
    public function getOutputFileName(): string {
        return $this->outputFileName;
    }
}
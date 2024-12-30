<?php
/**
 * TranslationService handles the loading and parsing of translation files.
 */

namespace App;

use App\OllamaApi;
use Psr\Log\LoggerInterface;

class ContentReplacer {
    private $translationService;
    private $ollamaApi;

    public function __construct(TranslationService $translationService, LoggerInterface $logger) {
        $this->translationService = $translationService;
        $this->ollamaApi = OllamaApi::getInstance($logger);
    }

    public function replaceContent(string $inputFilePath, string $locale, string $outputBaseDir): void {
        // Get translations for the current locale and admin files
        $translations = $this->translationService->getTranslations($locale);
        $adminTranslations = $this->translationService->getAdminTranslations($locale);

        // Merge translations
        $allTranslations = array_merge($translations, $adminTranslations);

        // Read the input file
        $fileContent = file_get_contents($inputFilePath);
        $lines = explode("\n", $fileContent);

        // Define output directory and file paths
        $localeOutputDir = $outputBaseDir . "/$locale";
        if (!is_dir($localeOutputDir)) {
            mkdir($localeOutputDir, 0777, true);
        }
        $outputFilePath = $localeOutputDir . "/" . $this->translationService->getComponentName() . "_replaced.ini";
        $missingKeysFilePath = "$localeOutputDir/missing_keys.ini";

        // Initialize arrays for updated lines and missing keys
        $updatedLines = [];
        $missingKeys = [];
        $replacementsMade = false;  // Flag to track if any replacements were made

        // Process each line
        foreach ($lines as $line) {
            $updatedLine = $line;
            if (preg_match('/^([^=]+)="([^"]*)"$/', trim($line), $matches)) {
                $key = trim($matches[1]);
                $originalValue = trim($matches[2]);

                // Replace key if found in translations, else mark as missing with value
                if (array_key_exists($key, $allTranslations)) {
                    $newValue = $allTranslations[$key];
                    $pattern = '/(' . preg_quote($key, '/') . '=")([^"]*)(")/';
                    $updatedLine = preg_replace($pattern, '${1}' . $newValue . '${3}', $line);
                    $replacementsMade = true;  // Set flag to true if replacement occurred
                } else {
                    // Store the missing key with its original value
                    $missingKeys[$key] = $originalValue;
                }
            }
            $updatedLines[] = $updatedLine;
        }

        // If no replacements were made, exit early and do not create or update files
        if (!$replacementsMade) {
            echo "No replacements found for locale $locale. Skipping file creation.\n";
            return;
        }

        // Write the updated content to the output file
        $this->writeToFile(implode("\n", $updatedLines), $outputFilePath);

        // If missing keys are found, log them with values
        if (!empty($missingKeys)) {
            // Try to resolve missing keys from admin translations and update the content
            $resolvedKeys = $this->tryResolveMissingKeys($missingKeys, $locale, $outputFilePath);

            // Update the missing keys file dynamically with remaining missing keys
            $remainingMissingKeys = array_diff_key($missingKeys, $resolvedKeys);
            if (!empty($remainingMissingKeys)) {
                $this->writeMissingKeysToFile($remainingMissingKeys, $missingKeysFilePath);
            } else {
                if (file_exists($missingKeysFilePath)) {
                    unlink($missingKeysFilePath); // Remove the missing keys file if all keys are resolved
                }
            }
        }
    }

    private function tryResolveMissingKeys(array $missingKeys, string $locale, string $outputFilePath): array {
        $resolvedKeys = [];
        $chunkSize = 5; // Adjust chunk size based on your server’s capacity
    
        // Split the $missingKeys array into smaller chunks
        $chunks = array_chunk($missingKeys, $chunkSize, true);
        echo "<h1>Resolved Missing Key List For Locale: $locale</h1>";
        echo "<ul>";
        foreach ($chunks as $chunk) {
            foreach ($chunk as $key => $originalValue) {
                // Use Ollama API to get translation for the missing key
                $newValue = $this->ollamaApi->getResponse($originalValue, $locale);
    
                if ($newValue !== null) {
                    // Update the output file with the new value
                    $fileContent = file_get_contents($outputFilePath);
                    $pattern = '/(' . preg_quote($key, '/') . '=")([^"]*)(")/';
                    $updatedContent = preg_replace($pattern, '${1}' . $newValue . '${3}', $fileContent);
                    file_put_contents($outputFilePath, $updatedContent);
                    $resolvedKeys[$key] = $newValue;
                    
                    echo "<li><strong>$key</strong>:" . $newValue . "</li>";
                }
            }
    
            // Optional: Add a short sleep to reduce load on the API and server
            sleep(2); // Adjust delay if needed
        }

        echo "</ul>";
    
        return $resolvedKeys;
    }
    

    private function writeToFile(string $content, string $filePath): void
    {
        $result = file_put_contents($filePath, $content);
        if ($result === false) {
            throw new \RuntimeException("Failed to write to file: $filePath");
        }
    }

    private function writeMissingKeysToFile(array $missingKeys, string $filePath): void {
        $content = '';
        foreach ($missingKeys as $key => $value) {
            $content .= "$key=\"$value\"\n"; // Write in key="value" format
        }
        $this->writeToFile($content, $filePath);
    }
}

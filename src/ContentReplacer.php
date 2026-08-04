<?php
namespace App;

use App\OllamaApi;
use Psr\Log\LoggerInterface;

class ContentReplacer
{
    private TranslationService $translationService;
    private LlmApiInterface $llmApi;
    private LoggerInterface $logger;
    private TranslationCache $cache;

    public function __construct(
        TranslationService $translationService,
        LlmApiInterface $llmApi,
        LoggerInterface $logger,
        ?TranslationCache $cache = null
    ) {
        $this->translationService = $translationService;
        $this->llmApi             = $llmApi;
        $this->logger             = $logger;
        $this->cache              = $cache ?? new TranslationCache();
    }

    /**
     * Replace content in translation files based on the given locale.
     *
     * @param string $inputFilePath Path to the input file
     * @param string $locale Locale to translate into
     * @param string $outputBaseDir Base directory for output files
     * @param bool $isAdmin Whether the file is for admin use
     * @param string $outputFileName The name of the output file
     * @param callable|null $progressCallback Optional callback: fn(int $processed, int $total, string $message)
     * @param int $batchSize Number of keys to translate per LLM batch request
     * @param array|null $selectedKeys Optional array of keys to translate
     * @param string|null $model Optional model override
     * @return bool True if successful, false otherwise
     */
    public function replaceContent(
        string $inputFilePath,
        string $locale,
        string $outputBaseDir,
        bool $isAdmin,
        string $outputFileName,
        ?callable $progressCallback = null,
        int $batchSize = 15,
        ?array $selectedKeys = null,
        ?string $model = null
    ): bool {
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

            if ($selectedKeys !== null && ! empty($selectedKeys)) {
                $missingTranslations = array_intersect_key($missingTranslations, array_flip($selectedKeys));
            }
            $totalKeys = count($missingTranslations);

            if (empty($missingTranslations)) {
                $this->logger->info("No missing translations found for locale '$locale'. Skipping file update.");
                if ($progressCallback) {
                    $progressCallback(0, 0, "No missing translations found for locale '$locale'.");
                }
                return true;
            }

            $translatedMissingKeys = [];
            $uncachedKeys = [];

            // 1. First pass: check cache for each missing key
            foreach ($missingTranslations as $key => $value) {
                $cached = $this->cache->get($value, $locale);
                if ($cached !== null) {
                    $translatedMissingKeys[$key] = $cached;
                    $this->logger->info("Translated key '$key' for locale '$locale': '$cached' (from cache)");
                } else {
                    $uncachedKeys[$key] = $value;
                }
            }

            $processedCount = count($translatedMissingKeys);
            if ($progressCallback) {
                $progressCallback($processedCount, $totalKeys, "Loaded $processedCount keys from cache.");
            }

            // 2. Second pass: Batch process uncached keys in chunks
            if (! empty($uncachedKeys)) {
                $chunks = array_chunk($uncachedKeys, $batchSize, true);

                foreach ($chunks as $chunk) {
                    $translatedChunk = $model !== null
                        ? $this->llmApi->getBatchResponse($chunk, $locale, $model)
                        : $this->llmApi->getBatchResponse($chunk, $locale);

                    foreach ($translatedChunk as $key => $translatedValue) {
                        $translatedMissingKeys[$key] = $translatedValue;
                        $originalValue = $chunk[$key] ?? $translatedValue;
                        $this->cache->set($originalValue, $locale, $translatedValue);
                        $this->logger->info("Translated key '$key' for locale '$locale': '$translatedValue'");
                    }

                    $processedCount += count($translatedChunk);
                    if ($progressCallback) {
                        $progressCallback($processedCount, $totalKeys, "Translated batch of " . count($translatedChunk) . " keys.");
                    }
                }
            }

            if (empty($translatedMissingKeys)) {
                $this->logger->warning("No translations were successfully produced for locale '$locale'");
                return false;
            }

            if (! $this->writeOrUpdateFile($translatedMissingKeys, $outputFilePath)) {
                $this->logger->error("Failed to write/update translations to file: '$outputFilePath'");
                return false;
            }

            $this->logger->info("Successfully updated " . count($translatedMissingKeys) . " translations in file: '$outputFilePath'");
            if ($progressCallback) {
                $progressCallback($totalKeys, $totalKeys, "Successfully completed translation.");
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error during translation process: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Write or update content in a file, creating target directory/file if needed,
     * appending missing keys, and updating existing key values.
     *
     * @param array $content Array of key-value pairs to write/update
     * @param string $filePath Path to the output file
     * @return bool True if successful, false otherwise
     */
    private function writeOrUpdateFile(array $content, string $filePath): bool
    {
        try {
            $dir = dirname($filePath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $lines = [];
            $existingKeys = [];

            if (file_exists($filePath)) {
                $rawLines = file($filePath, FILE_IGNORE_NEW_LINES);
                foreach ($rawLines as $line) {
                    $trimmed = trim($line);
                    if (! empty($trimmed) && strpos($trimmed, '=') !== false && $trimmed[0] !== ';') {
                        $parts = explode('=', $line, 2);
                        $key = trim($parts[0]);
                        if (array_key_exists($key, $content)) {
                            $valEscaped = $this->cleanIniValue((string) $content[$key]);
                            $lines[] = "$key=\"$valEscaped\"";
                            $existingKeys[$key] = true;
                            continue;
                        }
                    }
                    $lines[] = $line;
                }
            }

            foreach ($content as $key => $val) {
                if (! isset($existingKeys[$key])) {
                    $valEscaped = $this->cleanIniValue((string) $val);
                    $lines[] = "$key=\"$valEscaped\"";
                }
            }

            $finalContent = implode(PHP_EOL, $lines);
            if (! empty($lines)) {
                $finalContent .= PHP_EOL;
            }

            file_put_contents($filePath, $finalContent);
            $this->logger->info("Successfully wrote/updated " . count($content) . " keys in file: '$filePath'");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error writing/updating file '$filePath': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean and sanitize translation values for single-line INI syntax.
     */
    private function cleanIniValue(string $val): string
    {
        // Flatten multiline into single line
        $val = str_replace(["\r\n", "\r", "\n"], ' ', $val);
        $val = trim($val);

        // Strip any pre-existing backslash-quote escaping so we can re-apply once cleanly
        $val = str_replace('\"', '"', $val);

        // Strip wrapping outer quotes (single or double) that LLMs sometimes add
        while (
            (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))
        ) {
            $val = substr($val, 1, -1);
            $val = trim($val);
        }

        // Escape inner quotes once for valid Joomla .ini syntax: KEY="value with \"quotes\""
        return str_replace('"', '\"', $val);
    }
}

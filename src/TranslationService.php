<?php

namespace App;

/**
 * TranslationService handles the loading and parsing of translation files.
 */
class TranslationService
{
    /**
     * @var string Path to the public language files.
     */
    private string $baseLanguagePath;

    /**
     * @var string Path to the admin language files.
     */
    private string $baseAdminLanguagePath;

    /**
     * Component name
     *
     * @var string
     */
    private string $componentName;

    /**
     * Constructor to initialize paths for language files.
     *
     * @param string $baseLanguagePath Path to the public language files. Defaults to /language.
     * @param string $baseAdminLanguagePath Path to the admin language files. Defaults to /administrator/language.
     */
    public function __construct(
        string $baseLanguagePath = __DIR__ . "/language",
        string $baseAdminLanguagePath = __DIR__ . "/administrator/language"
    ) {
        $this->baseLanguagePath = $baseLanguagePath;
        $this->baseAdminLanguagePath = $baseAdminLanguagePath;
    }

    /**
     * Sets the base path for public language files.
     *
     * @param string $path The new base path for public language files.
     */
    public function setBaseLanguagePath(string $path): void
    {
        $this->baseLanguagePath = $path;
    }

    /**
     * Sets the base path for admin language files.
     *
     * @param string $path The new base path for admin language files.
     */
    public function setBaseAdminLanguagePath(string $path): void
    {
        $this->baseAdminLanguagePath = $path;
    }

    /**
     * Retrieves translations for the specified locale.
     *
     * @param string $locale The locale for which to load translations.
     * @return array Associative array of translations.
     */
    public function getTranslations(string $locale): array
    {
        return $this->loadTranslations($locale, false);
    }

    /**
     * Retrieves admin translations for the specified locale.
     *
     * @param string $locale The locale for which to load admin translations.
     * @return array Associative array of admin translations.
     */
    public function getAdminTranslations(string $locale): array
    {
        return $this->loadTranslations($locale, true);
    }

    /**
     * Loads and parses translations from a file based on locale and context.
     *
     * @param string $locale The locale of the translations.
     * @param bool $isAdmin Whether to load admin translations.
     * @return array Associative array of translations.
     * @throws \RuntimeException If the translation file does not exist.
     */
    private function loadTranslations(string $locale, bool $isAdmin): array
    {
        $filePath = $this->generateFilePath($locale, $isAdmin);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Translation file not found: $filePath");
        }

        return $this->parseTranslationsFromFile($filePath);
    }

    /**
     * Generates the file path for the translation file based on locale and context.
     *
     * @param string $locale The locale of the translations.
     * @param bool $isAdmin Whether to load the admin language file path.
     * @return string The generated file path for the translation file.
     */
    private function generateFilePath(string $locale, bool $isAdmin): string
    {
        $basePath = $isAdmin ? $this->baseAdminLanguagePath : $this->baseLanguagePath;
        return "$basePath/$locale/{$this->getComponentName()}.ini";
    }

    /**
     * Parses translations from a file into an associative array.
     *
     * @param string $filePath The path to the translation file.
     * @return array Associative array of translations.
     */
    private function parseTranslationsFromFile(string $filePath): array
    {
        $translations = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if ($this->isCommentOrEmpty($line)) {
                continue;
            }

            $parsed = $this->parseLine($line);
            if ($parsed) {
                [$key, $value] = $parsed;
                $translations[$key] = $value;
            }
        }

        return $translations;
    }

    /**
     * Checks if a line is a comment or an empty line.
     *
     * @param string $line The line to check.
     * @return bool True if the line is a comment or empty, otherwise false.
     */
    private function isCommentOrEmpty(string $line): bool
    {
        return empty(trim($line)) || strpos(trim($line), ';') === 0;
    }

    /**
     * Parses a line in the INI format for key-value pairs.
     *
     * @param string $line The line to parse.
     * @return array|null Array containing the key and value if parsed, otherwise null.
     */
    private function parseLine(string $line): ?array
    {
        if (preg_match('/^([^=]+)=(["\'])(.*)\2$/', trim($line), $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[3]);
            return [$key, $value];
        }
        
        return null;
    }

    /**
     * Sets the component name for translations.
     *
     * @param string $componentName The component name.
     */
    public function setComponentName(string $componentName): self
    {
        $this->componentName = $componentName;
        return $this;
    }

    /**
     * Retrieves the component name for translations.
     *
     * @return string The component name.
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }
}


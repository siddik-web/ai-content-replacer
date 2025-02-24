<?php

namespace App;

/**
 * TranslationService handles the loading and parsing of translation files.
 */
class TranslationService
{
    /**
     * @var string Path to the site language files.
     */
    private string $siteLanguagePath;

    /**
     * @var string Path to the admin language files.
     */
    private string $adminLanguagePath;

    /**
     * @var string Locale.
     */
    private string $locale;

    /**
     * Component name
     *
     * @var string
     */
    private string $componentName;

    /**
     * Cache for loaded translation files
     *
     * @var array
     */
    private array $cache = [];

    /**
     * Sets the base path for site language files.
     *
     * @param string $path The new base path for site language files.
     */
    public function setSiteLanguagePath(string $path): self
    {
        $this->siteLanguagePath = $path;

        return $this;
    }

    /**
     * Sets the base path for admin language files.
     *
     * @param string $path The new base path for admin language files.
     */
    public function setAdminLanguagePath(string $path): self
    {
        $this->adminLanguagePath = $path;

        return $this;
    }

    /**
     * Loads and parses translations from a file based on locale and context.
     *
     * @param string $locale The locale of the translations.
     * @param bool $isAdmin Whether to load admin translations.
     * @return array Associative array of translations.
     * @throws \RuntimeException If the translation file does not exist.
     */
    public function loadTranslations(string $locale, bool $isAdmin = false, $isSystemFile = false): array
    {
        $cacheKey = $this->getCacheKey($locale, $isAdmin);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $filePath = $this->generateFilePath($locale, $isAdmin, $isSystemFile);
        $translations = $this->parseTranslationsFromFile($filePath);
        
        $this->cache[$cacheKey] = $translations;
        
        return $translations;
    }

    /**
     * Generates the file path for the translation file based on locale and context.
     *
     * @param string $locale The locale of the translations.
     * @param bool $isAdmin Whether to load the admin language file path.
     * @return string The generated file path for the translation file.
     */
    private function generateFilePath(string $locale, bool $isAdmin, $isSystemFile = false): string
    {
        $basePath = $isAdmin ? $this->adminLanguagePath : $this->siteLanguagePath;

        $fileExtension  = 'ini';

        if ($isAdmin) {
            $fileExtension = $isSystemFile ? 'sys.ini' : 'ini';
        }

        return "$basePath/$locale/{$locale}.{$this->getComponentName()}." . $fileExtension;
    }

    /**
     * Parses translations from a file into an associative array.
     *
     * @param string $filePath The path to the translation file.
     * @return array Associative array of translations.
     */
    private function parseTranslationsFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Translation file not found: $filePath");
        }

        $translations = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: $filePath");
        }
        
        while (($line = fgets($handle)) !== false) {
            if ($this->isCommentOrEmpty($line)) {
                continue;
            }
            
            $parsed = $this->parseLine($line);
            if ($parsed) {
                [$key, $value] = $parsed;
                $translations[$key] = $value;
            }
        }
        
        fclose($handle);

        return $translations;
    }

    /**
     * Checks if a line is a comment or an empty line.
     *
     * @param string $line The line to check.
     * 
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
     * 
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
     * 
     * @return self
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
     * 
     * @return self
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /**
     * Retrieves the locale for translations.
     *
     * @return string The locale.
     * 
     * @return self
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets the locale for translations.
     *
     * @param string $locale The locale.
     * 
     * @return self
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the cache key for a given locale and admin status.
     * 
     * @param string $locale The locale.
     * 
     * @return string The cache key.
     */
    private function getCacheKey(string $locale, bool $isAdmin): string {
        return $locale . ($isAdmin ? '_admin' : '_site');
    }
}


<?php

namespace App;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;

class TranslationCache
{
    private CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        if ($cache === null) {
            $psr6Cache = new FilesystemAdapter('translations', 0, __DIR__ . '/../logs/cache');
            $this->cache = new Psr16Cache($psr6Cache);
        } else {
            $this->cache = $cache;
        }
    }

    /**
     * Get a cached translation if available.
     */
    public function get(string $text, string $locale): ?string
    {
        $key = $this->getCacheKey($text, $locale);
        return $this->cache->get($key, null);
    }

    /**
     * Store a translation in cache.
     */
    public function set(string $text, string $locale, string $translation, ?int $ttl = null): bool
    {
        $key = $this->getCacheKey($text, $locale);
        return $this->cache->set($key, $translation, $ttl);
    }

    /**
     * Store multiple translations in cache.
     */
    public function setMultiple(array $translations, string $locale, ?int $ttl = null): void
    {
        foreach ($translations as $text => $translation) {
            if ($translation !== null && $translation !== '') {
                $this->set($text, $locale, $translation, $ttl);
            }
        }
    }

    /**
     * Generate cache key for text and locale.
     */
    private function getCacheKey(string $text, string $locale): string
    {
        return 'trans_' . md5($locale . '_' . trim($text));
    }
}

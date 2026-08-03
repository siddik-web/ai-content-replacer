<?php

namespace Tests;

use App\TranslationCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class TranslationCacheTest extends TestCase
{
    public function testGetAndSetCache(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $cache = new TranslationCache($arrayCache);

        $this->assertNull($cache->get('Save Changes', 'fr-FR'));

        $cache->set('Save Changes', 'fr-FR', 'Enregistrer les modifications');
        $this->assertEquals('Enregistrer les modifications', $cache->get('Save Changes', 'fr-FR'));
    }

    public function testSetMultiple(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $cache = new TranslationCache($arrayCache);

        $items = [
            'Hello' => 'Hola',
            'World' => 'Mundo',
        ];

        $cache->setMultiple($items, 'es-ES');

        $this->assertEquals('Hola', $cache->get('Hello', 'es-ES'));
        $this->assertEquals('Mundo', $cache->get('World', 'es-ES'));
    }
}

<?php

namespace Tests;

use App\TranslationService;
use PHPUnit\Framework\TestCase;

class TranslationServiceTest extends TestCase
{
    public function testSetAndGetPaths()
    {
        $service = new TranslationService();
        $service->setSiteLanguagePath('/tmp/site')
                ->setAdminLanguagePath('/tmp/admin')
                ->setComponentName('com_test');

        $this->assertEquals('com_test', $service->getComponentName());
    }
}

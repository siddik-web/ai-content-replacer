<?php

namespace Tests;

use App\Util;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;

class UtilTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/util_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testCreateLoggerReturnsLoggerInstance(): void
    {
        $logger = Util::createLogger('test_logger');
        
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('test_logger', $logger->getName());
    }

    public function testCreateLoggerWithCustomName(): void
    {
        $logger = Util::createLogger('custom_name');
        
        $this->assertEquals('custom_name', $logger->getName());
    }

    public function testConstructFilePath(): void
    {
        $result = Util::constructFilePath('/path/to/lang', 'fr-FR', 'com_test', '.ini');
        
        $this->assertEquals('/path/to/lang/fr-FR.com_test.ini', $result);
    }

    public function testConstructFilePathWithSysExtension(): void
    {
        $result = Util::constructFilePath('/path/to/lang', 'de-DE', 'com_test', '.sys.ini');
        
        $this->assertEquals('/path/to/lang/de-DE.com_test.sys.ini', $result);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

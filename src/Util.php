<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * Utility class with helper functions for directory and file management.
 */
class Util
{
    /**
     * Creates a Monolog Logger instance with a file handler.
     *
     * @param string $name The logger name
     * @param string $logLevel The minimum log level (debug, info, notice, warning, error, critical, alert, emergency)
     * @return Logger
     */
    public static function createLogger(string $name = 'ollama_api', string $logLevel = 'info'): Logger
    {
        $logger = new Logger($name);

        $logsPath = dirname(__DIR__) . '/logs';
        if (!file_exists($logsPath)) {
            mkdir($logsPath, 0755, true);
        }

        $logsFile = $logsPath . '/ollama_api.log';
        $level = Level::from($logLevel);
        $logger->pushHandler(new StreamHandler($logsFile, $level));

        return $logger;
    }

    /**
     * Constructs a file path from base path, lang code, component name, and extension.
     *
     * @param string $basePath The base directory path
     * @param string $langCode The language code
     * @param string $componentName The component name
     * @param string $extension The file extension
     * @return string The constructed file path
     */
    public static function constructFilePath(string $basePath, string $langCode, string $componentName, string $extension): string {
        return implode(DIRECTORY_SEPARATOR, [$basePath, $langCode . '.' . $componentName . $extension]);
    }
}

<?php
ini_set('max_execution_time', 1500); //300 seconds = 5 minutes
require_once('./vendor/autoload.php');

use App\ContentReplacer;
use App\TranslationService;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

$logger = new Logger('ollama_api');

$logsPath = __DIR__ . '/logs';

if (!file_exists($logsPath)) {
    mkdir($logsPath);
}

$logsFile = $logsPath . '/ollama_api.log';
$logger->pushHandler(new StreamHandler($logsFile, Level::Error));

$inputFilePathSite = '/Users/siddiqur/Sites/Joomla4/language/en-GB/com_easystore.ini'; // Input file path
$inputFilePathAdmin = '/Users/siddiqur/Sites/Joomla4/administrator/language/en-GB/com_easystore.ini'; // Input file path

$outputFilePathSite = '/Users/siddiqur/Sites/Joomla4/language'; // Output file path
$outputFilePathAdmin = '/Users/siddiqur/Sites/Joomla4/administrator/language/'; // Output file path



// Create the TranslationService instance.
$translationService = new TranslationService($outputFilePathSite, $outputFilePathAdmin);
$contentReplacer = new ContentReplacer($translationService, $logger);

$locale = "fi-FI";

// $contentReplacer->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
// $contentReplacer->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);

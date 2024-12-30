<?php
ini_set('max_execution_time', 1500); //300 seconds = 5 minutes
require_once './vendor/autoload.php';

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

$rootPath = '/Users/siddiqur/Sites/Joomla4'; // Root path of the Joomla project
$componentName = 'com_easystore'; // Component name
$langCode = 'en-GB'; // Language code
$langFolder = 'language'; // Language folder
$adminFolder = 'administrator'; // Administrator folder
$langPath = $rootPath . '/' . $langFolder . '/' . $langCode; // Language path
$langAdminPath = $rootPath . '/' . $adminFolder . '/' . $langFolder . '/' . $langCode; // Language path

$inputFilePathSite = $langPath . '/' . $componentName . '.ini'; // Input file path
$inputFilePathAdmin = $langAdminPath . '/' . $componentName . '.ini'; // Input file path
$inputFilePathAdminSys = $langAdminPath . '/' . $componentName . '.sys.ini'; // Input file path

$outputFilePathSite = $rootPath . '/language'; // Output file path
$outputFilePathAdmin = $rootPath . '/administrator/language/'; // Output file path

// Create the TranslationService instance.
$translationService = new TranslationService();
$translationService->setBaseLanguagePath($outputFilePathSite)->setBaseAdminLanguagePath($outputFilePathAdmin)->setComponentName($componentName);
$contentReplacer = new ContentReplacer($translationService, $logger);


$requestCode = $_POST['code'] ?? '';
$requestFileName = $_POST['file'] ?? '';
$locale = $requestCode;

if (empty($requestCode)) {
    echo "<div class='container'><div class='error'>Invalid request! code parameter missing</div></div>";
    exit;
}

if (empty($requestFileName)) {
    echo "<div class='container'><div class='error'>Invalid request! file parameter missing</div></div>";
    exit;
}

switch ($requestFileName) {
    case 'site':
        $contentReplacer->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
        break;
    case 'admin':
        $contentReplacer->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);
        break;
    case 'sys':
        $contentReplacer->replaceContent($inputFilePathAdminSys, $locale, $outputFilePathAdmin);
        break;
    default:
        echo "<div class='container'><div class='error'>Invalid request</div></div>";
        break;
}
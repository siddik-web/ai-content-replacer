<?php
/**
 * Translate Language
 * @package Ollama
 * @author Md Siddiqur Rahman <siddikcoder@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */

ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
require_once './vendor/autoload.php';

use App\ContentReplacer;
use App\TranslationService;
use App\Util;

// Define constants for fixed values
define('LANG_FOLDER', 'language');
define('ADMIN_FOLDER', 'administrator');
define('INI_EXTENSION', '.ini');
define('SYS_INI_EXTENSION', '.sys.ini');

Util::writeLog();

$rootPath = '/Users/siddiqur/Sites/sppb5'; // Root path of the Joomla project

// Validate $rootPath to ensure it's set and not empty
if (!isset($rootPath) || empty($rootPath)) {
    throw new InvalidArgumentException('$rootPath must be defined and non-empty.');
}

$componentName = 'com_sppagebuilder'; // Component name
$langCode = 'en-GB'; // Language code

// Construct language paths using a helper function for clarity
$langPath = implode(DIRECTORY_SEPARATOR, [$rootPath, LANG_FOLDER, $langCode]);
$langAdminPath = implode(DIRECTORY_SEPARATOR, [$rootPath, ADMIN_FOLDER, LANG_FOLDER, $langCode]);

// Validate required variables
if (!isset($langPath, $langAdminPath, $componentName) || empty($componentName)) {
    throw new InvalidArgumentException('$langPath, $langAdminPath, and $componentName must be defined and non-empty.');
}

// Construct input file paths
$inputFilePathSite = Util::constructFilePath($langPath, $langCode, $componentName, INI_EXTENSION);
$inputFilePathAdmin = Util::constructFilePath($langAdminPath, $langCode, $componentName, INI_EXTENSION);
$inputFilePathAdminSys = Util::constructFilePath($langAdminPath, $langCode, $componentName, SYS_INI_EXTENSION);

$siteLanguagePath = implode(DIRECTORY_SEPARATOR, [$rootPath, LANG_FOLDER]); // Output file path
$adminLanguagePath = implode(DIRECTORY_SEPARATOR, [$rootPath, ADMIN_FOLDER, LANG_FOLDER]); // Output file path

// Create the TranslationService instance.
$translationService = new TranslationService();
$translationService->setComponentName($componentName)
                    ->setSiteLanguagePath($siteLanguagePath)
                    ->setAdminLanguagePath($adminLanguagePath);

$contentReplacer = new ContentReplacer($translationService, Util::createLogger());


$locale = $_POST['code'] ?? '';
$requestFileName = $_POST['file'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($locale)) {
    echo "<div class='container'><div class='error'>Invalid request! code parameter missing</div></div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($locale)) {
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathSite, $locale, $siteLanguagePath);
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathAdmin, $locale, $adminLanguagePath);
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.sys.ini')->replaceContent($inputFilePathAdminSys, $locale, $adminLanguagePath, true);
}

switch ($requestFileName) {
    case 'site':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathSite, $locale, $siteLanguagePath);
        break;
    case 'admin':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathAdmin, $locale, $adminLanguagePath);
        break;
    case 'sys':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.sys.ini')->replaceContent($inputFilePathAdminSys, $locale, $adminLanguagePath, true);
        break;

}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate Language - Ollama Translate Language</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f1f1;
        }

        .container {
            width: 50%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
            margin-left: 10px;
            text-align: center;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-group {
            width: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Translate Language - Ollama Translate Language</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="form-group">
                <label for="code">Code:</label>
                <input type="text" id="code" name="code" value="">
            </div>
            <div class="form-group">
                <label for="file">File:</label>
                <select id="file" name="file">
                    <option value="">Select</option>
                    <option value="site">Site</option>
                    <option value="admin">Admin</option>
                    <option value="sys">Admin Sys</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit">
            </div>
        </form>
    </div>
</body>
</html>

<?php
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

$outputFilePathSite = implode(DIRECTORY_SEPARATOR, [$rootPath, LANG_FOLDER]); // Output file path
$outputFilePathAdmin = implode(DIRECTORY_SEPARATOR, [$rootPath, ADMIN_FOLDER, LANG_FOLDER]); // Output file path

// Create the TranslationService instance.
$translationService = new TranslationService();
$translationService->setBaseLanguagePath($outputFilePathSite)
                    ->setBaseAdminLanguagePath($outputFilePathAdmin)
                    ->setComponentName($componentName);
$contentReplacer = new ContentReplacer($translationService, $logger);


$locale = $_POST['code'] ?? '';
$requestFileName = $_POST['file'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($locale)) {
    echo "<div class='container'><div class='error'>Invalid request! code parameter missing</div></div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($locale)) {
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);
    $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.sys.ini')->replaceContent($inputFilePathAdminSys, $locale, $outputFilePathAdmin, true);
}

switch ($requestFileName) {
    case 'site':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
        break;
    case 'admin':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.ini')->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);
        break;
    case 'sys':
        $contentReplacer->setOutputFileName($locale . '.com_sppagebuilder.sys.ini')->replaceContent($inputFilePathAdminSys, $locale, $outputFilePathAdmin, true);
        break;

}
?>







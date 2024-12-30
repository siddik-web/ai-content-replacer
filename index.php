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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($requestCode)) {
    echo "<div class='container'><div class='error'>Invalid request! code parameter missing</div></div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($requestFileName)) {
    $contentReplacer->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
    $contentReplacer->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);
    $contentReplacer->replaceContent($inputFilePathAdminSys, $locale, $outputFilePathAdmin, true);
}

switch ($requestFileName) {
    case 'site':
        $contentReplacer->replaceContent($inputFilePathSite, $locale, $outputFilePathSite);
        break;
    case 'admin':
        $contentReplacer->replaceContent($inputFilePathAdmin, $locale, $outputFilePathAdmin);
        break;
    case 'sys':
        $contentReplacer->replaceContent($inputFilePathAdminSys, $locale, $outputFilePathAdmin, true);
        break;
}
?>







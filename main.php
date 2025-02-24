<?php

require_once 'app.php';

$requestCode = $_POST['code'];
$requestFileName = $_POST['file'];

$translationApp = new TranslationApp('/Users/siddiqur/Sites/sppb5');
$translationApp->processTranslation($requestCode, $requestFileName);
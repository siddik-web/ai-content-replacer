<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

use App\TranslationService;
use App\TranslationCache;
use App\Util;

header('Content-Type: application/json');

try {
    $projectPath   = $_GET['projectPath'] ?? $_POST['projectPath'] ?? ($_ENV['PROJECT_PATH'] ?? '');
    $componentName = $_GET['componentName'] ?? $_POST['componentName'] ?? ($_ENV['COMPONENT_NAME'] ?? 'com_sppagebuilder');
    $code          = $_GET['code'] ?? $_POST['code'] ?? '';
    $fileType      = $_GET['file'] ?? $_POST['file'] ?? '';

    if (empty($projectPath) || ! is_dir($projectPath)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing project path']);
        exit;
    }

    if (empty($code) || empty($fileType)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Language code and file type parameters are required']);
        exit;
    }

    $sitePath  = implode(DIRECTORY_SEPARATOR, [$projectPath, LANG_FOLDER]);
    $adminPath = implode(DIRECTORY_SEPARATOR, [$projectPath, ADMIN_FOLDER, LANG_FOLDER]);

    $service = new TranslationService();
    $service->setComponentName($componentName)
            ->setSiteLanguagePath($sitePath)
            ->setAdminLanguagePath($adminPath);

    $isAdmin      = $fileType !== 'site';
    $isSystemFile = $isAdmin && $fileType === 'sys';

    // Load base en-GB translations
    $baseTranslations = $service->loadTranslations('en-GB', $isAdmin, $isSystemFile);
    $totalBase = count($baseTranslations);

    // Load target translations
    try {
        $targetTranslations = $service->loadTranslations($code, $isAdmin, $isSystemFile);
    } catch (\RuntimeException $e) {
        $targetTranslations = [];
    }

    $existingCount = count($targetTranslations);
    $missingMap    = array_diff_key($baseTranslations, $targetTranslations);
    $missingCount  = count($missingMap);

    $cache = new TranslationCache();
    $cachedCount = 0;
    $missingItems = [];

    foreach ($missingMap as $key => $value) {
        $cachedValue = $cache->get($value, $code);
        $isCached = $cachedValue !== null;
        if ($isCached) {
            $cachedCount++;
        }
        $missingItems[] = [
            'key'          => $key,
            'value'        => $value,
            'is_cached'    => $isCached,
            'cached_value' => $cachedValue,
        ];
    }

    $translatedItems = [];
    foreach ($targetTranslations as $key => $transVal) {
        $baseVal = $baseTranslations[$key] ?? '';
        $translatedItems[] = [
            'key'              => $key,
            'base_value'       => $baseVal,
            'translated_value' => $transVal,
        ];
    }

    echo json_encode([
        'status'              => 'success',
        'locale'              => $code,
        'file_type'           => $fileType,
        'total_base'          => $totalBase,
        'existing_translated' => $existingCount,
        'missing_count'       => $missingCount,
        'cached_count'        => $cachedCount,
        'missing_items'       => $missingItems,
        'translated_items'    => $translatedItems,
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}

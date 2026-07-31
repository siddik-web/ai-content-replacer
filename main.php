<?php

require_once 'app.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'));
    
    if (!is_object($input)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON input',
        ]);
        exit;
    }

    $requestCode = isset($input->code) ? trim($input->code) : '';
    $requestFileName = isset($input->file) ? trim($input->file) : '';

    if (empty($requestCode) || empty($requestFileName)) {
        http_response_code(400);
        throw new InvalidArgumentException('Code and file parameters are required');
    }

    $projectPath = isset($input->projectPath) && !empty($input->projectPath) 
        ? trim($input->projectPath) 
        : ($_ENV['PROJECT_PATH'] ?? '');
    $componentName = isset($input->componentName) && !empty($input->componentName) 
        ? trim($input->componentName) 
        : ($_ENV['COMPONENT_NAME'] ?? 'com_sppagebuilder');

    if (empty($projectPath)) {
        http_response_code(400);
        throw new InvalidArgumentException('Project path is required');
    }

    $translationApp = new TranslationApp($projectPath, $componentName);
    $success = $translationApp->processTranslation($requestCode, $requestFileName);
    
    if ($success) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Translations updated successfully.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update translations.',
        ]);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage(),
    ]);
}

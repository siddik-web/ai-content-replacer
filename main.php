<?php

require_once 'app.php';

try {
    $input = json_decode(file_get_contents('php://input'));
    
    $requestCode = isset($input->code) ? $input->code :'';
    $requestFileName = isset($input->file) ? $input->file : '';

    if (empty($requestCode) || empty($requestFileName)) {
        throw new InvalidArgumentException('Code and file parameters are required');
    }

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $projectPath = isset($input->projectPath) && !empty($input->projectPath) ? $input->projectPath : ($_ENV['PROJECT_PATH'] ?? '');
    $componentName = isset($input->componentName) && !empty($input->componentName) ? $input->componentName : ($_ENV['COMPONENT_NAME'] ?? 'com_sppagebuilder');

    if (empty($projectPath)) {
        throw new InvalidArgumentException('Project path is required');
    }

    $translationApp = new TranslationApp($projectPath, $componentName);
    $success = $translationApp->processTranslation($requestCode, $requestFileName);
    if ($success) {
        // Return success response with updated content
        echo json_encode([
            'status' => 'success',
            'message' => 'Translations updated successfully.'
        ]);
    } else {
        // Return failure response
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update translations.',
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' =>  $e->getMessage(),
    ]);
}

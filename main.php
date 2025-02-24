<?php

require_once 'app.php';

try {
    $input = json_decode(file_get_contents('php://input'));
    
    $requestCode = isset($input->code) ? $input->code :'';
    $requestFileName = isset($input->file) ? $input->file : '';

    if (empty($requestCode) || empty($requestFileName)) {
        throw new InvalidArgumentException('Code and file parameters are required');
    }

    $translationApp = new TranslationApp('/Users/siddiqur/Sites/sppb5');
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

<?php

require_once 'app.php';

use App\JobManager;

// 1. CLI Execution Mode (e.g. php main.php --job-id=job_123)
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['job-id:']);
    $jobId   = $options['job-id'] ?? null;

    if ($jobId) {
        $jobManager = new JobManager();
        $jobData    = $jobManager->getJob($jobId);

        if (! $jobData) {
            fwrite(STDERR, "Job not found: {$jobId}\n");
            exit(1);
        }

        try {
            $selectedKeys   = $jobData['selected_keys'] ?? null;
            $provider       = $jobData['provider'] ?? null;
            $apiKey         = $jobData['api_key'] ?? null;
            $model          = $jobData['model'] ?? null;

            $translationApp = new TranslationApp($jobData['project_path'], $jobData['component_name'], 'en-GB', $provider, $apiKey, $model);
            $success        = $translationApp->processTranslation(
                $jobData['locale'],
                $jobData['file_type'],
                function (int $processed, int $total, string $message) use ($jobManager, $jobId) {
                    $jobManager->updateProgress($jobId, $processed, $total, $message);
                },
                $selectedKeys
            );

            if ($success) {
                $jobManager->completeJob($jobId, 'Translation job finished successfully.');
            } else {
                $jobManager->failJob($jobId, 'Translation process returned false.');
            }
        } catch (\Exception $e) {
            $jobManager->failJob($jobId, $e->getMessage());
            exit(1);
        }
        exit(0);
    }
}

// 2. HTTP Web API Execution Mode
header('Content-Type: application/json');

try {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput);

    if (! is_object($input)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid JSON input',
        ]);
        exit;
    }

    $requestCode     = isset($input->code) ? trim($input->code) : '';
    $requestFileName = isset($input->file) ? trim($input->file) : '';

    if (empty($requestCode) || empty($requestFileName)) {
        http_response_code(400);
        throw new InvalidArgumentException('Code and file parameters are required');
    }

    $projectPath = isset($input->projectPath) && ! empty($input->projectPath)
        ? trim($input->projectPath)
        : ($_ENV['PROJECT_PATH'] ?? '');
    $componentName = isset($input->componentName) && ! empty($input->componentName)
        ? trim($input->componentName)
        : ($_ENV['COMPONENT_NAME'] ?? 'com_sppagebuilder');

    if (empty($projectPath) || !is_dir($projectPath)) {
        http_response_code(400);
        throw new InvalidArgumentException('Invalid project path provided.');
    }

    $selectedKeys = isset($input->selected_keys) && is_array($input->selected_keys) ? $input->selected_keys : null;
    $isAsync      = isset($input->async) ? (bool)$input->async : false;

    $provider = isset($input->provider) ? trim($input->provider) : null;
    $apiKey   = isset($input->apiKey) ? trim($input->apiKey) : (isset($input->api_key) ? trim($input->api_key) : null);
    $model    = isset($input->model) ? trim($input->model) : null;

    if ($isAsync) {
        $jobManager = new JobManager();
        $jobId      = $jobManager->createJob($requestCode, $requestFileName, $projectPath, $componentName, $selectedKeys, $provider, $apiKey, $model);

        // Spawn background CLI process
        $phpBin = PHP_BINARY ?: 'php';
        $cmd    = sprintf('%s %s --job-id=%s > /dev/null 2>&1 &', escapeshellarg($phpBin), escapeshellarg(__DIR__ . '/main.php'), escapeshellarg($jobId));
        exec($cmd);

        http_response_code(202);
        echo json_encode([
            'status'  => 'processing',
            'job_id'  => $jobId,
            'message' => 'Translation job queued in background.',
        ]);
        exit;
    }

    // Synchronous execution fallback
    $translationApp = new TranslationApp($projectPath, $componentName, 'en-GB', $provider, $apiKey, $model);
    $success        = $translationApp->processTranslation($requestCode, $requestFileName, null, $selectedKeys);

    if ($success) {
        echo json_encode([
            'status'  => 'success',
            'message' => 'Translations updated successfully.',
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Failed to update translations.',
        ]);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'An unexpected error occurred: ' . $e->getMessage(),
    ]);
}

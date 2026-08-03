<?php

require_once 'vendor/autoload.php';

use App\JobManager;

header('Content-Type: application/json');

$jobId = $_GET['job_id'] ?? $_GET['id'] ?? '';

if (empty($jobId)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing job_id parameter',
    ]);
    exit;
}

$jobManager = new JobManager();
$jobData = $jobManager->getJob($jobId);

if (! $jobData) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Job not found',
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'job'    => $jobData,
]);

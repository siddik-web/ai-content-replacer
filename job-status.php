<?php

require_once 'vendor/autoload.php';

use App\JobManager;

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$jobId = $_GET['job_id'] ?? $_GET['id'] ?? '';

$jobManager = new JobManager();

if ($action === 'list' || isset($_GET['list'])) {
    echo json_encode([
        'status' => 'success',
        'jobs'   => $jobManager->getAllJobs(),
    ]);
    exit;
}

if ($action === 'clear') {
    $jobManager->clearAllJobs();
    echo json_encode([
        'status'  => 'success',
        'message' => 'Job history cleared successfully.',
    ]);
    exit;
}


if (empty($jobId)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing job_id parameter',
    ]);
    exit;
}

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


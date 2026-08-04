<?php

namespace App;

class JobManager
{
    private string $jobsDir;

    public function __construct(?string $jobsDir = null)
    {
        $this->jobsDir = $jobsDir ?? __DIR__ . '/../storage/jobs';
        if (! is_dir($this->jobsDir)) {
            mkdir($this->jobsDir, 0755, true);
        }
    }

    /**
     * Create a new background translation job.
     */
    public function createJob(
        string $locale, 
        string $fileType, 
        string $projectPath, 
        string $componentName, 
        ?array $selectedKeys = null,
        ?string $provider = null,
        ?string $apiKey = null,
        ?string $model = null
    ): string {
        $jobId = 'job_' . uniqid() . '_' . substr(md5(microtime()), 0, 6);
        $jobData = [
            'job_id'         => $jobId,
            'created_at'     => time(),
            'updated_at'     => time(),
            'status'         => 'pending',
            'locale'         => $locale,
            'file_type'      => $fileType,
            'project_path'   => $projectPath,
            'component_name' => $componentName,
            'selected_keys'  => $selectedKeys,
            'provider'       => $provider,
            'api_key'        => $apiKey,
            'model'          => $model,
            'progress'       => [
                'processed'  => 0,
                'total'      => 0,
                'percentage' => 0,
                'message'    => 'Job initialized...',
            ],
            'logs'           => ['Job initialized'],
            'error'          => null,
        ];

        $this->saveJob($jobId, $jobData);
        return $jobId;
    }

    /**
     * Update job status and progress.
     */
    public function updateProgress(string $jobId, int $processed, int $total, string $message = ''): void
    {
        $jobData = $this->getJob($jobId);
        if (! $jobData) {
            return;
        }

        $percentage = $total > 0 ? (int) round(($processed / $total) * 100) : 0;

        $jobData['status']              = $processed >= $total && $total > 0 ? 'completed' : 'processing';
        $jobData['updated_at']          = time();
        $jobData['progress']['processed']  = $processed;
        $jobData['progress']['total']      = $total;
        $jobData['progress']['percentage'] = $percentage;
        if (! empty($message)) {
            $jobData['progress']['message'] = $message;
            $jobData['logs'][]              = date('H:i:s') . ' - ' . $message;
        }

        $this->saveJob($jobId, $jobData);
    }

    /**
     * Mark job as completed.
     */
    public function completeJob(string $jobId, string $message = 'Translation job finished successfully.'): void
    {
        $jobData = $this->getJob($jobId);
        if (! $jobData) {
            return;
        }

        $jobData['status']              = 'completed';
        $jobData['updated_at']          = time();
        $jobData['progress']['percentage'] = 100;
        $jobData['progress']['message']    = $message;
        $jobData['logs'][]              = date('H:i:s') . ' - ' . $message;

        $this->saveJob($jobId, $jobData);
    }

    /**
     * Mark job as failed.
     */
    public function failJob(string $jobId, string $errorMessage): void
    {
        $jobData = $this->getJob($jobId);
        if (! $jobData) {
            return;
        }

        $jobData['status']              = 'failed';
        $jobData['updated_at']          = time();
        $jobData['error']               = $errorMessage;
        $jobData['progress']['message']    = 'Failed: ' . $errorMessage;
        $jobData['logs'][]              = date('H:i:s') . ' - ERROR: ' . $errorMessage;

        $this->saveJob($jobId, $jobData);
    }

    /**
     * Get job data by ID.
     */
    public function getJob(string $jobId): ?array
    {
        $filePath = $this->getJobFilePath($jobId);
        if (! file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        return json_decode($content, true) ?: null;
    }

    private function saveJob(string $jobId, array $jobData): void
    {
        $filePath = $this->getJobFilePath($jobId);
        file_put_contents($filePath, json_encode($jobData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getJobFilePath(string $jobId): string
    {
        return $this->jobsDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $jobId) . '.json';
    }
}

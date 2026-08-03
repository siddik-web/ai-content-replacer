<?php

namespace Tests;

use App\JobManager;
use PHPUnit\Framework\TestCase;

class JobManagerTest extends TestCase
{
    private string $tempJobsDir;

    protected function setUp(): void
    {
        $this->tempJobsDir = sys_get_temp_dir() . '/test_jobs_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempJobsDir)) {
            $files = glob($this->tempJobsDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->tempJobsDir);
        }
    }

    public function testCreateJobAndReadStatus(): void
    {
        $jobManager = new JobManager($this->tempJobsDir);
        $jobId = $jobManager->createJob('es-ES', 'site', '/tmp/project', 'com_test');

        $this->assertStringStartsWith('job_', $jobId);

        $jobData = $jobManager->getJob($jobId);
        $this->assertNotNull($jobData);
        $this->assertEquals('pending', $jobData['status']);
        $this->assertEquals('es-ES', $jobData['locale']);
    }

    public function testUpdateProgressAndCompletion(): void
    {
        $jobManager = new JobManager($this->tempJobsDir);
        $jobId = $jobManager->createJob('de-DE', 'admin', '/tmp/project', 'com_test');

        $jobManager->updateProgress($jobId, 5, 20, 'Translated batch 1');
        $jobData = $jobManager->getJob($jobId);

        $this->assertEquals('processing', $jobData['status']);
        $this->assertEquals(25, $jobData['progress']['percentage']);
        $this->assertEquals(5, $jobData['progress']['processed']);

        $jobManager->completeJob($jobId, 'Finished!');
        $completedData = $jobManager->getJob($jobId);

        $this->assertEquals('completed', $completedData['status']);
        $this->assertEquals(100, $completedData['progress']['percentage']);
    }
}

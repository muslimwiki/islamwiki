<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\Queue
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Queue;

use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * SabrQueue (صبر) - Background Processing and Queue Management System
 *
 * Sabr means "Patience" in Arabic. This class provides comprehensive
 * background processing, job queuing, task scheduling, and queue
 * management for the IslamWiki application.
 *
 * This system is part of the Application Layer and handles all
 * asynchronous tasks, background processing, and job management.
 *
 * @category  Core
 * @package   IslamWiki\Core\Queue
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class SabrQueue
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Queue configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Active queues.
     *
     * @var array<string, array>
     */
    protected array $queues = [];

    /**
     * Job processors.
     *
     * @var array<string, callable>
     */
    protected array $processors = [];

    /**
     * Queue statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Whether the queue system is running.
     */
    protected bool $isRunning = false;

    /**
     * Constructor.
     *
     * @param ShahidLogger $logger The logging system
     * @param array        $config Queue configuration
     */
    public function __construct(ShahidLogger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeQueue();
    }

    /**
     * Initialize queue system.
     *
     * @return self
     */
    protected function initializeQueue(): self
    {
        $this->initializeStatistics();
        $this->initializeDefaultQueues();
        $this->logger->info('SabrQueue system initialized');

        return $this;
    }

    /**
     * Initialize queue statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'jobs' => [
                'total_created' => 0,
                'total_processed' => 0,
                'total_failed' => 0,
                'total_retried' => 0,
                'pending_jobs' => 0,
                'processing_jobs' => 0
            ],
            'queues' => [
                'total_queues' => 0,
                'active_queues' => 0,
                'jobs_per_queue' => []
            ],
            'performance' => [
                'average_processing_time' => 0.0,
                'total_processing_time' => 0.0,
                'fastest_job' => PHP_FLOAT_MAX,
                'slowest_job' => 0.0
            ],
            'errors' => [
                'processing_errors' => 0,
                'retry_errors' => 0,
                'system_errors' => 0
            ]
        ];

        return $this;
    }

    /**
     * Initialize default queues.
     *
     * @return self
     */
    protected function initializeDefaultQueues(): self
    {
        $defaultQueues = [
            'default' => [
                'name' => 'Default Queue',
                'description' => 'General purpose job processing',
                'priority' => 1,
                'max_workers' => 5,
                'retry_attempts' => 3,
                'retry_delay' => 60
            ],
            'high' => [
                'name' => 'High Priority Queue',
                'description' => 'Urgent job processing',
                'priority' => 10,
                'max_workers' => 3,
                'retry_attempts' => 2,
                'retry_delay' => 30
            ],
            'low' => [
                'name' => 'Low Priority Queue',
                'description' => 'Background job processing',
                'priority' => 0,
                'max_workers' => 2,
                'retry_attempts' => 5,
                'retry_delay' => 300
            ],
            'islamic' => [
                'name' => 'Islamic Content Queue',
                'description' => 'Islamic content processing and validation',
                'priority' => 5,
                'max_workers' => 3,
                'retry_attempts' => 3,
                'retry_delay' => 120
            ],
            'email' => [
                'name' => 'Email Queue',
                'description' => 'Email sending and processing',
                'priority' => 3,
                'max_workers' => 2,
                'retry_attempts' => 3,
                'retry_delay' => 180
            ]
        ];

        foreach ($defaultQueues as $queueName => $queueConfig) {
            $this->createQueue($queueName, $queueConfig);
        }

        return $this;
    }

    /**
     * Create a new queue.
     *
     * @param string $name   Queue name
     * @param array  $config Queue configuration
     * @return self
     */
    public function createQueue(string $name, array $config): self
    {
        $this->queues[$name] = [
            'name' => $config['name'] ?? $name,
            'description' => $config['description'] ?? '',
            'priority' => $config['priority'] ?? 1,
            'max_workers' => $config['max_workers'] ?? 3,
            'retry_attempts' => $config['retry_attempts'] ?? 3,
            'retry_delay' => $config['retry_delay'] ?? 60,
            'jobs' => [],
            'processing' => [],
            'failed' => [],
            'completed' => [],
            'created_at' => time(),
            'is_active' => true
        ];

        $this->statistics['queues']['total_queues']++;
        $this->statistics['queues']['active_queues']++;
        $this->statistics['queues']['jobs_per_queue'][$name] = 0;

        $this->logger->info("Queue created: {$name}");

        return $this;
    }

    /**
     * Add a job to a queue.
     *
     * @param string $queueName Queue name
     * @param string $jobType   Job type
     * @param array  $jobData   Job data
     * @param array  $options   Job options
     * @return string Job ID
     * @throws Exception If queue doesn't exist or job creation fails
     */
    public function addJob(string $queueName, string $jobType, array $jobData, array $options = []): string
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception("Queue '{$queueName}' does not exist");
        }

        $jobId = $this->generateJobId();
        $priority = $options['priority'] ?? $this->queues[$queueName]['priority'];
        $delay = $options['delay'] ?? 0;
        $scheduledAt = time() + $delay;

        $job = [
            'id' => $jobId,
            'type' => $jobType,
            'data' => $jobData,
            'queue' => $queueName,
            'priority' => $priority,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => $options['max_attempts'] ?? $this->queues[$queueName]['retry_attempts'],
            'retry_delay' => $options['retry_delay'] ?? $this->queues[$queueName]['retry_delay'],
            'created_at' => time(),
            'scheduled_at' => $scheduledAt,
            'started_at' => null,
            'completed_at' => null,
            'error_message' => null,
            'result' => null,
            'metadata' => $options['metadata'] ?? []
        ];

        $this->queues[$queueName]['jobs'][] = $job;
        $this->statistics['jobs']['total_created']++;
        $this->statistics['jobs']['pending_jobs']++;
        $this->statistics['queues']['jobs_per_queue'][$queueName]++;

        // Sort jobs by priority and scheduled time
        $this->sortQueueJobs($queueName);

        $this->logger->info("Job added to queue {$queueName}: {$jobId} ({$jobType})");

        return $jobId;
    }

    /**
     * Generate unique job ID.
     *
     * @return string
     */
    protected function generateJobId(): string
    {
        return uniqid('job_', true);
    }

    /**
     * Sort queue jobs by priority and scheduled time.
     *
     * @param string $queueName Queue name
     * @return self
     */
    protected function sortQueueJobs(string $queueName): self
    {
        if (!isset($this->queues[$queueName]['jobs'])) {
            return $this;
        }

        usort($this->queues[$queueName]['jobs'], function ($a, $b) {
            // First sort by priority (higher priority first)
            if ($a['priority'] !== $b['priority']) {
                return $b['priority'] - $a['priority'];
            }
            
            // Then sort by scheduled time (earlier first)
            return $a['scheduled_at'] - $b['scheduled_at'];
        });

        return $this;
    }

    /**
     * Process jobs in a queue.
     *
     * @param string $queueName Queue name
     * @param int    $maxJobs   Maximum jobs to process
     * @return int Number of jobs processed
     */
    public function processQueue(string $queueName, int $maxJobs = 10): int
    {
        if (!isset($this->queues[$queueName])) {
            $this->logger->error("Queue '{$queueName}' does not exist");
            return 0;
        }

        $processedCount = 0;
        $queue = &$this->queues[$queueName];

        // Process jobs that are scheduled and ready
        foreach ($queue['jobs'] as $key => $job) {
            if ($processedCount >= $maxJobs) {
                break;
            }

            if ($job['status'] === 'pending' && time() >= $job['scheduled_at']) {
                if ($this->processJob($queueName, $key)) {
                    $processedCount++;
                }
            }
        }

        if ($processedCount > 0) {
            $this->logger->info("Processed {$processedCount} jobs in queue {$queueName}");
        }

        return $processedCount;
    }

    /**
     * Process a specific job.
     *
     * @param string $queueName Queue name
     * @param int    $jobIndex  Job index in queue
     * @return bool Whether job was processed successfully
     */
    protected function processJob(string $queueName, int $jobIndex): bool
    {
        $queue = &$this->queues[$queueName];
        $job = &$queue['jobs'][$jobIndex];

        try {
            // Update job status
            $job['status'] = 'processing';
            $job['started_at'] = time();
            $job['attempts']++;

            $this->statistics['jobs']['pending_jobs']--;
            $this->statistics['jobs']['processing_jobs']++;

            // Move job to processing list
            $queue['processing'][] = $job;
            unset($queue['jobs'][$jobIndex]);

            // Reindex array
            $queue['jobs'] = array_values($queue['jobs']);

            // Process the job
            $startTime = microtime(true);
            $result = $this->executeJob($job);
            $processingTime = microtime(true) - $startTime;

            // Update performance statistics
            $this->updatePerformanceStatistics($processingTime);

            // Mark job as completed
            $job['status'] = 'completed';
            $job['completed_at'] = time();
            $job['result'] = $result;

            // Move to completed list
            $queue['completed'][] = $job;
            unset($queue['processing'][array_search($job, $queue['processing'])]);
            $queue['processing'] = array_values($queue['processing']);

            $this->statistics['jobs']['processing_jobs']--;
            $this->statistics['jobs']['total_processed']++;

            $this->logger->info("Job completed successfully: {$job['id']} in {$processingTime}s");

            return true;

        } catch (Exception $e) {
            $this->handleJobFailure($queueName, $jobIndex, $e->getMessage());
            return false;
        }
    }

    /**
     * Execute a job.
     *
     * @param array $job Job data
     * @return mixed Job result
     * @throws Exception If job execution fails
     */
    protected function executeJob(array $job): mixed
    {
        $jobType = $job['type'];
        $jobData = $job['data'];

        // Check if we have a processor for this job type
        if (isset($this->processors[$jobType])) {
            $processor = $this->processors[$jobType];
            return $processor($jobData, $job);
        }

        // Default job processing based on type
        switch ($jobType) {
            case 'email':
                return $this->processEmailJob($jobData);
            case 'islamic_content':
                return $this->processIslamicContentJob($jobData);
            case 'database_cleanup':
                return $this->processDatabaseCleanupJob($jobData);
            case 'cache_clear':
                return $this->processCacheClearJob($jobData);
            default:
                throw new Exception("Unknown job type: {$jobType}");
        }
    }

    /**
     * Process email job.
     *
     * @param array $jobData Job data
     * @return array
     */
    protected function processEmailJob(array $jobData): array
    {
        // TODO: Implement actual email processing
        return [
            'status' => 'sent',
            'recipient' => $jobData['to'] ?? 'unknown',
            'subject' => $jobData['subject'] ?? 'No subject',
            'sent_at' => time()
        ];
    }

    /**
     * Process Islamic content job.
     *
     * @param array $jobData Job data
     * @return array
     */
    protected function processIslamicContentJob(array $jobData): array
    {
        // TODO: Implement Islamic content processing
        return [
            'status' => 'processed',
            'content_id' => $jobData['content_id'] ?? 'unknown',
            'validation_result' => 'valid',
            'processed_at' => time()
        ];
    }

    /**
     * Process database cleanup job.
     *
     * @param array $jobData Job data
     * @return array
     */
    protected function processDatabaseCleanupJob(array $jobData): array
    {
        // TODO: Implement database cleanup
        return [
            'status' => 'completed',
            'tables_cleaned' => 0,
            'records_removed' => 0,
            'completed_at' => time()
        ];
    }

    /**
     * Process cache clear job.
     *
     * @param array $jobData Job data
     * @return array
     */
    protected function processCacheClearJob(array $jobData): array
    {
        // TODO: Implement cache clearing
        return [
            'status' => 'completed',
            'cache_cleared' => true,
            'completed_at' => time()
        ];
    }

    /**
     * Handle job failure.
     *
     * @param string $queueName    Queue name
     * @param int    $jobIndex     Job index
     * @param string $errorMessage Error message
     * @return self
     */
    protected function handleJobFailure(string $queueName, int $jobIndex, string $errorMessage): self
    {
        $queue = &$this->queues[$queueName];
        $job = &$queue['jobs'][$jobIndex];

        $job['error_message'] = $errorMessage;
        $this->statistics['jobs']['processing_jobs']--;

        // Check if job should be retried
        if ($job['attempts'] < $job['max_attempts']) {
            $job['status'] = 'pending';
            $job['scheduled_at'] = time() + $job['retry_delay'];
            $job['retry_delay'] *= 2; // Exponential backoff

            $this->statistics['jobs']['pending_jobs']++;
            $this->statistics['jobs']['total_retried']++;

            $this->logger->warning("Job failed, will retry: {$job['id']} (attempt {$job['attempts']})");
        } else {
            // Job has exceeded retry attempts
            $job['status'] = 'failed';
            $queue['failed'][] = $job;
            unset($queue['jobs'][$jobIndex]);
            $queue['jobs'] = array_values($queue['jobs']);

            $this->statistics['jobs']['total_failed']++;
            $this->statistics['errors']['processing_errors']++;

            $this->logger->error("Job failed permanently: {$job['id']} after {$job['attempts']} attempts");
        }

        return $this;
    }

    /**
     * Update performance statistics.
     *
     * @param float $processingTime Job processing time
     * @return self
     */
    protected function updatePerformanceStatistics(float $processingTime): self
    {
        $this->statistics['performance']['total_processing_time'] += $processingTime;
        
        $totalProcessed = $this->statistics['jobs']['total_processed'];
        if ($totalProcessed > 0) {
            $this->statistics['performance']['average_processing_time'] = 
                $this->statistics['performance']['total_processing_time'] / $totalProcessed;
        }

        if ($processingTime < $this->statistics['performance']['fastest_job']) {
            $this->statistics['performance']['fastest_job'] = $processingTime;
        }

        if ($processingTime > $this->statistics['performance']['slowest_job']) {
            $this->statistics['performance']['slowest_job'] = $processingTime;
        }

        return $this;
    }

    /**
     * Register a job processor.
     *
     * @param string   $jobType   Job type
     * @param callable $processor Job processor function
     * @return self
     */
    public function registerProcessor(string $jobType, callable $processor): self
    {
        $this->processors[$jobType] = $processor;
        $this->logger->info("Job processor registered for type: {$jobType}");

        return $this;
    }

    /**
     * Get queue status.
     *
     * @param string $queueName Queue name
     * @return array<string, mixed>|null
     */
    public function getQueueStatus(string $queueName): ?array
    {
        if (!isset($this->queues[$queueName])) {
            return null;
        }

        $queue = $this->queues[$queueName];
        
        return [
            'name' => $queue['name'],
            'description' => $queue['description'],
            'priority' => $queue['priority'],
            'is_active' => $queue['is_active'],
            'job_counts' => [
                'pending' => count($queue['jobs']),
                'processing' => count($queue['processing']),
                'completed' => count($queue['completed']),
                'failed' => count($queue['failed'])
            ],
            'created_at' => $queue['created_at']
        ];
    }

    /**
     * Get all queue statuses.
     *
     * @return array<string, array>
     */
    public function getAllQueueStatuses(): array
    {
        $statuses = [];
        
        foreach (array_keys($this->queues) as $queueName) {
            $statuses[$queueName] = $this->getQueueStatus($queueName);
        }

        return $statuses;
    }

    /**
     * Get queue statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Clear completed jobs from a queue.
     *
     * @param string $queueName Queue name
     * @param int    $maxAge    Maximum age in seconds
     * @return int Number of jobs cleared
     */
    public function clearCompletedJobs(string $queueName, int $maxAge = 86400): int
    {
        if (!isset($this->queues[$queueName])) {
            return 0;
        }

        $clearedCount = 0;
        $currentTime = time();
        $queue = &$this->queues[$queueName];

        foreach ($queue['completed'] as $key => $job) {
            if (($currentTime - $job['completed_at']) > $maxAge) {
                unset($queue['completed'][$key]);
                $clearedCount++;
            }
        }

        $queue['completed'] = array_values($queue['completed']);

        if ($clearedCount > 0) {
            $this->logger->info("Cleared {$clearedCount} completed jobs from queue {$queueName}");
        }

        return $clearedCount;
    }

    /**
     * Start the queue system.
     *
     * @return self
     */
    public function start(): self
    {
        if ($this->isRunning) {
            return $this;
        }

        $this->isRunning = true;
        $this->logger->info('SabrQueue system started');

        return $this;
    }

    /**
     * Stop the queue system.
     *
     * @return self
     */
    public function stop(): self
    {
        if (!$this->isRunning) {
            return $this;
        }

        $this->isRunning = false;
        $this->logger->info('SabrQueue system stopped');

        return $this;
    }

    /**
     * Check if queue system is running.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    /**
     * Get queue configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set queue configuration.
     *
     * @param array<string, mixed> $config Queue configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}

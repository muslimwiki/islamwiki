<?php
declare(strict_types=1);

namespace IslamWiki\Core\Queue\Drivers;

use IslamWiki\Core\Queue\Interfaces\QueueDriverInterface;
use IslamWiki\Core\Queue\Interfaces\JobInterface;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Memory Queue Driver
 *
 * Stores jobs in memory for fast processing (not persistent).
 */
class MemoryQueueDriver implements QueueDriverInterface
{
    private ShahidLogger $logger;
    private array $jobs = [];
    private array $failedJobs = [];
    private array $stats = [
        'total_jobs' => 0,
        'failed_jobs' => 0,
        'processing_jobs' => 0,
    ];

    /**
     * Create a new memory queue driver.
     */
    public function __construct(ShahidLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job): bool
    {
        try {
            $this->jobs[] = [
                'job' => $job,
                'created_at' => time(),
                'available_at' => time() + $job->getDelay(),
            ];

            $this->stats['total_jobs']++;
            $this->logger->info('Job pushed to memory queue', [
                'job_id' => $job->getId(),
                'queue' => $job->getQueue()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to push job to memory queue', [
                'job_id' => $job->getId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Pop a job from the queue.
     */
    public function pop(): ?JobInterface
    {
        try {
            // Sort by priority and creation time
            usort($this->jobs, function($a, $b) {
                $jobA = $a['job'];
                $jobB = $b['job'];
                
                if ($jobA->getPriority() !== $jobB->getPriority()) {
                    return $jobB->getPriority() - $jobA->getPriority();
                }
                
                return $a['created_at'] - $b['created_at'];
            });

            foreach ($this->jobs as $key => $jobData) {
                $job = $jobData['job'];
                
                if ($job->hasFailed() || $job->getAttempts() >= $job->getMaxAttempts()) {
                    continue;
                }

                if ($jobData['available_at'] > time()) {
                    continue;
                }

                // Remove from queue and return
                unset($this->jobs[$key]);
                $this->jobs = array_values($this->jobs); // Re-index array
                
                $this->stats['processing_jobs']++;
                return $job;
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to pop job from memory queue', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get the number of jobs in the queue.
     */
    public function size(string $queue = 'default'): int
    {
        $count = 0;
        foreach ($this->jobs as $jobData) {
            $job = $jobData['job'];
            if ($job->getQueue() === $queue && !$job->hasFailed()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue = 'default'): int
    {
        $count = 0;
        foreach ($this->jobs as $key => $jobData) {
            $job = $jobData['job'];
            if ($job->getQueue() === $queue) {
                unset($this->jobs[$key]);
                $count++;
            }
        }
        $this->jobs = array_values($this->jobs); // Re-index array
        
        $this->logger->info('Cleared memory queue', ['queue' => $queue, 'count' => $count]);
        return $count;
    }

    /**
     * Get failed jobs.
     */
    public function getFailed(): array
    {
        $failed = [];
        foreach ($this->jobs as $jobData) {
            $job = $jobData['job'];
            if ($job->hasFailed()) {
                $failed[] = [
                    'id' => $job->getId(),
                    'queue' => $job->getQueue(),
                    'class' => get_class($job),
                    'data' => $job->getData(),
                    'attempts' => $job->getAttempts(),
                    'failure_reason' => $job->getFailureReason(),
                    'created_at' => $jobData['created_at'],
                ];
            }
        }
        return $failed;
    }

    /**
     * Clear failed jobs.
     */
    public function clearFailed(): int
    {
        $count = 0;
        foreach ($this->jobs as $key => $jobData) {
            $job = $jobData['job'];
            if ($job->hasFailed()) {
                unset($this->jobs[$key]);
                $count++;
            }
        }
        $this->jobs = array_values($this->jobs); // Re-index array
        
        $this->logger->info('Cleared failed jobs from memory queue', ['count' => $count]);
        return $count;
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailed(int $maxRetries = 3): int
    {
        $count = 0;
        foreach ($this->jobs as $jobData) {
            $job = $jobData['job'];
            if ($job->hasFailed() && $job->getAttempts() < $maxRetries) {
                // Reset job state
                $job->markAsFailed(''); // Clear failure reason
                $count++;
            }
        }
        
        $this->logger->info('Retried failed jobs from memory queue', ['count' => $count]);
        return $count;
    }

    /**
     * Mark a job as failed.
     */
    public function markAsFailed(JobInterface $job): bool
    {
        foreach ($this->jobs as $jobData) {
            $queueJob = $jobData['job'];
            if ($queueJob->getId() === $job->getId()) {
                $this->stats['failed_jobs']++;
                $this->stats['processing_jobs']--;
                return true;
            }
        }
        return false;
    }

    /**
     * Get queue statistics.
     */
    public function getStats(): array
    {
        $failed = 0;
        foreach ($this->jobs as $jobData) {
            $job = $jobData['job'];
            if ($job->hasFailed()) {
                $failed++;
            }
        }

        return [
            'total_jobs' => count($this->jobs),
            'failed_jobs' => $failed,
            'processing_jobs' => $this->stats['processing_jobs'],
        ];
    }

    /**
     * Check if the driver is connected.
     */
    public function isConnected(): bool
    {
        return true; // Memory is always available
    }

    /**
     * Connect to the queue storage.
     */
    public function connect(): bool
    {
        return true;
    }

    /**
     * Disconnect from the queue storage.
     */
    public function disconnect(): void
    {
        $this->jobs = [];
        $this->failedJobs = [];
    }
} 
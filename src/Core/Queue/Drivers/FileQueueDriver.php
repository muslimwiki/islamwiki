<?php
declare(strict_types=1);

namespace IslamWiki\Core\Queue\Drivers;

use IslamWiki\Core\Queue\Interfaces\QueueDriverInterface;
use IslamWiki\Core\Queue\Interfaces\JobInterface;
use IslamWiki\Core\Logging\Shahid;

/**
 * File Queue Driver
 *
 * Stores jobs in files for simple persistence.
 */
class FileQueueDriver implements QueueDriverInterface
{
    private Shahid $logger;
    private string $storagePath;
    private array $stats = [
        'total_jobs' => 0,
        'failed_jobs' => 0,
        'processing_jobs' => 0,
    ];

    /**
     * Create a new file queue driver.
     */
    public function __construct(Shahid $logger)
    {
        $this->logger = $logger;
        $this->storagePath = __DIR__ . '/../../../../storage/queue';
        $this->ensureStorageDirectory();
    }

    /**
     * Ensure the storage directory exists.
     */
    private function ensureStorageDirectory(): void
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job): bool
    {
        try {
            $filename = $this->storagePath . '/' . $job->getId() . '.job';
            $data = [
                'id' => $job->getId(),
                'queue' => $job->getQueue(),
                'class' => get_class($job),
                'data' => $job->getData(),
                'attempts' => $job->getAttempts(),
                'max_attempts' => $job->getMaxAttempts(),
                'failed' => $job->hasFailed(),
                'failure_reason' => $job->getFailureReason(),
                'timeout' => $job->getTimeout(),
                'priority' => $job->getPriority(),
                'delay' => $job->getDelay(),
                'created_at' => time(),
                'available_at' => time() + $job->getDelay(),
            ];

            $result = file_put_contents($filename, json_encode($data));
            
            if ($result !== false) {
                $this->stats['total_jobs']++;
                $this->logger->info('Job pushed to file queue', [
                    'job_id' => $job->getId(),
                    'queue' => $job->getQueue()
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to push job to file queue', [
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
            $files = glob($this->storagePath . '/*.job');
            
            if (empty($files)) {
                return null;
            }

            // Sort by priority and creation time
            usort($files, function($a, $b) {
                $dataA = json_decode(file_get_contents($a), true);
                $dataB = json_decode(file_get_contents($b), true);
                
                if ($dataA['priority'] !== $dataB['priority']) {
                    return $dataB['priority'] - $dataA['priority'];
                }
                
                return $dataA['created_at'] - $dataB['created_at'];
            });

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                
                if ($data['failed'] || $data['attempts'] >= $data['max_attempts']) {
                    continue;
                }

                if ($data['available_at'] > time()) {
                    continue;
                }

                // Mark as processing by renaming
                $processingFile = $file . '.processing';
                if (rename($file, $processingFile)) {
                    $job = $this->createJobFromData($data);
                    $this->stats['processing_jobs']++;
                    return $job;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to pop job from file queue', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a job instance from data.
     */
    private function createJobFromData(array $data): JobInterface
    {
        $jobClass = $data['class'];
        
        if (!class_exists($jobClass)) {
            throw new \Exception("Job class {$jobClass} not found");
        }

        $job = new $jobClass($data['data'], $data['queue']);
        $job->setPriority($data['priority'] ?? 0);
        
        return $job;
    }

    /**
     * Get the number of jobs in the queue.
     */
    public function size(string $queue = 'default'): int
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $count = 0;

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['queue'] === $queue && !$data['failed']) {
                    $count++;
                }
            }

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get queue size', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue = 'default'): int
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $count = 0;

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['queue'] === $queue) {
                    if (unlink($file)) {
                        $count++;
                    }
                }
            }

            $this->logger->info('Cleared file queue', ['queue' => $queue, 'count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear file queue', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get failed jobs.
     */
    public function getFailed(): array
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $failed = [];

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['failed']) {
                    $failed[] = $data;
                }
            }

            return $failed;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get failed jobs', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear failed jobs.
     */
    public function clearFailed(): int
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $count = 0;

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['failed']) {
                    if (unlink($file)) {
                        $count++;
                    }
                }
            }

            $this->logger->info('Cleared failed jobs from file queue', ['count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear failed jobs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailed(int $maxRetries = 3): int
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $count = 0;

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['failed'] && $data['attempts'] < $maxRetries) {
                    $data['failed'] = false;
                    $data['attempts'] = 0;
                    $data['failure_reason'] = null;
                    $data['available_at'] = time();
                    
                    if (file_put_contents($file, json_encode($data))) {
                        $count++;
                    }
                }
            }

            $this->logger->info('Retried failed jobs from file queue', ['count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retry failed jobs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Mark a job as failed.
     */
    public function markAsFailed(JobInterface $job): bool
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['id'] === $job->getId()) {
                    $data['failed'] = true;
                    $data['failure_reason'] = $job->getFailureReason();
                    $data['attempts'] = $job->getAttempts() + 1;
                    
                    if (file_put_contents($file, json_encode($data))) {
                        $this->stats['failed_jobs']++;
                        $this->stats['processing_jobs']--;
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark job as failed', [
                'job_id' => $job->getId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get queue statistics.
     */
    public function getStats(): array
    {
        try {
            $files = glob($this->storagePath . '/*.job');
            $total = count($files);
            $failed = 0;
            $processing = 0;

            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data['failed']) {
                    $failed++;
                }
                if (strpos($file, '.processing') !== false) {
                    $processing++;
                }
            }

            return [
                'total_jobs' => $total,
                'failed_jobs' => $failed,
                'processing_jobs' => $processing,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get file queue stats', ['error' => $e->getMessage()]);
            return $this->stats;
        }
    }

    /**
     * Check if the driver is connected.
     */
    public function isConnected(): bool
    {
        return is_dir($this->storagePath) && is_writable($this->storagePath);
    }

    /**
     * Connect to the queue storage.
     */
    public function connect(): bool
    {
        return $this->isConnected();
    }

    /**
     * Disconnect from the queue storage.
     */
    public function disconnect(): void
    {
        // No connection to close for file-based storage
    }
} 
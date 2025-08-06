<?php
declare(strict_types=1);

/**
 * Sabr (صبر) - Queue System
 *
 * Comprehensive asynchronous job processing system for IslamWiki.
 * Sabr means "patience" in Arabic, representing the system that
 * patiently processes background tasks and time-consuming operations.
 *
 * @package IslamWiki\Core\Queue
 * @version 0.0.42
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Queue;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Queue\Interfaces\JobInterface;
use IslamWiki\Core\Queue\Interfaces\QueueDriverInterface;
use IslamWiki\Core\Queue\Jobs\EmailJob;
use IslamWiki\Core\Queue\Jobs\NotificationJob;
use IslamWiki\Core\Queue\Jobs\ReportJob;
use IslamWiki\Core\Queue\Jobs\CleanupJob;

/**
 * Sabr Queue System
 *
 * Handles asynchronous job processing including:
 * - Email sending
 * - Notification delivery
 * - Report generation
 * - System cleanup
 * - Background data processing
 * - File operations
 * - API calls
 */
class SabrQueue
{
    private AsasContainer $container;
    private ShahidLogger $logger;
    private Connection $db;
    private array $drivers = [];
    private array $config = [];
    private array $stats = [
        'jobs_processed' => 0,
        'jobs_failed' => 0,
        'jobs_queued' => 0,
        'processing_time' => 0,
    ];

    /**
     * Create a new Sabr queue system.
     */
    public function __construct(AsasContainer $container, ShahidLogger $logger, Connection $db)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->db = $db;
        $this->initializeDrivers();
        $this->logger->info('Sabr queue system initialized');
    }

    /**
     * Initialize queue drivers.
     */
    private function initializeDrivers(): void
    {
        $this->drivers = [
            'database' => new Drivers\DatabaseQueueDriver($this->db, $this->logger),
            'file' => new Drivers\FileQueueDriver($this->logger),
            'memory' => new Drivers\MemoryQueueDriver($this->logger),
            'redis' => new Drivers\RedisQueueDriver($this->logger),
        ];

        $this->logger->info('Sabr queue drivers initialized', [
            'drivers' => array_keys($this->drivers)
        ]);
    }

    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job, string $driver = 'database'): bool
    {
        try {
            $driverInstance = $this->getDriver($driver);
            $result = $driverInstance->push($job);
            
            if ($result) {
                $this->stats['jobs_queued']++;
                $this->logger->info('Job pushed to queue', [
                    'job_id' => $job->getId(),
                    'job_type' => get_class($job),
                    'driver' => $driver,
                    'queue' => $job->getQueue()
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to push job to queue', [
                'job_id' => $job->getId(),
                'error' => $e->getMessage(),
                'driver' => $driver
            ]);
            return false;
        }
    }

    /**
     * Process jobs from the queue.
     */
    public function process(string $driver = 'database', int $maxJobs = 10): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            $driverInstance = $this->getDriver($driver);
            
            for ($i = 0; $i < $maxJobs; $i++) {
                $job = $driverInstance->pop();
                
                if (!$job) {
                    break; // No more jobs
                }

                try {
                    $this->processJob($job);
                    $processed++;
                    $this->stats['jobs_processed']++;
                    
                    $this->logger->info('Job processed successfully', [
                        'job_id' => $job->getId(),
                        'job_type' => get_class($job),
                        'processing_time' => microtime(true) - $startTime
                    ]);
                } catch (\Exception $e) {
                    $failed++;
                    $this->stats['jobs_failed']++;
                    
                    $this->logger->error('Job processing failed', [
                        'job_id' => $job->getId(),
                        'job_type' => get_class($job),
                        'error' => $e->getMessage()
                    ]);

                    // Mark job as failed
                    $job->markAsFailed($e->getMessage());
                    $driverInstance->markAsFailed($job);
                }
            }

            $this->stats['processing_time'] += microtime(true) - $startTime;

            return [
                'processed' => $processed,
                'failed' => $failed,
                'total_time' => microtime(true) - $startTime
            ];
        } catch (\Exception $e) {
            $this->logger->error('Queue processing error', [
                'error' => $e->getMessage(),
                'driver' => $driver
            ]);
            return ['processed' => 0, 'failed' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process a single job.
     */
    private function processJob(JobInterface $job): void
    {
        $startTime = microtime(true);
        
        // Execute the job
        $result = $job->handle();
        
        $processingTime = microtime(true) - $startTime;
        
        $this->logger->info('Job executed', [
            'job_id' => $job->getId(),
            'job_type' => get_class($job),
            'processing_time' => $processingTime,
            'result' => $result
        ]);
    }

    /**
     * Get queue statistics.
     */
    public function getStats(): array
    {
        $driverStats = [];
        foreach ($this->drivers as $name => $driver) {
            $driverStats[$name] = $driver->getStats();
        }

        return [
            'system' => $this->stats,
            'drivers' => $driverStats,
            'total_jobs' => array_sum(array_column($driverStats, 'total_jobs')),
            'failed_jobs' => array_sum(array_column($driverStats, 'failed_jobs')),
            'processing_jobs' => array_sum(array_column($driverStats, 'processing_jobs')),
        ];
    }

    /**
     * Get a specific driver.
     */
    private function getDriver(string $driver): QueueDriverInterface
    {
        if (!isset($this->drivers[$driver])) {
            throw new \InvalidArgumentException("Queue driver '{$driver}' not found");
        }
        
        return $this->drivers[$driver];
    }

    /**
     * Create a new email job.
     */
    public function email(string $to, string $subject, string $body, array $options = []): bool
    {
        $job = new EmailJob($to, $subject, $body, $options);
        return $this->push($job);
    }

    /**
     * Create a new notification job.
     */
    public function notify(int $userId, string $type, array $data = []): bool
    {
        $job = new NotificationJob($userId, $type, $data);
        return $this->push($job);
    }

    /**
     * Create a new report generation job.
     */
    public function report(string $type, array $parameters = []): bool
    {
        $job = new ReportJob($type, $parameters);
        return $this->push($job);
    }

    /**
     * Create a new cleanup job.
     */
    public function cleanup(string $type, array $options = []): bool
    {
        $job = new CleanupJob($type, $options);
        return $this->push($job);
    }

    /**
     * Clear failed jobs.
     */
    public function clearFailed(string $driver = 'database'): int
    {
        try {
            $driverInstance = $this->getDriver($driver);
            $count = $driverInstance->clearFailed();
            
            $this->logger->info('Failed jobs cleared', [
                'count' => $count,
                'driver' => $driver
            ]);
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear failed jobs', [
                'error' => $e->getMessage(),
                'driver' => $driver
            ]);
            return 0;
        }
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailed(string $driver = 'database', int $maxRetries = 3): int
    {
        try {
            $driverInstance = $this->getDriver($driver);
            $count = $driverInstance->retryFailed($maxRetries);
            
            $this->logger->info('Failed jobs retried', [
                'count' => $count,
                'driver' => $driver,
                'max_retries' => $maxRetries
            ]);
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retry failed jobs', [
                'error' => $e->getMessage(),
                'driver' => $driver
            ]);
            return 0;
        }
    }

    /**
     * Get available drivers.
     */
    public function getDrivers(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Check if a driver is available.
     */
    public function hasDriver(string $driver): bool
    {
        return isset($this->drivers[$driver]);
    }
} 
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queue\Drivers;

use IslamWiki\Core\Queue\Interfaces\QueueDriverInterface;
use IslamWiki\Core\Queue\Interfaces\JobInterface;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Redis Queue Driver
 *
 * Stores jobs in Redis for high-performance queue processing.
 */
class RedisQueueDriver implements QueueDriverInterface
{
    private ShahidLogger $logger;
    private ?\Redis $redis;
    private array $stats = [
        'total_jobs' => 0,
        'failed_jobs' => 0,
        'processing_jobs' => 0,
    ];

    /**
     * Create a new Redis queue driver.
     */
    public function __construct(ShahidLogger $logger)
    {
        $this->logger = $logger;
        $this->redis = null;
    }

    /**
     * Connect to Redis.
     */
    private function connectToRedis(): bool
    {
        try {
            if (!$this->redis) {
                $this->redis = new \Redis();
                $this->redis->connect(
                    $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                    $_ENV['REDIS_PORT'] ?? 6379
                );

                if (isset($_ENV['REDIS_PASSWORD'])) {
                    $this->redis->auth($_ENV['REDIS_PASSWORD']);
                }

                if (isset($_ENV['REDIS_DATABASE'])) {
                    $this->redis->select((int) $_ENV['REDIS_DATABASE']);
                }
            }

            return $this->redis->ping() === '+PONG';
        } catch (\Exception $e) {
            $this->logger->error('Failed to connect to Redis', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job): bool
    {
        try {
            if (!$this->connectToRedis()) {
                return false;
            }

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

            $key = "queue:{$job->getQueue()}:jobs";
            $score = $data['available_at'];

            $result = $this->redis->zadd($key, $score, json_encode($data));

            if ($result) {
                $this->stats['total_jobs']++;
                $this->logger->info('Job pushed to Redis queue', [
                    'job_id' => $job->getId(),
                    'queue' => $job->getQueue()
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to push job to Redis queue', [
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
            if (!$this->connectToRedis()) {
                return null;
            }

            // Get jobs from all queues
            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $now = time();

                // Get jobs that are available now
                $jobs = $this->redis->zrangebyscore($key, 0, $now, ['limit' => [0, 1]]);

                if (!empty($jobs)) {
                    $jobData = json_decode($jobs[0], true);

                    if ($jobData['failed'] || $jobData['attempts'] >= $jobData['max_attempts']) {
                        // Remove failed job
                        $this->redis->zrem($key, $jobs[0]);
                        continue;
                    }

                    // Remove from queue
                    $this->redis->zrem($key, $jobs[0]);

                    // Create job instance
                    $jobClass = $jobData['class'];
                    if (!class_exists($jobClass)) {
                        throw new \Exception("Job class {$jobClass} not found");
                    }

                    $job = new $jobClass($jobData['data'], $jobData['queue']);
                    $job->setPriority($jobData['priority'] ?? 0);

                    $this->stats['processing_jobs']++;
                    return $job;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to pop job from Redis queue', [
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
        try {
            if (!$this->connectToRedis()) {
                return 0;
            }

            $key = "queue:{$queue}:jobs";
            $now = time();

            // Count jobs that are available now and not failed
            $jobs = $this->redis->zrangebyscore($key, 0, $now);
            $count = 0;

            foreach ($jobs as $jobJson) {
                $jobData = json_decode($jobJson, true);
                if (!$jobData['failed']) {
                    $count++;
                }
            }

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Redis queue size', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue = 'default'): int
    {
        try {
            if (!$this->connectToRedis()) {
                return 0;
            }

            $key = "queue:{$queue}:jobs";
            $count = $this->redis->zcard($key);

            if ($count > 0) {
                $this->redis->del($key);
            }

            $this->logger->info('Cleared Redis queue', ['queue' => $queue, 'count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear Redis queue', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get failed jobs.
     */
    public function getFailed(): array
    {
        try {
            if (!$this->connectToRedis()) {
                return [];
            }

            $failed = [];
            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $jobs = $this->redis->zrange($key, 0, -1);

                foreach ($jobs as $jobJson) {
                    $jobData = json_decode($jobJson, true);
                    if ($jobData['failed']) {
                        $failed[] = $jobData;
                    }
                }
            }

            return $failed;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get failed jobs from Redis', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear failed jobs.
     */
    public function clearFailed(): int
    {
        try {
            if (!$this->connectToRedis()) {
                return 0;
            }

            $count = 0;
            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $jobs = $this->redis->zrange($key, 0, -1);

                foreach ($jobs as $jobJson) {
                    $jobData = json_decode($jobJson, true);
                    if ($jobData['failed']) {
                        $this->redis->zrem($key, $jobJson);
                        $count++;
                    }
                }
            }

            $this->logger->info('Cleared failed jobs from Redis queue', ['count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear failed jobs from Redis', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailed(int $maxRetries = 3): int
    {
        try {
            if (!$this->connectToRedis()) {
                return 0;
            }

            $count = 0;
            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $jobs = $this->redis->zrange($key, 0, -1);

                foreach ($jobs as $jobJson) {
                    $jobData = json_decode($jobJson, true);
                    if ($jobData['failed'] && $jobData['attempts'] < $maxRetries) {
                        // Remove old job
                        $this->redis->zrem($key, $jobJson);

                        // Reset job state
                        $jobData['failed'] = false;
                        $jobData['attempts'] = 0;
                        $jobData['failure_reason'] = null;
                        $jobData['available_at'] = time();

                        // Add back to queue
                        $this->redis->zadd($key, $jobData['available_at'], json_encode($jobData));
                        $count++;
                    }
                }
            }

            $this->logger->info('Retried failed jobs from Redis queue', ['count' => $count]);
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retry failed jobs from Redis', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Mark a job as failed.
     */
    public function markAsFailed(JobInterface $job): bool
    {
        try {
            if (!$this->connectToRedis()) {
                return false;
            }

            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $jobs = $this->redis->zrange($key, 0, -1);

                foreach ($jobs as $jobJson) {
                    $jobData = json_decode($jobJson, true);
                    if ($jobData['id'] === $job->getId()) {
                        // Remove old job
                        $this->redis->zrem($key, $jobJson);

                        // Update job state
                        $jobData['failed'] = true;
                        $jobData['failure_reason'] = $job->getFailureReason();
                        $jobData['attempts'] = $job->getAttempts() + 1;

                        // Add back to queue
                        $this->redis->zadd($key, $jobData['available_at'], json_encode($jobData));

                        $this->stats['failed_jobs']++;
                        $this->stats['processing_jobs']--;
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark job as failed in Redis', [
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
            if (!$this->connectToRedis()) {
                return $this->stats;
            }

            $total = 0;
            $failed = 0;
            $queues = ['default', 'emails', 'notifications', 'reports', 'cleanup'];

            foreach ($queues as $queue) {
                $key = "queue:{$queue}:jobs";
                $jobs = $this->redis->zrange($key, 0, -1);

                foreach ($jobs as $jobJson) {
                    $jobData = json_decode($jobJson, true);
                    $total++;
                    if ($jobData['failed']) {
                        $failed++;
                    }
                }
            }

            return [
                'total_jobs' => $total,
                'failed_jobs' => $failed,
                'processing_jobs' => $this->stats['processing_jobs'],
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Redis queue stats', ['error' => $e->getMessage()]);
            return $this->stats;
        }
    }

    /**
     * Check if the driver is connected.
     */
    public function isConnected(): bool
    {
        return $this->connectToRedis();
    }

    /**
     * Connect to the queue storage.
     */
    public function connect(): bool
    {
        return $this->connectToRedis();
    }

    /**
     * Disconnect from the queue storage.
     */
    public function disconnect(): void
    {
        if ($this->redis) {
            $this->redis->close();
            $this->redis = null;
        }
    }
}

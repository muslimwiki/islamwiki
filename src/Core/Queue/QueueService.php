<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queues;

use Database;\Database

/**
 * Queue Queue Service (صبر - Patience/Persistence)
 * 
 * Background processing and job queue management system.
 * Part of the Application Layer in the Islamic core architecture.
 * 
 * @package IslamWiki\Core\Queues
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class QueueService
{
    private Database $database;
    private array $config;
    private array $drivers = [];

    public function __construct(Database $database, array $config = [])
    {
        $this->database = $database;
        $this->config = array_merge([
            'default' => 'database',
            'connections' => [
                'database' => [
                    'driver' => 'database',
                    'table' => 'mizan_jobs',
                    'connection' => 'default',
                ],
                'file' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/framework/queues',
                ],
                'memory' => [
                    'driver' => 'memory',
                ],
            ],
            'retry_after' => 90,
            'max_attempts' => 3,
            'timeout' => 60,
        ], $config);

        $this->initializeDrivers();
    }

    /**
     * Initialize queue drivers
     */
    private function initializeDrivers(): void
    {
        foreach ($this->config['connections'] as $name => $config) {
            $this->drivers[$name] = $this->createDriver($config);
        }
    }

    /**
     * Create queue driver instance
     */
    private function createDriver(array $config): QueueDriverInterface
    {
        return match ($config['driver']) {
            'database' => new QueueDatabaseDriver($this->database, $config),
            'file' => new QueueFileDriver($config),
            'memory' => new QueueMemoryDriver($config),
            default => throw new \InvalidArgumentException("Unsupported queue driver: {$config['driver']}"),
        };
    }

    /**
     * Push a job to the queue
     */
    public function push(string $job, array $data = [], string $queue = 'default'): bool
    {
        $driver = $this->getDriver();
        return $driver->push($job, $data, $queue);
    }

    /**
     * Push a delayed job to the queue
     */
    public function later(int $delay, string $job, array $data = [], string $queue = 'default'): bool
    {
        $driver = $this->getDriver();
        return $driver->later($delay, $job, $data, $queue);
    }

    /**
     * Pop a job from the queue
     */
    public function pop(string $queue = 'default'): ?QueueJob
    {
        $driver = $this->getDriver();
        return $driver->pop($queue);
    }

    /**
     * Get queue size
     */
    public function size(string $queue = 'default'): int
    {
        $driver = $this->getDriver();
        return $driver->size($queue);
    }

    /**
     * Clear queue
     */
    public function clear(string $queue = 'default'): bool
    {
        $driver = $this->getDriver();
        return $driver->clear($queue);
    }

    /**
     * Get failed jobs
     */
    public function getFailedJobs(): array
    {
        $driver = $this->getDriver();
        return $driver->getFailedJobs();
    }

    /**
     * Retry failed job
     */
    public function retry(int $jobId): bool
    {
        $driver = $this->getDriver();
        return $driver->retry($jobId);
    }

    /**
     * Delete failed job
     */
    public function deleteFailedJob(int $jobId): bool
    {
        $driver = $this->getDriver();
        return $driver->deleteFailedJob($jobId);
    }

    /**
     * Get queue driver
     */
    private function getDriver(): QueueDriverInterface
    {
        $default = $this->config['default'];
        
        if (!isset($this->drivers[$default])) {
            throw new \RuntimeException("Queue driver '{$default}' not found");
        }

        return $this->drivers[$default];
    }

    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        $stats = [];
        
        foreach ($this->drivers as $name => $driver) {
            $stats[$name] = $driver->getStats();
        }

        return $stats;
    }

    /**
     * Get queue configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

/**
 * Queue Driver Interface
 */
interface QueueDriverInterface
{
    public function push(string $job, array $data, string $queue): bool;
    public function later(int $delay, string $job, array $data, string $queue): bool;
    public function pop(string $queue): ?QueueJob;
    public function size(string $queue): int;
    public function clear(string $queue): bool;
    public function getFailedJobs(): array;
    public function retry(int $jobId): bool;
    public function deleteFailedJob(int $jobId): bool;
    public function getStats(): array;
}

/**
 * Job Class
 */
class QueueJob
{
    public int $id;
    public string $queue;
    public string $job;
    public array $data;
    public int $attempts;
    public int $maxAttempts;
    public ?int $reservedAt;
    public ?int $availableAt;
    public ?int $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->queue = $data['queue'] ?? 'default';
        $this->job = $data['job'] ?? '';
        $this->data = $data['data'] ?? [];
        $this->attempts = $data['attempts'] ?? 0;
        $this->maxAttempts = $data['max_attempts'] ?? 3;
        $this->reservedAt = $data['reserved_at'] ?? null;
        $this->availableAt = $data['available_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    /**
     * Execute the job
     */
    public function execute(): bool
    {
        if (!class_exists($this->job)) {
            throw new \RuntimeException("Job class '{$this->job}' not found");
        }

        $jobInstance = new $this->job();
        
        if (!method_exists($jobInstance, 'handle')) {
            throw new \RuntimeException("Job class '{$this->job}' must have a 'handle' method");
        }

        return $jobInstance->handle($this->data);
    }

    /**
     * Check if job can be retried
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    /**
     * Mark as reserved
     */
    public function markAsReserved(): void
    {
        $this->reservedAt = time();
    }

    /**
     * Mark as available
     */
    public function markAsAvailable(): void
    {
        $this->reservedAt = null;
    }

    /**
     * Get job data as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'queue' => $this->queue,
            'job' => $this->job,
            'data' => $this->data,
            'attempts' => $this->attempts,
            'max_attempts' => $this->maxAttempts,
            'reserved_at' => $this->reservedAt,
            'available_at' => $this->availableAt,
            'created_at' => $this->createdAt,
        ];
    }
}

/**
 * Database Queue Driver
 */
class QueueDatabaseDriver implements QueueDriverInterface
{
    private Database $database;
    private string $table;
    private array $stats = ['pushed' => 0, 'popped' => 0, 'failed' => 0];

    public function __construct(Database $database, array $config)
    {
        $this->database = $database;
        $this->table = $config['table'];
    }

    public function push(string $job, array $data, string $queue): bool
    {
        $result = $this->database->table($this->table)->insert([
            'queue' => $queue,
            'job' => $job,
            'data' => json_encode($data),
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        if ($result) {
            $this->stats['pushed']++;
            return true;
        }

        return false;
    }

    public function later(int $delay, string $job, array $data, string $queue): bool
    {
        $result = $this->database->table($this->table)->insert([
            'queue' => $queue,
            'job' => $job,
            'data' => json_encode($data),
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time() + $delay,
            'created_at' => time(),
        ]);

        if ($result) {
            $this->stats['pushed']++;
            return true;
        }

        return false;
    }

    public function pop(string $queue): ?QueueJob
    {
        $job = $this->database->table($this->table)
            ->where('queue', $queue)
            ->where('reserved_at', null)
            ->where('available_at', '<=', time())
            ->orderBy('id', 'asc')
            ->first();

        if (!$job) {
            return null;
        }

        // Mark as reserved
        $this->database->table($this->table)
            ->where('id', $job->id)
            ->update(['reserved_at' => time()]);

        $this->stats['popped']++;

        return new QueueJob([
            'id' => $job->id,
            'queue' => $job->queue,
            'job' => $job->job,
            'data' => json_decode($job->data, true),
            'attempts' => $job->attempts,
            'max_attempts' => $job->max_attempts,
            'reserved_at' => $job->reserved_at,
            'available_at' => $job->available_at,
            'created_at' => $job->created_at,
        ]);
    }

    public function size(string $queue): int
    {
        return $this->database->table($this->table)
            ->where('queue', $queue)
            ->where('reserved_at', null)
            ->where('available_at', '<=', time())
            ->count();
    }

    public function clear(string $queue): bool
    {
        $deleted = $this->database->table($this->table)
            ->where('queue', $queue)
            ->delete();

        return $deleted > 0;
    }

    public function getFailedJobs(): array
    {
        $jobs = $this->database->table($this->table)
            ->where('attempts', '>=', 3)
            ->get();

        return array_map(function ($job) {
            return new QueueJob([
                'id' => $job->id,
                'queue' => $job->queue,
                'job' => $job->job,
                'data' => json_decode($job->data, true),
                'attempts' => $job->attempts,
                'max_attempts' => $job->max_attempts,
                'reserved_at' => $job->reserved_at,
                'available_at' => $job->available_at,
                'created_at' => $job->created_at,
            ]);
        }, $jobs);
    }

    public function retry(int $jobId): bool
    {
        $result = $this->database->table($this->table)
            ->where('id', $jobId)
            ->update([
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => time(),
            ]);

        return $result > 0;
    }

    public function deleteFailedJob(int $jobId): bool
    {
        $deleted = $this->database->table($this->table)
            ->where('id', $jobId)
            ->delete();

        if ($deleted > 0) {
            $this->stats['failed']++;
            return true;
        }

        return false;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

/**
 * File Queue Driver
 */
class QueueFileDriver implements QueueDriverInterface
{
    private string $path;
    private array $stats = ['pushed' => 0, 'popped' => 0, 'failed' => 0];

    public function __construct(array $config)
    {
        $this->path = $config['path'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function push(string $job, array $data, string $queue): bool
    {
        $filename = $this->path . '/' . uniqid() . '.job';
        $jobData = [
            'queue' => $queue,
            'job' => $job,
            'data' => $data,
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ];

        $result = file_put_contents($filename, serialize($jobData));
        
        if ($result !== false) {
            $this->stats['pushed']++;
            return true;
        }

        return false;
    }

    public function later(int $delay, string $job, array $data, string $queue): bool
    {
        $filename = $this->path . '/' . uniqid() . '.job';
        $jobData = [
            'queue' => $queue,
            'job' => $job,
            'data' => $data,
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time() + $delay,
            'created_at' => time(),
        ];

        $result = file_put_contents($filename, serialize($jobData));
        
        if ($result !== false) {
            $this->stats['pushed']++;
            return true;
        }

        return false;
    }

    public function pop(string $queue): ?QueueJob
    {
        $files = glob($this->path . '/*.job');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $jobData = unserialize($content);
            
            if ($jobData['queue'] === $queue && 
                $jobData['reserved_at'] === null && 
                $jobData['available_at'] <= time()) {
                
                // Mark as reserved
                $jobData['reserved_at'] = time();
                file_put_contents($file, serialize($jobData));
                
                $this->stats['popped']++;
                
                return new QueueJob($jobData);
            }
        }

        return null;
    }

    public function size(string $queue): int
    {
        $files = glob($this->path . '/*.job');
        $count = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $jobData = unserialize($content);
            
            if ($jobData['queue'] === $queue && 
                $jobData['reserved_at'] === null && 
                $jobData['available_at'] <= time()) {
                $count++;
            }
        }
        
        return $count;
    }

    public function clear(string $queue): bool
    {
        $files = glob($this->path . '/*.job');
        $deleted = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $jobData = unserialize($content);
            
            if ($jobData['queue'] === $queue) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted > 0;
    }

    public function getFailedJobs(): array
    {
        $files = glob($this->path . '/*.job');
        $failedJobs = [];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $jobData = unserialize($content);
            
            if ($jobData['attempts'] >= 3) {
                $failedJobs[] = new QueueJob($jobData);
            }
        }
        
        return $failedJobs;
    }

    public function retry(int $jobId): bool
    {
        // File driver doesn't support job IDs, would need to implement differently
        return false;
    }

    public function deleteFailedJob(int $jobId): bool
    {
        // File driver doesn't support job IDs, would need to implement differently
        return false;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

/**
 * Memory Queue Driver
 */
class QueueMemoryDriver implements QueueDriverInterface
{
    private array $queues = [];
    private array $stats = ['pushed' => 0, 'popped' => 0, 'failed' => 0];

    public function __construct(array $config)
    {
        // Memory driver configuration
    }

    public function push(string $job, array $data, string $queue): bool
    {
        if (!isset($this->queues[$queue])) {
            $this->queues[$queue] = [];
        }

        $this->queues[$queue][] = [
            'id' => uniqid(),
            'queue' => $queue,
            'job' => $job,
            'data' => $data,
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ];

        $this->stats['pushed']++;
        return true;
    }

    public function later(int $delay, string $job, array $data, string $queue): bool
    {
        if (!isset($this->queues[$queue])) {
            $this->queues[$queue] = [];
        }

        $this->queues[$queue][] = [
            'id' => uniqid(),
            'queue' => $queue,
            'job' => $job,
            'data' => $data,
            'attempts' => 0,
            'max_attempts' => 3,
            'reserved_at' => null,
            'available_at' => time() + $delay,
            'created_at' => time(),
        ];

        $this->stats['pushed']++;
        return true;
    }

    public function pop(string $queue): ?QueueJob
    {
        if (!isset($this->queues[$queue])) {
            return null;
        }

        foreach ($this->queues[$queue] as $key => $jobData) {
            if ($jobData['reserved_at'] === null && $jobData['available_at'] <= time()) {
                // Mark as reserved
                $this->queues[$queue][$key]['reserved_at'] = time();
                
                $this->stats['popped']++;
                
                return new QueueJob($this->queues[$queue][$key]);
            }
        }

        return null;
    }

    public function size(string $queue): int
    {
        if (!isset($this->queues[$queue])) {
            return 0;
        }

        $count = 0;
        foreach ($this->queues[$queue] as $jobData) {
            if ($jobData['reserved_at'] === null && $jobData['available_at'] <= time()) {
                $count++;
            }
        }

        return $count;
    }

    public function clear(string $queue): bool
    {
        if (isset($this->queues[$queue])) {
            unset($this->queues[$queue]);
            return true;
        }

        return false;
    }

    public function getFailedJobs(): array
    {
        $failedJobs = [];
        
        foreach ($this->queues as $queue) {
            foreach ($queue as $jobData) {
                if ($jobData['attempts'] >= 3) {
                    $failedJobs[] = new QueueJob($jobData);
                }
            }
        }

        return $failedJobs;
    }

    public function retry(int $jobId): bool
    {
        // Memory driver doesn't support job IDs, would need to implement differently
        return false;
    }

    public function deleteFailedJob(int $jobId): bool
    {
        // Memory driver doesn't support job IDs, would need to implement differently
        return false;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
} 
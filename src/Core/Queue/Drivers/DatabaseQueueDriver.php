<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queue\Drivers;

use IslamWiki\Core\Queue\Interfaces\QueueDriverInterface;
use IslamWiki\Core\Queue\Interfaces\JobInterface;
use IslamWiki\Core\Database\Connection;
use Logger;\Logger

/**
 * Database Queue Driver
 *
 * Stores jobs in the database for persistence and reliability.
 */
class DatabaseQueueDriver implements QueueDriverInterface
{
    private Connection $db;
    private Logger $logger;
    private array $stats = [
        'total_jobs' => 0,
        'failed_jobs' => 0,
        'processing_jobs' => 0,
    ];

    /**
     * Create a new database queue driver.
     */
    public function __construct(Connection $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->createTables();
    }

    /**
     * Create the queue tables if they don't exist.
     */
    private function createTables(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS queue_jobs (
                id VARCHAR(255) PRIMARY KEY,
                queue VARCHAR(255) NOT NULL DEFAULT 'default',
                payload LONGTEXT NOT NULL,
                attempts INT NOT NULL DEFAULT 0,
                max_attempts INT NOT NULL DEFAULT 3,
                failed BOOLEAN NOT NULL DEFAULT FALSE,
                failure_reason TEXT NULL,
                reserved_at TIMESTAMP NULL,
                available_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_queue_available (queue, available_at),
                INDEX idx_failed (failed),
                INDEX idx_reserved (reserved_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $this->db->exec($sql);
            $this->logger->info('Queue tables created successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to create queue tables', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job): bool
    {
        try {
            $sql = "
                INSERT INTO queue_jobs (id, queue, payload, max_attempts, available_at)
                VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $job->getId(),
                $job->getQueue(),
                json_encode([
                    'class' => get_class($job),
                    'data' => $job->getData(),
                    'timeout' => $job->getTimeout(),
                    'priority' => $job->getPriority()
                ]),
                $job->getMaxAttempts(),
                $job->getDelay()
            ]);

            if ($result) {
                $this->stats['total_jobs']++;
                $this->logger->info('Job pushed to database queue', [
                    'job_id' => $job->getId(),
                    'queue' => $job->getQueue()
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to push job to database queue', [
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
            // Get the next available job
            $sql = "
                SELECT id, queue, payload, attempts, max_attempts, failed, failure_reason
                FROM queue_jobs
                WHERE failed = FALSE 
                AND attempts < max_attempts
                AND available_at <= NOW()
                AND reserved_at IS NULL
                ORDER BY priority DESC, created_at ASC
                LIMIT 1
                FOR UPDATE
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            // Mark the job as reserved
            $updateSql = "UPDATE queue_jobs SET reserved_at = NOW() WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$row['id']]);

            // Create job instance
            $payload = json_decode($row['payload'], true);
            $jobClass = $payload['class'];

            if (!class_exists($jobClass)) {
                throw new \Exception("Job class {$jobClass} not found");
            }

            $job = new $jobClass($payload['data'], $row['queue']);

            // Set job properties
            $job->setPriority($payload['priority'] ?? 0);

            $this->stats['processing_jobs']++;

            return $job;
        } catch (\Exception $e) {
            $this->logger->error('Failed to pop job from database queue', [
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
            $sql = "
                SELECT COUNT(*) as count
                FROM queue_jobs
                WHERE queue = ? AND failed = FALSE AND reserved_at IS NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$queue]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int) $row['count'];
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
            $sql = "DELETE FROM queue_jobs WHERE queue = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$queue]);

            $count = $stmt->rowCount();
            $this->logger->info('Cleared queue', ['queue' => $queue, 'count' => $count]);

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to clear queue', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get failed jobs.
     */
    public function getFailed(): array
    {
        try {
            $sql = "
                SELECT id, queue, payload, attempts, failure_reason, created_at
                FROM queue_jobs
                WHERE failed = TRUE
                ORDER BY created_at DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            $sql = "DELETE FROM queue_jobs WHERE failed = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $count = $stmt->rowCount();
            $this->logger->info('Cleared failed jobs', ['count' => $count]);

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
            $sql = "
                UPDATE queue_jobs
                SET failed = FALSE, attempts = 0, failure_reason = NULL, reserved_at = NULL, available_at = NOW()
                WHERE failed = TRUE AND attempts < ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maxRetries]);

            $count = $stmt->rowCount();
            $this->logger->info('Retried failed jobs', ['count' => $count]);

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
            $sql = "
                UPDATE queue_jobs
                SET failed = TRUE, failure_reason = ?, attempts = attempts + 1
                WHERE id = ?
            ";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $job->getFailureReason(),
                $job->getId()
            ]);

            if ($result) {
                $this->stats['failed_jobs']++;
                $this->stats['processing_jobs']--;
            }

            return $result;
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
            $sql = "
                SELECT 
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN failed = TRUE THEN 1 ELSE 0 END) as failed_jobs,
                    SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) as processing_jobs
                FROM queue_jobs
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return [
                'total_jobs' => (int) $row['total_jobs'],
                'failed_jobs' => (int) $row['failed_jobs'],
                'processing_jobs' => (int) $row['processing_jobs'],
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get queue stats', ['error' => $e->getMessage()]);
            return $this->stats;
        }
    }

    /**
     * Check if the driver is connected.
     */
    public function isConnected(): bool
    {
        try {
            $this->db->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        // Database connection is managed by the Connection class
    }
}

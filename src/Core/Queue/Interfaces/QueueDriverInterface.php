<?php
declare(strict_types=1);

namespace IslamWiki\Core\Queue\Interfaces;

/**
 * Queue Driver Interface
 *
 * Defines the contract for queue drivers that implement
 * different queue storage strategies (database, file, memory, redis, etc.).
 */
interface QueueDriverInterface
{
    /**
     * Push a job to the queue.
     */
    public function push(JobInterface $job): bool;

    /**
     * Pop a job from the queue.
     */
    public function pop(): ?JobInterface;

    /**
     * Get the number of jobs in the queue.
     */
    public function size(string $queue = 'default'): int;

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue = 'default'): int;

    /**
     * Get failed jobs.
     */
    public function getFailed(): array;

    /**
     * Clear failed jobs.
     */
    public function clearFailed(): int;

    /**
     * Retry failed jobs.
     */
    public function retryFailed(int $maxRetries = 3): int;

    /**
     * Mark a job as failed.
     */
    public function markAsFailed(JobInterface $job): bool;

    /**
     * Get queue statistics.
     */
    public function getStats(): array;

    /**
     * Check if the driver is connected.
     */
    public function isConnected(): bool;

    /**
     * Connect to the queue storage.
     */
    public function connect(): bool;

    /**
     * Disconnect from the queue storage.
     */
    public function disconnect(): void;
} 
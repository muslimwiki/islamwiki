<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queue\Jobs;

use IslamWiki\Core\Queue\Interfaces\JobInterface;

/**
 * Abstract Job
 *
 * Base class for all queue jobs providing common functionality.
 */
abstract class AbstractJob implements JobInterface
{
    protected string $id;
    protected string $queue;
    protected array $data;
    protected int $attempts = 0;
    protected int $maxAttempts = 3;
    protected bool $failed = false;
    protected ?string $failureReason = null;
    protected int $timeout = 60;
    protected int $delay = 0;
    protected int $priority = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data = [], string $queue = 'default')
    {
        $this->id = uniqid('job_', true);
        $this->queue = $queue;
        $this->data = $data;
    }

    /**
     * Get the job ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the job queue name.
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Get the job data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the job attempts.
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Get the maximum attempts.
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * Check if the job has failed.
     */
    public function hasFailed(): bool
    {
        return $this->failed;
    }

    /**
     * Get the failure reason.
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * Mark the job as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->failed = true;
        $this->failureReason = $reason;
    }

    /**
     * Increment the job attempts.
     */
    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    /**
     * Check if the job can be retried.
     */
    public function canRetry(): bool
    {
        return !$this->failed && $this->attempts < $this->maxAttempts;
    }

    /**
     * Get the job timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get the job delay in seconds.
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Set the job delay.
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    /**
     * Get the job priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set the job priority.
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Handle the job execution.
     * This method must be implemented by concrete job classes.
     */
    abstract public function handle(): mixed;
}

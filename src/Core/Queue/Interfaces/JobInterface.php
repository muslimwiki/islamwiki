<?php
declare(strict_types=1);

namespace IslamWiki\Core\Queue\Interfaces;

/**
 * Job Interface
 *
 * Defines the contract for all queue jobs.
 */
interface JobInterface
{
    /**
     * Get the job ID.
     */
    public function getId(): string;

    /**
     * Get the job queue name.
     */
    public function getQueue(): string;

    /**
     * Get the job data.
     */
    public function getData(): array;

    /**
     * Get the job attempts.
     */
    public function getAttempts(): int;

    /**
     * Get the maximum attempts.
     */
    public function getMaxAttempts(): int;

    /**
     * Check if the job has failed.
     */
    public function hasFailed(): bool;

    /**
     * Get the failure reason.
     */
    public function getFailureReason(): ?string;

    /**
     * Mark the job as failed.
     */
    public function markAsFailed(string $reason): void;

    /**
     * Increment the job attempts.
     */
    public function incrementAttempts(): void;

    /**
     * Check if the job can be retried.
     */
    public function canRetry(): bool;

    /**
     * Handle the job execution.
     */
    public function handle(): mixed;

    /**
     * Get the job timeout in seconds.
     */
    public function getTimeout(): int;

    /**
     * Get the job delay in seconds.
     */
    public function getDelay(): int;

    /**
     * Set the job delay.
     */
    public function setDelay(int $delay): void;

    /**
     * Get the job priority.
     */
    public function getPriority(): int;

    /**
     * Set the job priority.
     */
    public function setPriority(int $priority): void;
} 
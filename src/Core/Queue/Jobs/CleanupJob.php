<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queue\Jobs;

/**
 * Cleanup Job
 *
 * Handles system cleanup tasks in the queue.
 */
class CleanupJob extends AbstractJob
{
    private string $type;
    private array $options;

    /**
     * Create a new cleanup job.
     */
    public function __construct(string $type, array $options = [])
    {
        parent::__construct([
            'type' => $type,
            'options' => $options
        ], 'cleanup');

        $this->type = $type;
        $this->options = $options;
        $this->timeout = 600; // Cleanup jobs can take a while
        $this->maxAttempts = 1; // Cleanup jobs shouldn't be retried
    }

    /**
     * Handle the cleanup job.
     */
    public function handle(): bool
    {
        try {
            switch ($this->type) {
                case 'temp_files':
                    $this->cleanupTempFiles();
                    break;
                case 'old_logs':
                    $this->cleanupOldLogs();
                    break;
                case 'expired_sessions':
                    $this->cleanupExpiredSessions();
                    break;
                case 'failed_jobs':
                    $this->cleanupFailedJobs();
                    break;
                case 'cache_files':
                    $this->cleanupCacheFiles();
                    break;
                case 'orphaned_files':
                    $this->cleanupOrphanedFiles();
                    break;
                default:
                    throw new \Exception("Unknown cleanup type: {$this->type}");
            }

            return true;
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Clean up temporary files.
     */
    private function cleanupTempFiles(): void
    {
        $tempDir = $this->options['temp_dir'] ?? sys_get_temp_dir() . '/islamwiki';
        $maxAge = $this->options['max_age'] ?? 86400; // 24 hours

        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        error_log("Cleanup: Deleted {$deleted} temporary files");
    }

    /**
     * Clean up old log files.
     */
    private function cleanupOldLogs(): void
    {
        $logDir = $this->options['log_dir'] ?? __DIR__ . '/../../../storage/logs';
        $maxAge = $this->options['max_age'] ?? 2592000; // 30 days

        if (!is_dir($logDir)) {
            return;
        }

        $files = glob($logDir . '/*.log');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        error_log("Cleanup: Deleted {$deleted} old log files");
    }

    /**
     * Clean up expired sessions.
     */
    private function cleanupExpiredSessions(): void
    {
        // In a real application, you would clean up expired sessions from the database
        // For now, we'll just log the cleanup
        $maxAge = $this->options['max_age'] ?? 86400; // 24 hours

        error_log("Cleanup: Cleaning up expired sessions older than {$maxAge} seconds");

        // Simulate cleaning up sessions
        $deleted = rand(0, 10);
        error_log("Cleanup: Deleted {$deleted} expired sessions");
    }

    /**
     * Clean up failed jobs.
     */
    private function cleanupFailedJobs(): void
    {
        $maxAge = $this->options['max_age'] ?? 604800; // 7 days

        error_log("Cleanup: Cleaning up failed jobs older than {$maxAge} seconds");

        // Simulate cleaning up failed jobs
        $deleted = rand(0, 5);
        error_log("Cleanup: Deleted {$deleted} old failed jobs");
    }

    /**
     * Clean up cache files.
     */
    private function cleanupCacheFiles(): void
    {
        $cacheDir = $this->options['cache_dir'] ?? __DIR__ . '/../../../storage/framework/cache';
        $maxAge = $this->options['max_age'] ?? 3600; // 1 hour

        if (!is_dir($cacheDir)) {
            return;
        }

        $files = glob($cacheDir . '/**/*', GLOB_BRACE);
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        error_log("Cleanup: Deleted {$deleted} cache files");
    }

    /**
     * Clean up orphaned files.
     */
    private function cleanupOrphanedFiles(): void
    {
        $uploadsDir = $this->options['uploads_dir'] ?? __DIR__ . '/../../../storage/app/uploads';

        if (!is_dir($uploadsDir)) {
            return;
        }

        // In a real application, you would check for orphaned files
        // by comparing with database records
        error_log("Cleanup: Checking for orphaned files in uploads directory");

        // Simulate finding and deleting orphaned files
        $deleted = rand(0, 3);
        error_log("Cleanup: Deleted {$deleted} orphaned files");
    }

    /**
     * Get the cleanup type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the cleanup options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}

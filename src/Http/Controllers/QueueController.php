<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Queue\SabrQueue;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Queue Management Controller
 *
 * Handles queue monitoring, job management, and queue operations.
 */
class QueueController extends Controller
{
    private Sabr $queue;
    private Shahid $logger;

    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->queue = $container->get('queue');
        $this->logger = $container->get(ShahidLogger::class);
    }

    /**
     * Show queue dashboard.
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $stats = $this->queue->getStats();
            $drivers = $this->queue->getDrivers();
            $failedJobs = $this->queue->getFailed();

            return $this->render('queue/dashboard.twig', [
                'title' => 'Queue Management Dashboard',
                'stats' => $stats,
                'drivers' => $drivers,
                'failed_jobs' => $failedJobs,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Queue dashboard error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load queue dashboard', 500);
        }
    }

    /**
     * Get queue statistics.
     */
    public function stats(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $stats = $this->queue->getStats();

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Queue stats error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to get queue statistics'
            ], 500);
        }
    }

    /**
     * Process jobs from the queue.
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $driver = $params['driver'] ?? 'database';
            $maxJobs = (int) ($params['max_jobs'] ?? 10);

            $result = $this->queue->process($driver, $maxJobs);

            return $this->jsonResponse([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Queue processing error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to process queue jobs'
            ], 500);
        }
    }

    /**
     * Clear failed jobs.
     */
    public function clearFailed(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $driver = $params['driver'] ?? 'database';

            $count = $this->queue->clearFailed($driver);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'cleared_count' => $count,
                    'driver' => $driver
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Clear failed jobs error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to clear failed jobs'
            ], 500);
        }
    }

    /**
     * Retry failed jobs.
     */
    public function retryFailed(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $driver = $params['driver'] ?? 'database';
            $maxRetries = (int) ($params['max_retries'] ?? 3);

            $count = $this->queue->retryFailed($driver, $maxRetries);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'retried_count' => $count,
                    'driver' => $driver,
                    'max_retries' => $maxRetries
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Retry failed jobs error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to retry failed jobs'
            ], 500);
        }
    }

    /**
     * Get failed jobs.
     */
    public function getFailed(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $failedJobs = $this->queue->getFailed();

            return $this->jsonResponse([
                'success' => true,
                'data' => $failedJobs
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Get failed jobs error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to get failed jobs'
            ], 500);
        }
    }

    /**
     * Create a test job.
     */
    public function createTestJob(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $type = $params['type'] ?? 'email';
            $driver = $params['driver'] ?? 'database';

            $success = false;
            $jobId = null;

            switch ($type) {
                case 'email':
                    $success = $this->queue->email(
                        'test@example.com',
                        'Test Email',
                        'This is a test email from the queue system.',
                        ['from' => 'noreply@islam.wiki']
                    );
                    break;
                case 'notification':
                    $success = $this->queue->notify(
                        1,
                        'test',
                        ['message' => 'This is a test notification']
                    );
                    break;
                case 'report':
                    $success = $this->queue->report(
                        'user_activity',
                        ['period' => 'daily']
                    );
                    break;
                case 'cleanup':
                    $success = $this->queue->cleanup(
                        'temp_files',
                        ['max_age' => 3600]
                    );
                    break;
                default:
                    throw new \Exception("Unknown job type: {$type}");
            }

            if ($success) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "Test {$type} job created successfully",
                    'type' => $type,
                    'driver' => $driver
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Failed to create test {$type} job"
                ], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error('Create test job error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create test job'
            ], 500);
        }
    }

    /**
     * Get queue driver information.
     */
    public function getDriverInfo(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getQueryParams();
            $driver = $params['driver'] ?? 'database';

            $drivers = $this->queue->getDrivers();
            $hasDriver = $this->queue->hasDriver($driver);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'driver' => $driver,
                    'available' => $hasDriver,
                    'all_drivers' => $drivers,
                    'stats' => $this->queue->getStats()
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Get driver info error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to get driver information'
            ], 500);
        }
    }
}

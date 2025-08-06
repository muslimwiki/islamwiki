<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Caching\RihlahCaching;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Cache Management Controller
 * 
 * Handles cache monitoring, invalidation, and management operations.
 */
class CacheController extends Controller
{
    private Rihlah $cache;
    private Shahid $logger;
    
    /**
     * Create a new cache controller.
     */
    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->cache = $container->get('cache');
        $this->logger = $container->get(ShahidLogger::class);
    }
    
    /**
     * Show cache dashboard.
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $stats = $this->cache->getStats();
            $drivers = $this->cache->getDrivers();
            
            $this->logger->info('Cache dashboard accessed');
            
            return $this->render('cache/dashboard.twig', [
                'stats' => $stats,
                'drivers' => $drivers,
                'title' => 'Cache Management Dashboard',
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Cache dashboard error', [
                'error' => $e->getMessage(),
            ]);
            
            return $this->render('error.twig', [
                'error' => 'Failed to load cache dashboard',
                'details' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Show cache statistics.
     */
    public function stats(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $stats = $this->cache->getStats();
            
            $this->logger->info('Cache statistics accessed');
            
            return $this->jsonResponse([
                'success' => true,
                'stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Cache stats error', [
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Clear specific cache driver.
     */
    public function clear(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $driver = $params['driver'] ?? 'memory';
            
            if (!in_array($driver, $this->cache->getDrivers())) {
                throw new \InvalidArgumentException("Invalid cache driver: {$driver}");
            }
            
            $success = $this->cache->clear($driver);
            
            if ($success) {
                $this->logger->info('Cache cleared', ['driver' => $driver]);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "Cache cleared successfully for driver: {$driver}",
                    'driver' => $driver,
                ]);
            } else {
                throw new \RuntimeException("Failed to clear cache for driver: {$driver}");
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Cache clear error', [
                'driver' => $driver ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete specific cache key.
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $key = $params['key'] ?? '';
            $driver = $params['driver'] ?? 'memory';
            
            if (empty($key)) {
                throw new \InvalidArgumentException('Cache key is required');
            }
            
            if (!in_array($driver, $this->cache->getDrivers())) {
                throw new \InvalidArgumentException("Invalid cache driver: {$driver}");
            }
            
            $success = $this->cache->delete($key, $driver);
            
            if ($success) {
                $this->logger->info('Cache key deleted', [
                    'key' => $key,
                    'driver' => $driver,
                ]);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "Cache key '{$key}' deleted successfully",
                    'key' => $key,
                    'driver' => $driver,
                ]);
            } else {
                throw new \RuntimeException("Failed to delete cache key: {$key}");
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Cache delete error', [
                'key' => $key ?? 'unknown',
                'driver' => $driver ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Warm up cache.
     */
    public function warmUp(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->cache->warmUp();
            
            $this->logger->info('Cache warm-up completed');
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Cache warm-up completed successfully',
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Cache warm-up error', [
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Invalidate cache by pattern.
     */
    public function invalidate(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody();
            $pattern = $params['pattern'] ?? '';
            $driver = $params['driver'] ?? 'memory';
            
            if (empty($pattern)) {
                throw new \InvalidArgumentException('Cache pattern is required');
            }
            
            $invalidated = $this->invalidateByPattern($pattern, $driver);
            
            $this->logger->info('Cache invalidated by pattern', [
                'pattern' => $pattern,
                'driver' => $driver,
                'invalidated' => $invalidated,
            ]);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => "Cache invalidated successfully",
                'pattern' => $pattern,
                'driver' => $driver,
                'invalidated_count' => $invalidated,
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Cache invalidation error', [
                'pattern' => $pattern ?? 'unknown',
                'driver' => $driver ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get cache driver information.
     */
    public function driverInfo(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getQueryParams();
            $driver = $params['driver'] ?? 'memory';
            
            if (!in_array($driver, $this->cache->getDrivers())) {
                throw new \InvalidArgumentException("Invalid cache driver: {$driver}");
            }
            
            $driverInstance = $this->cache->getDriver($driver);
            $stats = $driverInstance->getStats();
            
            return $this->jsonResponse([
                'success' => true,
                'driver' => $driver,
                'stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Cache driver info error', [
                'driver' => $driver ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Invalidate cache entries by pattern.
     */
    private function invalidateByPattern(string $pattern, string $driver): int
    {
        $invalidated = 0;
        
        // This is a simplified implementation
        // In a real system, you'd need to iterate through all keys
        // and match against the pattern
        
        if ($driver === 'database') {
            // For database cache, we can use SQL LIKE
            $result = $this->db->execute(
                "DELETE FROM rihlah_cache WHERE `key` LIKE ?",
                [$pattern]
            );
            $invalidated = $result->rowCount();
        } else {
            // For other drivers, we'd need to implement pattern matching
            // This is a placeholder implementation
            $this->logger->warning('Pattern invalidation not fully implemented for driver', [
                'driver' => $driver,
                'pattern' => $pattern,
            ]);
        }
        
        return $invalidated;
    }
} 
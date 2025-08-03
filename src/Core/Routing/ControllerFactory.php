<?php
declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating controller instances with their dependencies.
 */
class ControllerFactory
{
    /**
     * @var Connection Database connection
     */
    private $db;
    
    /**
     * @var LoggerInterface Logger instance
     */
    private $logger;
    
    /**
     * @var Asas The dependency injection container
     */
    private $container;
    
    /**
     * Create a new ControllerFactory instance.
     *
     * @param Connection $db Database connection
     * @param LoggerInterface $logger Logger instance
     * @param Container $container The dependency injection container
     */
    public function __construct(Connection $db, LoggerInterface $logger, Asas $container)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->container = $container;
    }
    
    /**
     * Create a controller instance.
     *
     * @param string $controllerClass The fully qualified class name of the controller
     * @return object The controller instance
     * @throws \RuntimeException If the controller cannot be instantiated
     */
    public function create(string $controllerClass)
    {
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class {$controllerClass} does not exist");
        }
        
        try {
            // Try to resolve the controller from the container first
            if ($this->container->has($controllerClass)) {
                return $this->container->get($controllerClass);
            }
            
            // Otherwise, create a new instance with dependencies
            // Base Controller expects: (Connection $db, Container $container)
            return new $controllerClass($this->db, $this->container);
        } catch (\Exception $e) {
            $this->logger->error("Failed to create controller {$controllerClass}: " . $e->getMessage());
            throw new \RuntimeException("Failed to create controller: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Get the database connection.
     *
     * @return Connection
     */
    public function getDb(): Connection
    {
        return $this->db;
    }
    
    /**
     * Get the logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
    
    /**
     * Get the container instance.
     *
     * @return Container
     */
    public function getContainer(): Asas
    {
        return $this->container;
    }
}

<?php

declare(strict_types=1);

/**
 * Database Connection for IqraSearchExtension
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Database
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class SearchDatabaseConnection
{
    private ?PDO $connection = null;
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    /**
     * Load database configuration
     */
    private function loadConfig(): array
    {
        // Try to load from environment variables first
        $config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'islamwiki',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];

        // Try to load from LocalSettings.php if it exists
        if (file_exists(__DIR__ . '/../../../LocalSettings.php')) {
            include __DIR__ . '/../../../LocalSettings.php';
            
            // Check if MediaWiki-style config exists
            if (isset($wgDBserver)) {
                $config['host'] = $wgDBserver;
                $config['database'] = $wgDBname;
                $config['username'] = $wgDBuser;
                $config['password'] = $wgDBpassword;
            }
        }

        return $config;
    }

    /**
     * Get database connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            // Set timezone
            $this->connection->exec("SET time_zone = '+00:00'");
            
            error_log("Search database connection established successfully");
            
        } catch (PDOException $e) {
            error_log("Search database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test database connection
     */
    public function testConnection(): bool
    {
        try {
            $connection = $this->getConnection();
            $connection->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            error_log("Database connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Close database connection
     */
    public function close(): void
    {
        $this->connection = null;
    }

    /**
     * Execute a query and return results
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return single result
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $connection = $this->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database execute failed: " . $e->getMessage());
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->getConnection()->rollback();
    }
} 
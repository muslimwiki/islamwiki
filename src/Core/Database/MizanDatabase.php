<?php
declare(strict_types=1);

namespace IslamWiki\Core\Database;

use IslamWiki\Core\Logging\Shahid;
use PDO;
use PDOException;

/**
 * Mizan (ميزان) - Database System
 * 
 * Mizan means "Balance" or "Scale" in Arabic. This system provides
 * database connection management, data integrity, and balanced data
 * operations for the IslamWiki application.
 * 
 * @package IslamWiki\Core\Database
 */
class MizanDatabase
{
    /**
     * The database connection.
     *
     * @var PDO
     */
    private PDO $connection;

    /**
     * The logger instance.
     *
     * @var Shahid
     */
    private Shahid $logger;

    /**
     * Database configuration.
     *
     * @var array
     */
    private array $config;

    /**
     * Connection statistics.
     *
     * @var array
     */
    private array $statistics = [
        'queries' => 0,
        'errors' => 0,
        'connections' => 0,
        'transactions' => 0
    ];

    /**
     * Create a new Mizan database instance.
     *
     * @param Shahid $logger The logger instance
     * @param array $config Database configuration
     */
    public function __construct(Shahid $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection.
     *
     * @return void
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->config['host'] ?? 'localhost',
                $this->config['port'] ?? 3306,
                $this->config['database'] ?? 'islamwiki'
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'] ?? 'root',
                $this->config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );

            $this->statistics['connections']++;
            $this->logger->info('Mizan database connection established', [
                'system' => 'Mizan',
                'host' => $this->config['host'] ?? 'localhost',
                'database' => $this->config['database'] ?? 'islamwiki'
            ]);

        } catch (PDOException $e) {
            $this->statistics['errors']++;
            $this->logger->error('Mizan database connection failed', [
                'system' => 'Mizan',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the database connection.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a query.
     *
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $this->statistics['queries']++;
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $this->logger->debug('Mizan database query executed', [
                'system' => 'Mizan',
                'sql' => $sql,
                'params' => $params
            ]);

            return $stmt;

        } catch (PDOException $e) {
            $this->statistics['errors']++;
            $this->logger->error('Mizan database query failed', [
                'system' => 'Mizan',
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Begin a transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $this->statistics['transactions']++;
        $this->logger->debug('Mizan database transaction begun', [
            'system' => 'Mizan'
        ]);
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        $this->logger->debug('Mizan database transaction committed', [
            'system' => 'Mizan'
        ]);
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction.
     *
     * @return bool
     */
    public function rollback(): bool
    {
        $this->logger->debug('Mizan database transaction rolled back', [
            'system' => 'Mizan'
        ]);
        return $this->connection->rollback();
    }

    /**
     * Check if in transaction.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Get the last insert ID.
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Get database statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'system' => 'Mizan',
            'statistics' => $this->statistics,
            'config' => [
                'host' => $this->config['host'] ?? 'localhost',
                'database' => $this->config['database'] ?? 'islamwiki',
                'port' => $this->config['port'] ?? 3306
            ]
        ];
    }

    /**
     * Test database connection.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->connection->query('SELECT 1');
            $this->logger->info('Mizan database connection test successful', [
                'system' => 'Mizan'
            ]);
            return true;
        } catch (PDOException $e) {
            $this->logger->error('Mizan database connection test failed', [
                'system' => 'Mizan',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get database information.
     *
     * @return array
     */
    public function getDatabaseInfo(): array
    {
        try {
            $version = $this->connection->query('SELECT VERSION() as version')->fetch();
            $databases = $this->connection->query('SHOW DATABASES')->fetchAll();
            
            return [
                'version' => $version['version'] ?? 'Unknown',
                'databases' => array_column($databases, 'Database'),
                'connection_id' => $this->connection->query('SELECT CONNECTION_ID() as id')->fetch()['id'] ?? null
            ];
        } catch (PDOException $e) {
            $this->logger->error('Mizan database info retrieval failed', [
                'system' => 'Mizan',
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Close the database connection.
     *
     * @return void
     */
    public function close(): void
    {
        $this->connection = null;
        $this->logger->info('Mizan database connection closed', [
            'system' => 'Mizan'
        ]);
    }
} 
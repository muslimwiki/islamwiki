<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database\Islamic;

use PDO;
use RuntimeException;
use IslamWiki\Core\Database\Connection as CoreConnection;

/**
 * Islamic Database Manager
 *
 * Manages separate database connections for different Islamic content types:
 * - Quran Database: For Quran ayahs and translations
 * - Hadith Database: For Hadith collections and chains
 * - Wiki Database: For general Islamic wiki content
 * - Scholar Database: For scholar verification and credentials
 */
class IslamicDatabaseManager
{
    private array $configs;
    private array $connections = [];
    private ?CoreConnection $quranConnection = null;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Quran connection (app-level wrapper)
     */
    public function getQuranConnection(): CoreConnection
    {
        if ($this->quranConnection instanceof CoreConnection) {
            return $this->quranConnection;
        }
        $cfg = $this->configs['quran'] ?? [];
        $this->quranConnection = new CoreConnection($cfg);
        return $this->quranConnection;
    }

    /**
     * Raw PDO for Quran (for importers/scripts)
     */
    public function getQuranPdo(): PDO
    {
        return $this->getQuranConnection()->getPdo();
    }

    /**
     * Get the Hadith database connection.
     */
    public function getHadithConnection(): CoreConnection
    {
        return $this->getConnection('hadith');
    }

    /**
     * Get the Wiki database connection.
     */
    public function getWikiConnection(): CoreConnection
    {
        return $this->getConnection('wiki');
    }

    /**
     * Get the Scholar database connection.
     */
    public function getScholarConnection(): CoreConnection
    {
        return $this->getConnection('scholar');
    }

    /**
     * Get a specific database connection.
     */
    protected function getConnection(string $type): CoreConnection
    {
        if (!isset($this->connections[$type])) {
            $this->connections[$type] = $this->createConnection($type);
        }

        return $this->connections[$type];
    }

    /**
     * Create a new database connection.
     */
    protected function createConnection(string $type): CoreConnection
    {
        $config = $this->getConfig($type);

        if (!$config) {
            throw new RuntimeException("Database configuration for '{$type}' not found");
        }

        return new CoreConnection($config);
    }

    /**
     * Get the configuration for a specific database type.
     */
    protected function getConfig(string $type): ?array
    {
        return $this->configs[$type] ?? null;
    }

    /**
     * Get all active connections.
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * Disconnect all connections.
     */
    public function disconnectAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }
        $this->connections = [];
        $this->quranConnection = null;
    }

    /**
     * Test all database connections.
     */
    public function testConnections(): array
    {
        $results = [];

        $types = ['quran', 'hadith', 'wiki', 'scholar'];

        foreach ($types as $type) {
            try {
                $connection = $this->getConnection($type);
                $connection->getPdo();
                $results[$type] = [
                    'status' => 'connected',
                    'database' => $connection->getDatabaseName(),
                    'driver' => $connection->getDriverName()
                ];
            } catch (\Exception $e) {
                $results[$type] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get database statistics.
     */
    public function getDatabaseStats(): array
    {
        $stats = [];

        $types = ['quran', 'hadith', 'wiki', 'scholar'];

        foreach ($types as $type) {
            try {
                $connection = $this->getConnection($type);
                $pdo = $connection->getPdo();

                // Get table count
                $tableCount = $pdo->query("SHOW TABLES")->rowCount();

                // Get total rows across all tables
                $totalRows = 0;
                $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($tables as $table) {
                    $result = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                    $totalRows += (int) $result->fetchColumn();
                }

                $stats[$type] = [
                    'tables' => $tableCount,
                    'total_rows' => $totalRows,
                    'database_size' => $this->getDatabaseSize($connection)
                ];
            } catch (\Exception $e) {
                $stats[$type] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return $stats;
    }

    /**
     * Get database size in MB.
     */
    protected function getDatabaseSize(CoreConnection $connection): float
    {
        try {
            $database = $connection->getDatabaseName();
            $result = $connection->getPdo()->query(
                "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                 FROM information_schema.tables 
                 WHERE table_schema = '{$database}'"
            );

            return (float) $result->fetchColumn();
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}

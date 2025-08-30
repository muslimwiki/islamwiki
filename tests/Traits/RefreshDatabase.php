<?php

namespace Tests\Traits;

use PDO;

/**
 * A simple database refresh trait for tests
 * 
 * This trait can be used to refresh the database state between tests
 */
trait RefreshDatabase
{
    protected static $pdo = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }
    
    protected function refreshDatabase()
    {
        if (self::$pdo === null) {
            $this->initializeDatabase();
        }
        
        // Start a transaction
        self::$pdo->beginTransaction();
    }
    
    protected function tearDown(): void
    {
        // Rollback the transaction after each test
        if (self::$pdo !== null && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }
        
        parent::tearDown();
    }
    
    protected function initializeDatabase()
    {
        // This is a placeholder. In a real application, you would:
        // 1. Create a test database if it doesn't exist
        // 2. Run migrations
        // 3. Set up a PDO connection
        
        // Example:
        // $database = __DIR__ . '/../../database/testing.sqlite';
        // if (!file_exists($database)) {
        //     touch($database);
        // }
        // 
        // self::$pdo = new PDO("sqlite:$database");
        // self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // 
        // // Run migrations or schema creation
        // $this->runMigrations();
    }
    
    protected function runMigrations()
    {
        // Run your database migrations here
        // This is just a placeholder
    }
}

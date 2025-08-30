<?php

namespace Tests\Feature;

use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DatabaseTestCase extends BaseTestCase
{
    protected static $initialized = false;
    protected static $db;
    
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        if (!static::$initialized) {
            static::initializeDatabase();
            static::$initialized = true;
        }
    }
    
    protected static function initializeDatabase()
    {
        static::$db = new DB;
        
        static::$db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        static::$db->setAsGlobal();
        static::$db->bootEloquent();
        
        // Run migrations
        $migrator = new \Illuminate\Database\Migrations\Migrator(
            new \Illuminate\Database\Migrations\DatabaseMigrationRepository(
                static::$db->getDatabaseManager(),
                'migrations'
            ),
            static::$db->getDatabaseManager(),
            new \Illuminate\Filesystem\Filesystem(),
            [__DIR__ . '/../../database/migrations']
        );
        
        $migrator->setOutput(new \Symfony\Component\Console\Output\NullOutput);
        
        if (!$migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }
        
        $migrator->run([__DIR__ . '/../../database/migrations']);
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Begin a transaction for each test
        static::$db->connection()->beginTransaction();
    }
    
    protected function tearDown(): void
    {
        // Rollback the transaction after each test
        if (static::$db && static::$db->connection()) {
            static::$db->connection()->rollBack();
        }
        
        parent::tearDown();
    }
    
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        $count = static::$db->table($table)->where($data)->count();
        
        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].',
            $table,
            json_encode($data)
        ));
        
        return $this;
    }
    
    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        $count = static::$db->table($table)->where($data)->count();
        
        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].',
            $table,
            json_encode($data)
        ));
        
        return $this;
    }
}

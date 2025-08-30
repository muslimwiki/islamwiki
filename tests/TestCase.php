<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

abstract class TestCase extends BaseTestCase
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
        
        // Set the event dispatcher used by the model
        Model::setEventDispatcher(new \Illuminate\Events\Dispatcher);
        
        // Run migrations
        $migrator = new Migrator(
            new \Illuminate\Database\Migrations\DatabaseMigrationRepository(
                static::$db->getDatabaseManager(),
                'migrations'
            ),
            static::$db->getDatabaseManager(),
            new Filesystem()
        );
        
        $migrator->setOutput(new \Symfony\Component\Console\Output\NullOutput);
        
        if (!$migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }
        
        $migrator->run([__DIR__ . '/../database/migrations']);
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
    
    protected function seeInDatabase($table, array $data, $connection = null)
    {
        $count = static::$db->table($table)->where($data)->count();
        
        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].',
            $table,
            json_encode($data)
        ));
        
        return $this;
    }
    
    protected function notSeeInDatabase($table, array $data, $connection = null)
    {
        $count = static::$db->table($table)->where($data)->count();
        
        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].',
            $table,
            json_encode($data)
        ));
        
        return $this;
    }
    
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        return $this->seeInDatabase($table, $data, $connection);
    }
    
    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        return $this->notSeeInDatabase($table, $data, $connection);
    }
}

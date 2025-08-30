<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as DB;

// Set the default timezone
date_default_timezone_set('UTC');

// Load the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize the database connection
$db = new DB;

$db->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Make this Capsule instance available globally via static methods
$db->setAsGlobal();

// Setup the Eloquent ORM...
$db->bootEloquent();

// Run migrations directly with raw SQL
$pdo = $db->getConnection()->getPdo();
$pdo->exec('PRAGMA foreign_keys = OFF');

// Create migrations table if it doesn't exist
$pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
)');

// Include and run the migration directly
require_once __DIR__ . '/../database/migrations/2025_08_30_000000_create_pages_table_raw.php';

// Run the migration
$migration = new CreatePagesTableRaw();
$migration->up();

// Record the migration
$pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, 1)')
    ->execute(['2025_08_30_000000_create_pages_table_raw']);

// Base test case class
abstract class BaseTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Start a database transaction
        DB::beginTransaction();
    }
    
    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Roll back the transaction
        DB::rollBack();
        
        parent::tearDown();
    }
    
    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $connection
     * @return $this
     */
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        $this->assertTrue(
            $this->isInDatabase($table, $data, $connection),
            'Unable to find row in database table ['.$table.'] that matched attributes '.json_encode($data).'.'
        );
        
        return $this;
    }
    
    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $connection
     * @return $this
     */
    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        $this->assertFalse(
            $this->isInDatabase($table, $data, $connection),
            'Found unexpected row in database table ['.$table.'] that matched attributes '.json_encode($data).'.'
        );
        
        return $this;
    }
    
    /**
     * Check if the given data exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $connection
     * @return bool
     */
    protected function isInDatabase($table, array $data, $connection = null)
    {
        $query = DB::table($table);
        
        foreach ($data as $key => $value) {
            $query->where($key, $value);
        }
        
        return $query->exists();
    }

    /**
     * Clean up the testing environment before the next test.
     */
    
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    
    /**
     * Get a protected/private property value.
     *
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    public function getProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        return $property->getValue($object);
    }
    
    /**
     * Set a protected/private property value.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     */
    public function setProperty($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}

// Helper functions
if (!function_exists('test_path')) {
    /**
     * Get the path to the tests directory.
     *
     * @param  string  $path
     * @return string
     */
    function test_path($path = '')
    {
        return __DIR__ . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('test_data_path')) {
    /**
     * Get the path to the test data directory.
     *
     * @param  string  $path
     * @return string
     */
    function test_data_path($path = '')
    {
        return test_path('data' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

// Create test data directory if it doesn't exist
if (!is_dir(test_data_path())) {
    mkdir(test_data_path(), 0777, true);
}

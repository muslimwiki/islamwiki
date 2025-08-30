<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;

// Load environment variables from .env.testing if it exists
if (file_exists(dirname(__DIR__) . '/.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.testing');
    $dotenv->load();
}

// Initialize the database connection
$db = new DB;

$db->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Set the event dispatcher used by Eloquent models
$dispatcher = new Dispatcher();
$db->setEventDispatcher($dispatcher);

// Make this Capsule instance available globally via static methods
$db->setAsGlobal();

// Setup the Eloquent ORM...
$db->bootEloquent();

// Create the migrations repository
$repository = new DatabaseMigrationRepository(
    $db->getDatabaseManager(),
    'migrations'
);

// Create the migrator instance
$migrator = new Migrator(
    $repository,
    $db->getDatabaseManager(),
    new Filesystem(),
    $dispatcher
);

// Set the migration paths
$migrator->path(__DIR__ . '/../database/migrations');

// Run the migrations
if (!$migrator->repositoryExists()) {
    $repository->createRepository();
}

// Include and run our simple migration
require_once __DIR__ . '/../database/migrations/2025_08_30_000002_create_pages_simple.php';

// Run the migration
$migration = new CreatePagesSimple();
$migration->up();

// Record the migration
$db->table('migrations')->insert([
    'migration' => '2025_08_30_000002_create_pages_simple',
    'batch' => 1
]);

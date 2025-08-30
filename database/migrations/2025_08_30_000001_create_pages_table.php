<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connection;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Connection $db */
        $db = app(DatabaseManager::class)->connection();
        
        $db->statement('CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            is_published BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var Connection $db */
        $db = app(DatabaseManager::class)->connection();
        $db->statement('DROP TABLE IF EXISTS pages');
    }
}

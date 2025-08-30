<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as DB;

class CreatePagesSimple extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();
        
        $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
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
     */
    public function down()
    {
        $pdo = DB::connection()->getPdo();
        $pdo->exec('DROP TABLE IF EXISTS pages');
    }
}

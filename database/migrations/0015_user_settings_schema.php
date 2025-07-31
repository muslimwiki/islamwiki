<?php
/**
 * User Settings Schema Migration
 * 
 * Creates the user_settings table for storing individual user preferences
 * 
 * @package IslamWiki\Database\Migrations
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Schema\Builder;

return new class {
    public function up(Builder $schema): void
    {
        $schema->create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings')->comment('JSON object containing user settings');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
            
            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint to ensure one settings record per user
            $table->unique('user_id');
            
            // Index for faster lookups
            $table->index('user_id');
        });
    }

    public function down(Builder $schema): void
    {
        $schema->dropIfExists('user_settings');
    }
}; 
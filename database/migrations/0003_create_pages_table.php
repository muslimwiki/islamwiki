<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Database\Migrations;

use IslamWiki\Core\Database\Migrations\Migration;

/**
 * Create Pages Table Migration
 * 
 * Creates the pages table for wiki page management.
 */
class CreatePagesTable extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $this->schema->create('pages', function ($table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('namespace', 50)->default('main');
            $table->text('content');
            $table->text('content_html')->nullable();
            $table->string('summary', 500)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(true);
            $table->boolean('is_protected')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['slug']);
            $table->index(['namespace']);
            $table->index(['user_id']);
            $table->index(['is_published']);
            $table->index(['created_at']);
            $table->index(['updated_at']);
            
            // Full-text search indexes
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('pages');
    }
} 
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
 * Create Revisions Table Migration
 * 
 * Creates the revisions table for tracking page history and changes.
 */
class CreateRevisionsTable extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $this->schema->create('revisions', function ($table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('content');
            $table->text('content_html')->nullable();
            $table->string('summary', 500)->nullable();
            $table->string('change_type', 20)->default('edit'); // edit, create, revert
            $table->json('metadata')->nullable(); // Store additional revision data
            $table->timestamps();
            
            // Indexes
            $table->index(['page_id']);
            $table->index(['user_id']);
            $table->index(['created_at']);
            $table->index(['change_type']);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('revisions');
    }
} 
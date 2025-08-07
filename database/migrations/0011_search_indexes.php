<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
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

use IslamWiki\Core\Database\Migrations\Migration;

class CreateSearchIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create search statistics table
        $this->schema()->create('search_statistics', function ($table) {
            $table->id();
            $table->string('query', 255);
            $table->enum('search_type', ['all', 'pages', 'quran', 'hadith', 'calendar', 'prayer']);
            $table->integer('results_count')->default(0);
            $table->integer('search_time_ms')->default(0);
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('query');
            $table->index('search_type');
            $table->index('created_at');
        });

        // Create search suggestions table
        $this->schema()->create('search_suggestions', function ($table) {
            $table->id();
            $table->string('query', 255);
            $table->enum('suggestion_type', ['page', 'quran', 'hadith', 'calendar', 'prayer']);
            $table->string('suggestion_text', 500);
            $table->string('suggestion_url', 500);
            $table->integer('click_count')->default(0);
            $table->decimal('relevance_score', 5, 4)->default(0.0000);
            $table->timestamps();

            $table->index('query');
            $table->index('suggestion_type');
            $table->index('relevance_score');
            $table->index('click_count');
        });

        // Create search cache table for performance
        $this->schema()->create('search_cache', function ($table) {
            $table->id();
            $table->string('query_hash', 64);
            $table->text('query_params');
            $table->text('results');
            $table->integer('results_count')->default(0);
            $table->timestamp('expires_at');
            $table->enum('search_type', ['all', 'pages', 'quran', 'hadith', 'calendar', 'prayer']);
            $table->timestamps();

            $table->index('query_hash');
            $table->index('search_type');
            $table->index('expires_at');
            $table->unique(['query_hash', 'search_type']);
        });

        // Create search analytics table
        $this->schema()->create('search_analytics', function ($table) {
            $table->id();
            $table->date('date');
            $table->integer('total_searches')->default(0);
            $table->integer('unique_users')->default(0);
            $table->decimal('avg_results_per_search', 5, 2)->default(0.00);
            $table->decimal('avg_search_time_ms', 8, 2)->default(0.00);
            $table->json('most_popular_queries')->nullable();
            $table->json('search_type_distribution')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->unique('date');
        });

        // Insert initial search suggestions
        $this->insertInitialSuggestions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->drop('search_analytics');
        $this->schema()->drop('search_cache');
        $this->schema()->drop('search_suggestions');
        $this->schema()->drop('search_statistics');
    }

    /**
     * Insert initial search suggestions
     */
    private function insertInitialSuggestions(): void
    {
        $suggestions = [
            // Quran suggestions
            ['quran', 'Al-Fatiha', '/quran/chapter/1', 0.95],
            ['quran', 'Al-Baqarah', '/quran/chapter/2', 0.94],
            ['quran', 'Yasin', '/quran/chapter/36', 0.93],
            ['quran', 'Al-Kahf', '/quran/chapter/18', 0.92],
            ['quran', 'Ar-Rahman', '/quran/chapter/55', 0.91],

            // Hadith suggestions
            ['hadith', 'Sahih Bukhari', '/hadith/collection/1', 0.95],
            ['hadith', 'Sahih Muslim', '/hadith/collection/2', 0.94],
            ['hadith', 'Abu Dawud', '/hadith/collection/3', 0.93],
            ['hadith', 'Tirmidhi', '/hadith/collection/4', 0.92],
            ['hadith', 'Nasai', '/hadith/collection/5', 0.91],

            // Calendar suggestions
            ['calendar', 'Ramadan', '/calendar/search?q=Ramadan', 0.95],
            ['calendar', 'Eid al-Fitr', '/calendar/search?q=Eid al-Fitr', 0.94],
            ['calendar', 'Eid al-Adha', '/calendar/search?q=Eid al-Adha', 0.93],
            ['calendar', 'Mawlid', '/calendar/search?q=Mawlid', 0.92],
            ['calendar', 'Laylat al-Qadr', '/calendar/search?q=Laylat al-Qadr', 0.91],

            // Prayer suggestions
            ['prayer', 'Mecca', '/prayer/search?q=Mecca', 0.95],
            ['prayer', 'Medina', '/prayer/search?q=Medina', 0.94],
            ['prayer', 'Jerusalem', '/prayer/search?q=Jerusalem', 0.93],
            ['prayer', 'Istanbul', '/prayer/search?q=Istanbul', 0.92],
            ['prayer', 'Cairo', '/prayer/search?q=Cairo', 0.91],

            // Page suggestions
            ['page', 'Islam', '/Islam', 0.95],
            ['page', 'Quran', '/Quran', 0.94],
            ['page', 'Hadith', '/Hadith', 0.93],
            ['page', 'Prayer', '/Prayer', 0.92],
            ['page', 'Calendar', '/Calendar', 0.91]
        ];

        foreach ($suggestions as $suggestion) {
            $this->connection->table('search_suggestions')->insert([
                'query' => $suggestion[1], // suggestion_text
                'suggestion_type' => $suggestion[0], // suggestion_type
                'suggestion_text' => $suggestion[1], // suggestion_text
                'suggestion_url' => $suggestion[2], // suggestion_url
                'relevance_score' => $suggestion[3] // relevance_score
            ]);
        }
    }
};

return function ($connection) {
    return new CreateSearchIndexes($connection);
};

<?php

/**
 * Hadith Integration Migration
 *
 * Adds tables for Hadith integration features including:
 * - Hadith-wiki linking
 * - Search cache
 * - User interactions
 * - Statistics tracking
 *
 * @package IslamWiki\Database\Migrations
 * @version 0.0.14
 * @since Phase 4
 */

declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Connection;

return function (Connection $connection) {
    return new class ($connection) extends Migration
    {
        public function up(): void
        {
            error_log("[Migration] 0008_hadith_integration up() called");

            // Hadith-wiki linking table
            $this->schema()->create('hadith_wiki_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('wiki_page_id');
                $table->string('link_type', 50)->default('reference'); // reference, citation, related
                $table->text('context')->nullable(); // Context of the link
                $table->string('section', 100)->nullable(); // Section of the wiki page
                $table->timestamps();

                $table->index(['hadith_id', 'wiki_page_id']);
                $table->index('link_type');
            });

            // Hadith search cache
            $this->schema()->create('hadith_search_cache', function (Blueprint $table) {
                $table->id();
                $table->string('query_hash', 64); // MD5 hash of search query
                $table->text('query_params'); // JSON encoded search parameters
                $table->text('results'); // JSON encoded search results
                $table->integer('result_count')->default(0);
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->unique('query_hash');
                $table->index('expires_at');
            });

            // Hadith statistics tracking
            $this->schema()->create('hadith_verse_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->integer('views')->default(0);
                $table->integer('searches')->default(0);
                $table->integer('shares')->default(0);
                $table->integer('bookmarks')->default(0);
                $table->integer('comments')->default(0);
                $table->timestamp('last_viewed')->nullable();
                $table->timestamps();

                $table->index('hadith_id');
                $table->index('views');
                $table->index('last_viewed');
            });

            // User Hadith bookmarks
            $this->schema()->create('hadith_user_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('hadith_id');
                $table->text('notes')->nullable();
                $table->string('tags', 255)->nullable(); // Comma-separated tags
                $table->timestamps();

                $table->unique(['user_id', 'hadith_id']);
                $table->index('user_id');
                $table->index('hadith_id');
            });

            // Hadith references in wiki pages
            $this->schema()->create('hadith_verse_references', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('page_id');
                $table->unsignedBigInteger('hadith_id');
                $table->string('reference_format', 50)->default('standard'); // standard, inline, footnote
                $table->text('context')->nullable(); // Surrounding text context
                $table->integer('position')->default(0); // Position in the page
                $table->timestamps();

                $table->index(['page_id', 'hadith_id']);
                $table->index('reference_format');
            });

            // User study sessions
            $this->schema()->create('hadith_study_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('hadith_id');
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->string('study_type', 50)->default('reading'); // reading, memorization, analysis
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'hadith_id']);
                $table->index('started_at');
            });

            // Hadith comments
            $this->schema()->create('hadith_verse_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('hadith_id');
                $table->text('comment');
                $table->string('comment_type', 50)->default('general'); // general, question, explanation
                $table->boolean('is_public')->default(true);
                $table->boolean('is_approved')->default(false);
                $table->unsignedBigInteger('parent_id')->nullable(); // For threaded comments
                $table->timestamps();

                $table->index(['hadith_id', 'user_id']);
                $table->index('comment_type');
                $table->index('is_approved');
            });

            // Hadith tags
            $this->schema()->create('hadith_verse_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->text('description')->nullable();
                $table->string('color', 7)->default('#3B82F6'); // Hex color code
                $table->integer('usage_count')->default(0);
                $table->timestamps();

                $table->unique('slug');
                $table->index('name');
                $table->index('usage_count');
            });

            // Hadith tag assignments
            $this->schema()->create('hadith_verse_tag_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('assigned_by')->nullable(); // User who assigned the tag
                $table->timestamps();

                $table->unique(['hadith_id', 'tag_id']);
                $table->index('hadith_id');
                $table->index('tag_id');
            });

            // Hadith authenticity verification
            $this->schema()->create('hadith_authenticity_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('verified_by'); // Scholar who verified
                $table->string('authenticity_level', 50); // sahih, hasan, daif, etc.
                $table->text('verification_notes')->nullable();
                $table->string('verification_source', 255)->nullable();
                $table->timestamp('verified_at');
                $table->timestamps();

                $table->index(['hadith_id', 'authenticity_level']);
                $table->index('verified_by');
            });

            // Hadith chain verification
            $this->schema()->create('hadith_chain_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('narrator_id');
                $table->unsignedBigInteger('verified_by');
                $table->string('verification_status', 50)->default('pending'); // pending, verified, rejected
                $table->text('verification_notes')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->index(['hadith_id', 'narrator_id']);
                $table->index('verification_status');
            });

            echo "✅ Hadith integration tables created successfully\n";
        }

        public function down(): void
        {
            // Drop tables in reverse order
            $this->schema()->dropIfExists('hadith_chain_verifications');
            $this->schema()->dropIfExists('hadith_authenticity_verifications');
            $this->schema()->dropIfExists('hadith_verse_tag_assignments');
            $this->schema()->dropIfExists('hadith_verse_tags');
            $this->schema()->dropIfExists('hadith_verse_comments');
            $this->schema()->dropIfExists('hadith_study_sessions');
            $this->schema()->dropIfExists('hadith_verse_references');
            $this->schema()->dropIfExists('hadith_user_bookmarks');
            $this->schema()->dropIfExists('hadith_verse_stats');
            $this->schema()->dropIfExists('hadith_search_cache');
            $this->schema()->dropIfExists('hadith_wiki_links');

            echo "✅ Hadith integration tables dropped successfully\n";
        }
    };
};

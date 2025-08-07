<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Connection;

return function (Connection $connection) {
    return new class ($connection) extends Migration
    {
        public function up(): void
        {
            error_log("[Migration] 0007_quran_integration up() called");

            // Quran wiki links table - links Quran verses to wiki pages
            $this->schema()->create('quran_wiki_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quran_verse_id'); // Reference to quran_verses table
                $table->unsignedBigInteger('wiki_page_id'); // Reference to pages table
                $table->enum('link_type', ['reference', 'citation', 'widget', 'study']); // Type of link
                $table->text('context')->nullable(); // Context of the link
                $table->unsignedBigInteger('created_by')->nullable(); // User who created the link
                $table->boolean('is_verified')->default(false); // Scholar verification
                $table->unsignedBigInteger('verified_by')->nullable(); // Scholar who verified
                $table->timestamp('verified_at')->nullable(); // Verification timestamp
                $table->timestamps();

                $table->unique(['quran_verse_id', 'wiki_page_id', 'link_type']);
                $table->index('quran_verse_id');
                $table->index('wiki_page_id');
                $table->index('link_type');
                $table->index('is_verified');
            });

            // Quran search cache table - for performance optimization
            $this->schema()->create('quran_search_cache', function (Blueprint $table) {
                $table->id();
                $table->string('search_query', 255); // Search query
                $table->string('language', 10); // Language code
                $table->text('search_results'); // JSON encoded results
                $table->unsignedInteger('results_count'); // Number of results
                $table->timestamp('expires_at'); // Cache expiration
                $table->timestamps();

                $table->unique(['search_query', 'language']);
                $table->index('expires_at');
            });

            // Quran verse statistics table - for analytics
            $this->schema()->create('quran_verse_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id'); // Reference to quran_verses table
                $table->unsignedInteger('views_count')->default(0); // Number of views
                $table->unsignedInteger('searches_count')->default(0); // Number of searches
                $table->unsignedInteger('shares_count')->default(0); // Number of shares
                $table->unsignedInteger('bookmarks_count')->default(0); // Number of bookmarks
                $table->timestamp('last_viewed_at')->nullable(); // Last viewed timestamp
                $table->timestamps();

                $table->unique('verse_id');
                $table->index('views_count');
                $table->index('searches_count');
            });

            // Quran user bookmarks table
            $this->schema()->create('quran_user_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // Reference to users table
                $table->unsignedBigInteger('verse_id'); // Reference to quran_verses table
                $table->text('notes')->nullable(); // User notes
                $table->string('tags', 255)->nullable(); // User tags
                $table->timestamps();

                $table->unique(['user_id', 'verse_id']);
                $table->index('user_id');
                $table->index('verse_id');
            });

            // Quran verse references table - for cross-references
            $this->schema()->create('quran_verse_references', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_verse_id'); // Source verse
                $table->unsignedBigInteger('referenced_verse_id'); // Referenced verse
                $table->enum('reference_type', ['similar', 'related', 'explanation', 'context']); // Type of reference
                $table->text('reference_notes')->nullable(); // Notes about the reference
                $table->unsignedBigInteger('created_by')->nullable(); // User who created reference
                $table->boolean('is_verified')->default(false); // Scholar verification
                $table->timestamps();

                $table->unique(['source_verse_id', 'referenced_verse_id', 'reference_type']);
                $table->index('source_verse_id');
                $table->index('referenced_verse_id');
                $table->index('reference_type');
            });

            // Quran study sessions table - for tracking study sessions
            $this->schema()->create('quran_study_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // Reference to users table
                $table->unsignedBigInteger('verse_id')->nullable(); // Current verse
                $table->unsignedInteger('chapter_number')->nullable(); // Current chapter
                $table->unsignedInteger('verse_number')->nullable(); // Current verse number
                $table->timestamp('started_at'); // Session start time
                $table->timestamp('ended_at')->nullable(); // Session end time
                $table->unsignedInteger('duration_seconds')->default(0); // Session duration
                $table->text('session_notes')->nullable(); // Session notes
                $table->timestamps();

                $table->index('user_id');
                $table->index('verse_id');
                $table->index('started_at');
            });

            // Quran verse comments table - for user comments
            $this->schema()->create('quran_verse_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // Reference to users table
                $table->unsignedBigInteger('verse_id'); // Reference to quran_verses table
                $table->text('comment_text'); // Comment text
                $table->enum('comment_type', ['question', 'insight', 'explanation', 'discussion']); // Comment type
                $table->unsignedBigInteger('parent_comment_id')->nullable(); // For threaded comments
                $table->boolean('is_approved')->default(false); // Moderation status
                $table->unsignedBigInteger('approved_by')->nullable(); // Moderator
                $table->timestamp('approved_at')->nullable(); // Approval timestamp
                $table->timestamps();

                $table->index('user_id');
                $table->index('verse_id');
                $table->index('parent_comment_id');
                $table->index('is_approved');
            });

            // Quran verse tags table - for categorizing verses
            $this->schema()->create('quran_verse_tags', function (Blueprint $table) {
                $table->id();
                $table->string('tag_name', 100); // Tag name
                $table->string('tag_description', 255)->nullable(); // Tag description
                $table->string('tag_color', 7)->nullable(); // Tag color code
                $table->boolean('is_official')->default(false); // Official tag
                $table->unsignedBigInteger('created_by')->nullable(); // User who created tag
                $table->timestamps();

                $table->unique('tag_name');
                $table->index('is_official');
            });

            // Quran verse tag assignments table
            $this->schema()->create('quran_verse_tag_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id'); // Reference to quran_verses table
                $table->unsignedBigInteger('tag_id'); // Reference to quran_verse_tags table
                $table->unsignedBigInteger('assigned_by')->nullable(); // User who assigned tag
                $table->boolean('is_verified')->default(false); // Scholar verification
                $table->timestamps();

                $table->unique(['verse_id', 'tag_id']);
                $table->index('verse_id');
                $table->index('tag_id');
            });

            error_log("[Migration] 0007_quran_integration up() completed successfully");
        }

        public function down(): void
        {
            error_log("[Migration] 0007_quran_integration down() called");

            // Drop tables in reverse order
            $this->schema()->dropIfExists('quran_verse_tag_assignments');
            $this->schema()->dropIfExists('quran_verse_tags');
            $this->schema()->dropIfExists('quran_verse_comments');
            $this->schema()->dropIfExists('quran_study_sessions');
            $this->schema()->dropIfExists('quran_verse_references');
            $this->schema()->dropIfExists('quran_user_bookmarks');
            $this->schema()->dropIfExists('quran_verse_stats');
            $this->schema()->dropIfExists('quran_search_cache');
            $this->schema()->dropIfExists('quran_wiki_links');

            error_log("[Migration] 0007_quran_integration down() completed successfully");
        }
    };
};

<?php

/**
 * Migration: Advanced Islamic Features
 *
 * This migration creates the database schema for advanced Islamic features
 * including Islamic content management, user preferences, recommendation
 * system, and enhanced Islamic calendar features.
 *
 * @package IslamWiki
 * @version 0.0.22
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Connection;

return function (Connection $connection) {
    return new class ($connection) extends Migration
    {
        /**
         * Run the migration.
         */
        public function up(): void
        {
            // Islamic content table
            $this->schema()->create('islamic_content', function ($table) {
                $table->id();
                $table->string('title', 255)->comment('Content title');
                $table->text('content')->comment('Content body');
                $table->string('category', 50)->comment('Content category');
                $table->json('tags')->nullable()->comment('Content tags');
                $table->string('author', 100)->nullable()->comment('Content author');
                $table->string('source', 255)->nullable()->comment('Content source');
                $table->text('summary')->nullable()->comment('Content summary');
                $table->string('language', 10)->default('en')->comment('Content language');
                $table->boolean('is_published')->default(false)->comment('Whether content is published');
                $table->boolean('is_featured')->default(false)->comment('Whether content is featured');
                $table->boolean('is_verified')->default(false)->comment('Whether content is verified by scholars');
                $table->integer('view_count')->default(0)->comment('Number of views');
                $table->integer('like_count')->default(0)->comment('Number of likes');
                $table->integer('share_count')->default(0)->comment('Number of shares');
                $table->integer('comment_count')->default(0)->comment('Number of comments');
                $table->timestamps();

                $table->index('category');
                $table->index('is_published');
                $table->index('is_featured');
                $table->index('created_at');
                $table->index('view_count');
            });

            // User preferences table
            $this->schema()->create('user_preferences', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->json('preferences')->comment('User preferences JSON');
                $table->string('language', 10)->default('en')->comment('Preferred language');
                $table->string('timezone', 50)->nullable()->comment('User timezone');
                $table->string('prayer_method', 20)->default('MWL')->comment('Prayer calculation method');
                $table->boolean('notifications_enabled')->default(true)->comment('Whether notifications are enabled');
                $table->json('notification_settings')->nullable()->comment('Notification settings JSON');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('language');
                $table->index('prayer_method');
            });

            // User content history table
            $this->schema()->create('user_content_history', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->unsignedBigInteger('content_id')->comment('Content ID');
                $table->string('category', 50)->comment('Content category');
                $table->integer('view_duration')->nullable()->comment('View duration in seconds');
                $table->boolean('completed')->default(false)->comment('Whether content was fully viewed');
                $table->timestamp('viewed_at')->comment('When content was viewed');
                $table->timestamps();

                $table->index('user_id');
                $table->index('content_id');
                $table->index('category');
                $table->index('viewed_at');
            });

            // User comments table
            $this->schema()->create('user_comments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->unsignedBigInteger('content_id')->comment('Content ID');
                $table->text('comment')->comment('Comment text');
                $table->boolean('is_approved')->default(false)->comment('Whether comment is approved');
                $table->boolean('is_flagged')->default(false)->comment('Whether comment is flagged');
                $table->integer('like_count')->default(0)->comment('Number of likes');
                $table->integer('reply_count')->default(0)->comment('Number of replies');
                $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent comment ID');
                $table->timestamps();

                $table->index('user_id');
                $table->index('content_id');
                $table->index('is_approved');
                $table->index('parent_id');
                $table->index('created_at');
            });

            // Content categories table
            $this->schema()->create('content_categories', function ($table) {
                $table->id();
                $table->string('name', 100)->comment('Category name');
                $table->string('slug', 100)->unique()->comment('Category slug');
                $table->text('description')->nullable()->comment('Category description');
                $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent category ID');
                $table->integer('sort_order')->default(0)->comment('Sort order');
                $table->boolean('is_active')->default(true)->comment('Whether category is active');
                $table->timestamps();

                $table->index('parent_id');
                $table->index('slug');
                $table->index('is_active');
                $table->index('sort_order');
            });

            // Content tags table
            $this->schema()->create('content_tags', function ($table) {
                $table->id();
                $table->string('name', 100)->comment('Tag name');
                $table->string('slug', 100)->unique()->comment('Tag slug');
                $table->text('description')->nullable()->comment('Tag description');
                $table->string('color', 7)->nullable()->comment('Tag color (hex)');
                $table->boolean('is_active')->default(true)->comment('Whether tag is active');
                $table->timestamps();

                $table->index('slug');
                $table->index('is_active');
            });

            // Content tag assignments table
            $this->schema()->create('content_tag_assignments', function ($table) {
                $table->id();
                $table->unsignedBigInteger('content_id')->comment('Content ID');
                $table->unsignedBigInteger('tag_id')->comment('Tag ID');
                $table->timestamps();

                $table->unique(['content_id', 'tag_id']);
                $table->index('content_id');
                $table->index('tag_id');
            });

            // Islamic events table
            $this->schema()->create('islamic_events', function ($table) {
                $table->id();
                $table->string('title', 255)->comment('Event title');
                $table->text('description')->nullable()->comment('Event description');
                $table->date('hijri_date')->comment('Hijri date');
                $table->date('gregorian_date')->comment('Gregorian date');
                $table->string('event_type', 50)->comment('Event type (religious, historical, cultural)');
                $table->string('significance', 50)->comment('Event significance (high, medium, low)');
                $table->json('locations')->nullable()->comment('Event locations');
                $table->json('participants')->nullable()->comment('Event participants');
                $table->text('source_references')->nullable()->comment('Source references');
                $table->boolean('is_verified')->default(false)->comment('Whether event is verified');
                $table->timestamps();

                $table->index('hijri_date');
                $table->index('gregorian_date');
                $table->index('event_type');
                $table->index('significance');
                $table->index('is_verified');
            });

            // User study sessions table
            $this->schema()->create('user_study_sessions', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->string('session_type', 50)->comment('Session type (quran, hadith, fiqh, etc.)');
                $table->timestamp('started_at')->comment('When session started');
                $table->timestamp('ended_at')->nullable()->comment('When session ended');
                $table->integer('duration_minutes')->nullable()->comment('Session duration in minutes');
                $table->json('materials_studied')->nullable()->comment('Materials studied during session');
                $table->text('notes')->nullable()->comment('Session notes');
                $table->integer('focus_score')->nullable()->comment('Focus score (1-10)');
                $table->timestamps();

                $table->index('user_id');
                $table->index('session_type');
                $table->index('started_at');
                $table->index('ended_at');
            });

            // User learning progress table
            $this->schema()->create('user_learning_progress', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->string('subject', 100)->comment('Learning subject');
                $table->string('topic', 100)->comment('Learning topic');
                $table->integer('progress_percentage')->default(0)->comment('Progress percentage (0-100)');
                $table->integer('time_spent_minutes')->default(0)->comment('Total time spent in minutes');
                $table->integer('lessons_completed')->default(0)->comment('Number of lessons completed');
                $table->integer('total_lessons')->default(0)->comment('Total number of lessons');
                $table->timestamp('last_studied_at')->nullable()->comment('When last studied');
                $table->json('achievements')->nullable()->comment('Achievements earned');
                $table->timestamps();

                $table->unique(['user_id', 'subject', 'topic']);
                $table->index('user_id');
                $table->index('subject');
                $table->index('progress_percentage');
                $table->index('last_studied_at');
            });

            // Insert default data
            $this->insertDefaultCategories();
            $this->insertDefaultTags();
            $this->insertIslamicEvents();
        }

        /**
         * Reverse the migration.
         */
        public function down(): void
        {
            $this->schema()->drop('user_learning_progress');
            $this->schema()->drop('user_study_sessions');
            $this->schema()->drop('islamic_events');
            $this->schema()->drop('content_tag_assignments');
            $this->schema()->drop('content_tags');
            $this->schema()->drop('content_categories');
            $this->schema()->drop('user_comments');
            $this->schema()->drop('user_content_history');
            $this->schema()->drop('user_preferences');
            $this->schema()->drop('islamic_content');
        }

        /**
         * Insert default content categories.
         */
        private function insertDefaultCategories(): void
        {
            $categories = [
                ['name' => 'Quran Studies', 'slug' => 'quran-studies', 'description' => 'Quran recitation, memorization, and interpretation'],
                ['name' => 'Hadith Studies', 'slug' => 'hadith-studies', 'description' => 'Hadith collection, authentication, and understanding'],
                ['name' => 'Islamic Law (Fiqh)', 'slug' => 'islamic-law', 'description' => 'Islamic jurisprudence and legal rulings'],
                ['name' => 'Islamic History', 'slug' => 'islamic-history', 'description' => 'Islamic civilization and historical events'],
                ['name' => 'Islamic Ethics', 'slug' => 'islamic-ethics', 'description' => 'Moral teachings and character development'],
                ['name' => 'Islamic Spirituality', 'slug' => 'islamic-spirituality', 'description' => 'Sufism and spiritual practices'],
                ['name' => 'Islamic Art & Architecture', 'slug' => 'islamic-art', 'description' => 'Islamic artistic traditions and architectural styles'],
                ['name' => 'Islamic Science', 'slug' => 'islamic-science', 'description' => 'Scientific contributions from Islamic civilization'],
                ['name' => 'Contemporary Issues', 'slug' => 'contemporary-issues', 'description' => 'Modern Islamic challenges and solutions'],
                ['name' => 'Interfaith Dialogue', 'slug' => 'interfaith-dialogue', 'description' => 'Building bridges between faiths']
            ];

            foreach ($categories as $category) {
                $this->connection->table('content_categories')->insert([
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        /**
         * Insert default content tags.
         */
        private function insertDefaultTags(): void
        {
            $tags = [
                ['name' => 'Beginner', 'slug' => 'beginner', 'description' => 'Suitable for beginners', 'color' => '#28a745'],
                ['name' => 'Intermediate', 'slug' => 'intermediate', 'description' => 'Suitable for intermediate learners', 'color' => '#ffc107'],
                ['name' => 'Advanced', 'slug' => 'advanced', 'description' => 'Suitable for advanced learners', 'color' => '#dc3545'],
                ['name' => 'Memorization', 'slug' => 'memorization', 'description' => 'Content for memorization', 'color' => '#17a2b8'],
                ['name' => 'Recitation', 'slug' => 'recitation', 'description' => 'Content for recitation practice', 'color' => '#6f42c1'],
                ['name' => 'Tafsir', 'slug' => 'tafsir', 'description' => 'Quranic interpretation', 'color' => '#fd7e14'],
                ['name' => 'Authentic', 'slug' => 'authentic', 'description' => 'Authenticated content', 'color' => '#20c997'],
                ['name' => 'Scholarly', 'slug' => 'scholarly', 'description' => 'Academic or scholarly content', 'color' => '#6c757d'],
                ['name' => 'Practical', 'slug' => 'practical', 'description' => 'Practical application', 'color' => '#e83e8c'],
                ['name' => 'Theoretical', 'slug' => 'theoretical', 'description' => 'Theoretical knowledge', 'color' => '#495057']
            ];

            foreach ($tags as $tag) {
                $this->connection->table('content_tags')->insert([
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'description' => $tag['description'],
                    'color' => $tag['color'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        /**
         * Insert default Islamic events.
         */
        private function insertIslamicEvents(): void
        {
            $events = [
                [
                    'title' => 'Laylat al-Qadr',
                    'description' => 'The Night of Power, one of the holiest nights in Islam',
                    'hijri_date' => '1445-09-27',
                    'gregorian_date' => '2024-04-06',
                    'event_type' => 'religious',
                    'significance' => 'high',
                    'is_verified' => true
                ],
                [
                    'title' => 'Eid al-Fitr',
                    'description' => 'Festival of Breaking the Fast, celebrated at the end of Ramadan',
                    'hijri_date' => '1445-10-01',
                    'gregorian_date' => '2024-04-10',
                    'event_type' => 'religious',
                    'significance' => 'high',
                    'is_verified' => true
                ],
                [
                    'title' => 'Eid al-Adha',
                    'description' => 'Festival of Sacrifice, commemorating Prophet Ibrahim\'s willingness to sacrifice his son',
                    'hijri_date' => '1445-12-10',
                    'gregorian_date' => '2024-06-17',
                    'event_type' => 'religious',
                    'significance' => 'high',
                    'is_verified' => true
                ],
                [
                    'title' => 'Islamic New Year',
                    'description' => 'Beginning of the Islamic calendar year',
                    'hijri_date' => '1446-01-01',
                    'gregorian_date' => '2024-07-08',
                    'event_type' => 'religious',
                    'significance' => 'medium',
                    'is_verified' => true
                ],
                [
                    'title' => 'Mawlid al-Nabi',
                    'description' => 'Birthday of Prophet Muhammad (PBUH)',
                    'hijri_date' => '1446-03-12',
                    'gregorian_date' => '2024-09-16',
                    'event_type' => 'religious',
                    'significance' => 'high',
                    'is_verified' => true
                ]
            ];

            foreach ($events as $event) {
                $this->connection->table('islamic_events')->insert([
                    'title' => $event['title'],
                    'description' => $event['description'],
                    'hijri_date' => $event['hijri_date'],
                    'gregorian_date' => $event['gregorian_date'],
                    'event_type' => $event['event_type'],
                    'significance' => $event['significance'],
                    'is_verified' => $event['is_verified'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    };
};

<?php

/**
 * Migration: Islamic Calendar Tables
 * 
 * Creates tables for Islamic calendar functionality including:
 * - Islamic events and holidays
 * - Event categories
 * - Prayer times
 * - Hijri date conversions
 * - User interactions and analytics
 */

use IslamWiki\Core\Database\Migrations\Migration;

class IslamicCalendarMigration extends Migration
{
    public function up(): void
    {
        // Event categories table
        $this->schema()->create('event_categories', function($table) {
            $table->id();
            $table->string('name', 100)->notNull();
            $table->string('name_arabic', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6b7280'); // Hex color code
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });

        // Islamic events table
        $this->schema()->create('islamic_events', function($table) {
            $table->id();
            $table->string('title', 255)->notNull();
            $table->string('title_arabic', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('description_arabic')->nullable();
            $table->date('hijri_date')->notNull();
            $table->date('gregorian_date')->notNull();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_public_holiday')->default(false);
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('event_categories')->onDelete('set null');
            $table->index('hijri_date');
            $table->index('gregorian_date');
            $table->index('category_id');
            $table->index('is_holiday');
            $table->index('is_public_holiday');
        });

        // Prayer times table
        $this->schema()->create('prayer_times', function($table) {
            $table->id();
            $table->date('date')->notNull();
            $table->time('fajr')->nullable();
            $table->time('sunrise')->nullable();
            $table->time('dhuhr')->nullable();
            $table->time('asr')->nullable();
            $table->time('maghrib')->nullable();
            $table->time('isha')->nullable();
            $table->string('location', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('calculation_method', 50)->default('MWL'); // MWL, ISNA, etc.
            $table->timestamps();
            
            $table->unique('date');
            $table->index('date');
            $table->index('location');
        });

        // Hijri date conversions table (for caching)
        $this->schema()->create('hijri_dates', function($table) {
            $table->id();
            $table->date('gregorian_date')->notNull();
            $table->integer('hijri_year')->notNull();
            $table->integer('hijri_month')->notNull();
            $table->integer('hijri_day')->notNull();
            $table->string('hijri_date_formatted', 10)->notNull(); // YYYY-MM-DD
            $table->string('hijri_month_name', 50)->nullable();
            $table->string('hijri_month_name_arabic', 50)->nullable();
            $table->timestamps();
            
            $table->unique('gregorian_date');
            $table->index('hijri_date_formatted');
            $table->index('hijri_year');
            $table->index('hijri_month');
        });

        // Calendar integration tables
        $this->schema()->create('calendar_wiki_links', function($table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->notNull();
            $table->unsignedBigInteger('page_id')->notNull();
            $table->string('link_type', 50)->default('reference'); // reference, mention, etc.
            $table->text('context')->nullable();
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('islamic_events')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->unique(['event_id', 'page_id']);
            $table->index('link_type');
        });

        // Calendar search cache
        $this->schema()->create('calendar_search_cache', function($table) {
            $table->id();
            $table->string('query_hash', 64)->notNull();
            $table->text('query_params')->notNull(); // JSON
            $table->text('results')->notNull(); // JSON
            $table->integer('result_count')->notNull();
            $table->timestamp('expires_at')->notNull();
            $table->timestamps();
            
            $table->unique('query_hash');
            $table->index('expires_at');
        });

        // Calendar event statistics
        $this->schema()->create('calendar_event_stats', function($table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->notNull();
            $table->integer('views')->default(0);
            $table->integer('searches')->default(0);
            $table->integer('bookmarks')->default(0);
            $table->integer('shares')->default(0);
            $table->date('date')->notNull();
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('islamic_events')->onDelete('cascade');
            $table->unique(['event_id', 'date']);
            $table->index('date');
        });

        // User calendar interactions
        $this->schema()->create('calendar_user_bookmarks', function($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('event_id')->notNull();
            $table->text('notes')->nullable();
            $table->boolean('is_reminder')->default(false);
            $table->timestamp('reminder_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('islamic_events')->onDelete('cascade');
            $table->unique(['user_id', 'event_id']);
            $table->index('reminder_at');
        });

        // Calendar event comments
        $this->schema()->create('calendar_event_comments', function($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('event_id')->notNull();
            $table->text('comment')->notNull();
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('islamic_events')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index('event_id');
            $table->index('is_approved');
        });

        // Calendar reminders
        $this->schema()->create('calendar_reminders', function($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->notNull();
            $table->unsignedBigInteger('event_id')->notNull();
            $table->string('reminder_type', 50)->default('notification'); // notification, email, sms
            $table->timestamp('reminder_at')->notNull();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('islamic_events')->onDelete('cascade');
            $table->index('reminder_at');
            $table->index('is_sent');
        });

        // Insert default event categories
        $this->insertDefaultCategories();
    }

    public function down(): void
    {
        $this->schema()->drop('calendar_reminders');
        $this->schema()->drop('calendar_event_comments');
        $this->schema()->drop('calendar_user_bookmarks');
        $this->schema()->drop('calendar_event_stats');
        $this->schema()->drop('calendar_search_cache');
        $this->schema()->drop('calendar_wiki_links');
        $this->schema()->drop('hijri_dates');
        $this->schema()->drop('prayer_times');
        $this->schema()->drop('islamic_events');
        $this->schema()->drop('event_categories');
    }

    private function insertDefaultCategories()
    {
        $categories = [
            [
                'name' => 'Islamic Holidays',
                'name_arabic' => 'الأعياد الإسلامية',
                'description' => 'Major Islamic holidays and celebrations',
                'color' => '#dc2626',
                'sort_order' => 1
            ],
            [
                'name' => 'Historical Events',
                'name_arabic' => 'الأحداث التاريخية',
                'description' => 'Important historical events in Islamic history',
                'color' => '#7c3aed',
                'sort_order' => 2
            ],
            [
                'name' => 'Religious Observances',
                'name_arabic' => 'المناسبات الدينية',
                'description' => 'Religious observances and special days',
                'color' => '#059669',
                'sort_order' => 3
            ],
            [
                'name' => 'Prophet\'s Life',
                'name_arabic' => 'حياة النبي',
                'description' => 'Events related to the life of Prophet Muhammad (PBUH)',
                'color' => '#d97706',
                'sort_order' => 4
            ],
            [
                'name' => 'Islamic Scholars',
                'name_arabic' => 'العلماء المسلمين',
                'description' => 'Birth and death anniversaries of Islamic scholars',
                'color' => '#0891b2',
                'sort_order' => 5
            ],
            [
                'name' => 'Islamic Conquests',
                'name_arabic' => 'الفتوحات الإسلامية',
                'description' => 'Historical Islamic conquests and battles',
                'color' => '#be185d',
                'sort_order' => 6
            ],
            [
                'name' => 'Islamic Architecture',
                'name_arabic' => 'العمارة الإسلامية',
                'description' => 'Construction and completion of important Islamic buildings',
                'color' => '#059669',
                'sort_order' => 7
            ],
            [
                'name' => 'Islamic Literature',
                'name_arabic' => 'الأدب الإسلامي',
                'description' => 'Publication of important Islamic books and literature',
                'color' => '#7c2d12',
                'sort_order' => 8
            ]
        ];

        foreach ($categories as $category) {
            $this->connection->table('event_categories')->insert($category);
        }
    }
};

return function($connection) {
    return new IslamicCalendarMigration($connection);
}; 
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

class Migration_0014_AdvancedIslamicFeatures extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Islamic content table
        $this->createTable('islamic_content', function ($table) {
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
        $this->createTable('user_preferences', function ($table) {
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
        $this->createTable('user_content_history', function ($table) {
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
        $this->createTable('user_comments', function ($table) {
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
            $table->index('created_at');
        });

        // User likes table
        $this->createTable('user_likes', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User ID');
            $table->unsignedBigInteger('content_id')->comment('Content ID');
            $table->enum('type', ['like', 'dislike'])->default('like')->comment('Like type');
            $table->timestamps();

            $table->unique(['user_id', 'content_id']);
            $table->index('content_id');
            $table->index('type');
        });

        // User shares table
        $this->createTable('user_shares', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User ID');
            $table->unsignedBigInteger('content_id')->comment('Content ID');
            $table->string('platform', 50)->nullable()->comment('Sharing platform');
            $table->text('comment')->nullable()->comment('Share comment');
            $table->timestamps();

            $table->index('user_id');
            $table->index('content_id');
            $table->index('platform');
        });

        // Islamic events table
        $this->createTable('islamic_events', function ($table) {
            $table->id();
            $table->string('name', 100)->comment('Event name');
            $table->string('name_arabic', 100)->comment('Event name in Arabic');
            $table->text('description')->nullable()->comment('Event description');
            $table->string('hijri_month', 2)->comment('Hijri month');
            $table->string('hijri_day', 2)->comment('Hijri day');
            $table->enum('type', ['religious', 'historical', 'cultural'])->default('religious')->comment('Event type');
            $table->boolean('is_public_holiday')->default(false)->comment('Whether it is a public holiday');
            $table->json('custom_dates')->nullable()->comment('Custom dates for the event');
            $table->timestamps();

            $table->index(['hijri_month', 'hijri_day']);
            $table->index('type');
            $table->index('is_public_holiday');
        });

        // Prayer times cache table
        $this->createTable('prayer_times_cache', function ($table) {
            $table->id();
            $table->float('latitude')->comment('Location latitude');
            $table->float('longitude')->comment('Location longitude');
            $table->date('date')->comment('Prayer date');
            $table->string('method', 20)->comment('Calculation method');
            $table->json('prayer_times')->comment('Prayer times JSON');
            $table->json('qibla_direction')->comment('Qibla direction JSON');
            $table->json('lunar_phase')->comment('Lunar phase JSON');
            $table->timestamps();

            $table->unique(['latitude', 'longitude', 'date', 'method']);
            $table->index('date');
            $table->index('method');
        });

        // Islamic content categories table
        $this->createTable('islamic_content_categories', function ($table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Category name');
            $table->string('display_name', 100)->comment('Category display name');
            $table->string('display_name_arabic', 100)->comment('Category display name in Arabic');
            $table->text('description')->nullable()->comment('Category description');
            $table->string('icon', 50)->nullable()->comment('Category icon');
            $table->string('color', 7)->nullable()->comment('Category color');
            $table->integer('sort_order')->default(0)->comment('Sort order');
            $table->boolean('is_active')->default(true)->comment('Whether category is active');
            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
        });

        // Islamic content tags table
        $this->createTable('islamic_content_tags', function ($table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Tag name');
            $table->string('display_name', 100)->comment('Tag display name');
            $table->string('display_name_arabic', 100)->comment('Tag display name in Arabic');
            $table->text('description')->nullable()->comment('Tag description');
            $table->string('color', 7)->nullable()->comment('Tag color');
            $table->integer('usage_count')->default(0)->comment('Number of times tag is used');
            $table->boolean('is_active')->default(true)->comment('Whether tag is active');
            $table->timestamps();

            $table->index('usage_count');
            $table->index('is_active');
        });

        // Insert default Islamic content categories
        $this->insertDefaultCategories();

        // Insert default Islamic content tags
        $this->insertDefaultTags();

        // Insert major Islamic events
        $this->insertIslamicEvents();
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->dropTable('islamic_content_tags');
        $this->dropTable('islamic_content_categories');
        $this->dropTable('prayer_times_cache');
        $this->dropTable('islamic_events');
        $this->dropTable('user_shares');
        $this->dropTable('user_likes');
        $this->dropTable('user_comments');
        $this->dropTable('user_content_history');
        $this->dropTable('user_preferences');
        $this->dropTable('islamic_content');
    }

    /**
     * Insert default Islamic content categories.
     */
    private function insertDefaultCategories(): void
    {
        $categories = [
            [
                'name' => 'quran',
                'display_name' => 'Quran & Tafsir',
                'display_name_arabic' => 'القرآن والتفسير',
                'description' => 'Quranic studies, tafsir, and interpretation',
                'icon' => 'book-open',
                'color' => '#1f2937',
                'sort_order' => 1
            ],
            [
                'name' => 'hadith',
                'display_name' => 'Hadith & Sunnah',
                'display_name_arabic' => 'الحديث والسنة',
                'description' => 'Prophetic traditions and sayings',
                'icon' => 'chat-bubble-left-right',
                'color' => '#059669',
                'sort_order' => 2
            ],
            [
                'name' => 'fiqh',
                'display_name' => 'Islamic Law & Jurisprudence',
                'display_name_arabic' => 'الفقه والأحكام',
                'description' => 'Islamic legal rulings and jurisprudence',
                'icon' => 'scale',
                'color' => '#dc2626',
                'sort_order' => 3
            ],
            [
                'name' => 'aqeedah',
                'display_name' => 'Islamic Beliefs & Creed',
                'display_name_arabic' => 'العقيدة والإيمان',
                'description' => 'Islamic theology and beliefs',
                'icon' => 'heart',
                'color' => '#7c3aed',
                'sort_order' => 4
            ],
            [
                'name' => 'seerah',
                'display_name' => 'Prophet Muhammad (PBUH)',
                'display_name_arabic' => 'سيرة النبي محمد ﷺ',
                'description' => 'Life and teachings of Prophet Muhammad',
                'icon' => 'user',
                'color' => '#ea580c',
                'sort_order' => 5
            ],
            [
                'name' => 'history',
                'display_name' => 'Islamic History',
                'display_name_arabic' => 'التاريخ الإسلامي',
                'description' => 'Islamic historical events and figures',
                'icon' => 'clock',
                'color' => '#0891b2',
                'sort_order' => 6
            ],
            [
                'name' => 'scholars',
                'display_name' => 'Islamic Scholars',
                'display_name_arabic' => 'العلماء والمفكرون',
                'description' => 'Biographies and works of Islamic scholars',
                'icon' => 'academic-cap',
                'color' => '#65a30d',
                'sort_order' => 7
            ],
            [
                'name' => 'prayer',
                'display_name' => 'Prayer & Worship',
                'display_name_arabic' => 'الصلاة والعبادة',
                'description' => 'Prayer, worship, and spiritual practices',
                'icon' => 'hands-praying',
                'color' => '#059669',
                'sort_order' => 8
            ],
            [
                'name' => 'ramadan',
                'display_name' => 'Ramadan & Fasting',
                'display_name_arabic' => 'رمضان والصيام',
                'description' => 'Ramadan, fasting, and spiritual practices',
                'icon' => 'moon',
                'color' => '#7c3aed',
                'sort_order' => 9
            ],
            [
                'name' => 'hajj',
                'display_name' => 'Hajj & Umrah',
                'display_name_arabic' => 'الحج والعمرة',
                'description' => 'Pilgrimage and sacred journeys',
                'icon' => 'building-library',
                'color' => '#dc2626',
                'sort_order' => 10
            ],
            [
                'name' => 'charity',
                'display_name' => 'Charity & Zakat',
                'display_name_arabic' => 'الصدقة والزكاة',
                'description' => 'Charity, zakat, and social responsibility',
                'icon' => 'gift',
                'color' => '#65a30d',
                'sort_order' => 11
            ],
            [
                'name' => 'family',
                'display_name' => 'Family & Marriage',
                'display_name_arabic' => 'الأسرة والزواج',
                'description' => 'Family life, marriage, and relationships',
                'icon' => 'users',
                'color' => '#ea580c',
                'sort_order' => 12
            ],
            [
                'name' => 'education',
                'display_name' => 'Islamic Education',
                'display_name_arabic' => 'التعليم الإسلامي',
                'description' => 'Islamic education and learning',
                'icon' => 'academic-cap',
                'color' => '#0891b2',
                'sort_order' => 13
            ],
            [
                'name' => 'modern',
                'display_name' => 'Modern Islamic Issues',
                'display_name_arabic' => 'القضايا الإسلامية المعاصرة',
                'description' => 'Contemporary Islamic issues and challenges',
                'icon' => 'globe-alt',
                'color' => '#1f2937',
                'sort_order' => 14
            ]
        ];

        foreach ($categories as $category) {
            $this->insert('islamic_content_categories', $category);
        }
    }

    /**
     * Insert default Islamic content tags.
     */
    private function insertDefaultTags(): void
    {
        $tags = [
            [
                'name' => 'beginner',
                'display_name' => 'Beginner Level',
                'display_name_arabic' => 'مستوى المبتدئ',
                'description' => 'Content suitable for beginners',
                'color' => '#059669',
                'usage_count' => 0
            ],
            [
                'name' => 'intermediate',
                'display_name' => 'Intermediate Level',
                'display_name_arabic' => 'مستوى المتوسط',
                'description' => 'Content suitable for intermediate learners',
                'color' => '#ea580c',
                'usage_count' => 0
            ],
            [
                'name' => 'advanced',
                'display_name' => 'Advanced Level',
                'display_name_arabic' => 'مستوى المتقدم',
                'description' => 'Content suitable for advanced learners',
                'color' => '#dc2626',
                'usage_count' => 0
            ],
            [
                'name' => 'scholarly',
                'display_name' => 'Scholarly Content',
                'display_name_arabic' => 'محتوى علمي',
                'description' => 'Academic and scholarly content',
                'color' => '#7c3aed',
                'usage_count' => 0
            ],
            [
                'name' => 'practical',
                'display_name' => 'Practical Guidance',
                'display_name_arabic' => 'إرشادات عملية',
                'description' => 'Practical guidance and advice',
                'color' => '#65a30d',
                'usage_count' => 0
            ],
            [
                'name' => 'theoretical',
                'display_name' => 'Theoretical Knowledge',
                'display_name_arabic' => 'معرفة نظرية',
                'description' => 'Theoretical and conceptual knowledge',
                'color' => '#0891b2',
                'usage_count' => 0
            ],
            [
                'name' => 'historical',
                'display_name' => 'Historical Context',
                'display_name_arabic' => 'سياق تاريخي',
                'description' => 'Historical context and background',
                'color' => '#1f2937',
                'usage_count' => 0
            ],
            [
                'name' => 'contemporary',
                'display_name' => 'Contemporary Issues',
                'display_name_arabic' => 'قضايا معاصرة',
                'description' => 'Contemporary issues and challenges',
                'color' => '#ea580c',
                'usage_count' => 0
            ],
            [
                'name' => 'spiritual',
                'display_name' => 'Spiritual Development',
                'display_name_arabic' => 'التطور الروحي',
                'description' => 'Spiritual development and growth',
                'color' => '#7c3aed',
                'usage_count' => 0
            ],
            [
                'name' => 'social',
                'display_name' => 'Social Issues',
                'display_name_arabic' => 'قضايا اجتماعية',
                'description' => 'Social issues and community',
                'color' => '#059669',
                'usage_count' => 0
            ],
            [
                'name' => 'economic',
                'display_name' => 'Economic Principles',
                'display_name_arabic' => 'المبادئ الاقتصادية',
                'description' => 'Islamic economic principles',
                'color' => '#65a30d',
                'usage_count' => 0
            ],
            [
                'name' => 'political',
                'display_name' => 'Political Thought',
                'display_name_arabic' => 'الفكر السياسي',
                'description' => 'Islamic political thought',
                'color' => '#dc2626',
                'usage_count' => 0
            ],
            [
                'name' => 'scientific',
                'display_name' => 'Scientific Perspective',
                'display_name_arabic' => 'منظور علمي',
                'description' => 'Scientific and empirical perspective',
                'color' => '#0891b2',
                'usage_count' => 0
            ],
            [
                'name' => 'philosophical',
                'display_name' => 'Philosophical Discussion',
                'display_name_arabic' => 'مناقشة فلسفية',
                'description' => 'Philosophical and intellectual discussion',
                'color' => '#1f2937',
                'usage_count' => 0
            ]
        ];

        foreach ($tags as $tag) {
            $this->insert('islamic_content_tags', $tag);
        }
    }

    /**
     * Insert major Islamic events.
     */
    private function insertIslamicEvents(): void
    {
        $events = [
            [
                'name' => 'Ashura',
                'name_arabic' => 'عاشوراء',
                'description' => 'The 10th day of Muharram, commemorating the martyrdom of Husayn ibn Ali',
                'hijri_month' => '01',
                'hijri_day' => '10',
                'type' => 'religious',
                'is_public_holiday' => true
            ],
            [
                'name' => 'Arbaeen',
                'name_arabic' => 'الأربعين',
                'description' => 'The 40th day after Ashura, marking the end of the mourning period',
                'hijri_month' => '01',
                'hijri_day' => '27',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'Mawlid al-Nabi',
                'name_arabic' => 'مولد النبي',
                'description' => 'The birthday of Prophet Muhammad (peace be upon him)',
                'hijri_month' => '03',
                'hijri_day' => '12',
                'type' => 'religious',
                'is_public_holiday' => true
            ],
            [
                'name' => 'Laylat al-Miraj',
                'name_arabic' => 'ليلة الإسراء والمعراج',
                'description' => 'The night journey and ascension of Prophet Muhammad',
                'hijri_month' => '07',
                'hijri_day' => '27',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'Laylat al-Baraah',
                'name_arabic' => 'ليلة البراءة',
                'description' => 'The night of forgiveness and salvation',
                'hijri_month' => '08',
                'hijri_day' => '15',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'First day of Ramadan',
                'name_arabic' => 'أول يوم من رمضان',
                'description' => 'The beginning of the holy month of fasting',
                'hijri_month' => '09',
                'hijri_day' => '01',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'Laylat al-Qadr',
                'name_arabic' => 'ليلة القدر',
                'description' => 'The night of power, better than a thousand months',
                'hijri_month' => '09',
                'hijri_day' => '27',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'Eid al-Fitr',
                'name_arabic' => 'عيد الفطر',
                'description' => 'The festival of breaking the fast',
                'hijri_month' => '10',
                'hijri_day' => '01',
                'type' => 'religious',
                'is_public_holiday' => true
            ],
            [
                'name' => 'Day of Arafah',
                'name_arabic' => 'يوم عرفة',
                'description' => 'The day of standing at Arafah during Hajj',
                'hijri_month' => '12',
                'hijri_day' => '08',
                'type' => 'religious',
                'is_public_holiday' => false
            ],
            [
                'name' => 'Eid al-Adha',
                'name_arabic' => 'عيد الأضحى',
                'description' => 'The festival of sacrifice',
                'hijri_month' => '12',
                'hijri_day' => '10',
                'type' => 'religious',
                'is_public_holiday' => true
            ],
            [
                'name' => 'Eid al-Ghadeer',
                'name_arabic' => 'عيد الغدير',
                'description' => 'The day of Ghadir Khumm',
                'hijri_month' => '12',
                'hijri_day' => '18',
                'type' => 'religious',
                'is_public_holiday' => false
            ]
        ];

        foreach ($events as $event) {
            $this->insert('islamic_events', $event);
        }
    }
}

<?php

/**
 * Migration: User Settings Schema
 *
 * This migration creates the database schema for user settings and preferences
 * including user profiles, notification settings, privacy settings, and
 * customization options.
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
            // User profiles table
            $this->schema()->create('user_profiles', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->string('display_name', 100)->nullable()->comment('Display name');
                $table->text('bio')->nullable()->comment('User biography');
                $table->string('avatar', 255)->nullable()->comment('Avatar image path');
                $table->string('location', 100)->nullable()->comment('User location');
                $table->string('website', 255)->nullable()->comment('Personal website');
                $table->date('birth_date')->nullable()->comment('Birth date');
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Gender');
                $table->string('occupation', 100)->nullable()->comment('Occupation');
                $table->string('education', 100)->nullable()->comment('Education level');
                $table->json('interests')->nullable()->comment('User interests');
                $table->json('social_links')->nullable()->comment('Social media links');
                $table->boolean('is_public')->default(true)->comment('Whether profile is public');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('display_name');
                $table->index('location');
                $table->index('is_public');
            });

            // User notification settings table
            $this->schema()->create('user_notification_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->boolean('email_notifications')->default(true)->comment('Email notifications enabled');
                $table->boolean('push_notifications')->default(true)->comment('Push notifications enabled');
                $table->boolean('sms_notifications')->default(false)->comment('SMS notifications enabled');
                $table->json('notification_types')->comment('Types of notifications to receive');
                $table->string('email_frequency', 20)->default('daily')->comment('Email frequency');
                $table->time('quiet_hours_start')->nullable()->comment('Quiet hours start time');
                $table->time('quiet_hours_end')->nullable()->comment('Quiet hours end time');
                $table->json('muted_topics')->nullable()->comment('Muted topics');
                $table->json('muted_users')->nullable()->comment('Muted users');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('email_notifications');
                $table->index('push_notifications');
            });

            // User privacy settings table
            $this->schema()->create('user_privacy_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->enum('profile_visibility', ['public', 'friends', 'private'])->default('public')->comment('Profile visibility');
                $table->boolean('show_online_status')->default(true)->comment('Show online status');
                $table->boolean('show_last_seen')->default(true)->comment('Show last seen');
                $table->boolean('show_activity')->default(true)->comment('Show activity');
                $table->boolean('allow_friend_requests')->default(true)->comment('Allow friend requests');
                $table->boolean('allow_messages')->default(true)->comment('Allow direct messages');
                $table->boolean('show_email')->default(false)->comment('Show email address');
                $table->boolean('show_phone')->default(false)->comment('Show phone number');
                $table->json('blocked_users')->nullable()->comment('Blocked users');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('profile_visibility');
            });

            // User customization settings table
            $this->schema()->create('user_customization_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->string('theme', 50)->default('default')->comment('User theme');
                $table->string('language', 10)->default('en')->comment('Preferred language');
                $table->string('timezone', 50)->default('UTC')->comment('User timezone');
                $table->string('date_format', 20)->default('Y-m-d')->comment('Date format preference');
                $table->string('time_format', 10)->default('24')->comment('Time format (12/24)');
                $table->string('currency', 3)->default('USD')->comment('Preferred currency');
                $table->json('dashboard_layout')->nullable()->comment('Dashboard layout preferences');
                $table->json('sidebar_settings')->nullable()->comment('Sidebar settings');
                $table->json('color_scheme')->nullable()->comment('Color scheme preferences');
                $table->json('font_settings')->nullable()->comment('Font preferences');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('theme');
                $table->index('language');
                $table->index('timezone');
            });

            // User learning preferences table
            $this->schema()->create('user_learning_preferences', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->enum('learning_style', ['visual', 'auditory', 'kinesthetic', 'mixed'])->default('mixed')->comment('Learning style');
                $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner')->comment('Preferred difficulty');
                $table->integer('daily_study_time')->default(30)->comment('Daily study time in minutes');
                $table->json('preferred_subjects')->comment('Preferred learning subjects');
                $table->json('preferred_formats')->comment('Preferred content formats');
                $table->boolean('enable_reminders')->default(true)->comment('Enable study reminders');
                $table->json('reminder_schedule')->nullable()->comment('Reminder schedule');
                $table->boolean('enable_progress_tracking')->default(true)->comment('Enable progress tracking');
                $table->boolean('enable_achievements')->default(true)->comment('Enable achievements');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('learning_style');
                $table->index('difficulty_level');
            });

            // User prayer preferences table
            $this->schema()->create('user_prayer_preferences', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->string('calculation_method', 20)->default('MWL')->comment('Prayer calculation method');
                $table->string('asr_juristic', 20)->default('Standard')->comment('Asr juristic method');
                $table->boolean('adjust_high_lats')->default(false)->comment('Adjust for high latitudes');
                $table->integer('fajr_angle')->default(18)->comment('Fajr angle');
                $table->integer('isha_angle')->default(17)->comment('Isha angle');
                $table->integer('maghrib_offset')->default(0)->comment('Maghrib offset in minutes');
                $table->string('timezone', 50)->default('UTC')->comment('Prayer timezone');
                $table->boolean('enable_notifications')->default(true)->comment('Enable prayer notifications');
                $table->integer('notification_advance')->default(5)->comment('Notification advance in minutes');
                $table->json('custom_prayer_times')->nullable()->comment('Custom prayer times');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('calculation_method');
                $table->index('timezone');
            });

            // User content preferences table
            $this->schema()->create('user_content_preferences', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->json('content_filters')->comment('Content filtering preferences');
                $table->json('excluded_topics')->nullable()->comment('Excluded topics');
                $table->json('preferred_sources')->nullable()->comment('Preferred content sources');
                $table->boolean('show_arabic_text')->default(true)->comment('Show Arabic text');
                $table->boolean('show_transliteration')->default(true)->comment('Show transliteration');
                $table->boolean('show_translation')->default(true)->comment('Show translation');
                $table->string('translation_language', 10)->default('en')->comment('Preferred translation language');
                $table->boolean('auto_play_audio')->default(false)->comment('Auto-play audio content');
                $table->boolean('show_related_content')->default(true)->comment('Show related content');
                $table->integer('content_quality_filter')->default(0)->comment('Content quality filter level');
                $table->timestamps();

                $table->unique('user_id');
                $table->index('translation_language');
                $table->index('content_quality_filter');
            });

            // Insert default data
            $this->insertDefaultThemes();
            $this->insertDefaultLanguages();
        }

        /**
         * Reverse the migration.
         */
        public function down(): void
        {
            $this->schema()->drop('user_content_preferences');
            $this->schema()->drop('user_prayer_preferences');
            $this->schema()->drop('user_learning_preferences');
            $this->schema()->drop('user_customization_settings');
            $this->schema()->drop('user_privacy_settings');
            $this->schema()->drop('user_notification_settings');
            $this->schema()->drop('user_profiles');
        }

        /**
         * Insert default themes.
         */
        private function insertDefaultThemes(): void
        {
            $themes = [
                'default', 'dark', 'light', 'sepia', 'high-contrast',
                'bismillah', 'muslim', 'quran', 'islamic'
            ];

            // Note: Themes are just names, actual implementation would be in CSS
            // This is just for reference in the database
        }

        /**
         * Insert default languages.
         */
        private function insertDefaultLanguages(): void
        {
            $languages = [
                'en' => 'English',
                'ar' => 'العربية',
                'ur' => 'اردو',
                'tr' => 'Türkçe',
                'id' => 'Bahasa Indonesia',
                'ms' => 'Bahasa Melayu',
                'bn' => 'বাংলা',
                'fa' => 'فارسی',
                'hi' => 'हिन्दी',
                'sw' => 'Kiswahili'
            ];

            // Note: Languages are predefined, actual implementation would be in localization files
            // This is just for reference in the database
        }
    };
};

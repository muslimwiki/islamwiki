<?php

/**
 * Salah Times Schema Migration
 *
 * This migration creates the comprehensive salah times database schema
 * for the IslamWiki application, including salah time calculations,
 * user locations, notifications, and preferences.
 *
 * @package IslamWiki
 * @version 0.0.16
 * @license AGPL-3.0
 */

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Schema\Builder;

class CreateSalahTimesSchema extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Note: salah_times table already created in Islamic Calendar migration

        // User locations table
        if (!$this->schema()->hasTable('user_locations')) {
        $this->schema()->create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 255);
            $table->string('city', 255);
            $table->string('country', 255);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('timezone', 50);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_default']);
        });
        }

        // Salah time notifications table
        if (!$this->schema()->hasTable('salah_notifications')) {
        $this->schema()->create('salah_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('location_id');
            $table->enum('prayer', ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha']);
            $table->integer('minutes_before')->default(15);
            $table->boolean('is_enabled')->default(true);
            $table->string('notification_type', 50)->default('web');
            $table->string('sound_file', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('user_locations')->onDelete('cascade');
            $table->unique(['user_id', 'location_id', 'prayer']);
        });
        }

        // Salah time preferences table
        if (!$this->schema()->hasTable('salah_preferences')) {
        $this->schema()->create('salah_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('calculation_method', 50)->default('MWL');
            $table->string('asr_juristic', 20)->default('Standard');
            $table->boolean('adjust_high_lats')->default(true);
            $table->integer('minutes_offset')->default(0);
            $table->string('language', 10)->default('en');
            $table->string('time_format', 20)->default('24h');
            $table->boolean('show_sunrise')->default(true);
            $table->boolean('show_dua')->default(true);
            $table->boolean('show_qibla')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });
        }

        // Salah time history table
        if (!$this->schema()->hasTable('salah_history')) {
        $this->schema()->create('salah_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('location_id');
            $table->date('date');
            $table->enum('prayer', ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha']);
            $table->time('scheduled_time');
            $table->time('actual_time')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'missed', 'delayed'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('user_locations')->onDelete('cascade');
            $table->index(['user_id', 'date', 'prayer']);
        });
        }

        // Qibla direction table
        if (!$this->schema()->hasTable('qibla_directions')) {
        $this->schema()->create('qibla_directions', function (Blueprint $table) {
            $table->id();
            $table->string('location_name', 255);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('qibla_direction', 8, 4);
            $table->decimal('qibla_distance', 10, 2);
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
        }

        // Salah time widgets table
        if (!$this->schema()->hasTable('salah_widgets')) {
        $this->schema()->create('salah_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('widget_key', 255)->unique();
            $table->string('name', 255);
            $table->string('location_name', 255);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('timezone', 50);
            $table->string('calculation_method', 50)->default('MWL');
            $table->string('language', 10)->default('en');
            $table->string('time_format', 20)->default('24h');
            $table->boolean('show_sunrise')->default(true);
            $table->boolean('show_dua')->default(true);
            $table->boolean('show_qibla')->default(true);
            $table->string('theme', 50)->default('default');
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index(['widget_key', 'is_active']);
        });
        }

        // Salah time API cache table
        if (!$this->schema()->hasTable('salah_api_cache')) {
        $this->schema()->create('salah_api_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 255)->unique();
            $table->text('response_data');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['cache_key', 'expires_at']);
        });
        }

        // Salah time statistics table
        if (!$this->schema()->hasTable('salah_statistics')) {
        $this->schema()->create('salah_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_requests');
            $table->integer('unique_users');
            $table->integer('cache_hits');
            $table->integer('api_calls');
            $table->decimal('average_response_time', 8, 4);
            $table->timestamps();

            $table->index('date');
        });
        }

        // Salah time errors table
        if (!$this->schema()->hasTable('salah_errors')) {
        $this->schema()->create('salah_errors', function (Blueprint $table) {
            $table->id();
            $table->string('error_type', 100);
            $table->text('error_message');
            $table->text('request_data')->nullable();
            $table->string('location', 255)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['error_type', 'occurred_at']);
        });
        }

        // Salah time integration table
        if (!$this->schema()->hasTable('salah_wiki_links')) {
        $this->schema()->create('salah_wiki_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('location_name', 255);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('widget_type', 50)->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->index(['page_id', 'is_active']);
        });
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('salah_wiki_links');
        $this->schema()->dropIfExists('salah_errors');
        $this->schema()->dropIfExists('salah_statistics');
        $this->schema()->dropIfExists('salah_api_cache');
        $this->schema()->dropIfExists('salah_widgets');
        $this->schema()->dropIfExists('qibla_directions');
        $this->schema()->dropIfExists('salah_history');
        $this->schema()->dropIfExists('salah_preferences');
        $this->schema()->dropIfExists('salah_notifications');
        $this->schema()->dropIfExists('user_locations');
        // Note: prayer_times table handled by Islamic Calendar migration
    }
};

return function ($connection) {
    return new CreateSalahTimesSchema($connection);
};

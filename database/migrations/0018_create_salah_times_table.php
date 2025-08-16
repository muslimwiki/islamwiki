<?php

/**
 * Create Salah Times Table Migration
 *
 * This migration creates the missing salah_times table that the Iqra search system
 * requires. The table structure matches what the SalahTime model expects.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0
 */

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Schema\Builder;

class CreateSalahTimesTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Create the missing salah_times table
        if (!$this->schema()->hasTable('salah_times')) {
            $this->schema()->create('salah_times', function (Blueprint $table) {
                $table->id();
                $table->date('date')->notNull();
                $table->string('location_name', 255)->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('timezone', 50)->default('UTC');
                $table->time('fajr')->nullable();
                $table->time('sunrise')->nullable();
                $table->time('dhuhr')->nullable();
                $table->time('asr')->nullable();
                $table->time('maghrib')->nullable();
                $table->time('isha')->nullable();
                $table->string('calculation_method', 50)->default('MWL');
                $table->string('asr_juristic', 20)->default('Standard');
                $table->boolean('adjust_high_lats')->default(true);
                $table->integer('minutes_offset')->default(0);
                $table->unsignedBigInteger('location_id')->nullable();
                $table->timestamps();

                $table->index('date');
                $table->index('location_name');
                $table->index(['latitude', 'longitude']);
                $table->index('calculation_method');
                $table->index('location_id');
            });
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('salah_times');
    }
};

return function ($connection) {
    return new CreateSalahTimesTable($connection);
}; 
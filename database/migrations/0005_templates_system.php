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
            // Template categories
            $this->schema()->create('template_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('description')->nullable();
                $table->string('icon', 50)->nullable()->comment('Icon class or emoji');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Templates table
            $this->schema()->create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255)->unique()->comment('Template name (e.g., QuranVerse)');
                $table->text('content')->comment('Template HTML content');
                $table->json('parameters')->nullable()->comment('Template parameter definitions');
                $table->string('description')->nullable()->comment('Template description');
                $table->string('category', 100)->nullable()->comment('Template category (e.g., Islamic, General)');
                $table->string('author', 100)->nullable()->comment('Template author');
                $table->boolean('is_active')->default(true)->comment('Whether template is active');
                $table->boolean('is_system')->default(false)->comment('Whether template is a system template');
                $table->integer('usage_count')->default(0)->comment('Number of times template is used');
                $table->timestamp('last_used_at')->nullable()->comment('Last time template was used');
                $table->timestamps();
                
                // Indexes
                $table->index(['category', 'is_active']);
                $table->index(['name', 'is_active']);
                $table->index(['name', 'description']);
            });

            // Template usage tracking
            $this->schema()->create('template_usage', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('template_id');
                $table->unsignedBigInteger('page_id')->nullable();
                $table->string('page_slug')->nullable();
                $table->json('parameters_used')->nullable()->comment('Parameters used in this instance');
                $table->string('user_agent')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
                $table->foreign('page_id')->references('id')->on('pages')->onDelete('set null');
                
                // Indexes
                $table->index(['template_id', 'created_at']);
                $table->index(['page_id', 'created_at']);
            });
        }

        public function down(): void
        {
            $this->schema()->dropIfExists('template_usage');
            $this->schema()->dropIfExists('templates');
            $this->schema()->dropIfExists('template_categories');
        }
    };
}; 
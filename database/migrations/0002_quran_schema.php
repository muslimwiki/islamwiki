<?php
declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Connection;

return function(Connection $connection) {
    return new class($connection) extends Migration
    {
        public function up(): void
        {
            error_log("[Migration] 0002_quran_schema up() called");

            // Surahs (Chapters) table
            $this->schema()->create('surahs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('number')->unique(); // 1-114 (changed from unsignedTinyInteger)
                $table->string('name_arabic', 100); // Arabic name
                $table->string('name_english', 100); // English transliteration
                $table->string('name_translation', 100); // English translation
                $table->string('revelation_type', 20); // Meccan or Medinan
                $table->unsignedInteger('verses_count'); // Number of verses
                $table->unsignedInteger('juz_start')->nullable(); // Juz number
                $table->unsignedInteger('juz_end')->nullable(); // Juz number
                $table->text('description')->nullable(); // Brief description
                $table->timestamps();
                
                $table->index(['number', 'revelation_type']);
            });

            // Verses table
            $this->schema()->create('verses', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('surah_number'); // 1-114 (changed from unsignedTinyInteger)
                $table->unsignedInteger('verse_number'); // Verse number within surah
                $table->text('text_arabic'); // Arabic text
                $table->text('text_uthmani')->nullable(); // Uthmani script
                $table->text('text_indopak')->nullable(); // Indopak script
                $table->unsignedInteger('juz_number')->nullable(); // Juz number
                $table->unsignedInteger('hizb_number')->nullable(); // Hizb number
                $table->unsignedInteger('page_number')->nullable(); // Page number in Mushaf
                $table->unsignedInteger('ruku_number')->nullable(); // Ruku number
                $table->unsignedInteger('sajda_number')->nullable(); // Sajda number (if applicable)
                $table->timestamps();
                
                $table->unique(['surah_number', 'verse_number']);
                $table->index(['juz_number', 'hizb_number']);
                $table->index('page_number');
            });

            // Translations table
            $this->schema()->create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Translation name
                $table->string('language', 10); // Language code (en, ar, ur, etc.)
                $table->string('translator', 100); // Translator name
                $table->text('description')->nullable(); // Description
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_official')->default(false); // Official translation
                $table->boolean('is_active')->default(true); // Active translation
                $table->timestamps();
                
                $table->index(['language', 'is_active']);
            });

            // Verse translations table
            $this->schema()->create('verse_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id');
                $table->unsignedBigInteger('translation_id');
                $table->text('translation_text'); // Translated text
                $table->timestamps();
                
                $table->unique(['verse_id', 'translation_id']);
                $table->index('verse_id');
                $table->index('translation_id');
            });

            // Tajweed rules table
            $this->schema()->create('tajweed_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Rule name
                $table->string('arabic_name', 100); // Arabic name
                $table->text('description'); // Rule description
                $table->string('color_code', 7)->nullable(); // Color code for highlighting
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Verse tajweed table
            $this->schema()->create('verse_tajweed', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id');
                $table->unsignedBigInteger('tajweed_rule_id');
                $table->unsignedInteger('start_position'); // Start position in verse
                $table->unsignedInteger('end_position'); // End position in verse
                $table->timestamps();
                
                $table->index(['verse_id', 'start_position']);
                $table->index('tajweed_rule_id');
            });

            // Recitations table
            $this->schema()->create('recitations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Reciter name
                $table->string('arabic_name', 100); // Arabic name
                $table->string('style', 50)->nullable(); // Recitation style
                $table->text('description')->nullable(); // Description
                $table->string('country', 50)->nullable(); // Country of origin
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Verse recitations table
            $this->schema()->create('verse_recitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id');
                $table->unsignedBigInteger('recitation_id');
                $table->string('audio_url', 500); // Audio file URL
                $table->unsignedInteger('duration')->nullable(); // Duration in seconds
                $table->timestamps();
                
                $table->unique(['verse_id', 'recitation_id']);
                $table->index('verse_id');
                $table->index('recitation_id');
            });

            // Tafsir (Exegesis) table
            $this->schema()->create('tafsir_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Tafsir name
                $table->string('author', 100); // Author name
                $table->string('language', 10); // Language code
                $table->text('description')->nullable(); // Description
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Verse tafsir table
            $this->schema()->create('verse_tafsir', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id');
                $table->unsignedBigInteger('tafsir_source_id');
                $table->text('tafsir_text'); // Tafsir text
                $table->timestamps();
                
                $table->unique(['verse_id', 'tafsir_source_id']);
                $table->index('verse_id');
                $table->index('tafsir_source_id');
            });

            // Quranic topics table
            $this->schema()->create('quranic_topics', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Topic name
                $table->string('arabic_name', 100)->nullable(); // Arabic name
                $table->text('description')->nullable(); // Description
                $table->unsignedBigInteger('parent_id')->nullable(); // Parent topic
                $table->timestamps();
                
                $table->index('parent_id');
            });

            // Verse topics table
            $this->schema()->create('verse_topics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('verse_id');
                $table->unsignedBigInteger('topic_id');
                $table->timestamps();
                
                $table->unique(['verse_id', 'topic_id']);
                $table->index('verse_id');
                $table->index('topic_id');
            });
        }

        public function down(): void
        {
            $this->schema()->dropIfExists('verse_topics');
            $this->schema()->dropIfExists('quranic_topics');
            $this->schema()->dropIfExists('verse_tafsir');
            $this->schema()->dropIfExists('tafsir_sources');
            $this->schema()->dropIfExists('verse_recitations');
            $this->schema()->dropIfExists('recitations');
            $this->schema()->dropIfExists('verse_tajweed');
            $this->schema()->dropIfExists('tajweed_rules');
            $this->schema()->dropIfExists('verse_translations');
            $this->schema()->dropIfExists('translations');
            $this->schema()->dropIfExists('verses');
            $this->schema()->dropIfExists('surahs');
        }
    };
}; 
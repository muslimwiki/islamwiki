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
            error_log("[Migration] 0003_hadith_schema up() called");

            // Hadith collections table
            $this->schema()->create('hadith_collections', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Collection name
                $table->string('arabic_name', 100); // Arabic name
                $table->string('author', 100); // Author/Compiler name
                $table->string('arabic_author', 100); // Arabic author name
                $table->unsignedInteger('year_compiled')->nullable(); // Year compiled
                $table->unsignedInteger('total_hadiths')->nullable(); // Total hadiths
                $table->text('description')->nullable(); // Description
                $table->string('reliability_level', 20); // Sahih, Hasan, Da'if, etc.
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['reliability_level', 'is_active']);
            });

            // Narrators table
            $this->schema()->create('narrators', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Narrator name
                $table->string('arabic_name', 100); // Arabic name
                $table->string('kunyah', 50)->nullable(); // Kunyah (Abu, Ibn, etc.)
                $table->string('laqab', 50)->nullable(); // Laqab (nickname)
                $table->string('nasab', 100)->nullable(); // Nasab (lineage)
                $table->string('birth_place', 100)->nullable(); // Birth place
                $table->string('death_place', 100)->nullable(); // Death place
                $table->unsignedInteger('birth_year')->nullable(); // Birth year
                $table->unsignedInteger('death_year')->nullable(); // Death year
                $table->string('reliability_level', 20); // Thiqah, Saduq, etc.
                $table->text('biography')->nullable(); // Biography
                $table->boolean('is_sahabi')->default(false); // Is companion of Prophet
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['reliability_level', 'is_active']);
                $table->index('is_sahabi');
            });

            // Hadiths table
            $this->schema()->create('hadiths', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('collection_id');
                $table->string('hadith_number', 50); // Hadith number in collection
                $table->text('arabic_text'); // Arabic text
                $table->text('english_text')->nullable(); // English translation
                $table->string('grade', 20); // Sahih, Hasan, Da'if, etc.
                $table->string('category', 50)->nullable(); // Category/topic
                $table->text('explanation')->nullable(); // Explanation/commentary
                $table->unsignedInteger('chapter_number')->nullable(); // Chapter number
                $table->string('chapter_title', 200)->nullable(); // Chapter title
                $table->boolean('is_mutawatir')->default(false); // Is mutawatir (mass transmitted)
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['collection_id', 'hadith_number']);
                $table->index(['grade', 'is_active']);
                $table->index('category');
            });

            // Hadith chains table
            $this->schema()->create('hadith_chains', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('narrator_id');
                $table->unsignedInteger('chain_order'); // Order in the chain (1, 2, 3, etc.)
                $table->string('chain_type', 20)->default('primary'); // Primary, secondary, etc.
                $table->text('notes')->nullable(); // Notes about this narrator in chain
                $table->timestamps();

                $table->unique(['hadith_id', 'narrator_id', 'chain_order']);
                $table->index(['hadith_id', 'chain_order']);
                $table->index('narrator_id');
            });

            // Hadith topics table
            $this->schema()->create('hadith_topics', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Topic name
                $table->string('arabic_name', 100)->nullable(); // Arabic name
                $table->text('description')->nullable(); // Description
                $table->unsignedBigInteger('parent_id')->nullable(); // Parent topic
                $table->timestamps();

                $table->index('parent_id');
            });

            // Hadith topic relationships
            $this->schema()->create('hadith_topic_relations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('topic_id');
                $table->timestamps();

                $table->unique(['hadith_id', 'topic_id']);
                $table->index('hadith_id');
                $table->index('topic_id');
            });

            // Hadith translations table
            $this->schema()->create('hadith_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->string('language', 10); // Language code
                $table->string('translator', 100); // Translator name
                $table->text('translation_text'); // Translated text
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_official')->default(false); // Official translation
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['hadith_id', 'language', 'translator']);
                $table->index(['language', 'is_active']);
            });

            // Hadith commentaries table
            $this->schema()->create('hadith_commentaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->string('commentator', 100); // Commentator name
                $table->string('commentary_name', 200); // Commentary book name
                $table->text('commentary_text'); // Commentary text
                $table->string('language', 10); // Language code
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['hadith_id', 'language']);
                $table->index('commentator');
            });

            // Hadith rulings table
            $this->schema()->create('hadith_rulings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->string('scholar', 100); // Scholar name
                $table->string('ruling', 50); // Sahih, Hasan, Da'if, etc.
                $table->text('explanation')->nullable(); // Explanation of ruling
                $table->string('source', 255)->nullable(); // Source URL
                $table->timestamps();

                $table->unique(['hadith_id', 'scholar']);
                $table->index('ruling');
            });

            // Hadith references table
            $this->schema()->create('hadith_references', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('referenced_hadith_id');
                $table->string('reference_type', 50); // Similar, related, contradicting, etc.
                $table->text('notes')->nullable(); // Notes about the reference
                $table->timestamps();

                $table->unique(['hadith_id', 'referenced_hadith_id']);
                $table->index('reference_type');
            });

            // Hadith keywords table
            $this->schema()->create('hadith_keywords', function (Blueprint $table) {
                $table->id();
                $table->string('keyword', 100); // Keyword
                $table->string('arabic_keyword', 100)->nullable(); // Arabic keyword
                $table->text('description')->nullable(); // Description
                $table->timestamps();

                $table->unique('keyword');
            });

            // Hadith keyword relationships
            $this->schema()->create('hadith_keyword_relations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hadith_id');
                $table->unsignedBigInteger('keyword_id');
                $table->timestamps();

                $table->unique(['hadith_id', 'keyword_id']);
                $table->index('hadith_id');
                $table->index('keyword_id');
            });
        }

        public function down(): void
        {
            $this->schema()->dropIfExists('hadith_keyword_relations');
            $this->schema()->dropIfExists('hadith_keywords');
            $this->schema()->dropIfExists('hadith_references');
            $this->schema()->dropIfExists('hadith_rulings');
            $this->schema()->dropIfExists('hadith_commentaries');
            $this->schema()->dropIfExists('hadith_translations');
            $this->schema()->dropIfExists('hadith_topic_relations');
            $this->schema()->dropIfExists('hadith_topics');
            $this->schema()->dropIfExists('hadith_chains');
            $this->schema()->dropIfExists('hadiths');
            $this->schema()->dropIfExists('narrators');
            $this->schema()->dropIfExists('hadith_collections');
        }
    };
};

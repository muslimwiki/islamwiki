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
            error_log("[Migration] 0004_scholar_schema up() called");

            // Scholars table
            $this->schema()->create('scholars', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Scholar name
                $table->string('arabic_name', 100); // Arabic name
                $table->string('kunyah', 50)->nullable(); // Kunyah (Abu, Ibn, etc.)
                $table->string('laqab', 50)->nullable(); // Laqab (nickname)
                $table->string('nasab', 100)->nullable(); // Nasab (lineage)
                $table->string('birth_place', 100)->nullable(); // Birth place
                $table->string('death_place', 100)->nullable(); // Death place
                $table->unsignedInteger('birth_year')->nullable(); // Birth year
                $table->unsignedInteger('death_year')->nullable(); // Death year
                $table->string('era', 50)->nullable(); // Historical era
                $table->text('biography')->nullable(); // Biography
                $table->string('school_of_thought', 50)->nullable(); // Madhab
                $table->string('specialization', 100)->nullable(); // Specialization
                $table->string('verification_status', 20)->default('pending'); // pending, verified, rejected
                $table->unsignedBigInteger('verified_by')->nullable(); // Who verified this scholar
                $table->timestamp('verified_at')->nullable(); // When verified
                $table->text('verification_notes')->nullable(); // Verification notes
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['verification_status', 'is_active']);
                $table->index('school_of_thought');
                $table->index('specialization');
            });

            // Scholar credentials table
            $this->schema()->create('scholar_credentials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->string('credential_type', 50); // Degree, certification, etc.
                $table->string('institution', 200); // Institution name
                $table->string('arabic_institution', 200)->nullable(); // Arabic institution name
                $table->string('degree', 100)->nullable(); // Degree type
                $table->string('field_of_study', 100)->nullable(); // Field of study
                $table->unsignedInteger('year_obtained')->nullable(); // Year obtained
                $table->text('description')->nullable(); // Description
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_verified')->default(false); // Is credential verified
                $table->timestamps();

                $table->index(['scholar_id', 'credential_type']);
                $table->index('is_verified');
            });

            // Scholar works table
            $this->schema()->create('scholar_works', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->string('title', 200); // Work title
                $table->string('arabic_title', 200)->nullable(); // Arabic title
                $table->string('work_type', 50); // Book, article, fatwa, etc.
                $table->text('description')->nullable(); // Description
                $table->string('language', 10); // Language code
                $table->unsignedInteger('year_published')->nullable(); // Year published
                $table->string('publisher', 200)->nullable(); // Publisher
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_verified')->default(false); // Is work verified
                $table->timestamps();

                $table->index(['scholar_id', 'work_type']);
                $table->index('language');
                $table->index('is_verified');
            });

            // Scholar students table
            $this->schema()->create('scholar_students', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id'); // Teacher
                $table->unsignedBigInteger('student_id'); // Student
                $table->string('relationship_type', 50); // Teacher-student, mentor, etc.
                $table->unsignedInteger('start_year')->nullable(); // When relationship started
                $table->unsignedInteger('end_year')->nullable(); // When relationship ended
                $table->text('notes')->nullable(); // Notes about relationship
                $table->boolean('is_verified')->default(false); // Is relationship verified
                $table->timestamps();

                $table->unique(['scholar_id', 'student_id']);
                $table->index('relationship_type');
                $table->index('is_verified');
            });

            // Scholar teachers table
            $this->schema()->create('scholar_teachers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id'); // Student
                $table->unsignedBigInteger('teacher_id'); // Teacher
                $table->string('relationship_type', 50); // Teacher-student, mentor, etc.
                $table->unsignedInteger('start_year')->nullable(); // When relationship started
                $table->unsignedInteger('end_year')->nullable(); // When relationship ended
                $table->text('notes')->nullable(); // Notes about relationship
                $table->boolean('is_verified')->default(false); // Is relationship verified
                $table->timestamps();

                $table->unique(['scholar_id', 'teacher_id']);
                $table->index('relationship_type');
                $table->index('is_verified');
            });

            // Scholar fatwas table
            $this->schema()->create('scholar_fatwas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->string('title', 200); // Fatwa title
                $table->text('question'); // The question asked
                $table->text('answer'); // The fatwa answer
                $table->string('language', 10); // Language code
                $table->unsignedInteger('year_issued')->nullable(); // Year issued
                $table->string('source', 255)->nullable(); // Source URL
                $table->string('verification_status', 20)->default('pending'); // pending, verified, rejected
                $table->unsignedBigInteger('verified_by')->nullable(); // Who verified this fatwa
                $table->timestamp('verified_at')->nullable(); // When verified
                $table->text('verification_notes')->nullable(); // Verification notes
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['scholar_id', 'verification_status']);
                $table->index('language');
                $table->index('is_active');
            });

            // Scholar endorsements table
            $this->schema()->create('scholar_endorsements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('endorser_id'); // Scholar giving endorsement
                $table->unsignedBigInteger('endorsed_id'); // Scholar being endorsed
                $table->text('endorsement_text'); // Endorsement text
                $table->string('endorsement_type', 50); // Type of endorsement
                $table->unsignedInteger('year_issued')->nullable(); // Year issued
                $table->string('source', 255)->nullable(); // Source URL
                $table->boolean('is_verified')->default(false); // Is endorsement verified
                $table->timestamps();

                $table->unique(['endorser_id', 'endorsed_id']);
                $table->index('endorsement_type');
                $table->index('is_verified');
            });

            // Scholar verification requests table
            $this->schema()->create('scholar_verification_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->unsignedBigInteger('requested_by'); // User requesting verification
                $table->string('request_type', 50); // Type of verification request
                $table->text('request_details'); // Details of the request
                $table->string('status', 20)->default('pending'); // pending, approved, rejected
                $table->unsignedBigInteger('reviewed_by')->nullable(); // Who reviewed the request
                $table->timestamp('reviewed_at')->nullable(); // When reviewed
                $table->text('review_notes')->nullable(); // Review notes
                $table->timestamps();

                $table->index(['scholar_id', 'status']);
                $table->index('requested_by');
                $table->index('reviewed_by');
            });

            // Scholar categories table
            $this->schema()->create('scholar_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Category name
                $table->string('arabic_name', 100)->nullable(); // Arabic name
                $table->text('description')->nullable(); // Description
                $table->unsignedBigInteger('parent_id')->nullable(); // Parent category
                $table->timestamps();

                $table->index('parent_id');
            });

            // Scholar category relationships
            $this->schema()->create('scholar_category_relations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();

                $table->unique(['scholar_id', 'category_id']);
                $table->index('scholar_id');
                $table->index('category_id');
            });

            // Scholar keywords table
            $this->schema()->create('scholar_keywords', function (Blueprint $table) {
                $table->id();
                $table->string('keyword', 100); // Keyword
                $table->string('arabic_keyword', 100)->nullable(); // Arabic keyword
                $table->text('description')->nullable(); // Description
                $table->timestamps();

                $table->unique('keyword');
            });

            // Scholar keyword relationships
            $this->schema()->create('scholar_keyword_relations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scholar_id');
                $table->unsignedBigInteger('keyword_id');
                $table->timestamps();

                $table->unique(['scholar_id', 'keyword_id']);
                $table->index('scholar_id');
                $table->index('keyword_id');
            });
        }

        public function down(): void
        {
            $this->schema()->dropIfExists('scholar_keyword_relations');
            $this->schema()->dropIfExists('scholar_keywords');
            $this->schema()->dropIfExists('scholar_category_relations');
            $this->schema()->dropIfExists('scholar_categories');
            $this->schema()->dropIfExists('scholar_verification_requests');
            $this->schema()->dropIfExists('scholar_endorsements');
            $this->schema()->dropIfExists('scholar_fatwas');
            $this->schema()->dropIfExists('scholar_teachers');
            $this->schema()->dropIfExists('scholar_students');
            $this->schema()->dropIfExists('scholar_works');
            $this->schema()->dropIfExists('scholar_credentials');
            $this->schema()->dropIfExists('scholars');
        }
    };
};

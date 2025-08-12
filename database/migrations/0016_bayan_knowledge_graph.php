<?php

/**
 * Migration: Bayan Knowledge Graph
 *
 * This migration creates the database schema for the Bayan knowledge graph
 * system, including entities, relationships, knowledge nodes, and semantic
 * connections for Islamic knowledge representation.
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
            // Knowledge entities table
            $this->schema()->create('knowledge_entities', function ($table) {
                $table->id();
                $table->string('name', 255)->comment('Entity name');
                $table->string('type', 100)->comment('Entity type');
                $table->text('description')->nullable()->comment('Entity description');
                $table->json('attributes')->nullable()->comment('Entity attributes');
                $table->string('source', 255)->nullable()->comment('Source reference');
                $table->string('language', 10)->default('en')->comment('Entity language');
                $table->boolean('is_verified')->default(false)->comment('Whether entity is verified');
                $table->integer('confidence_score')->default(0)->comment('Confidence score (0-100)');
                $table->json('metadata')->nullable()->comment('Additional metadata');
                $table->timestamps();

                $table->index('name');
                $table->index('type');
                $table->index('language');
                $table->index('is_verified');
                $table->index('confidence_score');
            });

            // Knowledge relationships table
            $this->schema()->create('knowledge_relationships', function ($table) {
                $table->id();
                $table->unsignedBigInteger('source_entity_id')->comment('Source entity ID');
                $table->unsignedBigInteger('target_entity_id')->comment('Target entity ID');
                $table->string('relationship_type', 100)->comment('Type of relationship');
                $table->text('description')->nullable()->comment('Relationship description');
                $table->json('attributes')->nullable()->comment('Relationship attributes');
                $table->float('strength')->default(1.0)->comment('Relationship strength');
                $table->string('source', 255)->nullable()->comment('Source reference');
                $table->boolean('is_bidirectional')->default(false)->comment('Whether relationship is bidirectional');
                $table->boolean('is_verified')->default(false)->comment('Whether relationship is verified');
                $table->timestamps();

                $table->unique(['source_entity_id', 'target_entity_id', 'relationship_type']);
                $table->index('source_entity_id');
                $table->index('target_entity_id');
                $table->index('relationship_type');
                $table->index('strength');
                $table->index('is_verified');
            });

            // Knowledge nodes table
            $this->schema()->create('knowledge_nodes', function ($table) {
                $table->id();
                $table->string('node_id', 100)->unique()->comment('Unique node identifier');
                $table->string('title', 255)->comment('Node title');
                $table->text('content')->comment('Node content');
                $table->string('category', 100)->comment('Node category');
                $table->json('tags')->nullable()->comment('Node tags');
                $table->string('author', 100)->nullable()->comment('Node author');
                $table->string('source', 255)->nullable()->comment('Source reference');
                $table->string('language', 10)->default('en')->comment('Node language');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->comment('Node status');
                $table->integer('view_count')->default(0)->comment('Number of views');
                $table->integer('like_count')->default(0)->comment('Number of likes');
                $table->integer('share_count')->default(0)->comment('Number of shares');
                $table->json('metadata')->nullable()->comment('Additional metadata');
                $table->timestamps();

                $table->index('node_id');
                $table->index('title');
                $table->index('category');
                $table->index('status');
                $table->index('language');
                $table->index('view_count');
            });

            // Semantic connections table
            $this->schema()->create('semantic_connections', function ($table) {
                $table->id();
                $table->unsignedBigInteger('source_node_id')->comment('Source node ID');
                $table->unsignedBigInteger('target_node_id')->comment('Target node ID');
                $table->string('connection_type', 100)->comment('Type of semantic connection');
                $table->text('description')->nullable()->comment('Connection description');
                $table->float('similarity_score')->default(0.0)->comment('Semantic similarity score');
                $table->json('semantic_features')->nullable()->comment('Semantic features');
                $table->string('algorithm', 100)->nullable()->comment('Algorithm used for connection');
                $table->boolean('is_automatic')->default(true)->comment('Whether connection was automatic');
                $table->boolean('is_verified')->default(false)->comment('Whether connection is verified');
                $table->timestamps();

                $table->unique(['source_node_id', 'target_node_id', 'connection_type']);
                $table->index('source_node_id');
                $table->index('target_node_id');
                $table->index('connection_type');
                $table->index('similarity_score');
                $table->index('is_automatic');
            });

            // Knowledge categories table
            $this->schema()->create('knowledge_categories', function ($table) {
                $table->id();
                $table->string('name', 100)->comment('Category name');
                $table->string('slug', 100)->unique()->comment('Category slug');
                $table->text('description')->nullable()->comment('Category description');
                $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent category ID');
                $table->integer('sort_order')->default(0)->comment('Sort order');
                $table->string('icon', 50)->nullable()->comment('Category icon');
                $table->string('color', 7)->nullable()->comment('Category color (hex)');
                $table->boolean('is_active')->default(true)->comment('Whether category is active');
                $table->timestamps();

                $table->index('parent_id');
                $table->index('slug');
                $table->index('is_active');
                $table->index('sort_order');
            });

            // Knowledge tags table
            $this->schema()->create('knowledge_tags', function ($table) {
                $table->id();
                $table->string('name', 100)->comment('Tag name');
                $table->string('slug', 100)->unique()->comment('Tag slug');
                $table->text('description')->nullable()->comment('Tag description');
                $table->string('color', 7)->nullable()->comment('Tag color (hex)');
                $table->integer('usage_count')->default(0)->comment('Number of times tag is used');
                $table->boolean('is_active')->default(true)->comment('Whether tag is active');
                $table->timestamps();

                $table->index('slug');
                $table->index('is_active');
                $table->index('usage_count');
            });

            // Knowledge node tag assignments table
            $this->schema()->create('knowledge_node_tags', function ($table) {
                $table->id();
                $table->unsignedBigInteger('node_id')->comment('Knowledge node ID');
                $table->unsignedBigInteger('tag_id')->comment('Tag ID');
                $table->timestamps();

                $table->unique(['node_id', 'tag_id']);
                $table->index('node_id');
                $table->index('tag_id');
            });

            // Knowledge search index table
            $this->schema()->create('knowledge_search_index', function ($table) {
                $table->id();
                $table->unsignedBigInteger('node_id')->comment('Knowledge node ID');
                $table->text('searchable_content')->comment('Searchable text content');
                $table->json('keywords')->nullable()->comment('Extracted keywords');
                $table->json('semantic_vectors')->nullable()->comment('Semantic vector representation');
                $table->float('relevance_score')->default(0.0)->comment('Relevance score');
                $table->timestamp('last_indexed_at')->comment('When last indexed');
                $table->timestamps();

                $table->unique('node_id');
                $table->index('searchable_content');
                $table->index('relevance_score');
                $table->index('last_indexed_at');
            });

            // Insert default data
            $this->insertDefaultCategories();
            $this->insertDefaultTags();
        }

        /**
         * Reverse the migration.
         */
        public function down(): void
        {
            $this->schema()->drop('knowledge_search_index');
            $this->schema()->drop('knowledge_node_tags');
            $this->schema()->drop('knowledge_tags');
            $this->schema()->drop('knowledge_categories');
            $this->schema()->drop('semantic_connections');
            $this->schema()->drop('knowledge_nodes');
            $this->schema()->drop('knowledge_relationships');
            $this->schema()->drop('knowledge_entities');
        }

        /**
         * Insert default knowledge categories.
         */
        private function insertDefaultCategories(): void
        {
            $categories = [
                ['name' => 'Quranic Studies', 'slug' => 'quranic-studies', 'description' => 'Quran recitation, interpretation, and analysis'],
                ['name' => 'Hadith Sciences', 'slug' => 'hadith-sciences', 'description' => 'Hadith collection, authentication, and study'],
                ['name' => 'Islamic Law', 'slug' => 'islamic-law', 'description' => 'Fiqh, legal rulings, and jurisprudence'],
                ['name' => 'Islamic Theology', 'slug' => 'islamic-theology', 'description' => 'Aqeedah, beliefs, and theological concepts'],
                ['name' => 'Islamic History', 'slug' => 'islamic-history', 'description' => 'Historical events, figures, and periods'],
                ['name' => 'Islamic Philosophy', 'slug' => 'islamic-philosophy', 'description' => 'Philosophical thought and reasoning'],
                ['name' => 'Islamic Ethics', 'slug' => 'islamic-ethics', 'description' => 'Moral teachings and character development'],
                ['name' => 'Islamic Spirituality', 'slug' => 'islamic-spirituality', 'description' => 'Sufism and spiritual practices'],
                ['name' => 'Islamic Art & Architecture', 'slug' => 'islamic-art', 'description' => 'Artistic traditions and architectural styles'],
                ['name' => 'Islamic Science', 'slug' => 'islamic-science', 'description' => 'Scientific contributions and discoveries']
            ];

            foreach ($categories as $category) {
                $this->connection->table('knowledge_categories')->insert([
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        /**
         * Insert default knowledge tags.
         */
        private function insertDefaultTags(): void
        {
            $tags = [
                ['name' => 'Beginner', 'slug' => 'beginner', 'description' => 'Suitable for beginners', 'color' => '#28a745'],
                ['name' => 'Intermediate', 'slug' => 'intermediate', 'description' => 'Suitable for intermediate learners', 'color' => '#ffc107'],
                ['name' => 'Advanced', 'slug' => 'advanced', 'description' => 'Suitable for advanced learners', 'color' => '#dc3545'],
                ['name' => 'Authentic', 'slug' => 'authentic', 'description' => 'Authenticated and verified content', 'color' => '#20c997'],
                ['name' => 'Scholarly', 'slug' => 'scholarly', 'description' => 'Academic and scholarly content', 'color' => '#6c757d'],
                ['name' => 'Practical', 'slug' => 'practical', 'description' => 'Practical application and guidance', 'color' => '#e83e8c'],
                ['name' => 'Theoretical', 'slug' => 'theoretical', 'description' => 'Theoretical knowledge and concepts', 'color' => '#495057'],
                ['name' => 'Historical', 'slug' => 'historical', 'description' => 'Historical context and background', 'color' => '#17a2b8'],
                ['name' => 'Contemporary', 'slug' => 'contemporary', 'description' => 'Modern and contemporary issues', 'color' => '#fd7e14'],
                ['name' => 'Spiritual', 'slug' => 'spiritual', 'description' => 'Spiritual and mystical content', 'color' => '#6f42c1']
            ];

            foreach ($tags as $tag) {
                $this->connection->table('knowledge_tags')->insert([
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'description' => $tag['description'],
                    'color' => $tag['color'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    };
};

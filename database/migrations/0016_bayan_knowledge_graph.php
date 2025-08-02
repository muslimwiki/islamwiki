<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR ANY PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

/**
 * Bayan Knowledge Graph Migration
 * 
 * Creates the database tables for the Bayan knowledge graph system
 * that connects Islamic concepts, verses, hadith, scholars, and other entities.
 */
return [
    'version' => '0.0.34',
    'description' => 'Create Bayan knowledge graph system tables',
    'up' => function ($connection) {
        // Create bayan_nodes table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_nodes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL COMMENT 'Node type: concept, verse, hadith, scholar, etc.',
                title VARCHAR(255) NOT NULL COMMENT 'Node title/name',
                content TEXT NOT NULL COMMENT 'Node content/description',
                metadata JSON DEFAULT '{}' COMMENT 'Additional metadata as JSON',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                INDEX idx_type (type),
                INDEX idx_title (title),
                INDEX idx_created_at (created_at),
                INDEX idx_deleted_at (deleted_at),
                FULLTEXT idx_content (title, content)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Knowledge graph nodes representing Islamic concepts, verses, hadith, scholars, etc.'
        ");

        // Create bayan_edges table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_edges (
                id INT AUTO_INCREMENT PRIMARY KEY,
                source_id INT NOT NULL COMMENT 'Source node ID',
                target_id INT NOT NULL COMMENT 'Target node ID',
                type VARCHAR(50) NOT NULL COMMENT 'Relationship type: references, explains, authored_by, etc.',
                attributes JSON DEFAULT '{}' COMMENT 'Relationship attributes as JSON',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (source_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
                FOREIGN KEY (target_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
                INDEX idx_source_id (source_id),
                INDEX idx_target_id (target_id),
                INDEX idx_type (type),
                INDEX idx_created_at (created_at),
                INDEX idx_deleted_at (deleted_at),
                UNIQUE KEY unique_relationship (source_id, target_id, type, deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Knowledge graph relationships between nodes'
        ");

        // Create bayan_node_types table for predefined node types
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_node_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL UNIQUE COMMENT 'Node type identifier',
                name VARCHAR(100) NOT NULL COMMENT 'Human-readable name',
                description TEXT COMMENT 'Type description',
                icon VARCHAR(50) COMMENT 'Icon identifier',
                color VARCHAR(7) COMMENT 'Hex color code',
                is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether this type is active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Predefined node types for the knowledge graph'
        ");

        // Create bayan_edge_types table for predefined relationship types
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_edge_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL UNIQUE COMMENT 'Relationship type identifier',
                name VARCHAR(100) NOT NULL COMMENT 'Human-readable name',
                description TEXT COMMENT 'Relationship description',
                is_directed BOOLEAN DEFAULT TRUE COMMENT 'Whether this relationship is directed',
                is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether this type is active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Predefined relationship types for the knowledge graph'
        ");

        // Insert default node types
        $connection->exec("
            INSERT INTO bayan_node_types (type, name, description, icon, color) VALUES
            ('concept', 'Islamic Concept', 'General Islamic concepts and terms', 'lightbulb', '#4CAF50'),
            ('verse', 'Quran Verse', 'Verses from the Holy Quran', 'book-open', '#2196F3'),
            ('hadith', 'Hadith', 'Sayings and actions of Prophet Muhammad (PBUH)', 'quote-right', '#FF9800'),
            ('scholar', 'Scholar', 'Islamic scholars and authorities', 'user-graduate', '#9C27B0'),
            ('school', 'School of Thought', 'Islamic schools and madhabs', 'building', '#795548'),
            ('event', 'Historical Event', 'Important events in Islamic history', 'calendar-alt', '#F44336'),
            ('place', 'Place', 'Important places in Islamic history', 'map-marker-alt', '#607D8B'),
            ('person', 'Person', 'Important figures in Islamic history', 'user', '#E91E63'),
            ('book', 'Book', 'Islamic books and texts', 'book', '#3F51B5'),
            ('topic', 'Topic', 'General topics and subjects', 'folder', '#009688')
            ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description),
            icon = VALUES(icon),
            color = VALUES(color)
        ");

        // Insert default relationship types
        $connection->exec("
            INSERT INTO bayan_edge_types (type, name, description, is_directed) VALUES
            ('references', 'References', 'One concept references another', TRUE),
            ('explains', 'Explains', 'One concept explains another', TRUE),
            ('authored_by', 'Authored By', 'Content authored by a scholar', TRUE),
            ('belongs_to', 'Belongs To', 'Concept belongs to a category', TRUE),
            ('related_to', 'Related To', 'General relationship between concepts', FALSE),
            ('mentions', 'Mentions', 'One concept mentions another', TRUE),
            ('derived_from', 'Derived From', 'Concept derived from another', TRUE),
            ('similar_to', 'Similar To', 'Similar concepts', FALSE),
            ('opposes', 'Opposes', 'Opposing concepts', TRUE),
            ('supports', 'Supports', 'Supporting evidence or concept', TRUE)
            ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description),
            is_directed = VALUES(is_directed)
        ");

        // Create bayan_graph_metrics table for caching metrics
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_graph_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(100) NOT NULL COMMENT 'Metric identifier',
                metric_value JSON NOT NULL COMMENT 'Metric value as JSON',
                calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_metric_name (metric_name),
                INDEX idx_calculated_at (calculated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Cached graph metrics and statistics'
        ");

        // Create bayan_search_index table for full-text search
        $connection->exec("
            CREATE TABLE IF NOT EXISTS bayan_search_index (
                id INT AUTO_INCREMENT PRIMARY KEY,
                node_id INT NOT NULL COMMENT 'Reference to bayan_nodes.id',
                search_text TEXT NOT NULL COMMENT 'Searchable text content',
                search_vector TEXT COMMENT 'Full-text search vector',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (node_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
                INDEX idx_node_id (node_id),
                FULLTEXT idx_search_text (search_text)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Search index for knowledge graph nodes'
        ");

        echo "Bayan knowledge graph tables created successfully.\n";
    },
    'down' => function ($connection) {
        // Drop tables in reverse order
        $connection->exec("DROP TABLE IF EXISTS bayan_search_index");
        $connection->exec("DROP TABLE IF EXISTS bayan_graph_metrics");
        $connection->exec("DROP TABLE IF EXISTS bayan_edge_types");
        $connection->exec("DROP TABLE IF EXISTS bayan_node_types");
        $connection->exec("DROP TABLE IF EXISTS bayan_edges");
        $connection->exec("DROP TABLE IF EXISTS bayan_nodes");

        echo "Bayan knowledge graph tables dropped successfully.\n";
    }
]; 
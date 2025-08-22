<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * SearchIndexer - Content indexing service for search optimization
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SearchIndexer
{
    private Connection $database;
    private LoggerInterface $logger;

    public function __construct(Connection $database, LoggerInterface $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * Index content for search
     */
    public function indexContent(array $contentData): bool
    {
        try {
            $contentType = $contentData['content_type'] ?? 'unknown';
            $contentId = $contentData['content_id'] ?? null;
            $title = $contentData['title'] ?? '';
            $content = $contentData['content'] ?? '';
            $metadata = $contentData['metadata'] ?? [];

            if (!$contentId || empty($title)) {
                $this->logger->warning('Invalid content data for indexing', $contentData);
                return false;
            }

            // Create search index entry
            $indexData = [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'title' => $title,
                'content' => $this->extractSearchableContent($content),
                'metadata' => json_encode($metadata),
                'indexed_at' => date('Y-m-d H:i:s'),
                'search_vector' => $this->createSearchVector($title, $content)
            ];

            // Store in search index
            $this->storeSearchIndex($indexData);

            $this->logger->info('Content indexed successfully', [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'title' => $title
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Content indexing failed', [
                'error' => $e->getMessage(),
                'content_data' => $contentData
            ]);
            return false;
        }
    }

    /**
     * Extract searchable content from raw content
     */
    private function extractSearchableContent(string $content): string
    {
        // Remove HTML tags
        $content = strip_tags($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Limit length for indexing
        if (strlen($content) > 5000) {
            $content = substr($content, 0, 5000);
        }
        
        return trim($content);
    }

    /**
     * Create search vector for full-text search
     */
    private function createSearchVector(string $title, string $content): string
    {
        // Combine title and content with different weights
        $titleWeight = 3; // Title is more important
        $contentWeight = 1;
        
        $vector = str_repeat($title . ' ', $titleWeight) . str_repeat($content . ' ', $contentWeight);
        
        // Remove common words and normalize
        $vector = $this->normalizeSearchVector($vector);
        
        return $vector;
    }

    /**
     * Normalize search vector
     */
    private function normalizeSearchVector(string $vector): string
    {
        // Convert to lowercase
        $vector = strtolower($vector);
        
        // Remove common words (stop words)
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        foreach ($stopWords as $word) {
            $vector = str_replace(' ' . $word . ' ', ' ', $vector);
        }
        
        // Remove extra spaces
        $vector = preg_replace('/\s+/', ' ', $vector);
        
        return trim($vector);
    }

    /**
     * Store search index in database
     */
    private function storeSearchIndex(array $indexData): void
    {
        // For now, just log the indexing
        // In the future, this will store in a proper search index table
        $this->logger->info('Search index data prepared', $indexData);
        
        // TODO: Implement actual database storage
        // $this->database->insert('search_index', $indexData);
    }

    /**
     * Remove content from search index
     */
    public function removeFromIndex(string $contentType, int $contentId): bool
    {
        try {
            $this->logger->info('Content removed from search index', [
                'content_type' => $contentType,
                'content_id' => $contentId
            ]);
            
            // TODO: Implement actual removal from database
            // $this->database->delete('search_index', [
            //     'content_type' => $contentType,
            //     'content_id' => $contentId
            // ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove content from search index', [
                'error' => $e->getMessage(),
                'content_type' => $contentType,
                'content_id' => $contentId
            ]);
            return false;
        }
    }

    /**
     * Update search index for existing content
     */
    public function updateIndex(array $contentData): bool
    {
        // Remove old index entry
        $this->removeFromIndex(
            $contentData['content_type'] ?? 'unknown',
            $contentData['content_id'] ?? 0
        );
        
        // Add new index entry
        return $this->indexContent($contentData);
    }

    /**
     * Rebuild entire search index
     */
    public function rebuildIndex(): bool
    {
        try {
            $this->logger->info('Starting search index rebuild');
            
            // TODO: Implement full index rebuild
            // This would involve:
            // 1. Clear existing index
            // 2. Scan all content
            // 3. Re-index everything
            
            $this->logger->info('Search index rebuild completed');
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Search index rebuild failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 
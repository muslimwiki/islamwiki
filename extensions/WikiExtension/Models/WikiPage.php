<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;

/**
 * WikiPage Model - Wiki page data operations
 * 
 * @package IslamWiki\Extensions\WikiExtension\Models
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiPage
{
    private Connection $db;
    private string $table = 'wiki_pages';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get page by slug
     */
    public function getBySlug(string $slug): ?array
    {
        try {
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wp.slug = ? AND wp.deleted_at IS NULL";
            
            $result = $this->db->query($query, [$slug])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting wiki page by slug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get page by ID
     */
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wp.id = ? AND wp.deleted_at IS NULL";
            
            $result = $this->db->query($query, [$id])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting wiki page by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get featured pages
     */
    public function getFeaturedPages(int $limit = 6): array
    {
        try {
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wp.is_featured = 1 AND wp.deleted_at IS NULL
                      ORDER BY wp.featured_order ASC, wp.created_at DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting featured wiki pages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent pages
     */
    public function getRecentPages(int $limit = 10): array
    {
        try {
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wp.deleted_at IS NULL
                      ORDER BY wp.updated_at DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent wiki pages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pages by category
     */
    public function getPagesByCategory(string $categorySlug, int $limit = 20): array
    {
        try {
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wc.slug = ? AND wp.deleted_at IS NULL
                      ORDER BY wp.title ASC
                      LIMIT ?";
            
            return $this->db->query($query, [$categorySlug, $limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting wiki pages by category: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get page count by category
     */
    public function getPageCountByCategory(int $categoryId): int
    {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE category_id = ? AND deleted_at IS NULL";
            
            $result = $this->db->query($query, [$categoryId])->fetch();
            
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting page count by category: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Search pages
     */
    public function search(string $searchTerm, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        try {
            $whereConditions = ["wp.deleted_at IS NULL"];
            $params = [];
            
            // Search term
            if (!empty($searchTerm)) {
                $whereConditions[] = "(wp.title LIKE ? OR wp.content LIKE ? OR wp.meta_description LIKE ?)";
                $searchPattern = '%' . $searchTerm . '%';
                $params[] = $searchPattern;
                $params[] = $searchPattern;
                $params[] = $searchPattern;
            }
            
            // Category filter
            if (!empty($filters['category'])) {
                $whereConditions[] = "wc.slug = ?";
                $params[] = $filters['category'];
            }
            
            // Type filter
            if (!empty($filters['type'])) {
                $whereConditions[] = "wp.content_type = ?";
                $params[] = $filters['type'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            // Count total results
            $countQuery = "SELECT COUNT(*) as total FROM {$this->table} wp
                          LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                          {$whereClause}";
            
            $total = $this->db->query($countQuery, $params)->fetch()['total'] ?? 0;
            
            // Get results
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      {$whereClause}
                      ORDER BY wp.updated_at DESC
                      LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $results = $this->db->query($query, $params)->fetchAll();
            
            return [
                'results' => $results,
                'total' => $total
            ];
        } catch (Exception $e) {
            error_log("Error searching wiki pages: " . $e->getMessage());
            return ['results' => [], 'total' => 0];
        }
    }

    /**
     * Create new page
     */
    public function create(array $data): ?int
    {
        try {
            $query = "INSERT INTO {$this->table} 
                      (title, slug, content, meta_description, category_id, tags, content_type, 
                       user_id, is_featured, featured_order, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $data['title'],
                $data['slug'],
                $data['content'],
                $data['meta_description'] ?? '',
                $data['category_id'] ?? null,
                $data['tags'] ?? '',
                $data['content_type'] ?? 'page',
                $data['user_id'],
                $data['is_featured'] ?? 0,
                $data['featured_order'] ?? 0
            ];
            
            $this->db->query($query, $params);
            
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating wiki page: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update page
     */
    public function update(int $id, array $data): bool
    {
        try {
            $setFields = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, ['title', 'content', 'meta_description', 'category_id', 'tags', 
                                     'content_type', 'is_featured', 'featured_order', 'current_revision_id'])) {
                    $setFields[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($setFields)) {
                return false;
            }
            
            $setFields[] = "updated_at = NOW()";
            $params[] = $id;
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $setFields) . " WHERE id = ?";
            
            $this->db->query($query, $params);
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating wiki page: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete page (soft delete)
     */
    public function delete(int $id): bool
    {
        try {
            $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
            
            $this->db->query($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error deleting wiki page: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lock/unlock page
     */
    public function setLocked(int $id, bool $locked): bool
    {
        try {
            $query = "UPDATE {$this->table} SET is_locked = ?, updated_at = NOW() WHERE id = ?";
            
            $this->db->query($query, [$locked ? 1 : 0, $id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error setting page lock status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(int $id): bool
    {
        try {
            $query = "UPDATE {$this->table} SET view_count = view_count + 1, updated_at = NOW() WHERE id = ?";
            
            $this->db->query($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error incrementing page view count: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get wiki statistics
     */
    public function getWikiStats(): array
    {
        try {
            $stats = [];
            
            // Total pages
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['total_pages'] = (int)($result['count'] ?? 0);
            
            // Featured pages
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_featured = 1 AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['featured_pages'] = (int)($result['count'] ?? 0);
            
            // Recent activity
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['recent_activity'] = (int)($result['count'] ?? 0);
            
            // Total views
            $query = "SELECT SUM(view_count) as total FROM {$this->table} WHERE deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['total_views'] = (int)($result['total'] ?? 0);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting wiki statistics: " . $e->getMessage());
            return [
                'total_pages' => 0,
                'featured_pages' => 0,
                'recent_activity' => 0,
                'total_views' => 0
            ];
        }
    }

    /**
     * Get related pages
     */
    public function getRelatedPages(int $pageId, int $limit = 5): array
    {
        try {
            $page = $this->getById($pageId);
            if (!$page) {
                return [];
            }
            
            $query = "SELECT wp.*, wc.name as category_name, wc.slug as category_slug 
                      FROM {$this->table} wp
                      LEFT JOIN wiki_categories wc ON wp.category_id = wc.id
                      WHERE wp.id != ? AND wp.deleted_at IS NULL
                      AND (wp.category_id = ? OR wp.tags LIKE ?)
                      ORDER BY 
                        CASE WHEN wp.category_id = ? THEN 1 ELSE 2 END,
                        wp.updated_at DESC
                      LIMIT ?";
            
            $categoryId = $page['category_id'];
            $tags = $page['tags'];
            $tagPattern = '%' . $tags . '%';
            
            return $this->db->query($query, [$pageId, $categoryId, $tagPattern, $categoryId, $limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting related pages: " . $e->getMessage());
            return [];
        }
    }
} 
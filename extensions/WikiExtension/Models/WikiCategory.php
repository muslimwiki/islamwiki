<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;

/**
 * WikiCategory Model - Wiki category data operations
 * 
 * @package IslamWiki\Extensions\WikiExtension\Models
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiCategory
{
    private Connection $db;
    private string $table = 'wiki_categories';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get all categories
     */
    public function getAll(): array
    {
        try {
            $query = "SELECT c.*, 
                             COUNT(p.id) as page_count,
                             c2.name as parent_name,
                             c2.slug as parent_slug
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      LEFT JOIN {$this->table} c2 ON c.parent_id = c2.id
                      WHERE c.deleted_at IS NULL
                      GROUP BY c.id
                      ORDER BY c.name ASC";
            
            return $this->db->query($query)->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting all wiki categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get category by slug
     */
    public function getBySlug(string $slug): ?array
    {
        try {
            $query = "SELECT c.*, 
                             COUNT(p.id) as page_count,
                             c2.name as parent_name,
                             c2.slug as parent_slug
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      LEFT JOIN {$this->table} c2 ON c.parent_id = c2.id
                      WHERE c.slug = ? AND c.deleted_at IS NULL
                      GROUP BY c.id";
            
            $result = $this->db->query($query, [$slug])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting wiki category by slug: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get category by ID
     */
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT c.*, 
                             COUNT(p.id) as page_count,
                             c2.name as parent_name,
                             c2.slug as parent_slug
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      LEFT JOIN {$this->table} c2 ON c.parent_id = c2.id
                      WHERE c.id = ? AND c.deleted_at IS NULL
                      GROUP BY c.id";
            
            $result = $this->db->query($query, [$id])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting wiki category by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get subcategories
     */
    public function getSubcategories(int $parentId): array
    {
        try {
            $query = "SELECT c.*, COUNT(p.id) as page_count
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.parent_id = ? AND c.deleted_at IS NULL
                      GROUP BY c.id
                      ORDER BY c.name ASC";
            
            return $this->db->query($query, [$parentId])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting subcategories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get parent categories
     */
    public function getParentCategories(): array
    {
        try {
            $query = "SELECT c.*, COUNT(p.id) as page_count
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.parent_id IS NULL AND c.deleted_at IS NULL
                      GROUP BY c.id
                      ORDER BY c.name ASC";
            
            return $this->db->query($query)->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting parent categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get category tree
     */
    public function getCategoryTree(): array
    {
        try {
            $categories = $this->getAll();
            $tree = [];
            
            foreach ($categories as $category) {
                if ($category['parent_id'] === null) {
                    $tree[] = $this->buildCategoryNode($category, $categories);
                }
            }
            
            return $tree;
        } catch (Exception $e) {
            error_log("Error building category tree: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular categories
     */
    public function getPopularCategories(int $limit = 10): array
    {
        try {
            $query = "SELECT c.*, COUNT(p.id) as page_count
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.deleted_at IS NULL
                      GROUP BY c.id
                      HAVING page_count > 0
                      ORDER BY page_count DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting popular categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search categories
     */
    public function search(string $searchTerm, int $limit = 20): array
    {
        try {
            $query = "SELECT c.*, COUNT(p.id) as page_count
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.deleted_at IS NULL 
                      AND (c.name LIKE ? OR c.description LIKE ?)
                      GROUP BY c.id
                      ORDER BY 
                        CASE WHEN c.name LIKE ? THEN 1 ELSE 2 END,
                        c.name ASC
                      LIMIT ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $exactPattern = $searchTerm . '%';
            
            return $this->db->query($query, [$searchPattern, $searchPattern, $exactPattern, $limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error searching categories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new category
     */
    public function create(array $data): ?int
    {
        try {
            $query = "INSERT INTO {$this->table} 
                      (name, slug, description, parent_id, color, icon, sort_order, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $params = [
                $data['name'],
                $data['slug'],
                $data['description'] ?? '',
                $data['parent_id'] ?? null,
                $data['color'] ?? '#007cba',
                $data['icon'] ?? 'folder',
                $data['sort_order'] ?? 0
            ];
            
            $this->db->query($query, $params);
            
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating wiki category: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update category
     */
    public function update(int $id, array $data): bool
    {
        try {
            $setFields = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, ['name', 'slug', 'description', 'parent_id', 'color', 'icon', 'sort_order'])) {
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
            error_log("Error updating wiki category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete category (soft delete)
     */
    public function delete(int $id): bool
    {
        try {
            // Check if category has pages
            $pageCount = $this->getPageCount($id);
            if ($pageCount > 0) {
                return false; // Cannot delete category with pages
            }
            
            $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
            
            $this->db->query($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error deleting wiki category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get page count for category
     */
    public function getPageCount(int $categoryId): int
    {
        try {
            $query = "SELECT COUNT(*) as count FROM wiki_pages 
                      WHERE category_id = ? AND deleted_at IS NULL";
            
            $result = $this->db->query($query, [$categoryId])->fetch();
            
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting page count for category: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats(int $categoryId): array
    {
        try {
            $stats = [];
            
            // Total pages in category
            $query = "SELECT COUNT(*) as count FROM wiki_pages 
                      WHERE category_id = ? AND deleted_at IS NULL";
            $result = $this->db->query($query, [$categoryId])->fetch();
            $stats['total_pages'] = (int)($result['count'] ?? 0);
            
            // Recent pages
            $query = "SELECT COUNT(*) as count FROM wiki_pages 
                      WHERE category_id = ? AND deleted_at IS NULL 
                      AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->query($query, [$categoryId])->fetch();
            $stats['recent_pages'] = (int)($result['count'] ?? 0);
            
            // Total views
            $query = "SELECT SUM(view_count) as total FROM wiki_pages 
                      WHERE category_id = ? AND deleted_at IS NULL";
            $result = $this->db->query($query, [$categoryId])->fetch();
            $stats['total_views'] = (int)($result['total'] ?? 0);
            
            // Subcategories
            $subcategories = $this->getSubcategories($categoryId);
            $stats['subcategory_count'] = count($subcategories);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting category statistics: " . $e->getMessage());
            return [
                'total_pages' => 0,
                'recent_pages' => 0,
                'total_views' => 0,
                'subcategory_count' => 0
            ];
        }
    }

    /**
     * Get global category statistics
     */
    public function getGlobalStats(): array
    {
        try {
            $stats = [];
            
            // Total categories
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['total_categories'] = (int)($result['count'] ?? 0);
            
            // Parent categories
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id IS NULL AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['parent_categories'] = (int)($result['count'] ?? 0);
            
            // Categories with pages
            $query = "SELECT COUNT(DISTINCT c.id) as count FROM {$this->table} c
                      INNER JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['active_categories'] = (int)($result['count'] ?? 0);
            
            // Most popular category
            $query = "SELECT c.name, COUNT(p.id) as page_count
                      FROM {$this->table} c
                      LEFT JOIN wiki_pages p ON c.id = p.category_id AND p.deleted_at IS NULL
                      WHERE c.deleted_at IS NULL
                      GROUP BY c.id
                      ORDER BY page_count DESC
                      LIMIT 1";
            $result = $this->db->query($query)->fetch();
            $stats['most_popular'] = $result ?: null;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting global category statistics: " . $e->getMessage());
            return [
                'total_categories' => 0,
                'parent_categories' => 0,
                'active_categories' => 0,
                'most_popular' => null
            ];
        }
    }

    // Helper methods
    private function buildCategoryNode(array $category, array $allCategories): array
    {
        $node = $category;
        $node['children'] = [];
        
        foreach ($allCategories as $cat) {
            if ($cat['parent_id'] == $category['id']) {
                $node['children'][] = $this->buildCategoryNode($cat, $allCategories);
            }
        }
        
        return $node;
    }
} 
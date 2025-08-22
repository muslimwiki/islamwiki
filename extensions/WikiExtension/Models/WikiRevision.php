<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;

/**
 * WikiRevision Model - Wiki page revision data operations
 * 
 * @package IslamWiki\Extensions\WikiExtension\Models
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiRevision
{
    private Connection $db;
    private string $table = 'wiki_revisions';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get revision by ID
     */
    public function getById(int $id): ?array
    {
        try {
            $query = "SELECT r.*, u.username as editor_name, u.role as editor_role
                      FROM {$this->table} r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.id = ? AND r.deleted_at IS NULL";
            
            $result = $this->db->query($query, [$id])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting wiki revision by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get page revisions
     */
    public function getPageRevisions(int $pageId, int $limit = 50): array
    {
        try {
            $query = "SELECT r.*, u.username as editor_name, u.role as editor_role
                      FROM {$this->table} r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.page_id = ? AND r.deleted_at IS NULL
                      ORDER BY r.revision_number DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$pageId, $limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting page revisions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific revision by number
     */
    public function getByRevisionNumber(int $pageId, int $revisionNumber): ?array
    {
        try {
            $query = "SELECT r.*, u.username as editor_name, u.role as editor_role
                      FROM {$this->table} r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.page_id = ? AND r.revision_number = ? AND r.deleted_at IS NULL";
            
            $result = $this->db->query($query, [$pageId, $revisionNumber])->fetch();
            
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting revision by number: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get revision diff
     */
    public function getDiff(int $pageId, int $revision1, int $revision2): ?array
    {
        try {
            $rev1 = $this->getById($revision1);
            $rev2 = $this->getById($revision2);
            
            if (!$rev1 || !$rev2 || $rev1['page_id'] != $pageId || $rev2['page_id'] != $pageId) {
                return null;
            }
            
            return [
                'revision1' => $rev1,
                'revision2' => $rev2,
                'diff' => $this->generateDiff($rev1['content'], $rev2['content'])
            ];
        } catch (Exception $e) {
            error_log("Error getting revision diff: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new revision
     */
    public function create(array $data): ?int
    {
        try {
            // Get next revision number
            $nextNumber = $this->getNextRevisionNumber($data['page_id']);
            
            $query = "INSERT INTO {$this->table} 
                      (page_id, revision_number, title, content, meta_description, category_id, tags,
                       user_id, comment, revision_type, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $data['page_id'],
                $nextNumber,
                $data['title'],
                $data['content'],
                $data['meta_description'] ?? '',
                $data['category_id'] ?? null,
                $data['tags'] ?? '',
                $data['user_id'],
                $data['comment'] ?? 'Page updated',
                $data['revision_type'] ?? 'edit'
            ];
            
            $this->db->query($query, $params);
            
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating wiki revision: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update revision
     */
    public function update(int $id, array $data): bool
    {
        try {
            $setFields = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, ['comment', 'revision_type'])) {
                    $setFields[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($setFields)) {
                return false;
            }
            
            $params[] = $id;
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $setFields) . " WHERE id = ?";
            
            $this->db->query($query, $params);
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating wiki revision: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete revision (soft delete)
     */
    public function delete(int $id): bool
    {
        try {
            $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
            
            $this->db->query($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error deleting wiki revision: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get revision statistics for a page
     */
    public function getPageRevisionStats(int $pageId): array
    {
        try {
            $stats = [];
            
            // Total revisions
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL";
            $result = $this->db->query($query, [$pageId])->fetch();
            $stats['total_revisions'] = (int)($result['count'] ?? 0);
            
            // First revision
            $query = "SELECT created_at FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL 
                      ORDER BY revision_number ASC LIMIT 1";
            $result = $this->db->query($query, [$pageId])->fetch();
            $stats['first_revision'] = $result['created_at'] ?? null;
            
            // Last revision
            $query = "SELECT created_at FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL 
                      ORDER BY revision_number DESC LIMIT 1";
            $result = $this->db->query($query, [$pageId])->fetch();
            $stats['last_revision'] = $result['created_at'] ?? null;
            
            // Unique editors
            $query = "SELECT COUNT(DISTINCT user_id) as count FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL";
            $result = $this->db->query($query, [$pageId])->fetch();
            $stats['unique_editors'] = (int)($result['count'] ?? 0);
            
            // Recent activity
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL 
                      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->query($query, [$pageId])->fetch();
            $stats['recent_activity'] = (int)($result['count'] ?? 0);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting page revision statistics: " . $e->getMessage());
            return [
                'total_revisions' => 0,
                'first_revision' => null,
                'last_revision' => null,
                'unique_editors' => 0,
                'recent_activity' => 0
            ];
        }
    }

    /**
     * Get global revision statistics
     */
    public function getGlobalRevisionStats(): array
    {
        try {
            $stats = [];
            
            // Total revisions
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['total_revisions'] = (int)($result['count'] ?? 0);
            
            // Revisions today
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['revisions_today'] = (int)($result['count'] ?? 0);
            
            // Revisions this week
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['revisions_this_week'] = (int)($result['count'] ?? 0);
            
            // Revisions this month
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL";
            $result = $this->db->query($query)->fetch();
            $stats['revisions_this_month'] = (int)($result['count'] ?? 0);
            
            // Most active editors
            $query = "SELECT u.username, COUNT(r.id) as revision_count
                      FROM {$this->table} r
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.deleted_at IS NULL
                      GROUP BY r.user_id
                      ORDER BY revision_count DESC
                      LIMIT 5";
            $stats['most_active_editors'] = $this->db->query($query)->fetchAll();
            
            // Revision types
            $query = "SELECT revision_type, COUNT(*) as count
                      FROM {$this->table}
                      WHERE deleted_at IS NULL
                      GROUP BY revision_type
                      ORDER BY count DESC";
            $stats['revision_types'] = $this->db->query($query)->fetchAll();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting global revision statistics: " . $e->getMessage());
            return [
                'total_revisions' => 0,
                'revisions_today' => 0,
                'revisions_this_week' => 0,
                'revisions_this_month' => 0,
                'most_active_editors' => [],
                'revision_types' => []
            ];
        }
    }

    /**
     * Get recent revisions across all pages
     */
    public function getRecentRevisions(int $limit = 20): array
    {
        try {
            $query = "SELECT r.*, p.title as page_title, p.slug as page_slug,
                             u.username as editor_name, u.role as editor_role
                      FROM {$this->table} r
                      LEFT JOIN wiki_pages p ON r.page_id = p.id
                      LEFT JOIN users u ON r.user_id = u.id
                      WHERE r.deleted_at IS NULL AND p.deleted_at IS NULL
                      ORDER BY r.created_at DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent revisions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get revisions by user
     */
    public function getRevisionsByUser(int $userId, int $limit = 20): array
    {
        try {
            $query = "SELECT r.*, p.title as page_title, p.slug as page_slug
                      FROM {$this->table} r
                      LEFT JOIN wiki_pages p ON r.page_id = p.id
                      WHERE r.user_id = ? AND r.deleted_at IS NULL AND p.deleted_at IS NULL
                      ORDER BY r.created_at DESC
                      LIMIT ?";
            
            return $this->db->query($query, [$userId, $limit])->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting revisions by user: " . $e->getMessage());
            return [];
        }
    }

    // Helper methods
    private function getNextRevisionNumber(int $pageId): int
    {
        try {
            $query = "SELECT MAX(revision_number) as max_number FROM {$this->table} 
                      WHERE page_id = ? AND deleted_at IS NULL";
            
            $result = $this->db->query($query, [$pageId])->fetch();
            
            return (int)($result['max_number'] ?? 0) + 1;
        } catch (Exception $e) {
            error_log("Error getting next revision number: " . $e->getMessage());
            return 1;
        }
    }

    private function generateDiff(string $oldContent, string $newContent): array
    {
        // Simple diff implementation - replace with more sophisticated diff library
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine !== $newLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => 'changed'
                ];
            } else {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => 'unchanged'
                ];
            }
        }
        
        return $diff;
    }
} 
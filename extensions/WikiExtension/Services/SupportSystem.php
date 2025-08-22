<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Services;

use IslamWiki\Core\Database\Connection;

/**
 * User support system service for WikiExtension
 * 
 * @package IslamWiki\Extensions\WikiExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SupportSystem
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Create a support ticket
     */
    public function createSupportTicket(array $data): int
    {
        try {
            $sql = "INSERT INTO wiki_support_tickets (
                user_id, subject, description, priority, status, 
                category, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['user_id'] ?? 0,
                $data['subject'] ?? '',
                $data['description'] ?? '',
                $data['priority'] ?? 'medium',
                $data['status'] ?? 'open',
                $data['category'] ?? 'general'
            ]);
            
            return (int) $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('Failed to create support ticket: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Collect user feedback
     */
    public function collectFeedback(array $data): bool
    {
        try {
            $sql = "INSERT INTO wiki_user_feedback (
                user_id, page_id, rating, comment, feedback_type,
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['user_id'] ?? 0,
                $data['page_id'] ?? 0,
                $data['rating'] ?? 0,
                $data['comment'] ?? '',
                $data['feedback_type'] ?? 'general'
            ]);
            
            return true;
        } catch (\Exception $e) {
            error_log('Failed to collect feedback: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate FAQ content
     */
    public function generateFAQ(): array
    {
        try {
            $sql = "SELECT 
                question, answer, category, helpful_count,
                created_at, updated_at
                FROM wiki_faq 
                WHERE is_active = 1 
                ORDER BY category, helpful_count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $faqs = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $category = $row['category'];
                if (!isset($faqs[$category])) {
                    $faqs[$category] = [];
                }
                $faqs[$category][] = $row;
            }
            
            return $faqs;
        } catch (\Exception $e) {
            error_log('Failed to generate FAQ: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Track user issues and trends
     */
    public function trackUserIssues(): array
    {
        try {
            $sql = "SELECT 
                category,
                COUNT(*) as total_issues,
                AVG(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolution_rate,
                AVG(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_rate,
                AVG(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical_priority_rate
                FROM wiki_support_tickets 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY category";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $trends = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $trends[] = $row;
            }
            
            return $trends;
        } catch (\Exception $e) {
            error_log('Failed to track user issues: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get support ticket by ID
     */
    public function getSupportTicket(int $ticketId): ?array
    {
        try {
            $sql = "SELECT * FROM wiki_support_tickets WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticketId]);
            
            $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $ticket ?: null;
        } catch (\Exception $e) {
            error_log('Failed to get support ticket: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update support ticket status
     */
    public function updateTicketStatus(int $ticketId, string $status, ?string $resolution = null): bool
    {
        try {
            $sql = "UPDATE wiki_support_tickets 
                    SET status = ?, resolution = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $resolution, $ticketId]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Failed to update ticket status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's support tickets
     */
    public function getUserTickets(int $userId): array
    {
        try {
            $sql = "SELECT * FROM wiki_support_tickets 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            $tickets = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $tickets[] = $row;
            }
            
            return $tickets;
        } catch (\Exception $e) {
            error_log('Failed to get user tickets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search support tickets
     */
    public function searchTickets(string $query, array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM wiki_support_tickets WHERE 1=1";
            $params = [];
            
            if (!empty($query)) {
                $sql .= " AND (subject LIKE ? OR description LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['priority'])) {
                $sql .= " AND priority = ?";
                $params[] = $filters['priority'];
            }
            
            if (!empty($filters['category'])) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $tickets = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $tickets[] = $row;
            }
            
            return $tickets;
        } catch (\Exception $e) {
            error_log('Failed to search tickets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get support statistics
     */
    public function getSupportStatistics(): array
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total_tickets,
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open_tickets,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_tickets,
                COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority_tickets,
                COUNT(CASE WHEN priority = 'critical' THEN 1 END) as critical_priority_tickets,
                AVG(CASE WHEN status = 'resolved' THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) END) as avg_resolution_time
                FROM wiki_support_tickets 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $stats ?: [];
        } catch (\Exception $e) {
            error_log('Failed to get support statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark FAQ as helpful
     */
    public function markFAQHelpful(int $faqId): bool
    {
        try {
            $sql = "UPDATE wiki_faq SET helpful_count = helpful_count + 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$faqId]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log('Failed to mark FAQ helpful: ' . $e->getMessage());
            return false;
        }
    }
} 
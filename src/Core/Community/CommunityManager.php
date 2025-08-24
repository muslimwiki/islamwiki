<?php

/**
 * Community Manager
 *
 * Comprehensive community management system with user contributions,
 * moderation tools, reputation system, and community features.
 *
 * @package IslamWiki\Core\Community
 * @version 0.0.23
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Community;

use IslamWiki\Core\Database\Connection;
use Logger;\Logger

class CommunityManager
{
    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The logger instance.
     */
    private Logger $logger;

    /**
     * Create a new community manager instance.
     */
    public function __construct(Connection $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Submit user contribution.
     */
    public function submitContribution(int $userId, array $contributionData): array
    {
        try {
            // Validate contribution data
            $this->validateContribution($contributionData);

            // Create contribution record
            $contributionId = $this->db->table('user_contributions')->insert([
                'user_id' => $userId,
                'type' => $contributionData['type'],
                'title' => $contributionData['title'],
                'content' => $contributionData['content'],
                'category' => $contributionData['category'],
                'tags' => json_encode($contributionData['tags'] ?? []),
                'status' => 'pending',
                'submitted_at' => date('Y-m-d H:i:s')
            ]);

            // Log contribution submission
            $this->logContributionActivity($userId, 'submitted', $contributionId);

            $this->logger->info('User contribution submitted', [
                'user_id' => $userId,
                'contribution_id' => $contributionId,
                'type' => $contributionData['type']
            ]);

            return [
                'success' => true,
                'contribution_id' => $contributionId,
                'message' => 'Contribution submitted successfully and awaiting review'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Contribution submission failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit contribution: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user contributions.
     */
    public function getUserContributions(int $userId, string $status = null, int $limit = 20): array
    {
        try {
            $query = $this->db->table('user_contributions')
                ->where('user_id', $userId);

            if ($status) {
                $query->where('status', $status);
            }

            $contributions = $query->orderBy('submitted_at', 'desc')
                ->limit($limit)
                ->get();

            $this->logger->info('User contributions retrieved', [
                'user_id' => $userId,
                'count' => count($contributions)
            ]);

            return $contributions;
        } catch (\Exception $e) {
            $this->logger->error('User contributions retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending contributions for moderation.
     */
    public function getPendingContributions(int $limit = 50): array
    {
        try {
            $contributions = $this->db->table('user_contributions')
                ->where('status', 'pending')
                ->orderBy('submitted_at', 'asc')
                ->limit($limit)
                ->get();

            $this->logger->info('Pending contributions retrieved', [
                'count' => count($contributions)
            ]);

            return $contributions;
        } catch (\Exception $e) {
            $this->logger->error('Pending contributions retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Approve contribution.
     */
    public function approveContribution(int $contributionId, int $moderatorId, string $notes = ''): array
    {
        try {
            $contribution = $this->db->table('user_contributions')
                ->where('id', $contributionId)
                ->where('status', 'pending')
                ->first();

            if (!$contribution) {
                return [
                    'success' => false,
                    'message' => 'Contribution not found or already processed'
                ];
            }

            // Update contribution status
            $this->db->table('user_contributions')
                ->where('id', $contributionId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => $moderatorId,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'moderation_notes' => $notes
                ]);

            // Add reputation points to user
            $this->addReputationPoints($contribution['user_id'], 10, 'contribution_approved');

            // Log moderation activity
            $this->logModerationActivity($moderatorId, 'approved', $contributionId);

            $this->logger->info('Contribution approved', [
                'contribution_id' => $contributionId,
                'moderator_id' => $moderatorId,
                'user_id' => $contribution['user_id']
            ]);

            return [
                'success' => true,
                'message' => 'Contribution approved successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Contribution approval failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to approve contribution: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject contribution.
     */
    public function rejectContribution(int $contributionId, int $moderatorId, string $reason): array
    {
        try {
            $contribution = $this->db->table('user_contributions')
                ->where('id', $contributionId)
                ->where('status', 'pending')
                ->first();

            if (!$contribution) {
                return [
                    'success' => false,
                    'message' => 'Contribution not found or already processed'
                ];
            }

            // Update contribution status
            $this->db->table('user_contributions')
                ->where('id', $contributionId)
                ->update([
                    'status' => 'rejected',
                    'rejected_by' => $moderatorId,
                    'rejected_at' => date('Y-m-d H:i:s'),
                    'rejection_reason' => $reason
                ]);

            // Log moderation activity
            $this->logModerationActivity($moderatorId, 'rejected', $contributionId);

            $this->logger->info('Contribution rejected', [
                'contribution_id' => $contributionId,
                'moderator_id' => $moderatorId,
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'message' => 'Contribution rejected successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Contribution rejection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to reject contribution: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get user reputation.
     */
    public function getUserReputation(int $userId): array
    {
        try {
            $reputation = $this->db->table('user_reputation')
                ->where('user_id', $userId)
                ->first();

            if (!$reputation) {
                // Create reputation record if it doesn't exist
                $this->db->table('user_reputation')->insert([
                    'user_id' => $userId,
                    'points' => 0,
                    'level' => 'newcomer',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $reputation = [
                    'user_id' => $userId,
                    'points' => 0,
                    'level' => 'newcomer'
                ];
            }

            // Get reputation history
            $history = $this->db->table('reputation_history')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            return [
                'reputation' => $reputation,
                'history' => $history
            ];
        } catch (\Exception $e) {
            $this->logger->error('User reputation retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add reputation points.
     */
    private function addReputationPoints(int $userId, int $points, string $reason): void
    {
        try {
            // Get current reputation
            $reputation = $this->db->table('user_reputation')
                ->where('user_id', $userId)
                ->first();

            $currentPoints = $reputation ? $reputation['points'] : 0;
            $newPoints = $currentPoints + $points;

            // Determine new level
            $newLevel = $this->calculateReputationLevel($newPoints);

            // Update reputation
            if ($reputation) {
                $this->db->table('user_reputation')
                    ->where('user_id', $userId)
                    ->update([
                        'points' => $newPoints,
                        'level' => $newLevel,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            } else {
                $this->db->table('user_reputation')->insert([
                    'user_id' => $userId,
                    'points' => $newPoints,
                    'level' => $newLevel,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Log reputation change
            $this->db->table('reputation_history')->insert([
                'user_id' => $userId,
                'points' => $points,
                'reason' => $reason,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info('Reputation points added', [
                'user_id' => $userId,
                'points' => $points,
                'reason' => $reason,
                'new_total' => $newPoints
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Reputation points addition failed: ' . $e->getMessage());
        }
    }

    /**
     * Calculate reputation level.
     */
    private function calculateReputationLevel(int $points): string
    {
        if ($points >= 1000) {
            return 'expert';
        }
        if ($points >= 500) {
            return 'veteran';
        }
        if ($points >= 200) {
            return 'contributor';
        }
        if ($points >= 50) {
            return 'member';
        }
        if ($points >= 10) {
            return 'active';
        }
        return 'newcomer';
    }

    /**
     * Get community statistics.
     */
    public function getCommunityStats(): array
    {
        try {
            $stats = [];

            // Return default stats when database methods are not available
            $stats['total_users'] = 0;
            $stats['active_users'] = 0;
            $stats['total_contributions'] = 0;
            $stats['pending_contributions'] = 0;
            $stats['approved_contributions'] = 0;
            $stats['top_contributors'] = [];
            $stats['recent_activity'] = [];

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error('Community stats retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get community discussions.
     */
    public function getCommunityDiscussions(int $limit = 20): array
    {
        try {
            // Return empty array when database methods are not available
            $discussions = [];

            $this->logger->info('Community discussions retrieved', [
                'count' => count($discussions)
            ]);

            return $discussions;
        } catch (\Exception $e) {
            $this->logger->error('Community discussions retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create community discussion.
     */
    public function createDiscussion(int $userId, array $discussionData): array
    {
        try {
            // Validate discussion data
            $this->validateDiscussion($discussionData);

            // Create discussion
            $discussionId = $this->db->table('community_discussions')->insert([
                'user_id' => $userId,
                'title' => $discussionData['title'],
                'content' => $discussionData['content'],
                'category' => $discussionData['category'],
                'tags' => json_encode($discussionData['tags'] ?? []),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Log discussion creation
            $this->logCommunityActivity($userId, 'created_discussion', $discussionId);

            $this->logger->info('Community discussion created', [
                'user_id' => $userId,
                'discussion_id' => $discussionId
            ]);

            return [
                'success' => true,
                'discussion_id' => $discussionId,
                'message' => 'Discussion created successfully'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Discussion creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create discussion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate contribution data.
     */
    private function validateContribution(array $data): void
    {
        if (empty($data['title']) || strlen($data['title']) < 5) {
            throw new \Exception('Title must be at least 5 characters long');
        }

        if (empty($data['content']) || strlen($data['content']) < 20) {
            throw new \Exception('Content must be at least 20 characters long');
        }

        if (empty($data['type']) || !in_array($data['type'], ['article', 'hadith', 'quran', 'scholar', 'event'])) {
            throw new \Exception('Invalid contribution type');
        }

        if (empty($data['category'])) {
            throw new \Exception('Category is required');
        }
    }

    /**
     * Validate discussion data.
     */
    private function validateDiscussion(array $data): void
    {
        if (empty($data['title']) || strlen($data['title']) < 5) {
            throw new \Exception('Title must be at least 5 characters long');
        }

        if (empty($data['content']) || strlen($data['content']) < 20) {
            throw new \Exception('Content must be at least 20 characters long');
        }

        if (empty($data['category'])) {
            throw new \Exception('Category is required');
        }
    }

    /**
     * Log contribution activity.
     */
    private function logContributionActivity(int $userId, string $action, int $contributionId): void
    {
        try {
            $this->db->table('community_activity')->insert([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => 'contribution',
                'entity_id' => $contributionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Log moderation activity.
     */
    private function logModerationActivity(int $moderatorId, string $action, int $contributionId): void
    {
        try {
            $this->db->table('moderation_log')->insert([
                'moderator_id' => $moderatorId,
                'action' => $action,
                'entity_type' => 'contribution',
                'entity_id' => $contributionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Moderation logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Log community activity.
     */
    private function logCommunityActivity(int $userId, string $action, int $entityId = null): void
    {
        try {
            $this->db->table('community_activity')->insert([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => 'discussion',
                'entity_id' => $entityId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Community activity logging failed: ' . $e->getMessage());
        }
    }
}

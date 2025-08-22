<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Services;

use IslamWiki\Core\Database\Connection;

/**
 * Success measurement service for WikiExtension
 * 
 * @package IslamWiki\Extensions\WikiExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SuccessMetrics
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Measure performance metrics
     */
    public function measurePerformance(): array
    {
        try {
            $sql = "SELECT 
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(database_queries) as avg_database_queries,
                AVG(memory_usage) as avg_memory_usage,
                AVG(cache_efficiency) as avg_cache_efficiency,
                COUNT(*) as total_requests,
                SUM(benchmarks_passed) as successful_requests
                FROM wiki_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($stats) {
                $stats['success_rate'] = $stats['total_requests'] > 0 
                    ? ($stats['successful_requests'] / $stats['total_requests']) * 100 
                    : 0;
            }
            
            return $stats ?: [];
        } catch (\Exception $e) {
            error_log('Failed to measure performance: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Measure user satisfaction
     */
    public function measureUserSatisfaction(): array
    {
        try {
            $sql = "SELECT 
                AVG(rating) as avg_rating,
                COUNT(*) as total_feedback,
                COUNT(CASE WHEN rating >= 4 THEN 1 END) as satisfied_users,
                COUNT(CASE WHEN rating <= 2 THEN 1 END) as dissatisfied_users,
                feedback_type,
                COUNT(*) as feedback_count
                FROM wiki_user_feedback 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY feedback_type";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $satisfaction = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $satisfaction[] = $row;
            }
            
            return $satisfaction;
        } catch (\Exception $e) {
            error_log('Failed to measure user satisfaction: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Measure system stability
     */
    public function measureSystemStability(): array
    {
        try {
            $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_requests,
                SUM(benchmarks_passed) as successful_requests,
                AVG(execution_time) as avg_execution_time,
                AVG(memory_usage) as avg_memory_usage
                FROM wiki_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $stability = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $row['success_rate'] = $row['total_requests'] > 0 
                    ? ($row['successful_requests'] / $row['total_requests']) * 100 
                    : 0;
                $stability[] = $row;
            }
            
            return $stability;
        } catch (\Exception $e) {
            error_log('Failed to measure system stability: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Measure user adoption
     */
    public function measureUserAdoption(): array
    {
        try {
            $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(DISTINCT user_id) as new_users,
                COUNT(*) as total_actions
                FROM wiki_user_activity 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $adoption = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $adoption[] = $row;
            }
            
            return $adoption;
        } catch (\Exception $e) {
            error_log('Failed to measure user adoption: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Measure content creation
     */
    public function measureContentCreation(): array
    {
        try {
            $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as pages_created,
                COUNT(DISTINCT user_id) as active_creators,
                AVG(LENGTH(content)) as avg_content_length
                FROM wiki_pages 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $content = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $content[] = $row;
            }
            
            return $content;
        } catch (\Exception $e) {
            error_log('Failed to measure content creation: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Measure community engagement
     */
    public function measureCommunityEngagement(): array
    {
        try {
            $sql = "SELECT 
                COUNT(DISTINCT user_id) as active_users,
                COUNT(*) as total_actions,
                AVG(actions_per_user) as avg_actions_per_user
                FROM (
                    SELECT 
                        user_id,
                        COUNT(*) as actions_per_user
                    FROM wiki_user_activity 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY user_id
                ) user_activity";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $engagement = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $engagement ?: [];
        } catch (\Exception $e) {
            error_log('Failed to measure community engagement: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate comprehensive success report
     */
    public function generateSuccessReport(): array
    {
        $performance = $this->measurePerformance();
        $satisfaction = $this->measureUserSatisfaction();
        $stability = $this->measureSystemStability();
        $adoption = $this->measureUserAdoption();
        $content = $this->measureContentCreation();
        $engagement = $this->measureCommunityEngagement();
        
        // Calculate overall success score
        $successScore = $this->calculateOverallSuccessScore([
            'performance' => $performance,
            'satisfaction' => $satisfaction,
            'stability' => $stability,
            'adoption' => $adoption,
            'content' => $content,
            'engagement' => $engagement
        ]);
        
        return [
            'overall_success_score' => $successScore,
            'performance_metrics' => $performance,
            'user_satisfaction' => $satisfaction,
            'system_stability' => $stability,
            'user_adoption' => $adoption,
            'content_creation' => $content,
            'community_engagement' => $engagement,
            'recommendations' => $this->generateSuccessRecommendations([
                'performance' => $performance,
                'satisfaction' => $satisfaction,
                'stability' => $stability,
                'adoption' => $adoption,
                'content' => $content,
                'engagement' => $engagement
            ]),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate overall success score
     */
    private function calculateOverallSuccessScore(array $metrics): float
    {
        $scores = [];
        
        // Performance score (30% weight)
        if (!empty($metrics['performance'])) {
            $perf = $metrics['performance'];
            $perfScore = 0;
            if (isset($perf['success_rate'])) $perfScore += $perf['success_rate'] * 0.5;
            if (isset($perf['avg_execution_time']) && $perf['avg_execution_time'] < 2.0) $perfScore += 25;
            if (isset($perf['avg_cache_efficiency']) && $perf['avg_cache_efficiency'] > 0.9) $perfScore += 25;
            $scores['performance'] = $perfScore;
        }
        
        // User satisfaction score (25% weight)
        if (!empty($metrics['satisfaction'])) {
            $sat = $metrics['satisfaction'];
            $satScore = 0;
            foreach ($sat as $item) {
                if (isset($item['avg_rating'])) $satScore += $item['avg_rating'] * 20;
            }
            $scores['satisfaction'] = min(100, $satScore);
        }
        
        // System stability score (20% weight)
        if (!empty($metrics['stability'])) {
            $stab = $metrics['stability'];
            $stabScore = 0;
            foreach ($stab as $item) {
                if (isset($item['success_rate'])) $stabScore += $item['success_rate'];
            }
            $scores['stability'] = count($stab) > 0 ? $stabScore / count($stab) : 0;
        }
        
        // User adoption score (15% weight)
        if (!empty($metrics['adoption'])) {
            $adopt = $metrics['adoption'];
            $adoptScore = 0;
            foreach ($adopt as $item) {
                if (isset($item['new_users'])) $adoptScore += $item['new_users'];
            }
            $scores['adoption'] = min(100, $adoptScore * 2);
        }
        
        // Content creation score (10% weight)
        if (!empty($metrics['content'])) {
            $cont = $metrics['content'];
            $contScore = 0;
            foreach ($cont as $item) {
                if (isset($item['pages_created'])) $contScore += $item['pages_created'];
            }
            $scores['content'] = min(100, $contScore * 5);
        }
        
        // Calculate weighted average
        $weights = ['performance' => 0.30, 'satisfaction' => 0.25, 'stability' => 0.20, 'adoption' => 0.15, 'content' => 0.10];
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($weights as $metric => $weight) {
            if (isset($scores[$metric])) {
                $totalScore += $scores[$metric] * $weight;
                $totalWeight += $weight;
            }
        }
        
        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    /**
     * Generate success recommendations
     */
    private function generateSuccessRecommendations(array $metrics): array
    {
        $recommendations = [];
        
        // Performance recommendations
        if (!empty($metrics['performance'])) {
            $perf = $metrics['performance'];
            if (isset($perf['avg_execution_time']) && $perf['avg_execution_time'] > 2.0) {
                $recommendations[] = 'Page load times are above target. Consider implementing additional caching or query optimization.';
            }
            if (isset($perf['avg_cache_efficiency']) && $perf['avg_cache_efficiency'] < 0.9) {
                $recommendations[] = 'Cache efficiency is below target. Consider cache warming strategies or cache invalidation optimization.';
            }
        }
        
        // User satisfaction recommendations
        if (!empty($metrics['satisfaction'])) {
            $sat = $metrics['satisfaction'];
            foreach ($sat as $item) {
                if (isset($item['avg_rating']) && $item['avg_rating'] < 4.0) {
                    $recommendations[] = "User satisfaction for {$item['feedback_type']} is below target. Consider improving user experience and addressing user concerns.";
                }
            }
        }
        
        // System stability recommendations
        if (!empty($metrics['stability'])) {
            $stab = $metrics['stability'];
            $lowSuccessDays = 0;
            foreach ($stab as $item) {
                if (isset($item['success_rate']) && $item['success_rate'] < 95) {
                    $lowSuccessDays++;
                }
            }
            if ($lowSuccessDays > 2) {
                $recommendations[] = 'System stability has been below target for multiple days. Consider investigating and resolving underlying issues.';
            }
        }
        
        // User adoption recommendations
        if (!empty($metrics['adoption'])) {
            $adopt = $metrics['adoption'];
            $totalNewUsers = 0;
            foreach ($adopt as $item) {
                if (isset($item['new_users'])) {
                    $totalNewUsers += $item['new_users'];
                }
            }
            if ($totalNewUsers < 10) {
                $recommendations[] = 'User adoption rate is low. Consider improving onboarding, user experience, and marketing efforts.';
            }
        }
        
        // Content creation recommendations
        if (!empty($metrics['content'])) {
            $cont = $metrics['content'];
            $totalPages = 0;
            foreach ($cont as $item) {
                if (isset($item['pages_created'])) {
                    $totalPages += $item['pages_created'];
                }
            }
            if ($totalPages < 20) {
                $recommendations[] = 'Content creation rate is low. Consider encouraging user contributions and providing content creation tools.';
            }
        }
        
        return $recommendations;
    }
} 
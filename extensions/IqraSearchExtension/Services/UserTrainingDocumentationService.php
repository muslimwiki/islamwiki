<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * User Training & Documentation Service
 * Provides enterprise-grade training materials and documentation
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class UserTrainingDocumentationService
{
    private Connection $db;
    private LoggerInterface $logger;
    private array $trainingModules;
    private array $documentationCategories;

    public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->initializeTrainingModules();
        $this->initializeDocumentationCategories();
    }

    /**
     * Initialize training modules
     */
    private function initializeTrainingModules(): void
    {
        $this->trainingModules = [
            'beginner' => [
                'search_basics' => [
                    'title' => 'Search Basics',
                    'description' => 'Learn the fundamentals of Islamic search',
                    'duration' => '15 minutes',
                    'difficulty' => 'Beginner',
                    'topics' => [
                        'Basic search techniques',
                        'Understanding search results',
                        'Using filters and sorting',
                        'Search tips and tricks'
                    ]
                ],
                'user_interface' => [
                    'title' => 'User Interface Guide',
                    'description' => 'Navigate the platform effectively',
                    'duration' => '20 minutes',
                    'difficulty' => 'Beginner',
                    'topics' => [
                        'Navigation and menus',
                        'Dashboard overview',
                        'Settings and preferences',
                        'Help and support'
                    ]
                ]
            ],
            'intermediate' => [
                'advanced_search' => [
                    'title' => 'Advanced Search Techniques',
                    'description' => 'Master advanced search capabilities',
                    'duration' => '25 minutes',
                    'difficulty' => 'Intermediate',
                    'topics' => [
                        'Boolean search operators',
                        'Advanced filters',
                        'Search syntax',
                        'Custom search queries'
                    ]
                ],
                'personalization' => [
                    'title' => 'Personalization Features',
                    'description' => 'Customize your search experience',
                    'duration' => '20 minutes',
                    'difficulty' => 'Intermediate',
                    'topics' => [
                        'Setting preferences',
                        'Language preferences',
                        'Content type preferences',
                        'Search history management'
                    ]
                ]
            ],
            'advanced' => [
                'ai_recommendations' => [
                    'title' => 'AI-Powered Recommendations',
                    'description' => 'Leverage artificial intelligence features',
                    'duration' => '30 minutes',
                    'difficulty' => 'Advanced',
                    'topics' => [
                        'Understanding AI recommendations',
                        'Training the AI system',
                        'Optimizing recommendations',
                        'AI feature customization'
                    ]
                ],
                'analytics_dashboard' => [
                    'title' => 'Analytics Dashboard',
                    'description' => 'Use advanced analytics and insights',
                    'duration' => '35 minutes',
                    'difficulty' => 'Advanced',
                    'topics' => [
                        'Dashboard overview',
                        'Performance metrics',
                        'User behavior analytics',
                        'Custom reports'
                    ]
                ]
            ],
            'admin' => [
                'system_administration' => [
                    'title' => 'System Administration',
                    'description' => 'Manage the platform effectively',
                    'duration' => '45 minutes',
                    'difficulty' => 'Admin',
                    'topics' => [
                        'User management',
                        'Content moderation',
                        'System configuration',
                        'Performance monitoring'
                    ]
                ],
                'content_moderation' => [
                    'title' => 'Content Moderation',
                    'description' => 'Ensure content quality and safety',
                    'duration' => '40 minutes',
                    'difficulty' => 'Admin',
                    'topics' => [
                        'Moderation workflow',
                        'Quality assessment',
                        'Community guidelines',
                        'Moderation tools'
                    ]
                ]
            ]
        ];
    }

    /**
     * Initialize documentation categories
     */
    private function initializeDocumentationCategories(): void
    {
        $this->documentationCategories = [
            'user_guides' => [
                'title' => 'User Guides',
                'description' => 'Comprehensive guides for end users',
                'documents' => [
                    'getting_started' => 'Getting Started Guide',
                    'search_guide' => 'Complete Search Guide',
                    'user_preferences' => 'User Preferences Guide',
                    'troubleshooting' => 'Troubleshooting Guide'
                ]
            ],
            'admin_guides' => [
                'title' => 'Administrator Guides',
                'description' => 'Guides for system administrators',
                'documents' => [
                    'admin_dashboard' => 'Admin Dashboard Guide',
                    'user_management' => 'User Management Guide',
                    'content_moderation' => 'Content Moderation Guide',
                    'system_configuration' => 'System Configuration Guide'
                ]
            ],
            'developer_guides' => [
                'title' => 'Developer Guides',
                'description' => 'Technical documentation for developers',
                'documents' => [
                    'api_reference' => 'API Reference Documentation',
                    'extension_development' => 'Extension Development Guide',
                    'customization_guide' => 'Customization Guide',
                    'deployment_guide' => 'Deployment Guide'
                ]
            ],
            'video_tutorials' => [
                'title' => 'Video Tutorials',
                'description' => 'Step-by-step video tutorials',
                'documents' => [
                    'search_tutorial' => 'Search Tutorial Video',
                    'dashboard_tutorial' => 'Dashboard Tutorial Video',
                    'preferences_tutorial' => 'Preferences Tutorial Video',
                    'admin_tutorial' => 'Admin Tutorial Video'
                ]
            ]
        ];
    }

    /**
     * Get training modules for user level
     */
    public function getTrainingModules(string $userLevel = 'beginner'): array
    {
        try {
            $this->logger->info("Getting training modules for user level: {$userLevel}");

            if (!isset($this->trainingModules[$userLevel])) {
                $userLevel = 'beginner'; // Default to beginner
            }

            $modules = $this->trainingModules[$userLevel];
            
            // Add progress tracking for each module
            foreach ($modules as $key => &$module) {
                $module['module_id'] = $key;
                $module['progress'] = $this->getUserModuleProgress($key);
                $module['estimated_completion'] = $this->calculateEstimatedCompletion($module);
            }

            $this->logger->info("Retrieved " . count($modules) . " training modules for {$userLevel}");

            return $modules;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get training modules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all training modules
     */
    public function getAllTrainingModules(): array
    {
        try {
            $this->logger->info("Getting all training modules");

            $allModules = [];
            foreach ($this->trainingModules as $level => $modules) {
                $allModules[$level] = $this->getTrainingModules($level);
            }

            return $allModules;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get all training modules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get documentation for category
     */
    public function getDocumentation(string $category = 'user_guides'): array
    {
        try {
            $this->logger->info("Getting documentation for category: {$category}");

            if (!isset($this->documentationCategories[$category])) {
                $category = 'user_guides'; // Default to user guides
            }

            $documentation = $this->documentationCategories[$category];
            
            // Add document details and access information
            foreach ($documentation['documents'] as $key => $title) {
                $documentation['documents'][$key] = [
                    'title' => $title,
                    'document_id' => $key,
                    'access_level' => $this->getDocumentAccessLevel($key),
                    'last_updated' => $this->getDocumentLastUpdated($key),
                    'estimated_read_time' => $this->getEstimatedReadTime($key)
                ];
            }

            $this->logger->info("Retrieved documentation for category: {$category}");

            return $documentation;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get documentation: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all documentation
     */
    public function getAllDocumentation(): array
    {
        try {
            $this->logger->info("Getting all documentation");

            $allDocumentation = [];
            foreach ($this->documentationCategories as $category => $docs) {
                $allDocumentation[$category] = $this->getDocumentation($category);
            }

            return $allDocumentation;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get all documentation: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Start training module
     */
    public function startTrainingModule(string $moduleId, int $userId): array
    {
        try {
            $this->logger->info("User {$userId} starting training module: {$moduleId}");

            // Find the module
            $module = $this->findTrainingModule($moduleId);
            if (!$module) {
                throw new \Exception("Training module not found: {$moduleId}");
            }

            // Record module start
            $startResult = $this->recordModuleStart($moduleId, $userId);
            
            // Get module content
            $moduleContent = $this->getModuleContent($moduleId);

            $result = [
                'success' => true,
                'module' => $module,
                'content' => $moduleContent,
                'start_time' => date('Y-m-d H:i:s'),
                'estimated_duration' => $module['duration'],
                'progress' => 0
            ];

            $this->logger->info("Training module started successfully", [
                'module_id' => $moduleId,
                'user_id' => $userId
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Failed to start training module: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Complete training module
     */
    public function completeTrainingModule(string $moduleId, int $userId): array
    {
        try {
            $this->logger->info("User {$userId} completing training module: {$moduleId}");

            // Record module completion
            $completionResult = $this->recordModuleCompletion($moduleId, $userId);
            
            // Update user progress
            $progressResult = $this->updateUserProgress($moduleId, $userId);
            
            // Award completion certificate
            $certificateResult = $this->awardCompletionCertificate($moduleId, $userId);

            $result = [
                'success' => true,
                'module_id' => $moduleId,
                'completion_time' => date('Y-m-d H:i:s'),
                'certificate' => $certificateResult,
                'progress_updated' => $progressResult,
                'message' => 'Training module completed successfully'
            ];

            $this->logger->info("Training module completed successfully", [
                'module_id' => $moduleId,
                'user_id' => $userId
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Failed to complete training module: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user training progress
     */
    public function getUserTrainingProgress(int $userId): array
    {
        try {
            $this->logger->info("Getting training progress for user: {$userId}");

            $progress = [
                'user_id' => $userId,
                'overall_progress' => 0,
                'modules_completed' => 0,
                'total_modules' => 0,
                'certificates_earned' => 0,
                'current_modules' => [],
                'completed_modules' => [],
                'recommended_modules' => []
            ];

            // Get all training modules
            $allModules = $this->getAllTrainingModules();
            $totalModules = 0;
            $completedModules = 0;

            foreach ($allModules as $level => $modules) {
                foreach ($modules as $moduleId => $module) {
                    $totalModules++;
                    $moduleProgress = $this->getUserModuleProgress($moduleId, $userId);
                    
                    if ($moduleProgress['completed']) {
                        $completedModules++;
                        $progress['completed_modules'][] = [
                            'module_id' => $moduleId,
                            'title' => $module['title'],
                            'completion_date' => $moduleProgress['completion_date'],
                            'certificate' => $moduleProgress['certificate']
                        ];
                    } elseif ($moduleProgress['in_progress']) {
                        $progress['current_modules'][] = [
                            'module_id' => $moduleId,
                            'title' => $module['title'],
                            'progress_percentage' => $moduleProgress['progress_percentage'],
                            'estimated_completion' => $moduleProgress['estimated_completion']
                        ];
                    }
                }
            }

            // Calculate overall progress
            $progress['total_modules'] = $totalModules;
            $progress['modules_completed'] = $completedModules;
            $progress['overall_progress'] = $totalModules > 0 ? ($completedModules / $totalModules) * 100 : 0;
            $progress['certificates_earned'] = count($progress['completed_modules']);

            // Get recommended modules
            $progress['recommended_modules'] = $this->getRecommendedModules($userId);

            $this->logger->info("Training progress retrieved for user {$userId}", [
                'overall_progress' => $progress['overall_progress'],
                'modules_completed' => $progress['modules_completed']
            ]);

            return $progress;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user training progress: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recommended training modules
     */
    public function getRecommendedModules(int $userId): array
    {
        try {
            // Get user's current level and completed modules
            $userProgress = $this->getUserTrainingProgress($userId);
            $completedModuleIds = array_column($userProgress['completed_modules'], 'module_id');
            
            $recommendations = [];
            
            // Recommend next level modules
            $userLevel = $this->determineUserLevel($completedModuleIds);
            $nextLevel = $this->getNextLevel($userLevel);
            
            if ($nextLevel && isset($this->trainingModules[$nextLevel])) {
                foreach ($this->trainingModules[$nextLevel] as $moduleId => $module) {
                    if (!in_array($moduleId, $completedModuleIds)) {
                        $recommendations[] = [
                            'module_id' => $moduleId,
                            'title' => $module['title'],
                            'description' => $module['description'],
                            'difficulty' => $module['difficulty'],
                            'duration' => $module['duration'],
                            'reason' => 'Next level progression'
                        ];
                    }
                }
            }
            
            // Limit recommendations
            return array_slice($recommendations, 0, 3);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get recommended modules: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get training certificate
     */
    public function getTrainingCertificate(string $moduleId, int $userId): array
    {
        try {
            $this->logger->info("Getting training certificate for user {$userId}, module {$moduleId}");

            $module = $this->findTrainingModule($moduleId);
            if (!$module) {
                throw new \Exception("Training module not found: {$moduleId}");
            }

            $userProgress = $this->getUserModuleProgress($moduleId, $userId);
            if (!$userProgress['completed']) {
                throw new \Exception("Module not completed: {$moduleId}");
            }

            $certificate = [
                'certificate_id' => "CERT_{$moduleId}_{$userId}_" . time(),
                'user_id' => $userId,
                'module_id' => $moduleId,
                'module_title' => $module['title'],
                'completion_date' => $userProgress['completion_date'],
                'difficulty_level' => $module['difficulty'],
                'duration' => $module['duration'],
                'issued_date' => date('Y-m-d H:i:s'),
                'valid_until' => date('Y-m-d H:i:s', strtotime('+2 years')),
                'certificate_url' => "/certificates/{$moduleId}_{$userId}.pdf"
            ];

            $this->logger->info("Training certificate generated successfully", [
                'certificate_id' => $certificate['certificate_id']
            ]);

            return $certificate;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get training certificate: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Search documentation
     */
    public function searchDocumentation(string $query, array $filters = []): array
    {
        try {
            $this->logger->info("Searching documentation with query: {$query}");

            $searchResults = [];
            
            // Search through all documentation
            foreach ($this->documentationCategories as $category => $docs) {
                foreach ($docs['documents'] as $docId => $title) {
                    $relevance = $this->calculateSearchRelevance($query, $title, $docId);
                    
                    if ($relevance > 0.3) { // Minimum relevance threshold
                        $searchResults[] = [
                            'document_id' => $docId,
                            'title' => $title,
                            'category' => $category,
                            'category_title' => $docs['title'],
                            'relevance_score' => $relevance,
                            'access_level' => $this->getDocumentAccessLevel($docId),
                            'last_updated' => $this->getDocumentLastUpdated($docId)
                        ];
                    }
                }
            }
            
            // Sort by relevance score
            usort($searchResults, function($a, $b) {
                return ($b['relevance_score'] ?? 0) <=> ($a['relevance_score'] ?? 0);
            });
            
            // Apply filters
            if (!empty($filters['category'])) {
                $searchResults = array_filter($searchResults, function($result) use ($filters) {
                    return $result['category'] === $filters['category'];
                });
            }
            
            if (!empty($filters['access_level'])) {
                $searchResults = array_filter($searchResults, function($result) use ($filters) {
                    return $result['access_level'] === $filters['access_level'];
                });
            }

            $this->logger->info("Documentation search completed", [
                'query' => $query,
                'results_count' => count($searchResults)
            ]);

            return $searchResults;

        } catch (\Exception $e) {
            $this->logger->error("Documentation search failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get video tutorials
     */
    public function getVideoTutorials(): array
    {
        try {
            $this->logger->info("Getting video tutorials");

            $tutorials = [
                'search_tutorial' => [
                    'title' => 'Complete Search Tutorial',
                    'description' => 'Learn how to use all search features effectively',
                    'duration' => '12:45',
                    'difficulty' => 'Beginner',
                    'video_url' => '/tutorials/search_tutorial.mp4',
                    'thumbnail_url' => '/tutorials/search_thumbnail.jpg',
                    'topics_covered' => [
                        'Basic search techniques',
                        'Advanced search operators',
                        'Filtering and sorting',
                        'Search preferences'
                    ]
                ],
                'dashboard_tutorial' => [
                    'title' => 'Dashboard Mastery',
                    'description' => 'Master the analytics dashboard and insights',
                    'duration' => '18:32',
                    'difficulty' => 'Intermediate',
                    'video_url' => '/tutorials/dashboard_tutorial.mp4',
                    'thumbnail_url' => '/tutorials/dashboard_thumbnail.jpg',
                    'topics_covered' => [
                        'Dashboard overview',
                        'Analytics interpretation',
                        'Custom reports',
                        'Performance monitoring'
                    ]
                ],
                'preferences_tutorial' => [
                    'title' => 'Personalization Guide',
                    'description' => 'Customize your experience with preferences',
                    'duration' => '15:18',
                    'difficulty' => 'Beginner',
                    'video_url' => '/tutorials/preferences_tutorial.mp4',
                    'thumbnail_url' => '/tutorials/preferences_thumbnail.jpg',
                    'topics_covered' => [
                        'Setting preferences',
                        'Language options',
                        'Content filters',
                        'Search history'
                    ]
                ],
                'admin_tutorial' => [
                    'title' => 'Administrator Guide',
                    'description' => 'Complete guide for system administrators',
                    'duration' => '25:45',
                    'difficulty' => 'Advanced',
                    'video_url' => '/tutorials/admin_tutorial.mp4',
                    'thumbnail_url' => '/tutorials/admin_thumbnail.jpg',
                    'topics_covered' => [
                        'User management',
                        'Content moderation',
                        'System configuration',
                        'Performance monitoring'
                    ]
                ]
            ];

            $this->logger->info("Video tutorials retrieved successfully", [
                'tutorials_count' => count($tutorials)
            ]);

            return $tutorials;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get video tutorials: " . $e->getMessage());
            return [];
        }
    }

    // Helper methods
    private function findTrainingModule(string $moduleId): ?array
    {
        foreach ($this->trainingModules as $level => $modules) {
            if (isset($modules[$moduleId])) {
                return $modules[$moduleId];
            }
        }
        return null;
    }

    private function getUserModuleProgress(string $moduleId, int $userId = 0): array
    {
        // Mock user module progress for now
        return [
            'completed' => false,
            'in_progress' => false,
            'progress_percentage' => 0,
            'completion_date' => null,
            'certificate' => null,
            'estimated_completion' => null
        ];
    }

    private function calculateEstimatedCompletion(array $module): string
    {
        // Mock estimated completion calculation
        $duration = $module['duration'];
        $minutes = (int) $duration;
        return "{$minutes} minutes";
    }

    private function getDocumentAccessLevel(string $documentId): string
    {
        // Mock document access levels
        $accessLevels = [
            'getting_started' => 'public',
            'search_guide' => 'public',
            'admin_dashboard' => 'admin',
            'api_reference' => 'developer'
        ];
        
        return $accessLevels[$documentId] ?? 'public';
    }

    private function getDocumentLastUpdated(string $documentId): string
    {
        // Mock document last updated dates
        return date('Y-m-d H:i:s', strtotime('-1 week'));
    }

    private function getEstimatedReadTime(string $documentId): string
    {
        // Mock estimated read times
        $readTimes = [
            'getting_started' => '10 minutes',
            'search_guide' => '25 minutes',
            'admin_dashboard' => '35 minutes',
            'api_reference' => '45 minutes'
        ];
        
        return $readTimes[$documentId] ?? '15 minutes';
    }

    private function recordModuleStart(string $moduleId, int $userId): bool
    {
        // Mock module start recording
        return true;
    }

    private function getModuleContent(string $moduleId): array
    {
        // Mock module content
        return [
            'sections' => [
                'introduction' => 'Module introduction content',
                'main_content' => 'Main module content',
                'exercises' => 'Practice exercises',
                'summary' => 'Module summary'
            ],
            'resources' => [
                'videos' => ['tutorial_video.mp4'],
                'documents' => ['reference_guide.pdf'],
                'links' => ['https://example.com/resources']
            ]
        ];
    }

    private function recordModuleCompletion(string $moduleId, int $userId): bool
    {
        // Mock module completion recording
        return true;
    }

    private function updateUserProgress(string $moduleId, int $userId): bool
    {
        // Mock user progress update
        return true;
    }

    private function awardCompletionCertificate(string $moduleId, int $userId): array
    {
        // Mock certificate award
        return [
            'certificate_id' => "CERT_{$moduleId}_{$userId}_" . time(),
            'awarded_date' => date('Y-m-d H:i:s')
        ];
    }

    private function determineUserLevel(array $completedModuleIds): string
    {
        // Mock user level determination
        if (count($completedModuleIds) >= 8) return 'advanced';
        if (count($completedModuleIds) >= 4) return 'intermediate';
        return 'beginner';
    }

    private function getNextLevel(string $currentLevel): string
    {
        $levelProgression = [
            'beginner' => 'intermediate',
            'intermediate' => 'advanced',
            'advanced' => 'admin'
        ];
        
        return $levelProgression[$currentLevel] ?? null;
    }

    private function calculateSearchRelevance(string $query, string $title, string $docId): float
    {
        // Simple relevance calculation
        $queryWords = explode(' ', strtolower($query));
        $titleWords = explode(' ', strtolower($title));
        
        $commonWords = array_intersect($queryWords, $titleWords);
        $relevance = count($commonWords) / max(count($queryWords), 1);
        
        return min($relevance, 1.0);
    }
} 
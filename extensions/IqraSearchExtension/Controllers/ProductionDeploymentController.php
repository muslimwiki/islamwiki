<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Extensions\IqraSearchExtension\Services\ProductionDeploymentService;
use IslamWiki\Extensions\IqraSearchExtension\Services\UserTrainingDocumentationService;
use Psr\Log\LoggerInterface;

/**
 * Production Deployment Controller
 * Manages the complete production deployment and launch process
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ProductionDeploymentController
{
    private ProductionDeploymentService $deploymentService;
    private UserTrainingDocumentationService $trainingService;
    private LoggerInterface $logger;

    public function __construct(
        ProductionDeploymentService $deploymentService,
        UserTrainingDocumentationService $trainingService,
        LoggerInterface $logger
    ) {
        $this->deploymentService = $deploymentService;
        $this->trainingService = $trainingService;
        $this->logger = $logger;
    }

    /**
     * Show production deployment dashboard
     */
    public function dashboard(): Response
    {
        try {
            $this->logger->info('Production deployment dashboard accessed');

            $deploymentStatus = $this->deploymentService->getDeploymentStatus();
            $deploymentConfig = $this->deploymentService->getDeploymentConfig();
            $deploymentHistory = $this->deploymentService->getDeploymentHistory();
            $isDeploymentInProgress = $this->deploymentService->isDeploymentInProgress();

            $data = [
                'deployment_status' => $deploymentStatus,
                'deployment_config' => $deploymentConfig,
                'deployment_history' => $deploymentHistory,
                'is_deployment_in_progress' => $isDeploymentInProgress,
                'current_environment' => $this->getCurrentEnvironment(),
                'production_readiness' => $this->getProductionReadiness(),
                'launch_checklist' => $this->getLaunchChecklist()
            ];

            return new Response(
                $this->renderTemplate('production_deployment/dashboard.twig', $data),
                200,
                ['Content-Type' => 'text/html']
            );

        } catch (\Exception $e) {
            $this->logger->error('Production deployment dashboard error: ' . $e->getMessage());
            return new Response('Error loading deployment dashboard', 500);
        }
    }

    /**
     * Start production deployment
     */
    public function startDeployment(Request $request): Response
    {
        try {
            $targetEnvironment = $request->get('environment', 'production');
            
            $this->logger->info("Starting production deployment to {$targetEnvironment}");

            // Validate deployment request
            if (!$this->validateDeploymentRequest($targetEnvironment)) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Invalid deployment request'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            // Start deployment
            $deploymentResult = $this->deploymentService->startProductionDeployment($targetEnvironment);

            if ($deploymentResult['overall_status'] === 'success') {
                $this->logger->info("Production deployment started successfully to {$targetEnvironment}");
                
                return new Response(json_encode([
                    'success' => true,
                    'message' => "Deployment to {$targetEnvironment} started successfully",
                    'deployment_id' => $deploymentResult['start_time'],
                    'estimated_duration' => $deploymentResult['duration'] ?? 'Unknown'
                ]), 200, ['Content-Type' => 'application/json']);
            } else {
                $this->logger->error("Production deployment failed to {$targetEnvironment}");
                
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Deployment failed',
                    'details' => $deploymentResult
                ]), 500, ['Content-Type' => 'application/json']);
            }

        } catch (\Exception $e) {
            $this->logger->error('Production deployment start error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to start deployment: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get deployment status
     */
    public function getDeploymentStatus(): Response
    {
        try {
            $deploymentStatus = $this->deploymentService->getDeploymentStatus();
            $isDeploymentInProgress = $this->deploymentService->isDeploymentInProgress();

            $data = [
                'deployment_status' => $deploymentStatus,
                'is_deployment_in_progress' => $isDeploymentInProgress,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get deployment status error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get deployment status'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Rollback deployment
     */
    public function rollbackDeployment(Request $request): Response
    {
        try {
            $targetEnvironment = $request->get('environment', 'production');
            
            $this->logger->info("Rolling back deployment for {$targetEnvironment}");

            $rollbackResult = $this->deploymentService->rollbackDeployment($targetEnvironment);

            if ($rollbackResult['success']) {
                $this->logger->info("Deployment rollback completed successfully for {$targetEnvironment}");
                
                return new Response(json_encode([
                    'success' => true,
                    'message' => "Deployment rollback completed for {$targetEnvironment}",
                    'rollback_details' => $rollbackResult
                ]), 200, ['Content-Type' => 'application/json']);
            } else {
                $this->logger->error("Deployment rollback failed for {$targetEnvironment}");
                
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Rollback failed',
                    'details' => $rollbackResult
                ]), 500, ['Content-Type' => 'application/json']);
            }

        } catch (\Exception $e) {
            $this->logger->error('Deployment rollback error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to rollback deployment: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get deployment history
     */
    public function getDeploymentHistory(): Response
    {
        try {
            $deploymentHistory = $this->deploymentService->getDeploymentHistory();

            return new Response(json_encode([
                'success' => true,
                'deployment_history' => $deploymentHistory
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get deployment history error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get deployment history'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get production readiness status
     */
    public function getProductionReadiness(): Response
    {
        try {
            $productionReadiness = $this->getProductionReadiness();

            return new Response(json_encode([
                'success' => true,
                'production_readiness' => $productionReadiness
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get production readiness error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get production readiness'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get launch checklist
     */
    public function getLaunchChecklist(): Response
    {
        try {
            $launchChecklist = $this->getLaunchChecklist();

            return new Response(json_encode([
                'success' => true,
                'launch_checklist' => $launchChecklist
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get launch checklist error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get launch checklist'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get training modules
     */
    public function getTrainingModules(Request $request): Response
    {
        try {
            $userLevel = $request->get('level', 'beginner');
            
            $trainingModules = $this->trainingService->getTrainingModules($userLevel);

            return new Response(json_encode([
                'success' => true,
                'training_modules' => $trainingModules,
                'user_level' => $userLevel
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get training modules error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get training modules'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get documentation
     */
    public function getDocumentation(Request $request): Response
    {
        try {
            $category = $request->get('category', 'user_guides');
            
            $documentation = $this->trainingService->getDocumentation($category);

            return new Response(json_encode([
                'success' => true,
                'documentation' => $documentation,
                'category' => $category
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get documentation error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get documentation'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Search documentation
     */
    public function searchDocumentation(Request $request): Response
    {
        try {
            $query = $request->get('query', '');
            $filters = $request->get('filters', []);
            
            if (empty($query)) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Search query is required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $searchResults = $this->trainingService->searchDocumentation($query, $filters);

            return new Response(json_encode([
                'success' => true,
                'search_results' => $searchResults,
                'query' => $query,
                'results_count' => count($searchResults)
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Search documentation error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to search documentation'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get video tutorials
     */
    public function getVideoTutorials(): Response
    {
        try {
            $videoTutorials = $this->trainingService->getVideoTutorials();

            return new Response(json_encode([
                'success' => true,
                'video_tutorials' => $videoTutorials
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get video tutorials error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get video tutorials'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get user training progress
     */
    public function getUserTrainingProgress(Request $request): Response
    {
        try {
            $userId = (int) $request->get('user_id', 0);
            
            if ($userId <= 0) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Valid user ID is required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $userProgress = $this->trainingService->getUserTrainingProgress($userId);

            return new Response(json_encode([
                'success' => true,
                'user_progress' => $userProgress,
                'user_id' => $userId
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get user training progress error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get user training progress'
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Start training module
     */
    public function startTrainingModule(Request $request): Response
    {
        try {
            $moduleId = $request->get('module_id', '');
            $userId = (int) $request->get('user_id', 0);
            
            if (empty($moduleId) || $userId <= 0) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Module ID and user ID are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $moduleResult = $this->trainingService->startTrainingModule($moduleId, $userId);

            if ($moduleResult['success']) {
                return new Response(json_encode([
                    'success' => true,
                    'module' => $moduleResult['module'],
                    'content' => $moduleResult['content'],
                    'start_time' => $moduleResult['start_time']
                ]), 200, ['Content-Type' => 'application/json']);
            } else {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Failed to start training module'
                ]), 500, ['Content-Type' => 'application/json']);
            }

        } catch (\Exception $e) {
            $this->logger->error('Start training module error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to start training module: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Complete training module
     */
    public function completeTrainingModule(Request $request): Response
    {
        try {
            $moduleId = $request->get('module_id', '');
            $userId = (int) $request->get('user_id', 0);
            
            if (empty($moduleId) || $userId <= 0) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Module ID and user ID are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $completionResult = $this->trainingService->completeTrainingModule($moduleId, $userId);

            if ($completionResult['success']) {
                return new Response(json_encode([
                    'success' => true,
                    'message' => 'Training module completed successfully',
                    'certificate' => $completionResult['certificate'],
                    'completion_time' => $completionResult['completion_time']
                ]), 200, ['Content-Type' => 'application/json']);
            } else {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Failed to complete training module'
                ]), 500, ['Content-Type' => 'application/json']);
            }

        } catch (\Exception $e) {
            $this->logger->error('Complete training module error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to complete training module: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get training certificate
     */
    public function getTrainingCertificate(Request $request): Response
    {
        try {
            $moduleId = $request->get('module_id', '');
            $userId = (int) $request->get('user_id', 0);
            
            if (empty($moduleId) || $userId <= 0) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => 'Module ID and user ID are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $certificate = $this->trainingService->getTrainingCertificate($moduleId, $userId);

            if (isset($certificate['success']) && !$certificate['success']) {
                return new Response(json_encode([
                    'success' => false,
                    'error' => $certificate['error']
                ]), 400, ['Content-Type' => 'application/json']);
            }

            return new Response(json_encode([
                'success' => true,
                'certificate' => $certificate
            ]), 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            $this->logger->error('Get training certificate error: ' . $e->getMessage());
            
            return new Response(json_encode([
                'success' => false,
                'error' => 'Failed to get training certificate: ' . $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    // Helper methods
    private function validateDeploymentRequest(string $targetEnvironment): bool
    {
        $validEnvironments = ['development', 'staging', 'production'];
        return in_array($targetEnvironment, $validEnvironments);
    }

    private function getCurrentEnvironment(): string
    {
        // Mock current environment detection
        return 'development';
    }

    private function getProductionReadiness(): array
    {
        // Mock production readiness data
        return [
            'system_health' => 95.0,
            'performance_ready' => true,
            'security_ready' => true,
            'scalability_ready' => true,
            'overall_ready' => true
        ];
    }

    private function getLaunchChecklist(): array
    {
        // Mock launch checklist data
        return [
            'technical' => [
                'code_deployment' => 'Ready',
                'database_migration' => 'Ready',
                'configuration_update' => 'Ready',
                'cache_clear' => 'Ready'
            ],
            'business' => [
                'user_communication' => 'Ready',
                'support_preparation' => 'Ready',
                'monitoring_setup' => 'Ready',
                'rollback_plan' => 'Ready'
            ],
            'user_experience' => [
                'user_onboarding' => 'Ready',
                'help_documentation' => 'Ready',
                'support_system' => 'Ready',
                'feedback_collection' => 'Ready'
            ],
            'compliance' => [
                'data_protection' => 'Compliant',
                'access_control' => 'Compliant',
                'audit_logging' => 'Compliant',
                'security_standards' => 'Compliant'
            ]
        ];
    }

    private function renderTemplate(string $template, array $data): string
    {
        // Mock template rendering for now
        return json_encode($data);
    }
} 
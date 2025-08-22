<?php

declare(strict_types=1);

/**
 * Production Deployment Routes
 * Routes for the production deployment and launch system
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

use IslamWiki\Extensions\IqraSearchExtension\Controllers\ProductionDeploymentController;

// Production Deployment Routes
$router->group('/admin/production-deployment', function($router) {
    
    // Dashboard
    $router->get('/', [ProductionDeploymentController::class, 'dashboard']);
    
    // Deployment actions
    $router->post('/start', [ProductionDeploymentController::class, 'startDeployment']);
    $router->post('/rollback', [ProductionDeploymentController::class, 'rollbackDeployment']);
    
    // Status and information
    $router->get('/status', [ProductionDeploymentController::class, 'getDeploymentStatus']);
    $router->get('/history', [ProductionDeploymentController::class, 'getDeploymentHistory']);
    $router->get('/readiness', [ProductionDeploymentController::class, 'getProductionReadiness']);
    $router->get('/checklist', [ProductionDeploymentController::class, 'getLaunchChecklist']);
    
    // Training and documentation
    $router->get('/training', [ProductionDeploymentController::class, 'getTrainingModules']);
    $router->get('/documentation', [ProductionDeploymentController::class, 'getDocumentation']);
    $router->get('/search-docs', [ProductionDeploymentController::class, 'searchDocumentation']);
    $router->get('/video-tutorials', [ProductionDeploymentController::class, 'getVideoTutorials']);
    
    // User progress
    $router->get('/user-progress', [ProductionDeploymentController::class, 'getUserTrainingProgress']);
    $router->post('/start-module', [ProductionDeploymentController::class, 'startTrainingModule']);
    $router->post('/complete-module', [ProductionDeploymentController::class, 'completeTrainingModule']);
    $router->get('/certificate', [ProductionDeploymentController::class, 'getTrainingCertificate']);
    
}, ['middleware' => 'admin']); 
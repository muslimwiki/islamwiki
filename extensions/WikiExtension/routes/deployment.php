<?php

declare(strict_types=1);

/**
 * Deployment routes for WikiExtension Phase 8
 * 
 * @package IslamWiki\Extensions\WikiExtension\Routes
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

use IslamWiki\Extensions\WikiExtension\Controllers\DeploymentController;

// Deployment dashboard and monitoring routes
$app->get('/wiki/deployment', [DeploymentController::class, 'dashboard']);
$app->get('/wiki/deployment/performance', [DeploymentController::class, 'performance']);
$app->get('/wiki/deployment/support', [DeploymentController::class, 'support']);
$app->get('/wiki/deployment/metrics', [DeploymentController::class, 'metrics']);
$app->get('/wiki/deployment/status', [DeploymentController::class, 'status']);
$app->get('/wiki/deployment/health', [DeploymentController::class, 'health']);

// Deployment action routes
$app->post('/wiki/deployment/staging', [DeploymentController::class, 'deployToStaging']);
$app->post('/wiki/deployment/production', [DeploymentController::class, 'deployToProduction']);
$app->post('/wiki/deployment/rollback', [DeploymentController::class, 'rollback']);

// API routes for deployment monitoring
$app->get('/api/wiki/deployment/status', [DeploymentController::class, 'status']);
$app->get('/api/wiki/deployment/health', [DeploymentController::class, 'health']);
$app->get('/api/wiki/deployment/performance', [DeploymentController::class, 'performance']);
$app->get('/api/wiki/deployment/support', [DeploymentController::class, 'support']);
$app->get('/api/wiki/deployment/metrics', [DeploymentController::class, 'metrics']); 
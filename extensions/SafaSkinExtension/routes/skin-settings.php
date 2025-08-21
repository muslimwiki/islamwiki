<?php

declare(strict_types=1);

/**
 * Skin Settings Routes
 * 
 * Routes for the enhanced skin settings interface.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Routes
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

use IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController;

// Skin Settings Routes
$router->group(['prefix' => '/admin/skins', 'middleware' => ['auth', 'admin']], function ($router) {
    
    // Main settings page
    $router->get('/', [SkinSettingsController::class, 'index'])
           ->name('admin.skins.index');
    
    // Skin gallery
    $router->get('/gallery', [SkinSettingsController::class, 'gallery'])
           ->name('admin.skins.gallery');
    
    // Skin customization
    $router->get('/customize', [SkinSettingsController::class, 'customize'])
           ->name('admin.skins.customize');
    
    // Skin switching
    $router->post('/switch', [SkinSettingsController::class, 'switchSkin'])
           ->name('admin.skins.switch');
    
    // Live preview
    $router->get('/preview', [SkinSettingsController::class, 'preview'])
           ->name('admin.skins.preview');
    
    // Save customization
    $router->post('/save-customization', [SkinSettingsController::class, 'saveCustomization'])
           ->name('admin.skins.save-customization');
});

// Public skin preview routes (for non-admin users)
$router->group(['prefix' => '/skins', 'middleware' => ['auth']], function ($router) {
    
    // Public skin preview
    $router->get('/preview/{skin}', [SkinSettingsController::class, 'publicPreview'])
           ->name('skins.public-preview');
    
    // Skin information
    $router->get('/{skin}/info', [SkinSettingsController::class, 'skinInfo'])
           ->name('skins.info');
});

// API routes for AJAX calls
$router->group(['prefix' => '/api/skins', 'middleware' => ['auth', 'api']], function ($router) {
    
    // Get available skins
    $router->get('/', [SkinSettingsController::class, 'getAvailableSkins'])
           ->name('api.skins.list');
    
    // Get skin details
    $router->get('/{skin}', [SkinSettingsController::class, 'getSkinDetails'])
           ->name('api.skins.details');
    
    // Update skin settings
    $router->put('/{skin}/settings', [SkinSettingsController::class, 'updateSkinSettings'])
           ->name('api.skins.update-settings');
    
    // Get skin assets
    $router->get('/{skin}/assets', [SkinSettingsController::class, 'getSkinAssets'])
           ->name('api.skins.assets');
    
    // Validate skin
    $router->post('/{skin}/validate', [SkinSettingsController::class, 'validateSkin'])
           ->name('api.skins.validate');
}); 
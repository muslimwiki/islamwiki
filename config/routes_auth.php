<?php

declare(strict_types=1);

/**
 * Authentication and User Management Routes
 * 
 * Routes for user authentication, user settings, and admin functionality.
 * 
 * @package IslamWiki\Config
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

use IslamWiki\Core\Auth\AmanAuthenticationController;
use IslamWiki\Core\Auth\UserSettingsController;
use IslamWiki\Core\Admin\AdminController;
use IslamWiki\Extensions\SafaSkinExtension\Controllers\SkinSettingsController;

// Authentication Routes (Public)
$router->group(['prefix' => '/auth'], function ($router) {
    
    // Login routes
    $router->get('/login', [AmanAuthenticationController::class, 'showLogin'])
           ->name('auth.login');
    $router->post('/login', [AmanAuthenticationController::class, 'login'])
           ->name('auth.login.post');
    
    // Registration routes
    $router->get('/register', [AmanAuthenticationController::class, 'showRegister'])
           ->name('auth.register');
    $router->post('/register', [AmanAuthenticationController::class, 'register'])
           ->name('auth.register.post');
    
    // Logout route
    $router->post('/logout', [AmanAuthenticationController::class, 'logout'])
           ->name('auth.logout');
    
    // Password reset routes
    $router->get('/forgot-password', [AmanAuthenticationController::class, 'showForgotPassword'])
           ->name('auth.forgot-password');
    $router->post('/forgot-password', [AmanAuthenticationController::class, 'forgotPassword'])
           ->name('auth.forgot-password.post');
    
    $router->get('/reset-password/{token}', [AmanAuthenticationController::class, 'showResetPassword'])
           ->name('auth.reset-password');
    $router->post('/reset-password', [AmanAuthenticationController::class, 'resetPassword'])
           ->name('auth.reset-password.post');
});

// User Profile Routes (Authenticated Users)
$router->group(['prefix' => '/profile', 'middleware' => ['auth']], function ($router) {
    
    $router->get('/', [AmanAuthenticationController::class, 'showProfile'])
           ->name('auth.profile');
    $router->post('/update', [AmanAuthenticationController::class, 'updateProfile'])
           ->name('auth.profile.update');
    $router->post('/change-password', [AmanAuthenticationController::class, 'changePassword'])
           ->name('auth.profile.change-password');
});

// User Settings Routes (Authenticated Users)
$router->group(['prefix' => '/user/settings', 'middleware' => ['auth']], function ($router) {
    
    // Main settings page
    $router->get('/', [UserSettingsController::class, 'index'])
           ->name('user.settings.index');
    
    // Profile settings
    $router->get('/profile', [UserSettingsController::class, 'profile'])
           ->name('user.settings.profile');
    
    // Appearance settings (including skin selection)
    $router->get('/appearance', [UserSettingsController::class, 'appearance'])
           ->name('user.settings.appearance');
    
    // Notification settings
    $router->get('/notifications', [UserSettingsController::class, 'notifications'])
           ->name('user.settings.notifications');
    
    // Privacy settings
    $router->get('/privacy', [UserSettingsController::class, 'privacy'])
           ->name('user.settings.privacy');
    
    // API routes for AJAX updates
    $router->post('/preferences', [UserSettingsController::class, 'updatePreferences'])
           ->name('user.settings.preferences');
    
    $router->post('/skin', [UserSettingsController::class, 'updateSkin'])
           ->name('user.settings.skin');
    
    $router->post('/notifications', [UserSettingsController::class, 'updateNotifications'])
           ->name('user.settings.notifications.update');
    
    $router->post('/privacy', [UserSettingsController::class, 'updatePrivacy'])
           ->name('user.settings.privacy.update');
    
    // Data export and account deletion
    $router->get('/export-data', [UserSettingsController::class, 'exportData'])
           ->name('user.settings.export-data');
    
    $router->post('/delete-account', [UserSettingsController::class, 'deleteAccount'])
           ->name('user.settings.delete-account');
});

// Admin Routes (Admin Users Only)
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($router) {
    
    // Admin dashboard
    $router->get('/', [AdminController::class, 'dashboard'])
           ->name('admin.dashboard');
    
    // User management
    $router->get('/users', [AdminController::class, 'users'])
           ->name('admin.users');
    
    $router->post('/users', [AdminController::class, 'createUser'])
           ->name('admin.users.create');
    
    $router->put('/users/{id}', [AdminController::class, 'updateUser'])
           ->name('admin.users.update');
    
    $router->delete('/users/{id}', [AdminController::class, 'deleteUser'])
           ->name('admin.users.delete');
    
    // Role management
    $router->get('/roles', [AdminController::class, 'roles'])
           ->name('admin.roles');
    
    $router->post('/roles', [AdminController::class, 'createRole'])
           ->name('admin.roles.create');
    
    $router->put('/roles/{id}', [AdminController::class, 'updateRole'])
           ->name('admin.roles.update');
    
    $router->delete('/roles/{id}', [AdminController::class, 'deleteRole'])
           ->name('admin.roles.delete');
    
    // System settings
    $router->get('/settings', [AdminController::class, 'settings'])
           ->name('admin.settings');
    
    // System logs
    $router->get('/logs', [AdminController::class, 'logs'])
           ->name('admin.logs');
    
    // Skin management (Admin access to SafaSkinExtension)
    $router->get('/skins', [AdminController::class, 'skins'])
           ->name('admin.skins');
});

// SafaSkinExtension Admin Routes (Admin Users Only)
$router->group(['prefix' => '/admin/skins', 'middleware' => ['auth', 'admin']], function ($router) {
    
    // Main skin settings page
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

// API routes for AJAX calls (Authenticated Users)
$router->group(['prefix' => '/api', 'middleware' => ['auth']], function ($router) {
    
    // User settings API
    $router->group(['prefix' => '/user'], function ($router) {
        
        $router->get('/preferences', [UserSettingsController::class, 'getPreferences'])
               ->name('api.user.preferences');
        
        $router->put('/preferences', [UserSettingsController::class, 'updatePreferences'])
               ->name('api.user.preferences.update');
        
        $router->get('/skin-preferences', [UserSettingsController::class, 'getSkinPreferences'])
               ->name('api.user.skin-preferences');
        
        $router->put('/skin-preferences', [UserSettingsController::class, 'updateSkin'])
               ->name('api.user.skin-preferences.update');
    });
    
    // Skins API
    $router->group(['prefix' => '/skins'], function ($router) {
        
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
});

// Admin API routes (Admin Users Only)
$router->group(['prefix' => '/api/admin', 'middleware' => ['auth', 'admin']], function ($router) {
    
    // User management API
    $router->group(['prefix' => '/users'], function ($router) {
        
        $router->get('/', [AdminController::class, 'getUsers'])
               ->name('api.admin.users.list');
        
        $router->get('/{id}', [AdminController::class, 'getUser'])
               ->name('api.admin.users.show');
        
        $router->post('/', [AdminController::class, 'createUser'])
               ->name('api.admin.users.create');
        
        $router->put('/{id}', [AdminController::class, 'updateUser'])
               ->name('api.admin.users.update');
        
        $router->delete('/{id}', [AdminController::class, 'deleteUser'])
               ->name('api.admin.users.delete');
    });
    
    // Role management API
    $router->group(['prefix' => '/roles'], function ($router) {
        
        $router->get('/', [AdminController::class, 'getRoles'])
               ->name('api.admin.roles.list');
        
        $router->get('/{id}', [AdminController::class, 'getRole'])
               ->name('api.admin.roles.show');
        
        $router->post('/', [AdminController::class, 'createRole'])
               ->name('api.admin.roles.create');
        
        $router->put('/{id}', [AdminController::class, 'updateRole'])
               ->name('api.admin.roles.update');
        
        $router->delete('/{id}', [AdminController::class, 'deleteRole'])
               ->name('api.admin.roles.delete');
    });
    
    // System API
    $router->group(['prefix' => '/system'], function ($router) {
        
        $router->get('/stats', [AdminController::class, 'getSystemStats'])
               ->name('api.admin.system.stats');
        
        $router->get('/health', [AdminController::class, 'getSystemHealth'])
               ->name('api.admin.system.health');
        
        $router->get('/logs', [AdminController::class, 'getSystemLogs'])
               ->name('api.admin.system.logs');
    });
});

// Middleware definitions
$router->middleware('auth', function ($request, $next) {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        return redirect('/auth/login');
    }
    return $next($request);
});

$router->middleware('admin', function ($request, $next) {
    // Check if user has admin role
    if (!isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
        return redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
    }
    return $next($request);
}); 
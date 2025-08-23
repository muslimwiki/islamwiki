<?php

/**
 * AmanSecurity Service Provider
 *
 * Registers the AmanSecurity extension services with the container.
 *
 * @package IslamWiki\Extensions\AmanSecurity\Providers
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\AmanSecurity\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Extensions\AmanSecurity\AmanSecurity;
use IslamWiki\Extensions\AmanSecurity\Services\UserManagementService;
use IslamWiki\Extensions\AmanSecurity\Services\SecurityMonitoringService;

class AmanSecurityServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(AsasContainer $container): void
    {
        // Register the AmanSecurity authentication manager
        $container->set(AmanSecurity::class, function (AsasContainer $container) {
            $session = $container->get('session');
            $db = $container->get('db');
            
            // Get extension configuration
            $config = $this->getExtensionConfig();
            
            return new AmanSecurity($session, $db, $config);
        });

        // Register User Management Service
        $container->set(UserManagementService::class, function (AsasContainer $container) {
            $db = $container->get('db');
            $config = $this->getExtensionConfig();
            
            return new UserManagementService($db, $config);
        });

        // Register Security Monitoring Service
        $container->set(SecurityMonitoringService::class, function (AsasContainer $container) {
            $db = $container->get('db');
            $config = $this->getExtensionConfig();
            
            return new SecurityMonitoringService($db, $config);
        });

        // Register 'auth' alias to point to AmanSecurity
        $container->alias('auth', AmanSecurity::class);
        
        // Register 'aman.security' alias for extension-specific access
        $container->alias('aman.security', AmanSecurity::class);
        
        // Register service aliases
        $container->alias('aman.user.management', UserManagementService::class);
        $container->alias('aman.security.monitoring', SecurityMonitoringService::class);
    }

    /**
     * Boot the service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Any boot-time initialization can go here
        // For now, we don't need any boot-time setup
    }

    /**
     * Get extension configuration.
     */
    private function getExtensionConfig(): array
    {
        $configPath = __DIR__ . '/../config/aman-security.php';
        
        if (file_exists($configPath)) {
            return require $configPath;
        }
        
        // Return default configuration
        return [
            'session_timeout' => 3600,
            'max_login_attempts' => 5,
            'password_min_length' => 8,
            'require_email_verification' => true,
            'enable_two_factor' => false,
            'login_attempts_window' => 900, // 15 minutes
            'password_history_count' => 5,
            'session_regeneration' => true,
            'csrf_protection' => true,
            'rate_limiting' => true,
            'security_monitoring' => true,
            'user_management' => true,
            'threat_detection' => true,
            'ip_blocking' => true,
            'activity_logging' => true
        ];
    }
} 
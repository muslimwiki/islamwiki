<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Asas;
use IslamWiki\Core\Session\Wisal;

/**
 * Session Service Provider
 * 
 * Registers session management services in the container.
 */
class SessionServiceProvider
{
    /**
     * Register the session services.
     */
    public function register(Asas $container): void
    {
        // Register session manager
        $container->bind(Wisal::class, function() {
            $config = [
                'name' => getenv('SESSION_NAME') ?: 'islamwiki_session',
                'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
                'path' => getenv('SESSION_PATH') ?: '/',
                'secure' => getenv('SESSION_SECURE') !== 'false',
                'http_only' => getenv('SESSION_HTTP_ONLY') !== 'false',
                'same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',
            ];
            
            return new Wisal($config);
        });
        
        // Register session manager as singleton
        $container->singleton('session', function($container) {
            return $container->get(Wisal::class);
        });
    }
    
    /**
     * Boot the session services.
     */
    public function boot(Asas $container): void
    {
        // Start session early for web requests to ensure consistency
        if (php_sapi_name() !== 'cli') {
            // Set session save path to local storage
            $sessionPath = __DIR__ . '/../../storage/sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0777, true);
            }
            session_save_path($sessionPath);
            
            // Set session name before any session operations
            session_name('islamwiki_session');
            
            // Start session manually to ensure proper initialization
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Get the session manager and configure it
            $session = $container->get('session');
            
            // Force session write to ensure cookie is set
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
                session_start();
            }
        }
    }
} 
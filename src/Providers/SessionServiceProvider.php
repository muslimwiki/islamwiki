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

use IslamWiki\Core\Container;
use IslamWiki\Core\Session\SessionManager;

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
    public function register(Container $container): void
    {
        // Register session manager
        $container->bind(SessionManager::class, function() {
            $config = [
                'name' => getenv('SESSION_NAME') ?: 'islamwiki_session',
                'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
                'path' => getenv('SESSION_PATH') ?: '/',
                'secure' => getenv('SESSION_SECURE') === 'true',
                'http_only' => getenv('SESSION_HTTP_ONLY') !== 'false',
                'same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',
            ];
            
            return new SessionManager($config);
        });
        
        // Register session manager as singleton
        $container->singleton('session', function($container) {
            return $container->get(SessionManager::class);
        });
    }
    
    /**
     * Boot the session services.
     */
    public function boot(Container $container): void
    {
        // Start session early for web requests to ensure consistency
        if (php_sapi_name() !== 'cli') {
            $session = $container->get('session');
            $session->start();
        }
    }
} 
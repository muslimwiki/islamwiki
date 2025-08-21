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

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Session\WisalSession;

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
    public function register(AsasContainer $container): void
    {
        // Register session manager as singleton
        $container->set('session', function () use ($container) {
            $config = [
                'name' => getenv('SESSION_NAME') ?: 'islamwiki_session',
                'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
                'path' => getenv('SESSION_PATH') ?: '/',
                'secure' => getenv('SESSION_SECURE') !== 'false',
                'http_only' => getenv('SESSION_HTTP_ONLY') !== 'false',
                'same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',
            ];

            $logger = $container->get('logger');
            return new WisalSession($logger, $config);
        });
        
        // Also register with the class name for type-hinted injection
        $container->set(WisalSession::class, function () use ($container) {
            $config = [
                'name' => getenv('SESSION_NAME') ?: 'islamwiki_session',
                'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
                'path' => getenv('SESSION_PATH') ?: '/',
                'secure' => getenv('SESSION_SECURE') !== 'false',
                'http_only' => getenv('SESSION_HTTP_ONLY') !== 'false',
                'same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',
            ];

            $logger = $container->get('logger');
            return new WisalSession($logger, $config);
        });
    }

    /**
     * Boot the session services.
     */
    public function boot(AsasContainer $container): void
    {
        // Start the session when the provider boots, but only if not already started
        try {
            $session = $container->get('session');
            
            // Check if session is already started to avoid conflicts
            if (session_status() === PHP_SESSION_NONE) {
                $session->start();
                error_log("SessionServiceProvider: Session started successfully");
            } else {
                error_log("SessionServiceProvider: Session already active, skipping start");
            }
        } catch (\Exception $e) {
            error_log("SessionServiceProvider: Error starting session: " . $e->getMessage());
        }
    }
}

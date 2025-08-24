<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
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

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Session;\Session

/**
 * Authentication Middleware
 *
 * Protects routes that require authentication.
 */
class AuthenticationMiddleware
{
    /**
     * @var Session Session manager instance
     */
    private Session $session;

    /**
     * Create a new authentication middleware instance.
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, callable $next): Response
    {
        // Check if user is authenticated
        if (!$this->session->isLoggedIn()) {
            // Redirect to login page with return URL
            $returnUrl = urlencode($request->getUri()->getPath());
            return new Response(
                status: 302,
                headers: ['Location' => "/login?redirect={$returnUrl}"],
                body: ''
            );
        }

        // User is authenticated, continue to next middleware/controller
        return $next($request);
    }
}

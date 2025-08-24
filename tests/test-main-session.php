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

require_once __DIR__ . '/../vendor/autoload.php';

// Simulate the main application flow
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');

echo "<h1>🔍 Main Application Session Test</h1>";

// Get the session from the container
$container = $app->getContainer();
$session = $container->get('session');

echo "<h2>Session Information</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";

echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session Manager Methods</h2>";
echo "<p><strong>isLoggedIn:</strong> " . ($session->isLoggedIn() ? 'true' : 'false') . "</p>";
echo "<p><strong>getUserId:</strong> " . ($session->getUserId() ?? 'null') . "</p>";
echo "<p><strong>getUsername:</strong> " . ($session->getUsername() ?? 'null') . "</p>";
echo "<p><strong>isAdmin:</strong> " . ($session->isAdmin() ? 'true' : 'false') . "</p>";

echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Test setting a value
$session->put('test_main_value', 'test_main_data');
echo "<h2>After Setting Test Value</h2>";
echo "<p><strong>Test Value:</strong> " . $session->get('test_main_value') . "</p>";
echo "<p><strong>Session Data:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test CSRF token
$csrfToken = $session->getCsrfToken();
echo "<h2>CSRF Token Test</h2>";
echo "<p><strong>CSRF Token:</strong> $csrfToken</p>";
echo "<p><strong>Token Valid:</strong> " . ($session->verifyCsrfToken($csrfToken) ? 'Yes' : 'No') . "</p>";

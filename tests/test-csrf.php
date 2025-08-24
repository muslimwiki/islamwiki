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

// Initialize the application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');

echo "<h1>🔍 CSRF Token Debug</h1>";

// Get the session from the container
$container = $app->getContainer();
$session = $container->get('session');
$session->start();

echo "<h2>Session Information</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";

echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>CSRF Token Test</h2>";

// Test 1: Generate a CSRF token
$token1 = $session->getCsrfToken();
echo "<p><strong>First CSRF Token:</strong> $token1</p>";

// Test 2: Get the same token again
$token2 = $session->getCsrfToken();
echo "<p><strong>Second CSRF Token:</strong> $token2</p>";

// Test 3: Verify the tokens match
$tokensMatch = hash_equals($token1, $token2);
echo "<p><strong>Tokens Match:</strong> " . ($tokensMatch ? 'Yes' : 'No') . "</p>";

// Test 4: Verify the token
$isValid = $session->verifyCsrfToken($token1);
echo "<p><strong>Token Valid:</strong> " . ($isValid ? 'Yes' : 'No') . "</p>";

echo "<h2>Updated Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookies</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

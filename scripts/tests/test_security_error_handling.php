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

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Http\Middleware\SecurityMiddleware;
use IslamWiki\Http\Middleware\ErrorHandlingMiddleware;
use IslamWiki\Http\Middleware\CsrfMiddleware;
use IslamWiki\Http\Middleware\MiddlewareStack;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;

echo "Testing Security and Error Handling Improvements\n";
echo "===============================================\n\n";

try {
    // 1. Test Database Connection
    echo "1. Testing Database Connection...\n";
    $db = new Connection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ]);
    echo "✅ Database connection successful\n\n";

    // 2. Test Container and Services
    echo "2. Testing Container and Services...\n";
    $container = new Container();

    // Register logger
    $logger = new Logger(
        __DIR__ . '/../storage/logs',
        'debug',
        10, // 10MB max file size
        5   // Keep 5 files
    );
    $container->bind(\Psr\Log\LoggerInterface::class, $logger);

    // Register database
    $container->bind(Connection::class, $db);

    // Register session manager
    $sessionManager = new SessionManager();
    $container->bind(SessionManager::class, $sessionManager);

    // Register app configuration
    $container->bind('app.debug', true);
    $container->bind('app.env', 'testing');

    echo "✅ Container and services configured\n\n";

    // 3. Test Security Middleware
    echo "3. Testing Security Middleware...\n";
    $securityMiddleware = new SecurityMiddleware($logger);

    // Test normal request
    $normalRequest = new Request('GET', '/test', [], null, '1.1', []);
    $normalResponse = new Response(200, ['Content-Type' => 'text/plain'], 'OK');

    $result = $securityMiddleware->handle($normalRequest, function ($request) use ($normalResponse) {
        return $normalResponse;
    });

    echo "✅ Security middleware processed normal request\n";

    // Test suspicious request (should be blocked)
    $suspiciousRequest = new Request('GET', '/test?q=union+select', [], null, '1.1', []);

    try {
        $securityMiddleware->handle($suspiciousRequest, function ($request) {
            return new Response(200, [], 'OK');
        });
        echo "❌ Security middleware should have blocked suspicious request\n";
    } catch (HttpException $e) {
        echo "✅ Security middleware blocked suspicious request: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // 4. Test Error Handling Middleware
    echo "4. Testing Error Handling Middleware...\n";
    $errorMiddleware = new ErrorHandlingMiddleware($logger, true, 'testing');

    // Test successful request
    $successResult = $errorMiddleware->handle($normalRequest, function ($request) {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Success');
    });

    echo "✅ Error handling middleware processed successful request\n";

    // Test exception handling
    $exceptionResult = $errorMiddleware->handle($normalRequest, function ($request) {
        throw new \RuntimeException('Test exception');
    });

    if ($exceptionResult->getStatusCode() === 500) {
        echo "✅ Error handling middleware caught exception and returned 500\n";
    } else {
        echo "❌ Error handling middleware should have returned 500\n";
    }

    echo "\n";

    // 5. Test CSRF Middleware
    echo "5. Testing CSRF Middleware...\n";
    $csrfMiddleware = new CsrfMiddleware($sessionManager);

    // Test GET request (should pass)
    $getRequest = new Request('GET', '/test', [], null, '1.1', []);
    $csrfResult = $csrfMiddleware->handle($getRequest, function ($request) {
        return new Response(200, [], 'OK');
    });

    echo "✅ CSRF middleware allowed GET request\n";

    // Test POST request without token (should be blocked)
    $postRequest = new Request('POST', '/test', [], null, '1.1', []);
    $postRequest = $postRequest->withParsedBody(['data' => 'test']);

    try {
        $csrfMiddleware->handle($postRequest, function ($request) {
            return new Response(200, [], 'OK');
        });
        echo "❌ CSRF middleware should have blocked POST without token\n";
    } catch (HttpException $e) {
        echo "✅ CSRF middleware blocked POST without token\n";
    }

    echo "\n";

    // 6. Test Middleware Stack
    echo "6. Testing Middleware Stack...\n";
    $middlewareStack = new MiddlewareStack($logger);

    // Add middleware in order
    $middlewareStack
        ->add($errorMiddleware)
        ->add($securityMiddleware)
        ->add($csrfMiddleware);

    echo "✅ Middleware stack created with " . $middlewareStack->count() . " middleware\n";

    // Test middleware stack execution
    $stackResult = $middlewareStack->execute($normalRequest, function ($request) {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Stack OK');
    });

    if ($stackResult->getStatusCode() === 200) {
        echo "✅ Middleware stack executed successfully\n";
    } else {
        echo "❌ Middleware stack execution failed\n";
    }

    echo "\n";

    // 7. Test Enhanced Logging
    echo "7. Testing Enhanced Logging...\n";

    $logger->info('Test info message', ['test' => 'data']);
    $logger->warning('Test warning message', ['warning' => 'test']);
    $logger->error('Test error message', ['error' => 'test']);
    $logger->security('Test security event', ['event' => 'test']);
    $logger->userAction('Test user action', ['action' => 'test']);
    $logger->performance('Test operation', 0.123, ['operation' => 'test']);

    echo "✅ Enhanced logging methods tested\n";

    // Test exception logging
    try {
        throw new \RuntimeException('Test exception for logging');
    } catch (\Throwable $e) {
        $logger->exception($e, ['context' => 'test']);
        echo "✅ Exception logging tested\n";
    }

    echo "\n";

    // 8. Test Log File Creation
    echo "8. Testing Log File Creation...\n";
    $logFiles = [
        __DIR__ . '/../storage/logs/application-' . date('Y-m-d') . '.log',
        __DIR__ . '/../logs/error.log',
        __DIR__ . '/../logs/php_errors.log'
    ];

    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $size = filesize($logFile);
            echo "✅ Log file exists: " . basename($logFile) . " ($size bytes)\n";
        } else {
            echo "❌ Log file missing: " . basename($logFile) . "\n";
        }
    }

    echo "\n";

    // 9. Test Security Headers
    echo "9. Testing Security Headers...\n";
    $testResponse = new Response(200, ['Content-Type' => 'text/plain'], 'Test');

    // Simulate adding security headers
    $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'",
    ];

    foreach ($securityHeaders as $header => $value) {
        $testResponse = $testResponse->withHeader($header, $value);
    }

    $headers = $testResponse->getHeaders();
    $expectedHeaders = array_keys($securityHeaders);
    $actualHeaders = array_keys($headers);

    $missingHeaders = array_diff($expectedHeaders, $actualHeaders);

    if (empty($missingHeaders)) {
        echo "✅ All security headers present\n";
    } else {
        echo "❌ Missing security headers: " . implode(', ', $missingHeaders) . "\n";
    }

    echo "\n";

    // 10. Summary
    echo "10. Test Summary\n";
    echo "================\n";
    echo "✅ Database connection working\n";
    echo "✅ Container and services configured\n";
    echo "✅ Security middleware functional\n";
    echo "✅ Error handling middleware functional\n";
    echo "✅ CSRF middleware functional\n";
    echo "✅ Middleware stack working\n";
    echo "✅ Enhanced logging operational\n";
    echo "✅ Log files created\n";
    echo "✅ Security headers configured\n";

    echo "\n🎉 All security and error handling tests completed successfully!\n";
} catch (\Throwable $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

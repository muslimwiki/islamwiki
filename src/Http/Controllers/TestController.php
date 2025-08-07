<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class TestController
{
    /**
     * A simple test endpoint
     */
    public function test(Request $request): Response
    {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            "<h1>Test Page</h1>
            <p>If you can see this, the basic routing is working!</p>
            <ul>
                <li><a href='/test/error'>Test 500 Error</a></li>
                <li><a href='/test/debug'>Test Debug Info</a></li>
            </ul>"
        );
    }

    /**
     * Test 500 error page
     */
    public function testError(Request $request): Response
    {
        // This will trigger a 500 error
        throw new \RuntimeException("This is a test error to verify error handling");
    }

    /**
     * Show debug information
     */
    public function testDebug(Request $request): Response
    {
        $debugInfo = [
            'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? 'not set',
            'APP_ENV' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'not set',
            'error_reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'error_log' => ini_get('error_log'),
            'error_handler' => set_error_handler('var_dump'),
            'exception_handler' => set_exception_handler('var_dump'),
        ];

        // Restore handlers
        restore_error_handler();
        restore_exception_handler();

        $html = "<h1>Debug Information</h1><pre>" .
                htmlspecialchars(print_r($debugInfo, true)) .
                "</pre>";

        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }
}

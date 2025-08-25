<?php
// Simple working router to test basic functionality
error_log('SIMPLE ROUTER: Starting');

// Simple route handling
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

error_log("SIMPLE ROUTER: URI: $requestUri, Method: $requestMethod");

// Simple routing logic
switch ($requestUri) {
    case '/test-simple':
        echo "Simple test route works!";
        echo "<br>Current time: " . date('Y-m-d H:i:s');
        break;
        
    case '/login':
        echo "Login page works!";
        echo "<br>Current time: " . date('Y-m-d H:i:s');
        break;
        
    case '/admin/dashboard':
        echo "Admin dashboard works!";
        echo "<br>Current time: " . date('Y-m-d H:i:s');
        break;
        
    default:
        echo "Route not found: $requestUri";
        echo "<br>Available routes: /test-simple, /login, /admin/dashboard";
        break;
}

error_log('SIMPLE ROUTER: Completed');
?> 
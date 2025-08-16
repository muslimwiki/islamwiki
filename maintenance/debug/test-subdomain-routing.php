<?php

/**
 * Test Subdomain Routing
 * 
 * Simple test to check if subdomain routing is working.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== Testing Subdomain Routing ===\n\n";

// Test 1: Check current host
echo "1. Current host information:\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "   SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";

// Test 2: Check if we can detect language from host
echo "\n2. Language detection test:\n";
$host = $_SERVER['HTTP_HOST'] ?? 'local.islam.wiki';
$language = 'en'; // default

$supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];
foreach ($supportedLanguages as $code) {
    if (strpos($host, $code . '.') === 0) {
        $language = $code;
        break;
    }
}

echo "   Detected language: {$language}\n";
echo "   Host: {$host}\n";

// Test 3: Check if this is a subdomain request
echo "\n3. Subdomain detection:\n";
$isSubdomain = false;
foreach ($supportedLanguages as $code) {
    if (strpos($host, $code . '.') === 0) {
        $isSubdomain = true;
        break;
    }
}

echo "   Is subdomain request: " . ($isSubdomain ? 'YES' : 'NO') . "\n";

// Test 4: Show what should happen
echo "\n4. Expected behavior:\n";
if ($isSubdomain) {
    echo "   ✅ This is a subdomain request for language: {$language}\n";
    echo "   ✅ The middleware should detect this and set the language\n";
    echo "   ✅ The page should display in {$language}\n";
} else {
    echo "   ℹ️  This is not a subdomain request\n";
    echo "   ℹ️  To test subdomain routing, visit: ar.local.islam.wiki\n";
    echo "   ℹ️  Or: ur.local.islam.wiki, tr.local.islam.wiki, etc.\n";
}

echo "\n=== Test Complete ===\n"; 
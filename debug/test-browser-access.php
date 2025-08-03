<?php
/**
 * Test Browser Access
 * 
 * This script tests the website with browser-like headers to ensure
 * it works correctly in a real browser environment.
 */

echo "<h1>🌐 Browser Access Test</h1>\n";
echo "<p>Testing local.islam.wiki with browser-like headers...</p>\n";

// Test 1: Basic curl without browser headers
echo "<h2>Test 1: Basic curl</h2>\n";
$basic_curl = shell_exec('curl -s -I https://local.islam.wiki 2>/dev/null');
echo "<pre>" . htmlspecialchars($basic_curl) . "</pre>\n";

// Test 2: curl with browser headers but no compression
echo "<h2>Test 2: Browser headers (no compression)</h2>\n";
$browser_curl = shell_exec('curl -H "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:141.0) Gecko/20100101 Firefox/141.0" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8" -H "Accept-Language: en-US,en;q=0.5" -H "DNT: 1" -H "Connection: keep-alive" -H "Upgrade-Insecure-Requests: 1" -s -I https://local.islam.wiki 2>/dev/null');
echo "<pre>" . htmlspecialchars($browser_curl) . "</pre>\n";

// Test 3: curl with full browser headers including compression
echo "<h2>Test 3: Full browser headers (with compression)</h2>\n";
$full_browser_curl = shell_exec('curl -H "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:141.0) Gecko/20100101 Firefox/141.0" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8" -H "Accept-Language: en-US,en;q=0.5" -H "Accept-Encoding: gzip, deflate, br" -H "DNT: 1" -H "Connection: keep-alive" -H "Upgrade-Insecure-Requests: 1" -s -I https://local.islam.wiki 2>/dev/null');
echo "<pre>" . htmlspecialchars($full_browser_curl) . "</pre>\n";

// Test 4: Get actual content with browser headers
echo "<h2>Test 4: Content with browser headers</h2>\n";
$content = shell_exec('curl -H "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:141.0) Gecko/20100101 Firefox/141.0" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8" -H "Accept-Language: en-US,en;q=0.5" -H "DNT: 1" -H "Connection: keep-alive" -H "Upgrade-Insecure-Requests: 1" -s https://local.islam.wiki 2>/dev/null');

if ($content && strpos($content, '<!DOCTYPE html>') !== false) {
    echo "<p style='color: green;'>✅ SUCCESS: Website is accessible with browser headers</p>\n";
    echo "<p>Content length: " . strlen($content) . " characters</p>\n";
    echo "<p>First 200 characters:</p>\n";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>\n";
} else {
    echo "<p style='color: red;'>❌ FAILED: Website not accessible with browser headers</p>\n";
    echo "<p>Response:</p>\n";
    echo "<pre>" . htmlspecialchars($content) . "</pre>\n";
}

// Test 5: Test specific pages
echo "<h2>Test 5: Specific Pages</h2>\n";

$pages = [
    'settings-skin-management.php' => 'Skin Management Settings',
    'test-muslim-skin.php' => 'Muslim Skin Test',
    'index.php' => 'Main Index'
];

foreach ($pages as $page => $description) {
    $page_content = shell_exec("curl -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:141.0) Gecko/20100101 Firefox/141.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' -H 'DNT: 1' -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1' -s -I https://local.islam.wiki/$page 2>/dev/null");
    
    if (strpos($page_content, '200 OK') !== false) {
        echo "<p style='color: green;'>✅ $description ($page): OK</p>\n";
    } else {
        echo "<p style='color: red;'>❌ $description ($page): FAILED</p>\n";
        echo "<pre>" . htmlspecialchars($page_content) . "</pre>\n";
    }
}

echo "<h2>🎯 Summary</h2>\n";
echo "<p><strong>Important Note:</strong> When testing web applications, always test through a real browser or with proper browser headers. The compression headers can cause issues if not configured correctly.</p>\n";
echo "<p><strong>Recommendation:</strong> Test the site in a real browser at <a href='https://local.islam.wiki' target='_blank'>https://local.islam.wiki</a></p>\n";
?> 
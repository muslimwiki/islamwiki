<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Session\Wisal;

// Start session before any output
$session = new Wisal([
    'name' => 'islamwiki_session',
    'lifetime' => 86400,
    'path' => '/',
    'secure' => false,
    'http_only' => true,
    'same_site' => 'Lax'
]);

$session->start();

echo "🔍 Simple Session Test\n";
echo "=====================\n\n";

echo "📊 Session Status:\n";
echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . (session_id() ?: 'Not set') . "\n";
echo "- Session Save Path: " . session_save_path() . "\n";
echo "- SAPI: " . php_sapi_name() . "\n";

echo "\n📋 Session Data:\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "- Session Data: " . print_r($_SESSION, true) . "\n";
} else {
    echo "- No active session\n";
}

echo "\n🧪 Testing Session Operations:\n";
$session->put('test_key', 'test_value_' . time());
echo "- Set test value\n";

$value = $session->get('test_key');
echo "- Retrieved value: " . ($value ?? 'null') . "\n";

echo "\n✅ Simple session test completed\n"; 
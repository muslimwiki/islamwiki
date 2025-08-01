<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

echo "<h1>Session Shared Test</h1>";

// Use the same session configuration as the main app
$sessionName = getenv('SESSION_NAME') ?: 'islamwiki_session';
$sessionLifetime = (int)(getenv('SESSION_LIFETIME') ?: 86400);
$sessionPath = getenv('SESSION_PATH') ?: '/';
$sessionSecure = getenv('SESSION_SECURE') !== 'false';
$sessionHttpOnly = getenv('SESSION_HTTP_ONLY') !== 'false';
$sessionSameSite = getenv('SESSION_SAME_SITE') ?: 'Lax';

echo "<p>Session config:</p>";
echo "<ul>";
echo "<li>Name: $sessionName</li>";
echo "<li>Lifetime: $sessionLifetime</li>";
echo "<li>Path: $sessionPath</li>";
echo "<li>Secure: " . ($sessionSecure ? 'true' : 'false') . "</li>";
echo "<li>HttpOnly: " . ($sessionHttpOnly ? 'true' : 'false') . "</li>";
echo "<li>SameSite: $sessionSameSite</li>";
echo "</ul>";

// Set session configuration
ini_set('session.use_strict_mode', '1');
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', $sessionHttpOnly ? '1' : '0');
ini_set('session.cookie_secure', $sessionSecure ? '1' : '0');
ini_set('session.cookie_samesite', $sessionSameSite);
ini_set('session.cookie_path', $sessionPath);
ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
ini_set('session.cookie_lifetime', (string) $sessionLifetime);

// Set session name
session_name($sessionName);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Session Data</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session name: " . session_name() . "</p>";
echo "<p>Session data: " . json_encode($_SESSION) . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p>✅ User ID in session: {$_SESSION['user_id']}</p>";
    echo "<p>✅ Username: {$_SESSION['username']}</p>";
    echo "<p>✅ Is Admin: " . ($_SESSION['is_admin'] ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p>❌ No user_id in session - NOT LOGGED IN</p>";
    echo "<p><a href='/test-login-debug.php'>Login Here</a></p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
echo "<p><a href='/test-login-debug.php'>Login Again</a></p>";
?> 
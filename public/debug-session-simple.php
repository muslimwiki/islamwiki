<?php
/**
 * Simple Session Debug Script
 * 
 * This script checks the current session state without any complex initialization
 */

// Start output buffering to prevent headers already sent errors
ob_start();

echo "<h1>🔍 Simple Session Debug</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check session status before any operations
echo "<h2>📊 Initial Session State</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'Not set') . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";

// Check if session is already active
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p><strong>⚠️ WARNING:</strong> Session is already active!</p>";
    echo "<p><strong>Session Data:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p><strong>✅ Session is not active</strong></p>";
}

// Check cookies
echo "<h2>🍪 Cookies</h2>";
if (empty($_COOKIE)) {
    echo "<p>No cookies found</p>";
} else {
    echo "<p><strong>Cookies found:</strong></p>";
    echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
}

// Check session files
echo "<h2>📁 Session Files</h2>";
$sessionPath = __DIR__ . '/../storage/sessions';
if (is_dir($sessionPath)) {
    $files = scandir($sessionPath);
    $sessionFiles = array_filter($files, function($file) {
        return strpos($file, 'sess_') === 0;
    });
    
    echo "<p><strong>Session files in $sessionPath:</strong></p>";
    echo "<p>Found " . count($sessionFiles) . " session files</p>";
    
    if (count($sessionFiles) > 0) {
        echo "<ul>";
        foreach (array_slice($sessionFiles, 0, 10) as $file) {
            $filePath = $sessionPath . '/' . $file;
            $size = filesize($filePath);
            $mtime = date('Y-m-d H:i:s', filemtime($filePath));
            echo "<li>$file - Size: $size bytes - Modified: $mtime</li>";
        }
        if (count($sessionFiles) > 10) {
            echo "<li>... and " . (count($sessionFiles) - 10) . " more</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p><strong>❌ Session directory not found:</strong> $sessionPath</p>";
}

// Check environment variables
echo "<h2>🌍 Environment Variables</h2>";
$sessionVars = [
    'SESSION_NAME' => getenv('SESSION_NAME'),
    'SESSION_LIFETIME' => getenv('SESSION_LIFETIME'),
    'SESSION_PATH' => getenv('SESSION_PATH'),
    'SESSION_SECURE' => getenv('SESSION_SECURE'),
    'SESSION_HTTP_ONLY' => getenv('SESSION_HTTP_ONLY'),
    'SESSION_SAME_SITE' => getenv('SESSION_SAME_SITE'),
];

echo "<p><strong>Session environment variables:</strong></p>";
echo "<ul>";
foreach ($sessionVars as $key => $value) {
    $displayValue = $value ?: 'Not set';
    echo "<li><strong>$key:</strong> $displayValue</li>";
}
echo "</ul>";

// Check PHP session configuration
echo "<h2>⚙️ PHP Session Configuration</h2>";
$sessionConfig = [
    'session.save_handler' => ini_get('session.save_handler'),
    'session.save_path' => ini_get('session.save_path'),
    'session.name' => ini_get('session.name'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
];

echo "<p><strong>Current PHP session settings:</strong></p>";
echo "<ul>";
foreach ($sessionConfig as $key => $value) {
    $displayValue = $value ?: 'Not set';
    echo "<li><strong>$key:</strong> $displayValue</li>";
}
echo "</ul>";

echo "<h2>🔧 Actions</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='action' value='start'>Start Session</button>";
echo "<button type='submit' name='action' value='destroy'>Destroy Session</button>";
echo "<button type='submit' name='action' value='refresh'>Refresh Page</button>";
echo "</form>";

// Handle actions
if ($_POST['action'] ?? false) {
    echo "<h2>🎯 Action Results</h2>";
    
    switch ($_POST['action']) {
        case 'start':
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                echo "<p><strong>✅ Session started</strong></p>";
                echo "<p><strong>New Session ID:</strong> " . session_id() . "</p>";
            } else {
                echo "<p><strong>⚠️ Session already active</strong></p>";
            }
            break;
            
        case 'destroy':
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
                echo "<p><strong>✅ Session destroyed</strong></p>";
            } else {
                echo "<p><strong>⚠️ No active session to destroy</strong></p>";
            }
            break;
            
        case 'refresh':
            echo "<p><strong>🔄 Page refreshed</strong></p>";
            break;
    }
}

// Final session state
echo "<h2>📊 Final Session State</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'Not set') . "</p>";
echo "<p><strong>Session Data:</strong></p>";
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p>No session data</p>";
}

// End output buffering and send
ob_end_flush(); 
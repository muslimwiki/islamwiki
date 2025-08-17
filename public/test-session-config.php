<?php
/**
 * Test Session Configuration
 * 
 * This script tests the session configuration step by step
 */

// Start output buffering
ob_start();

echo "<h1>🧪 Test Session Configuration</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Step 1: Check initial state
echo "<h2>📊 Step 1: Initial State</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'Not set') . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";

// Step 2: Set session configuration
echo "<h2>⚙️ Step 2: Setting Session Configuration</h2>";

// Set session save path
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
    echo "<p>✅ Created session directory: $sessionPath</p>";
} else {
    echo "<p>✅ Session directory exists: $sessionPath</p>";
}

// Set session configuration
session_save_path($sessionPath);
echo "<p>✅ Set session save path: $sessionPath</p>";

// Set session name
session_name('islamwiki_session');
echo "<p>✅ Set session name: islamwiki_session</p>";

// Set other session options
ini_set('session.use_strict_mode', '1');
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
echo "<p>✅ Set session options</p>";

// Step 3: Check configuration after setting
echo "<h2>🔍 Step 3: Configuration After Setting</h2>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Use Cookies:</strong> " . ini_get('session.use_cookies') . "</p>";
echo "<p><strong>Session Use Only Cookies:</strong> " . ini_get('session.use_only_cookies') . "</p>";

// Step 4: Start session
echo "<h2>🚀 Step 4: Starting Session</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>✅ Session started</p>";
    echo "<p><strong>New Session ID:</strong> " . session_id() . "</p>";
} else {
    echo "<p>⚠️ Session already active</p>";
}

// Step 5: Check session after starting
echo "<h2>📋 Step 5: Session After Starting</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID:</strong> " . (session_id() ?: 'Not set') . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

// Step 6: Test session data
echo "<h2>💾 Step 6: Testing Session Data</h2>";
$_SESSION['test_key'] = 'test_value_' . time();
echo "<p>✅ Set test session data</p>";

// Step 7: Check session data
echo "<h2>📊 Step 7: Session Data</h2>";
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
    echo "<p><strong>Session Data:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p>❌ No session data</p>";
}

// Step 8: Check session file
echo "<h2>📁 Step 8: Session File</h2>";
$sessionId = session_id();
if ($sessionId) {
    $sessionFile = $sessionPath . '/sess_' . $sessionId;
    if (file_exists($sessionFile)) {
        $size = filesize($sessionFile);
        $mtime = date('Y-m-d H:i:s', filemtime($sessionFile));
        echo "<p>✅ Session file exists: $sessionFile</p>";
        echo "<p><strong>Size:</strong> $size bytes</p>";
        echo "<p><strong>Modified:</strong> $mtime</p>";
        
        // Try to read session file content
        $content = file_get_contents($sessionFile);
        if ($content !== false) {
            echo "<p><strong>Content:</strong></p>";
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
        } else {
            echo "<p>❌ Could not read session file</p>";
        }
    } else {
        echo "<p>❌ Session file not found: $sessionFile</p>";
    }
} else {
    echo "<p>❌ No session ID</p>";
}

// Step 9: Check cookies
echo "<h2>🍪 Step 9: Cookies</h2>";
if (empty($_COOKIE)) {
    echo "<p>No cookies found</p>";
} else {
    echo "<p><strong>Cookies found:</strong></p>";
    echo "<pre>" . print_r($_COOKIE, true) . "</pre>";
}

// Step 10: Test session persistence
echo "<h2>🔄 Step 10: Test Session Persistence</h2>";
echo "<p>Current session ID: " . session_id() . "</p>";
echo "<p>Test value: " . ($_SESSION['test_key'] ?? 'Not set') . "</p>";

echo "<h2>🔧 Actions</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='action' value='refresh'>Refresh Page</button>";
echo "<button type='submit' name='action' value='destroy'>Destroy Session</button>";
echo "</form>";

// Handle actions
if ($_POST['action'] ?? false) {
    echo "<h2>🎯 Action Results</h2>";
    
    switch ($_POST['action']) {
        case 'refresh':
            echo "<p><strong>🔄 Page refreshed</strong></p>";
            break;
            
        case 'destroy':
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
                echo "<p><strong>✅ Session destroyed</strong></p>";
            } else {
                echo "<p><strong>⚠️ No active session to destroy</strong></p>";
            }
            break;
    }
}

// End output buffering and send
ob_end_flush(); 
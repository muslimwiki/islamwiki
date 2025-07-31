<?php
/**
 * Test User Settings
 * 
 * This page demonstrates user-specific settings functionality
 * 
 * @package IslamWiki\Test
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize the application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                // Simulate login for testing
                $session->login(1, 'testuser', false); // User ID 1, username 'testuser', not admin
                $message = 'Logged in as testuser (ID: 1)';
                $messageType = 'success';
                break;
                
            case 'logout':
                $session->logout();
                $message = 'Logged out successfully';
                $messageType = 'info';
                break;
                
            case 'update_skin':
                if ($session->isLoggedIn()) {
                    $userId = $session->getUserId();
                    $skinName = $_POST['skin'] ?? 'Bismillah';
                    
                    // Update user's skin preference
                    $currentSettings = [];
                    try {
                        $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
                        $stmt->execute([$userId]);
                        $result = $stmt->fetch();
                        if ($result) {
                            $currentSettings = json_decode($result['settings'], true) ?? [];
                        }
                    } catch (\Throwable $e) {
                        // Table might not exist yet, that's okay
                    }
                    
                    $currentSettings['skin'] = $skinName;
                    $currentSettings['updated_at'] = date('Y-m-d H:i:s');
                    
                    try {
                        $stmt = $db->prepare("
                            INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
                            VALUES (?, ?, NOW(), NOW())
                            ON DUPLICATE KEY UPDATE 
                            settings = VALUES(settings), 
                            updated_at = VALUES(updated_at)
                        ");
                        
                        $settingsJson = json_encode($currentSettings);
                        $stmt->execute([$userId, $settingsJson]);
                        
                        $message = "Skin updated to $skinName for user $userId";
                        $messageType = 'success';
                    } catch (\Throwable $e) {
                        $message = "Error updating skin: " . $e->getMessage();
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Please log in first';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get current user settings
$userSettings = [];
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    try {
        $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        if ($result) {
            $userSettings = json_decode($result['settings'], true) ?? [];
        }
    } catch (\Throwable $e) {
        // Table might not exist yet
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings Test - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, button { padding: 8px 12px; border-radius: 4px; border: 1px solid #ddd; }
        button { background-color: #007bff; color: white; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .skin-option { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .skin-option.active { background-color: #d4edda; border-color: #28a745; }
    </style>
</head>
<body>
    <h1>👤 User Settings Test</h1>
    
    <?php if ($message): ?>
        <div class="status <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="status <?php echo $session->isLoggedIn() ? 'success' : 'error'; ?>">
        <h2>Authentication Status</h2>
        <p><strong>Logged In:</strong> <?php echo $session->isLoggedIn() ? 'Yes' : 'No'; ?></p>
        
        <?php if ($session->isLoggedIn()): ?>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($session->getUserId() ?? 'null'); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($session->getUsername() ?? 'null'); ?></p>
            <p><strong>Is Admin:</strong> <?php echo $session->isAdmin() ? 'Yes' : 'No'; ?></p>
        <?php else: ?>
            <p>User is not logged in. Please log in to access settings.</p>
        <?php endif; ?>
    </div>
    
    <?php if (!$session->isLoggedIn()): ?>
        <div class="status info">
            <h2>Login for Testing</h2>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <button type="submit">Login as Test User</button>
            </form>
        </div>
    <?php else: ?>
        <div class="status info">
            <h2>User Settings</h2>
            <p><strong>Current Skin:</strong> <?php echo htmlspecialchars($userSettings['skin'] ?? 'Bismillah (default)'); ?></p>
            <p><strong>Last Updated:</strong> <?php echo htmlspecialchars($userSettings['updated_at'] ?? 'Never'); ?></p>
        </div>
        
        <div class="status info">
            <h2>Change Skin</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_skin">
                <div class="form-group">
                    <label for="skin">Select Skin:</label>
                    <select name="skin" id="skin">
                        <option value="Bismillah" <?php echo ($userSettings['skin'] ?? 'Bismillah') === 'Bismillah' ? 'selected' : ''; ?>>Bismillah (Default)</option>
                        <option value="BlueSkin" <?php echo ($userSettings['skin'] ?? '') === 'BlueSkin' ? 'selected' : ''; ?>>BlueSkin</option>
                        <option value="GreenSkin" <?php echo ($userSettings['skin'] ?? '') === 'GreenSkin' ? 'selected' : ''; ?>>GreenSkin</option>
                    </select>
                </div>
                <button type="submit">Update Skin</button>
            </form>
        </div>
        
        <div class="status info">
            <h2>Test API Endpoints</h2>
            <p>Try these authenticated endpoints:</p>
            <ul>
                <li><a href="/settings">Settings Page</a></li>
                <li><a href="/settings/skins">Settings API</a></li>
                <li><a href="/test-green-skin.php">GreenSkin Test Page</a></li>
            </ul>
        </div>
        
        <div class="status warning">
            <h2>Logout</h2>
            <form method="POST">
                <input type="hidden" name="action" value="logout">
                <button type="submit">Logout</button>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="status info">
        <h2>Debug Information</h2>
        <pre><?php
            echo "User Settings:\n";
            print_r($userSettings);
            echo "\n\nSession Data:\n";
            print_r($_SESSION);
        ?></pre>
    </div>
</body>
</html> 
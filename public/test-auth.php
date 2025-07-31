<?php
/**
 * Test Authentication
 * 
 * This page tests the authentication system
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Test - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔐 Authentication Test</h1>
    
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
    
    <div class="status info">
        <h2>Session Information</h2>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Session Status:</strong> <?php echo session_status(); ?></p>
        <p><strong>Session Name:</strong> <?php echo session_name(); ?></p>
    </div>
    
    <div class="status info">
        <h2>Test Settings Access</h2>
        <p>Try accessing these endpoints:</p>
        <ul>
            <li><a href="/settings">Settings Page</a></li>
            <li><a href="/settings/skins">Settings API</a></li>
        </ul>
    </div>
    
    <div class="status info">
        <h2>Debug Information</h2>
        <pre><?php
            echo "Session Data:\n";
            print_r($_SESSION);
            echo "\n\nCookies:\n";
            print_r($_COOKIE);
        ?></pre>
    </div>
</body>
</html> 
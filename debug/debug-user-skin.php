<?php

/**
 * Debug User Skin Settings
 *
 * This script checks what skin is stored in the database for the current user.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

// Define the application's base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Autoload file not found. Please run `composer install` to install the project dependencies.');
}

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use Application;\Application
use IslamWiki\Core\Database\Connection;

echo "🔍 Debug User Skin Settings\n";
echo "===========================\n\n";

try {
    // Initialize the application
    $app = new Application(BASE_PATH);
    echo "✅ Application created successfully\n";

    // Get database connection
    $db = $app->getContainer()->get('db');
    echo "✅ Database connection established\n";

    // Get session
    $session = $app->getContainer()->get('session');
    echo "✅ Session manager loaded\n";

    if (!$session->isLoggedIn()) {
        echo "❌ User is not logged in\n";
        exit;
    }

    $userId = $session->getUserId();
    $username = $session->getUsername();
    echo "✅ User logged in: $username (ID: $userId)\n\n";

    // Check user settings in database
    $stmt = $db->prepare("
        SELECT settings FROM user_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
        $settings = json_decode($result['settings'], true) ?? [];
        echo "📋 User settings found:\n";
        echo "   Raw settings: " . $result['settings'] . "\n";
        echo "   Decoded settings: " . print_r($settings, true) . "\n";

        if (isset($settings['skin'])) {
            echo "   Stored skin: " . $settings['skin'] . "\n";
        } else {
            echo "   No skin setting found in database\n";
        }
    } else {
        echo "❌ No user settings found in database\n";
    }

    // Check what SkinManager thinks is active
    $skinManager = $app->getContainer()->get('skin.manager');
    $activeSkin = $skinManager->getActiveSkinForUser($userId);
    $activeSkinName = $skinManager->getActiveSkinNameForUser($userId);

    echo "\n🎨 SkinManager results:\n";
        $temp_4c33102a = ($activeSkin ? get_class($activeSkin) : 'null') . "\n";
        echo "   Active skin object: " . $temp_4c33102a;
    echo "   Active skin name: " . $activeSkinName . "\n";

    if ($activeSkin) {
        echo "   Skin name: " . $activeSkin->getName() . "\n";
        echo "   Skin version: " . $activeSkin->getVersion() . "\n";
    }

    // Check available skins
    $availableSkins = $skinManager->getSkins();
    echo "\n📚 Available skins:\n";
    foreach ($availableSkins as $name => $skin) {
        echo "   - $name: " . $skin->getName() . " (v" . $skin->getVersion() . ")\n";
    }
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

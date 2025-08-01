<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');

echo "<h1>Session Test</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "</p>";

if ($session->isLoggedIn()) {
    echo "<p>User ID: " . $session->getUserId() . "</p>";
    echo "<p>Username: " . $session->getUsername() . "</p>";
} else {
    echo "<p>Not logged in</p>";
    echo "<p><a href='?login=1'>Simulate Login</a></p>";
}

if (isset($_GET['login'])) {
    $session->login(1, 'testuser');
    echo "<p>✅ Logged in as testuser</p>";
    echo "<p><a href='?'>Refresh</a></p>";
}

echo "<h2>Skin Info</h2>";
$skinManager = $container->get('skin.manager');
echo "<p>Active skin: " . $skinManager->getActiveSkinName() . "</p>";

if ($session->isLoggedIn()) {
    $userSkin = $skinManager->getActiveSkinForUser($session->getUserId());
    echo "<p>User skin: " . ($userSkin ? $userSkin->getName() : 'null') . "</p>";
} 
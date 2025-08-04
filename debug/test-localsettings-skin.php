<?php
// Include LocalSettings to see what's being set
require_once __DIR__ . '/../LocalSettings.php';

echo "🔍 Test LocalSettings Skin Configuration\n";
echo "=======================================\n\n";

// Check if $wgActiveSkin is set
echo "📋 LocalSettings Configuration:\n";
echo "- \$wgActiveSkin is set: " . (isset($wgActiveSkin) ? 'Yes' : 'No') . "\n";
if (isset($wgActiveSkin)) {
    echo "- \$wgActiveSkin value: " . $wgActiveSkin . "\n";
}

// Check environment variable
echo "- ACTIVE_SKIN env var: " . (getenv('ACTIVE_SKIN') ?: 'Not set') . "\n";

// Check if the skin directory exists
$muslimSkinPath = __DIR__ . '/../skins/Muslim';
$bismillahSkinPath = __DIR__ . '/../skins/Bismillah';

echo "\n📁 Skin Directory Check:\n";
echo "- Muslim skin directory exists: " . (is_dir($muslimSkinPath) ? 'Yes' : 'No') . "\n";
echo "- Bismillah skin directory exists: " . (is_dir($bismillahSkinPath) ? 'Yes' : 'No') . "\n";

// Check skin.json files
$muslimConfigPath = $muslimSkinPath . '/skin.json';
$bismillahConfigPath = $bismillahSkinPath . '/skin.json';

echo "\n📄 Skin Configuration Files:\n";
echo "- Muslim skin.json exists: " . (file_exists($muslimConfigPath) ? 'Yes' : 'No') . "\n";
echo "- Bismillah skin.json exists: " . (file_exists($bismillahConfigPath) ? 'Yes' : 'No') . "\n";

if (file_exists($muslimConfigPath)) {
    $muslimConfig = json_decode(file_get_contents($muslimConfigPath), true);
    echo "- Muslim skin name in config: " . ($muslimConfig['name'] ?? 'Not set') . "\n";
}

if (file_exists($bismillahConfigPath)) {
    $bismillahConfig = json_decode(file_get_contents($bismillahConfigPath), true);
    echo "- Bismillah skin name in config: " . ($bismillahConfig['name'] ?? 'Not set') . "\n";
} 
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
        $temp_0736af98 = (is_dir($muslimSkinPath) ? 'Yes' : 'No') . "\n";
        echo "- Muslim skin directory exists: " . $temp_0736af98;
        $temp_de6f2343 = (is_dir($bismillahSkinPath) ? 'Yes' : 'No') . "\n";
        echo "- Bismillah skin directory exists: " . $temp_de6f2343;

// Check skin.json files
$muslimConfigPath = $muslimSkinPath . '/skin.json';
$bismillahConfigPath = $bismillahSkinPath . '/skin.json';

echo "\n📄 Skin Configuration Files:\n";
        $temp_ba180ced = (file_exists($muslimConfigPath) ? 'Yes' : 'No') . "\n";
        echo "- Muslim skin.json exists: " . $temp_ba180ced;
        $temp_df80ba3a = (file_exists($bismillahConfigPath) ? 'Yes' : 'No') . "\n";
        echo "- Bismillah skin.json exists: " . $temp_df80ba3a;

if (file_exists($muslimConfigPath)) {
    $muslimConfig = json_decode(file_get_contents($muslimConfigPath), true);
        $temp_8793ae7e = ($muslimConfig['name'] ?? 'Not set') . "\n";
        echo "- Muslim skin name in config: " . $temp_8793ae7e;
}

if (file_exists($bismillahConfigPath)) {
    $bismillahConfig = json_decode(file_get_contents($bismillahConfigPath), true);
        $temp_ba8431d0 = ($bismillahConfig['name'] ?? 'Not set') . "\n";
        echo "- Bismillah skin name in config: " . $temp_ba8431d0;
}

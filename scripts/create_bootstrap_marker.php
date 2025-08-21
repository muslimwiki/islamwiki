<?php
// Simple script to create bootstrap marker file
echo "🔧 Creating bootstrap marker file...\n";

// Create storage/framework directory if it doesn't exist
$markerDir = __DIR__ . '/../storage/framework';
if (!is_dir($markerDir)) {
    if (mkdir($markerDir, 0755, true)) {
        echo "✅ Created directory: {$markerDir}\n";
    } else {
        echo "❌ Failed to create directory: {$markerDir}\n";
        exit(1);
    }
} else {
    echo "✅ Directory already exists: {$markerDir}\n";
}

// Create the bootstrap marker file
$markerFile = $markerDir . '/app_bootstrapped';
if (file_put_contents($markerFile, date('Y-m-d H:i:s'))) {
    echo "✅ Bootstrap marker created: {$markerFile}\n";
    echo "✅ Web interface is now accessible!\n";
} else {
    echo "❌ Failed to create bootstrap marker file\n";
    exit(1);
}

echo "\n🎉 Bootstrap marker created successfully!\n";
echo "🌐 You can now visit: http://local.islam.wiki/\n";
echo "📁 Marker file location: {$markerFile}\n"; 
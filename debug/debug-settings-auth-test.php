<?php
declare(strict_types=1);

/**
 * Debug Settings Authentication Test
 * 
 * Tests the settings page with simulated authentication.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Simulate the SettingsController with authentication
class DebugSettingsController
{
    private $db;
    private $session;
    
    public function __construct()
    {
        // Initialize database connection
        $dbConfig = [
            'driver' => getenv('DB_CONNECTION') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'database' => getenv('DB_DATABASE') ?: 'islamwiki',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];
        
        $this->db = new \IslamWiki\Core\Database\Connection($dbConfig);
        
        // Simulate authenticated session
        $this->session = new class {
            public function isLoggedIn(): bool { return true; }
            public function getUserId(): int { return 1; }
        };
    }
    
    public function index(): array
    {
        $userId = $this->session->getUserId();
        
        // Get user settings
        $userSettings = $this->getUserSettings($userId);
        $userActiveSkin = $userSettings['skin'] ?? 'bismillah';
        
        // Get current user
        $user = null;
        try {
            $user = \IslamWiki\Models\User::find($userId, $this->db);
        } catch (\Exception $e) {
            // User not found, continue with null user
        }
        
        // Dynamically discover available skins
        $availableSkins = $this->discoverAvailableSkins();
        
        // Simulate skin manager
        $loadedSkins = $this->simulateSkinManager();
        
        $skinOptions = [];
        
        // Process discovered skins
        foreach ($availableSkins as $skinKey => $skinData) {
            $lowerSkinName = strtolower($skinData['name']);
            
            if (isset($loadedSkins[$lowerSkinName])) {
                $skin = $loadedSkins[$lowerSkinName];
                
                $isActive = $lowerSkinName === strtolower($userActiveSkin);
                
                $skinOptions[$skinData['name']] = [
                    'name' => $skin->getName(),
                    'version' => $skin->getVersion(),
                    'author' => $skin->getAuthor(),
                    'description' => $skin->getDescription(),
                    'active' => $isActive,
                    'css_key' => $lowerSkinName,
                    'directory' => $skinData['directory'],
                    'features' => $skinData['features'] ?? [],
                    'config' => $skinData['config'] ?? []
                ];
            }
        }
        
        return [
            'title' => 'Settings - IslamWiki',
            'user' => $user,
            'skinOptions' => $skinOptions,
            'activeSkin' => $userActiveSkin,
            'availableSkins' => $availableSkins,
            'userSettings' => $userSettings
        ];
    }
    
    private function discoverAvailableSkins(): array
    {
        $skinsDir = __DIR__ . '/../skins';
        $availableSkins = [];
        
        if (!is_dir($skinsDir)) {
            return $availableSkins;
        }
        
        $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    
                    if ($config && isset($config['name'])) {
                        $availableSkins[strtolower($skinName)] = [
                            'name' => $config['name'],
                            'version' => $config['version'] ?? '0.0.1',
                            'author' => $config['author'] ?? 'Unknown',
                            'description' => $config['description'] ?? '',
                            'directory' => $skinName,
                            'features' => $config['features'] ?? [],
                            'config' => $config['config'] ?? [],
                            'dependencies' => $config['dependencies'] ?? []
                        ];
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
        }
        
        return $availableSkins;
    }
    
    private function simulateSkinManager(): array
    {
        return [
            'bismillah' => new class {
                public function getName(): string { return 'Bismillah'; }
                public function getVersion(): string { return '0.0.28'; }
                public function getAuthor(): string { return 'IslamWiki Team'; }
                public function getDescription(): string { return 'The default skin for IslamWiki with modern Islamic design and beautiful gradients.'; }
            },
            'muslim' => new class {
                public function getName(): string { return 'Muslim'; }
                public function getVersion(): string { return '0.0.1'; }
                public function getAuthor(): string { return 'IslamWiki Team'; }
                public function getDescription(): string { return 'A beautiful, usable, responsive skin inspired by Citizen MediaWiki skin with Islamic design elements.'; }
            }
        ];
    }
    
    private function getUserSettings(int $userId): array
    {
        try {
            $result = $this->db->first("
                SELECT settings FROM user_settings 
                WHERE user_id = ?
            ", [$userId]);
            
            if ($result) {
                $settings = json_decode($result->settings, true) ?? [];
                return $settings;
            }
            
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}

echo "🔍 Testing Settings with Authentication\n";
echo "======================================\n\n";

try {
    $controller = new DebugSettingsController();
    $result = $controller->index();
    
    echo "✅ Settings controller executed successfully\n\n";
    
    echo "📊 Results:\n";
    echo "===========\n";
    echo "Title: {$result['title']}\n";
    echo "User: " . ($result['user'] ? $result['user']->username : 'null') . "\n";
    echo "Active Skin: {$result['activeSkin']}\n";
    echo "Available Skins: " . count($result['availableSkins']) . "\n";
    echo "Skin Options: " . count($result['skinOptions']) . "\n";
    echo "User Settings: " . count($result['userSettings']) . "\n\n";
    
    if (!empty($result['skinOptions'])) {
        echo "🎨 Skin Options:\n";
        echo "===============\n";
        foreach ($result['skinOptions'] as $skinName => $skinData) {
            echo "  - {$skinData['name']} (v{$skinData['version']})\n";
            echo "    Author: {$skinData['author']}\n";
            echo "    Active: " . ($skinData['active'] ? 'Yes' : 'No') . "\n";
            echo "    Features: " . implode(', ', $skinData['features']) . "\n\n";
        }
    } else {
        echo "❌ No skin options generated\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "✅ Authentication test completed!\n"; 
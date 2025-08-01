<?php
declare(strict_types=1);

echo "<h1>Simple Database Test</h1>";

try {
    // Load environment variables manually
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }
    
    // Get database config
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "<p>🔧 Database config:</p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Port: $port</li>";
    echo "<li>Database: $database</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Password: " . (empty($password) ? 'empty' : 'set') . "</li>";
    echo "</ul>";
    
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p>✅ PDO connection created successfully</p>";
    
    // Test basic query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Basic query test: " . ($result ? 'success' : 'failed') . "</p>";
    
    // Check if user_settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_settings'");
    $tableExists = $stmt->fetch();
    echo "<p>✅ user_settings table exists: " . ($tableExists ? 'yes' : 'no') . "</p>";
    
    if ($tableExists) {
        // Check table structure - use SHOW COLUMNS instead of DESCRIBE
        $stmt = $pdo->query("SHOW COLUMNS FROM user_settings");
        $columns = $stmt->fetchAll();
        echo "<p>📋 Table structure:</p>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>{$column['Field']} - {$column['Type']}</li>";
        }
        echo "</ul>";
        
        // Check if there are any records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_settings");
        $count = $stmt->fetch();
        echo "<p>📊 Total records in user_settings: {$count['count']}</p>";
        
        // Show all records
        $stmt = $pdo->query("SELECT * FROM user_settings LIMIT 5");
        $records = $stmt->fetchAll();
        echo "<p>📋 Records:</p>";
        foreach ($records as $record) {
            echo "<p>User ID: {$record['user_id']}, Settings: {$record['settings']}</p>";
        }
        
        // Test inserting a record
        echo "<p>🧪 Testing insert...</p>";
        try {
            $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, settings) VALUES (?, ?) ON DUPLICATE KEY UPDATE settings = ?");
            $testSettings = json_encode(['test' => 'value']);
            $stmt->execute([999, $testSettings, $testSettings]);
            echo "<p>✅ Insert test successful</p>";
            
            // Clean up test record
            $stmt = $pdo->prepare("DELETE FROM user_settings WHERE user_id = ?");
            $stmt->execute([999]);
            echo "<p>✅ Cleanup successful</p>";
        } catch (Exception $e) {
            echo "<p>❌ Insert test failed: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Error code: " . $e->getCode() . "</p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 
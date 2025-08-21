<?php

/**
 * Test Authentication System
 *
 * This script tests the new authentication system to ensure it works properly.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/../LocalSettings.php';

// Start session
session_start();

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host={$wgDBserver};dbname={$wgDBname};charset=utf8mb4",
        $wgDBuser,
        $wgDBpassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    echo "✅ Database connection successful\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Test 1: Check if users table exists
echo "\n1. Checking users table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";

        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "   Table structure:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']}: {$column['Type']}\n";
        }
    } else {
        echo "❌ Users table does not exist\n";
        echo "   Creating users table...\n";

        $sql = "CREATE TABLE users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            bio TEXT NULL,
            avatar_url VARCHAR(255) NULL,
            is_active BOOLEAN DEFAULT TRUE,
            is_admin BOOLEAN DEFAULT FALSE,
            email_verified_at TIMESTAMP NULL,
            password_reset_token VARCHAR(100) NULL,
            password_reset_expires TIMESTAMP NULL,
            remember_token VARCHAR(100) NULL,
            last_login_at TIMESTAMP NULL,
            last_login_ip VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_active (is_active)
        )";

        $pdo->exec($sql);
        echo "✅ Users table created successfully\n";
    }
} catch (PDOException $e) {
    die("❌ Error checking/creating users table: " . $e->getMessage() . "\n");
}

// Test 2: Create a test user
echo "\n2. Creating test user...\n";
try {
    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['testuser']);

    if ($stmt->rowCount() > 0) {
        echo "✅ Test user already exists\n";
    } else {
        // Create test user
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, display_name, is_active, is_admin) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            'testuser',
            'test@example.com',
            $hashedPassword,
            'Test User',
            1,
            0
        ]);

        echo "✅ Test user created successfully\n";
        echo "   Username: testuser\n";
        echo "   Password: password123\n";
    }
} catch (PDOException $e) {
    die("❌ Error creating test user: " . $e->getMessage() . "\n");
}

// Test 3: Test authentication
echo "\n3. Testing authentication...\n";
try {
    // Test valid credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute(['testuser']);
    $user = $stmt->fetch();

    if ($user && password_verify('password123', $user['password'])) {
        echo "✅ Authentication test successful\n";
        echo "   User ID: {$user['id']}\n";
        echo "   Username: {$user['username']}\n";
        echo "   Display Name: {$user['display_name']}\n";
        echo "   Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Authentication test failed\n";
    }

    // Test invalid credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute(['testuser']);
    $user = $stmt->fetch();

    if ($user && !password_verify('wrongpassword', $user['password'])) {
        echo "✅ Invalid password correctly rejected\n";
    } else {
        echo "❌ Invalid password test failed\n";
    }
} catch (PDOException $e) {
    die("❌ Error testing authentication: " . $e->getMessage() . "\n");
}

// Test 4: Test session management
echo "\n4. Testing session management...\n";
try {
    // Simulate login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];

    echo "✅ Session data set\n";
    echo "   Session ID: " . session_id() . "\n";
    echo "   User ID in session: " . ($_SESSION['user_id'] ?? 'null') . "\n";
    echo "   Username in session: " . ($_SESSION['username'] ?? 'null') . "\n";
    echo "   Is Admin in session: " . ($_SESSION['is_admin'] ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "❌ Error testing session management: " . $e->getMessage() . "\n";
}

// Test 5: Test AuthManager class
echo "\n5. Testing AuthManager class...\n";
try {
    // Create a simple mock for testing
    class MockSessionManager
    {
        private $sessionData = [];

        public function isLoggedIn(): bool
        {
            return isset($_SESSION['user_id']);
        }

        public function getUserId(): ?int
        {
            return $_SESSION['user_id'] ?? null;
        }

        public function getUsername(): ?string
        {
            return $_SESSION['username'] ?? null;
        }

        public function isAdmin(): bool
        {
            return $_SESSION['is_admin'] ?? false;
        }

        public function login(int $userId, string $username, bool $isAdmin = false): void
        {
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = $isAdmin;
        }

        public function logout(): void
        {
            unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['is_admin']);
        }
    }

    class MockConnection
    {
        private $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function select(string $query, array $params = []): array
        {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        public function insert(string $table, array $data): int
        {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $stmt = $this->pdo->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
            $stmt->execute($data);

            return (int) $this->pdo->lastInsertId();
        }

        public function update(string $table, array $data, array $where): int
        {
            $setClause = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
            $whereClause = implode(' AND ', array_map(fn($col) => "{$col} = :where_{$col}", array_keys($where)));

            $stmt = $this->pdo->prepare("UPDATE {$table} SET {$setClause} WHERE {$whereClause}");

            $params = $data;
            foreach ($where as $key => $value) {
                $params["where_{$key}"] = $value;
            }

            $stmt->execute($params);
            return $stmt->rowCount();
        }
    }

    $sessionManager = new MockSessionManager();
    $connection = new MockConnection($pdo);

    // Test AuthManager instantiation
    $authManager = new \IslamWiki\Core\Auth\AuthManager($sessionManager, $connection);
    echo "✅ AuthManager instantiated successfully\n";

    // Test authentication check
    $isLoggedIn = $authManager->check();
    echo "   User logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";

    // Test user data retrieval
    $currentUser = $authManager->user();
    if ($currentUser) {
        echo "   Current user: {$currentUser['username']}\n";
        echo "   User ID: {$currentUser['id']}\n";
    } else {
        echo "   No current user\n";
    }

    // Test permission checking
    $canCreatePages = $authManager->can('create_pages');
    $canDeletePages = $authManager->can('delete_pages');
    echo "   Can create pages: " . ($canCreatePages ? 'Yes' : 'No') . "\n";
    echo "   Can delete pages: " . ($canDeletePages ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "❌ Error testing AuthManager: " . $e->getMessage() . "\n";
}

// Test 6: Test web routes
echo "\n6. Testing web routes...\n";
try {
    // Test login page
    $loginUrl = 'https://local.islam.wiki/login';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✅ Login page accessible (HTTP {$httpCode})\n";
    } else {
        echo "❌ Login page not accessible (HTTP {$httpCode})\n";
    }

    // Test register page
    $registerUrl = 'https://local.islam.wiki/register';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $registerUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✅ Register page accessible (HTTP {$httpCode})\n";
    } else {
        echo "❌ Register page not accessible (HTTP {$httpCode})\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing web routes: " . $e->getMessage() . "\n";
}

echo "\n🎉 Authentication system test completed!\n";
echo "\nNext steps:\n";
echo "1. Visit https://local.islam.wiki/login to test login\n";
echo "2. Visit https://local.islam.wiki/register to test registration\n";
echo "3. Try creating a page at https://local.islam.wiki/pages/create\n";
echo "4. Test credentials: testuser / password123\n";

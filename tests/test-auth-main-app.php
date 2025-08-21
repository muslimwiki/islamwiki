<?php

/**
 * Test Authentication with Main App Configuration
 *
 * This test uses the same session configuration as the main application.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Start session with the same configuration as the main app
session_name('islamwiki_session');
session_start();

// Initialize database connection
$pdo = new PDO(
    "mysql:host={$wgDBserver};dbname={$wgDBname};charset=utf8mb4",
    $wgDBuser,
    $wgDBpassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please provide both username and password';
    } else {
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - use the same session keys as the main app
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];

            $success = "Login successful! Welcome, {$user['username']}";
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    $success = 'You have been logged out';
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = null;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main App Auth Test - IslamWiki</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: Inter, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8fafc; 
        }
        .container { 
            max-width: 600px; 
            margin: 50px auto; 
            background: white; 
            padding: 2rem; 
            border-radius: 1rem; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
        }
        .title { 
            text-align: center; 
            margin-bottom: 2rem; 
            color: #1f2937; 
        }
        .form-group { 
            margin-bottom: 1rem; 
        }
        .form-label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 500; 
            color: #374151; 
        }
        .form-input { 
            width: 100%; 
            padding: 0.75rem; 
            border: 1px solid #d1d5db; 
            border-radius: 0.5rem; 
            font-size: 0.9rem; 
            box-sizing: border-box;
        }
        .form-input:focus { 
            outline: none; 
            border-color: #4f46e5; 
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
        }
        .btn { 
            width: 100%; 
            padding: 0.75rem; 
            background: #4f46e5; 
            color: white; 
            border: none; 
            border-radius: 0.5rem; 
            font-weight: 500; 
            cursor: pointer; 
            font-size: 0.9rem;
        }
        .btn:hover { 
            background: #4338ca; 
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .error { 
            color: #dc2626; 
            margin-bottom: 1rem; 
            padding: 0.75rem; 
            background: #fef2f2; 
            border: 1px solid #fecaca; 
            border-radius: 0.5rem; 
        }
        .success { 
            color: #059669; 
            margin-bottom: 1rem; 
            padding: 0.75rem; 
            background: #f0fdf4; 
            border: 1px solid #bbf7d0; 
            border-radius: 0.5rem; 
        }
        .info { 
            color: #1d4ed8; 
            margin-bottom: 1rem; 
            padding: 0.75rem; 
            background: #eff6ff; 
            border: 1px solid #bfdbfe; 
            border-radius: 0.5rem; 
        }
        .user-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .links { 
            text-align: center; 
            margin-top: 1rem; 
        }
        .links a { 
            color: #4f46e5; 
            text-decoration: none; 
            margin: 0 0.5rem;
        }
        .links a:hover { 
            text-decoration: underline; 
        }
        .test-credentials {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .session-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-family: monospace;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Main App Auth Test - IslamWiki</h1>
        
        <?php if ($error) : ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success) : ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Session Information -->
        <div class="session-info">
            <h4>Session Information:</h4>
            <p><strong>Session Name:</strong> <?= session_name() ?></p>
            <p><strong>Session ID:</strong> <?= session_id() ?></p>
            <p><strong>Session Status:</strong> <?= session_status() ?></p>
            <p><strong>Session Data:</strong> <?= htmlspecialchars(json_encode($_SESSION)) ?></p>
        </div>
        
        <?php if ($isLoggedIn && $currentUser) : ?>
            <div class="user-info">
                <h3>✅ Logged In (Main App Session)</h3>
                <p><strong>User ID:</strong> <?= $currentUser['id'] ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($currentUser['username']) ?></p>
                <p><strong>Display Name:</strong> <?= htmlspecialchars($currentUser['display_name'] ?: $currentUser['username']) ?></p>
                <p><strong>Is Admin:</strong> <?= $currentUser['is_admin'] ? 'Yes' : 'No' ?></p>
                <p><strong>Session ID:</strong> <?= session_id() ?></p>
            </div>
            
            <div class="info">
                <h4>Test Links (should work now):</h4>
                <p><a href="https://local.islam.wiki/pages/create" target="_blank">Create Page</a> - Test if you can access page creation</p>
                <p><a href="https://local.islam.wiki/dashboard" target="_blank">Dashboard</a> - Test dashboard access</p>
                <p><a href="https://local.islam.wiki/profile" target="_blank">Profile</a> - Test profile access</p>
            </div>
            
            <a href="?logout=1" class="btn btn-secondary">Logout</a>
            
        <?php else : ?>
            <div class="test-credentials">
                <h4>🧪 Test Credentials:</h4>
                <p><strong>Username:</strong> testuser</p>
                <p><strong>Password:</strong> password123</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" required value="testuser">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required value="password123">
                </div>
                <button type="submit" class="btn">Sign In</button>
            </form>
            
            <div class="links">
                <a href="https://local.islam.wiki/login" target="_blank">Main Login Page</a> | 
                <a href="https://local.islam.wiki/register" target="_blank">Register</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 
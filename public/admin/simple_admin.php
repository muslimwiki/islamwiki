<?php
// Simple Admin Dashboard
session_start();

// Simple authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IslamWiki</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f6fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background-color: var(--primary);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo a {
            color: white;
            text-decoration: none;
        }
        
        .user-menu a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        
        .card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card h2 {
            margin-bottom: 15px;
            color: var(--dark);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #2ecc71;
            margin-right: 5px;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="simple_admin.php">IslamWiki Admin</a>
                <span style="margin-left: 20px; font-size: 0.9rem;">
                    <a href="/" style="color: #bdc3c7; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </span>
            </div>
            <div class="user-menu">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="card">
            <h2>Dashboard Overview</h2>
            <p><span class="status"></span> System Status: <strong>Operational</strong></p>
            
            <div class="dashboard-stats">
                <div class="stat-box">
                    <span class="stat-number">1,234</span>
                    <span class="stat-label">Total Visitors</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">567</span>
                    <span class="stat-label">New Users</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">89</span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">24</span>
                    <span class="stat-label">Comments</span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <div>
                <a href="#" class="btn"><i class="fas fa-plus"></i> New Article</a>
                <a href="#" class="btn"><i class="fas fa-user"></i> Manage Users</a>
                <a href="#" class="btn"><i class="fas fa-file-alt"></i> View Pages</a>
                <a href="#" class="btn"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>
        
        <div class="card">
            <h2>System Information</h2>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
            <p><strong>Server Name:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'N/A'; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></p>
        </div>
    </div>
</body>
</html>

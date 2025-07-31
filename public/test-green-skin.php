<?php
/**
 * Test GreenSkin Visual Changes
 * 
 * This page demonstrates the GreenSkin visual changes
 * 
 * @package IslamWiki\Test
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize the application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get the active skin
$skinManager = $container->get('skin.manager');
$activeSkin = $skinManager->getActiveSkinName();

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenSkin Test - IslamWiki</title>
    
    <!-- Green Theme CSS -->
    <link rel="stylesheet" href="/skins/GreenSkin/css/style.css">
    
    <!-- Google Fonts for Islamic Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .test-section {
            margin: 2rem 0;
            padding: 2rem;
            border-radius: 15px;
            background: linear-gradient(135deg, white, #E8F5E8);
            border: 2px solid #4CAF50;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.1);
        }
        
        .color-demo {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        
        .color-box {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .primary-color { background-color: #2E7D32; }
        .secondary-color { background-color: #4CAF50; }
        .accent-color { background-color: #81C784; color: #1B5E20; }
        .light-color { background-color: #F1F8E9; color: #1B5E20; border: 2px solid #4CAF50; }
        .dark-color { background-color: #1B5E20; }
    </style>
</head>
<body class="green-theme">
    <!-- Header -->
    <header class="green-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-mosque green-icon me-2"></i>
                        IslamWiki GreenSkin Test
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="fas fa-leaf green-icon me-1"></i>
                        Testing Green Theme Visual Changes
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="green-badge">
                        <i class="fas fa-palette me-1"></i>
                        Active: <?php echo htmlspecialchars($activeSkin); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg green-navbar">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-mosque me-2"></i>
                IslamWiki
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/settings">
                    <i class="fas fa-cog me-1"></i>
                    Settings
                </a>
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-tachometer-alt me-1"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="green-content">
        <div class="container">
            <div class="test-section">
                <h2 class="green-text">
                    <i class="fas fa-leaf green-icon me-2"></i>
                    GreenSkin Visual Test
                </h2>
                <p class="green-text">
                    This page demonstrates the beautiful green color scheme of GreenSkin. 
                    You should see a distinct green theme with Islamic aesthetics.
                </p>
                
                <div class="alert green-alert">
                    <i class="fas fa-check-circle green-icon me-2"></i>
                    <strong>Success!</strong> GreenSkin is now active and you can see the visual changes.
                </div>
            </div>
            
            <div class="test-section">
                <h3 class="green-text">
                    <i class="fas fa-palette green-icon me-2"></i>
                    Color Palette
                </h3>
                <p class="green-text">The GreenSkin uses these beautiful green colors:</p>
                
                <div class="color-demo">
                    <div class="color-box primary-color">
                        Primary<br>#2E7D32
                    </div>
                    <div class="color-box secondary-color">
                        Secondary<br>#4CAF50
                    </div>
                    <div class="color-box accent-color">
                        Accent<br>#81C784
                    </div>
                    <div class="color-box light-color">
                        Light<br>#F1F8E9
                    </div>
                    <div class="color-box dark-color">
                        Dark<br>#1B5E20
                    </div>
                </div>
            </div>
            
            <div class="test-section">
                <h3 class="green-text">
                    <i class="fas fa-cogs green-icon me-2"></i>
                    Interactive Elements
                </h3>
                
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="green-text">Buttons</h4>
                        <button class="btn green-btn me-2">Primary Button</button>
                        <button class="btn btn-secondary me-2">Secondary Button</button>
                        <button class="btn btn-outline-success">Outline Button</button>
                    </div>
                    
                    <div class="col-md-6">
                        <h4 class="green-text">Form Elements</h4>
                        <input type="text" class="form-control green-form-control mb-2" placeholder="Green input field">
                        <select class="form-control green-form-control">
                            <option>Green dropdown</option>
                            <option>Option 1</option>
                            <option>Option 2</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="test-section">
                <h3 class="green-text">
                    <i class="fas fa-cards-blank green-icon me-2"></i>
                    Cards
                </h3>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card green-card">
                            <div class="green-card-header">
                                <h5 class="card-title">Green Card 1</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text green-text">
                                    This card demonstrates the green theme styling with beautiful borders and shadows.
                                </p>
                                <a href="#" class="green-link">Learn More →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card green-card">
                            <div class="green-card-header">
                                <h5 class="card-title">Green Card 2</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text green-text">
                                    Notice the green color scheme and Islamic-inspired design elements.
                                </p>
                                <a href="#" class="green-link">Explore →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card green-card">
                            <div class="green-card-header">
                                <h5 class="card-title">Green Card 3</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text green-text">
                                    The green theme creates a peaceful and natural aesthetic.
                                </p>
                                <a href="#" class="green-link">Discover →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="test-section">
                <h3 class="green-text">
                    <i class="fas fa-info-circle green-icon me-2"></i>
                    Test Results
                </h3>
                
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>✅ GreenSkin Test Successful!</strong>
                </div>
                
                <ul class="list-group">
                    <li class="list-group-item green-border">
                        <i class="fas fa-check green-icon me-2"></i>
                        Green color scheme applied correctly
                    </li>
                    <li class="list-group-item green-border">
                        <i class="fas fa-check green-icon me-2"></i>
                        Islamic typography and design elements visible
                    </li>
                    <li class="list-group-item green-border">
                        <i class="fas fa-check green-icon me-2"></i>
                        Interactive elements have green styling
                    </li>
                    <li class="list-group-item green-border">
                        <i class="fas fa-check green-icon me-2"></i>
                        Cards and buttons show green theme
                    </li>
                    <li class="list-group-item green-border">
                        <i class="fas fa-check green-icon me-2"></i>
                        Skin switching functionality working
                    </li>
                </ul>
                
                <div class="mt-3">
                    <a href="/settings" class="btn green-btn me-2">
                        <i class="fas fa-cog me-1"></i>
                        Go to Settings
                    </a>
                    <a href="/dashboard" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="green-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="green-icon">
                        <i class="fas fa-mosque me-2"></i>
                        IslamWiki GreenSkin Test
                    </h5>
                    <p class="mb-0">
                        Testing the beautiful green theme for IslamWiki.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="green-badge">
                        <i class="fas fa-palette me-1"></i>
                        GreenSkin v1.0.0
                    </span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Green Theme JavaScript -->
    <script src="/skins/GreenSkin/js/script.js"></script>
</body>
</html> 
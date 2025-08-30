<?php
// Simple index.php for testing
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html>
<head>
    <title>IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>
    <h1>Welcome to IslamWiki</h1>
    <p>The server is running correctly!</p>
    
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; display: inline-block;">
        <a href="/admin/" style="text-decoration: none; color: #fff; background-color: #2c3e50; padding: 10px 20px; border-radius: 4px; font-weight: bold;">
            <i class="fas fa-tachometer-alt"></i> Go to Admin Panel
        </a>
    </div>
</body>
</html>';

exit;

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Simple Working Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Simple Working Test</h1>
    
    <div class="test">
        <h3>Manual Test:</h3>
        <button onclick="toggleTest()" class="btn">Manual Toggle</button>
        <div id="test-content" class="alert alert-success" style="display: none;">This should show/hide</div>
    </div>

    <div class="test">
        <h3>ZamZam Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">ZamZam Toggle</button>
            <div z-show="test" class="alert alert-success">This should show/hide</div>
        </div>
    </div>

    <script>
        // Manual test function
        function toggleTest() {
            const content = document.getElementById('test-content');
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        }
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            console.log('ZamZam script loaded');
        };
        script.onerror = function() {
            console.error('Failed to load ZamZam script');
        };
        document.head.appendChild(script);
    </script>
</body>
</html> 
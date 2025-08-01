<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Minimal Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Minimal Test</h1>
    
    <div class="test">
        <h3>Debug Info:</h3>
        <div id="debug">Loading...</div>
    </div>

    <div class="test">
        <h3>Minimal Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">Toggle Test</button>
            <div z-show="test" class="alert alert-success">This should show/hide</div>
        </div>
    </div>

    <script>
        // Simple debug
        const debugDiv = document.getElementById('debug');
        
        function updateDebug() {
            const info = [
                'ZamZam loaded: ' + (typeof window.ZamZam !== 'undefined'),
                'ZamZamInstance: ' + (typeof window.ZamZamInstance !== 'undefined'),
                'Components: ' + (window.ZamZamInstance ? window.ZamZamInstance.components.size : 'N/A'),
                'Document ready: ' + (document.readyState === 'complete')
            ].join('<br>');
            
            debugDiv.innerHTML = info;
        }
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            console.log('ZamZam script loaded');
            setTimeout(updateDebug, 500);
        };
        script.onerror = function() {
            console.error('Failed to load ZamZam script');
            debugDiv.innerHTML = '❌ Failed to load ZamZam script';
        };
        document.head.appendChild(script);
        
        // Update debug every second
        setInterval(updateDebug, 1000);
    </script>
</body>
</html> 
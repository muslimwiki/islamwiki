<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .error { background: #fee2e2; color: #991b1b; }
        .success { background: #d1fae5; color: #065f46; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Debug Page</h1>
    
    <div class="debug">
        <h3>Debug Information:</h3>
        <div id="debug-info">Loading...</div>
    </div>

    <div class="debug">
        <h3>Simple Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">Toggle Test</button>
            <div z-show="test" class="success">This should show/hide</div>
        </div>
    </div>

    <script>
        // Debug before loading ZamZam
        console.log('=== ZAMZAM DEBUG START ===');
        console.log('Document ready state:', document.readyState);
        console.log('ZamZam before load:', typeof window.ZamZam);
        console.log('ZamZamInstance before load:', typeof window.ZamZamInstance);
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            console.log('ZamZam script loaded');
            console.log('ZamZam after load:', typeof window.ZamZam);
            console.log('ZamZamInstance after load:', typeof window.ZamZamInstance);
            
            setTimeout(() => {
                console.log('After timeout - ZamZamInstance:', window.ZamZamInstance);
                updateDebugInfo();
            }, 500);
        };
        script.onerror = function() {
            console.error('Failed to load ZamZam script');
            document.getElementById('debug-info').innerHTML = '<div class="error">❌ Failed to load ZamZam script</div>';
        };
        document.head.appendChild(script);
        
        function updateDebugInfo() {
            const debugDiv = document.getElementById('debug-info');
            const info = [
                'ZamZam loaded: ' + (typeof window.ZamZam !== 'undefined'),
                'ZamZamInstance: ' + (typeof window.ZamZamInstance !== 'undefined'),
                'Components found: ' + (window.ZamZamInstance ? window.ZamZamInstance.components.size : 'N/A'),
                'Document ready: ' + (document.readyState === 'complete')
            ].join('<br>');
            
            debugDiv.innerHTML = info;
        }
    </script>
</body>
</html> 
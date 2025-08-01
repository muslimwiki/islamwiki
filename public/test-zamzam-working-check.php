<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Working Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status { padding: 10px; border-radius: 4px; margin: 10px 0; font-weight: bold; }
        .status.success { background: #d1fae5; color: #065f46; }
        .status.error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Working Check</h1>
    
    <div class="test">
        <h3>Status:</h3>
        <div id="status">Checking...</div>
    </div>

    <div class="test">
        <h3>Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">Toggle Test</button>
            <div z-show="test" class="alert alert-success">This should show/hide</div>
        </div>
    </div>

    <script>
        const statusDiv = document.getElementById('status');
        
        function checkZamZam() {
            const checks = [];
            
            // Check if ZamZam is loaded
            checks.push('ZamZam loaded: ' + (typeof window.ZamZam !== 'undefined'));
            checks.push('ZamZamInstance: ' + (typeof window.ZamZamInstance !== 'undefined'));
            
            if (window.ZamZamInstance) {
                checks.push('Components found: ' + window.ZamZamInstance.components.size);
                
                // Check if elements with z-data exist
                const zDataElements = document.querySelectorAll('[z-data]');
                checks.push('z-data elements: ' + zDataElements.length);
                
                // Check if elements with z-click exist
                const zClickElements = document.querySelectorAll('[z-click]');
                checks.push('z-click elements: ' + zClickElements.length);
                
                // Check if elements with z-show exist
                const zShowElements = document.querySelectorAll('[z-show]');
                checks.push('z-show elements: ' + zShowElements.length);
                
                if (window.ZamZamInstance.components.size > 0) {
                    statusDiv.innerHTML = '<div class="status success">✅ ZamZam is working! Components found.</div>';
                } else {
                    statusDiv.innerHTML = '<div class="status error">❌ ZamZam loaded but no components found.</div>';
                }
            } else {
                statusDiv.innerHTML = '<div class="status error">❌ ZamZam failed to load.</div>';
            }
            
            console.log('ZamZam checks:', checks);
        }
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            console.log('ZamZam script loaded');
            setTimeout(checkZamZam, 1000);
        };
        script.onerror = function() {
            console.error('Failed to load ZamZam script');
            statusDiv.innerHTML = '<div class="status error">❌ Failed to load ZamZam script</div>';
        };
        document.head.appendChild(script);
        
        // Check every 2 seconds
        setInterval(checkZamZam, 2000);
    </script>
</body>
</html> 
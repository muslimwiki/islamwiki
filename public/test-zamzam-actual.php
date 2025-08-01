<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Actual Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Actual Test</h1>
    
    <div class="test">
        <h3>Console Output:</h3>
        <div id="console">Loading...</div>
    </div>

    <div class="test">
        <h3>Simple Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">Toggle Test</button>
            <div z-show="test" class="alert alert-success">This should show/hide</div>
        </div>
    </div>

    <script>
        // Capture console output
        const originalLog = console.log;
        const originalError = console.error;
        const consoleDiv = document.getElementById('console');
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            consoleDiv.innerHTML += '<div style="color: blue;">LOG: ' + args.join(' ') + '</div>';
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            consoleDiv.innerHTML += '<div style="color: red;">ERROR: ' + args.join(' ') + '</div>';
        };
        
        console.log('=== ZAMZAM ACTUAL TEST START ===');
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
                if (window.ZamZamInstance) {
                    console.log('Components found:', window.ZamZamInstance.components.size);
                    window.ZamZamInstance.components.forEach((component, id) => {
                        console.log('Component:', id, component);
                    });
                }
            }, 1000);
        };
        script.onerror = function() {
            console.error('Failed to load ZamZam script');
        };
        document.head.appendChild(script);
    </script>
</body>
</html> 
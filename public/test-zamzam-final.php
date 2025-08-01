<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Final Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
        }
        .status.success {
            background: #d1fae5;
            color: #065f46;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Final Test</h1>
    
    <div class="test-box">
        <h2>Status Check</h2>
        <div id="status">Checking ZamZam status...</div>
    </div>

    <div class="test-box">
        <h2>Basic Toggle Test</h2>
        <div z-data='{"show": false}'>
            <button z-click="show = !show" class="btn">
                Toggle Content
            </button>
            <div z-show="show" class="alert alert-success">
                ✅ ZamZam.js is working! This content is now visible.
            </div>
        </div>
    </div>

    <div class="test-box">
        <h2>Counter Test</h2>
        <div z-data='{"count": 0}'>
            <p>Count: <span z-text="count"></span></p>
            <button z-click="count++" class="btn">+1</button>
            <button z-click="count--" class="btn">-1</button>
            <button z-click="count = 0" class="btn">Reset</button>
        </div>
    </div>

    <div class="test-box">
        <h2>Text Binding Test</h2>
        <div z-data='{"name": ""}'>
            <input type="text" z-model="name" placeholder="Enter your name" style="width: 100%; padding: 8px; margin: 10px 0;">
            <p>Hello, <span z-text="name || 'Guest'"></span>!</p>
        </div>
    </div>

    <script src="/js/zamzam.js"></script>
    <script>
        // Test if ZamZam is loaded
        setTimeout(() => {
            const statusDiv = document.getElementById('status');
            
            if (window.ZamZamInstance) {
                statusDiv.innerHTML = '<div class="status success">✅ ZamZam is working correctly!</div>';
                console.log('✅ ZamZam is working!');
            } else {
                statusDiv.innerHTML = '<div class="status error">❌ ZamZam failed to load</div>';
                console.log('❌ ZamZam failed to load');
            }
        }, 1000);
    </script>
</body>
</html> 
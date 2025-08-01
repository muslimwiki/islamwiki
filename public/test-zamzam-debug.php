<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam.js Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { margin: 20px 0; padding: 10px; border: 1px solid #ccc; }
        button { padding: 10px; margin: 5px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <h1>ZamZam.js Debug Test</h1>
    
    <!-- Simple Test 1 -->
    <div class="test">
        <h3>Test 1: Basic Toggle</h3>
        <div z-data='{"show": false}'>
            <button z-click="show = !show">Toggle</button>
            <div z-show="show">This should show/hide</div>
        </div>
    </div>

    <!-- Simple Test 2 -->
    <div class="test">
        <h3>Test 2: Counter</h3>
        <div z-data='{"count": 0}'>
            <p>Count: <span z-text="count"></span></p>
            <button z-click="count++">+</button>
            <button z-click="count--">-</button>
        </div>
    </div>

    <!-- Simple Test 3 -->
    <div class="test">
        <h3>Test 3: Text Input</h3>
        <div z-data='{"name": ""}'>
            <input type="text" z-model="name" placeholder="Enter name">
            <p>Hello, <span z-text="name || 'Guest'"></span>!</p>
        </div>
    </div>

    <script src="/js/zamzam-debug.js"></script>
</body>
</html> 
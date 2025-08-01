<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam.js Test</title>
    <link rel="stylesheet" href="/css/zamzam.css">
    <style>
        body {
            font-family: 'Amiri', serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .test-section {
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
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            min-width: 200px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin: 10px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <h1>🕌 ZamZam.js Test Page</h1>
    <p>Testing the custom JavaScript framework for IslamWiki</p>

    <!-- Test 1: Basic Toggle -->
    <div class="test-section">
        <h2>Test 1: Basic Toggle</h2>
        <div z-data='{"show": false}'>
            <button z-click="show = !show" class="btn">
                Toggle Content
            </button>
            <div z-show="show" class="alert alert-success">
                This content is now visible! ZamZam.js is working.
            </div>
        </div>
    </div>

    <!-- Test 2: Counter -->
    <div class="test-section">
        <h2>Test 2: Counter</h2>
        <div z-data='{"count": 0}'>
            <p>Count: <span z-text="count"></span></p>
            <button z-click="count++" class="btn">Increment</button>
            <button z-click="count--" class="btn">Decrement</button>
            <button z-click="count = 0" class="btn">Reset</button>
        </div>
    </div>

    <!-- Test 3: Form Input -->
    <div class="test-section">
        <h2>Test 3: Form Input</h2>
        <div z-data='{"name": "", "email": ""}'>
            <div class="form-group">
                <label>Name:</label>
                <input type="text" z-model="name" placeholder="Enter your name">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" z-model="email" placeholder="Enter your email">
            </div>
            <p>Hello, <span z-text="name || 'Guest'"></span>!</p>
            <p>Your email: <span z-text="email || 'Not provided'"></span></p>
        </div>
    </div>

    <!-- Test 4: Dropdown Menu -->
    <div class="test-section">
        <h2>Test 4: Dropdown Menu</h2>
        <div class="dropdown" z-data='{"open": false}'>
            <button z-click="open = !open" class="btn">
                Open Menu
            </button>
            <div z-show="open" z-click-away="open = false" class="dropdown-menu">
                <p>This is a dropdown menu</p>
                <p>Click outside to close</p>
                <button z-click="open = false" class="btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Test 5: Conditional Classes -->
    <div class="test-section">
        <h2>Test 5: Conditional Classes</h2>
        <div z-data='{"isActive": false, "isError": false}'>
            <button z-click="isActive = !isActive" class="btn">
                Toggle Active
            </button>
            <button z-click="isError = !isError" class="btn">
                Toggle Error
            </button>
            <div z-class='{"alert-success": isActive, "alert-error": isError}'>
                This div changes classes based on state
            </div>
        </div>
    </div>

    <!-- Test 6: Methods -->
    <div class="test-section">
        <h2>Test 6: Methods</h2>
        <div z-data='{"message": "", "showAlert": false}' 
             z-methods='{"showMessage": "function(msg) { this.message = msg; this.showAlert = true; setTimeout(() => this.showAlert = false, 3000); }"}'>
            <button z-click="showMessage('Bismillah! ZamZam.js is working perfectly.')" class="btn">
                Show Islamic Message
            </button>
            <div z-show="showAlert" class="alert alert-success">
                <span z-text="message"></span>
            </div>
        </div>
    </div>

    <!-- Test 7: Transitions -->
    <div class="test-section">
        <h2>Test 7: Transitions</h2>
        <div z-data='{"showTransition": false}'>
            <button z-click="showTransition = !showTransition" class="btn">
                Toggle with Transition
            </button>
            <div z-show="showTransition" 
                 class="alert alert-success z-transition z-duration-300">
                This element has smooth transitions!
            </div>
        </div>
    </div>

    <!-- Test 8: Islamic-themed Animation -->
    <div class="test-section">
        <h2>Test 8: Islamic-themed Animation</h2>
        <div z-data='{"showPrayer": false}'>
            <button z-click="showPrayer = !showPrayer" class="btn">
                Show Prayer Animation
            </button>
            <div z-show="showPrayer" 
                 class="alert alert-success z-prayer-fade">
                🕌 Prayer animation is active
            </div>
        </div>
    </div>

    <script src="/js/zamzam.js"></script>
    <script>
        // Test if ZamZam is loaded
        console.log('ZamZam loaded:', typeof window.ZamZam);
        console.log('ZamZam instance:', window.ZamZamInstance);
    </script>
</body>
</html> 
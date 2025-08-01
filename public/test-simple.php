<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple ZamZam Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { margin: 20px 0; padding: 10px; border: 1px solid #ccc; }
        button { padding: 10px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Simple ZamZam Test</h1>
    
    <div class="test">
        <h3>Basic Toggle Test</h3>
        <div z-data='{"show": false}'>
            <button z-click="show = !show">Toggle</button>
            <div z-show="show">This should show/hide</div>
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
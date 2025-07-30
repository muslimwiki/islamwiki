<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\Page;

echo "Testing Enhanced Content Rendering\n";
echo "==================================\n\n";

try {
    // Create database connection
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Create a test page with rich markdown content
    $testContent = <<<'MARKDOWN'
# Enhanced Content Rendering Test

This page demonstrates the enhanced content rendering capabilities of IslamWiki.

## Features

### Headers
- **H1**: Main page title
- **H2**: Section headers  
- **H3**: Subsection headers

### Text Formatting
- **Bold text** using `**bold**`
- *Italic text* using `*italic*`
- `Inline code` using backticks

### Lists

#### Unordered List
- First item
- Second item
- Third item with **bold** and *italic*

#### Ordered List
1. First numbered item
2. Second numbered item
3. Third numbered item

### Code Blocks

#### PHP Code
```php
<?php
function helloWorld() {
    echo "Hello, World!";
    return true;
}
```

#### JavaScript Code
```javascript
function greet(name) {
    console.log(`Hello, ${name}!`);
    return `Welcome ${name}`;
}
```

#### HTML Code
```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>
```

### Blockquotes

> This is a blockquote with important information.
> 
> It can span multiple lines and contain **bold** and *italic* text.

### Links

- [IslamWiki Homepage](https://islamwiki.org)
- [GitHub Repository](https://github.com/islamwiki/islamwiki)
- Auto-linked URL: https://example.com

### Horizontal Rules

---

Above and below are horizontal rules created with `---`.

---

### Mixed Content

Here's a paragraph with **bold text**, *italic text*, `inline code`, and a [link](https://example.com).

> This blockquote contains:
> - A list item
> - **Bold text**
> - `Inline code`

```python
def calculate_fibonacci(n):
    if n <= 1:
        return n
    return calculate_fibonacci(n-1) + calculate_fibonacci(n-2)
```

This demonstrates the full range of markdown features supported by the enhanced content rendering system.
MARKDOWN;

    // Create the test page
    $testPage = new Page($connection, [
        'title' => 'Enhanced Content Rendering Test v2',
        'slug' => 'content-rendering-test-v2',
        'content' => $testContent,
        'content_format' => 'markdown',
        'namespace' => 'main',
        'is_locked' => false
    ]);
    
    $saved = $testPage->save();
    echo "  " . ($saved ? "✅" : "❌") . " Test page created successfully\n";
    
    if ($saved) {
        echo "  📊 Test page details:\n";
        echo "    - ID: {$testPage->getAttribute('id')}\n";
        echo "    - Title: {$testPage->getAttribute('title')}\n";
        echo "    - Slug: {$testPage->getAttribute('slug')}\n";
        echo "    - Content length: " . strlen($testPage->getAttribute('content')) . " characters\n";
    }

    // Test the content rendering
    echo "\n🔍 Testing content rendering...\n";
    
    // Create a mock PageController to test rendering
    $container = new \IslamWiki\Core\Container();
    $container->bind('db', $connection);
    
    $logger = new \IslamWiki\Core\Logging\Logger(__DIR__ . '/../logs');
    $container->bind(\Psr\Log\LoggerInterface::class, function() use ($logger) {
        return $logger;
    });
    
    $pageController = new \IslamWiki\Http\Controllers\PageController($connection, $container);
    
    // Test the parseWikiText method
    $reflection = new ReflectionClass($pageController);
    $method = $reflection->getMethod('parseWikiText');
    $method->setAccessible(true);
    
    $renderedContent = $method->invoke($pageController, $testContent);
    
    echo "  ✅ Content rendering completed\n";
    echo "  📊 Rendered content length: " . strlen($renderedContent) . " characters\n";
    
    // Check for specific HTML elements
    $checks = [
        'Headers' => ['<h1>', '<h2>', '<h3>'],
        'Bold text' => ['<strong>'],
        'Italic text' => ['<em>'],
        'Code blocks' => ['<pre class="code-block'],
        'Inline code' => ['<code>'],
        'Lists' => ['<ul>', '<ol>'],
        'Blockquotes' => ['<blockquote>'],
        'Links' => ['<a href='],
        'Horizontal rules' => ['<hr>']
    ];
    
    echo "\n📋 Rendering checks:\n";
    foreach ($checks as $feature => $elements) {
        $found = false;
        foreach ($elements as $element) {
            if (strpos($renderedContent, $element) !== false) {
                $found = true;
                break;
            }
        }
        echo "  " . ($found ? "✅" : "❌") . " {$feature}\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Enhanced content rendering test completed successfully!\n";
echo "\nYou can now visit: http://local.islam.wiki/content-rendering-test-v2\n";
echo "\nDone!\n"; 
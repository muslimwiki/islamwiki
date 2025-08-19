# IslamWiki Extension Development Guide

## 🎯 **Overview**

IslamWiki's extension system follows a **WordPress-inspired plugin architecture** combined with **modern PHP practices**. Extensions allow you to add new functionality, content types, and features to the platform while maintaining clean separation of concerns.

---

## 🏗️ **Extension Architecture**

### **Design Principles**
- **Modular**: Each extension is self-contained
- **Hook-Based**: Action and filter hook system
- **Service-Oriented**: Dependency injection support
- **Template-Driven**: Twig template integration
- **Performance-Focused**: Lazy loading and optimization

### **Extension Types**
```
Extension Categories:
├── 📁 Content Extensions      # Add new content types
├── 📁 Functionality Extensions # Add new features
├── 📁 Integration Extensions  # Connect external services
├── 📁 Theme Extensions        # Add new skins/themes
└── 📁 Utility Extensions      # Helper and utility functions
```

---

## 📁 **Extension Structure**

### **Standard Extension Layout**
```
extensions/
├── 📁 {ExtensionName}/
│   ├── 📄 {ExtensionName}.php      # Main extension class
│   ├── 📄 extension.json           # Extension metadata
│   ├── 📁 assets/                  # CSS, JS, images
│   │   ├── 📁 css/
│   │   ├── 📁 js/
│   │   └── 📁 images/
│   ├── 📁 templates/               # Twig templates
│   ├── 📁 database/                # Database migrations
│   │   ├── 📁 migrations/
│   │   └── 📁 seeds/
│   ├── 📁 src/                     # PHP source code
│   │   ├── 📁 Controllers/
│   │   ├── 📁 Models/
│   │   ├── 📁 Services/
│   │   └── 📁 Providers/
│   ├── 📁 docs/                    # Extension documentation
│   ├── 📁 tests/                   # Extension tests
│   └── 📄 README.md                # Extension readme
```

### **Required Files**
- **Main Extension Class**: Core extension functionality
- **Extension Metadata**: Configuration and information
- **README**: Documentation and usage instructions

---

## 🔌 **Extension Development**

### **1. Create Extension Directory**
```bash
mkdir -p extensions/MyExtension/{assets/{css,js,images},templates,database/{migrations,seeds},src/{Controllers,Models,Services,Providers},docs,tests}
```

### **2. Create Extension Metadata**
```json
{
  "name": "MyExtension",
  "version": "0.0.1",
  "description": "A sample extension for IslamWiki",
  "author": "Your Name",
  "license": "GPL-3.0",
  "requires": {
    "islamwiki": ">=0.0.19",
    "php": ">=8.0"
  },
  "category": "content",
  "tags": ["sample", "tutorial"],
  "homepage": "https://github.com/yourname/MyExtension"
}
```

### **3. Create Main Extension Class**
```php
<?php

namespace IslamWiki\Extensions\MyExtension;

use IslamWiki\Core\Extensions\ExtensionInterface;
use IslamWiki\Core\Extensions\ExtensionManager;

class MyExtension implements ExtensionInterface
{
    private ExtensionManager $manager;
    
    public function __construct(ExtensionManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function getName(): string
    {
        return 'MyExtension';
    }
    
    public function getVersion(): string
    {
        return '0.0.1';
    }
    
    public function install(): bool
    {
        // Installation logic
        return true;
    }
    
    public function uninstall(): bool
    {
        // Cleanup logic
        return true;
    }
    
    public function activate(): bool
    {
        // Activation logic
        return true;
    }
    
    public function deactivate(): bool
    {
        // Deactivation logic
        return true;
    }
    
    public function registerHooks(): void
    {
        // Register hooks
        $this->manager->addAction('init', [$this, 'onInit']);
        $this->manager->addFilter('content_types', [$this, 'addContentTypes']);
    }
    
    public function onInit(): void
    {
        // Extension initialization
    }
    
    public function addContentTypes(array $types): array
    {
        // Add new content types
        $types['my_content'] = 'My Content Type';
        return $types;
    }
}
```

---

## 🪝 **Hook System**

### **Action Hooks**
Actions are events that extensions can listen to and respond to:

```php
// Register an action hook
$this->manager->addAction('user_registered', [$this, 'onUserRegistered']);

// Implement the action handler
public function onUserRegistered(User $user): void
{
    // Send welcome email
    $this->emailService->sendWelcomeEmail($user);
}
```

### **Filter Hooks**
Filters allow extensions to modify data:

```php
// Register a filter hook
$this->manager->addFilter('content_title', [$this, 'modifyContentTitle']);

// Implement the filter handler
public function modifyContentTitle(string $title): string
{
    // Modify the title
    return 'Modified: ' . $title;
}
```

### **Available Hooks**
```
Core Hooks:
├── 📁 init                    # System initialization
├── 📁 user_registered         # User registration
├── 📁 user_login             # User login
├── 📁 content_created        # Content creation
├── 📁 content_updated        # Content update
├── 📁 content_deleted        # Content deletion
├── 📁 extension_activated    # Extension activation
├── 📁 extension_deactivated  # Extension deactivation
└── 📁 admin_menu             # Admin menu generation
```

---

## 🎨 **Template Integration**

### **Twig Template Support**
Extensions can provide their own templates:

```php
// Register template directory
$this->manager->addTemplatePath(__DIR__ . '/templates');

// Use in controller
public function render(): string
{
    return $this->twigRenderer->render('my_extension/index.twig', [
        'data' => $this->getData()
    ]);
}
```

### **Template Structure**
```
templates/
├── 📁 layouts/               # Layout templates
├── 📁 pages/                 # Page templates
├── 📁 components/            # Reusable components
└── 📁 partials/              # Partial templates
```

### **Template Example**
```twig
{# templates/pages/index.twig #}
{% extends "layouts/default.twig" %}

{% block title %}My Extension{% endblock %}

{% block content %}
<div class="my-extension">
    <h1>{{ title }}</h1>
    <div class="content">
        {{ content|raw }}
    </div>
</div>
{% endblock %}
```

---

## 🗄️ **Database Integration**

### **Migration System**
Extensions can provide database migrations:

```php
// Create migration file
class CreateMyExtensionTables extends Migration
{
    public function up(): void
    {
        $this->schema->create('my_extension_data', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('my_extension_data');
    }
}
```

### **Model Integration**
```php
<?php

namespace IslamWiki\Extensions\MyExtension\Models;

use IslamWiki\Core\Models\BaseModel;

class MyExtensionData extends BaseModel
{
    protected $table = 'my_extension_data';
    
    protected $fillable = [
        'title',
        'content'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
```

---

## 🎯 **Content Type Extensions**

### **Adding New Content Types**
```php
public function addContentTypes(array $types): array
{
    $types['fatwa'] = [
        'name' => 'Fatwa',
        'singular' => 'Fatwa',
        'plural' => 'Fatawa',
        'supports' => ['title', 'editor', 'author', 'categories'],
        'taxonomies' => ['fatwa_category', 'scholar'],
        'capabilities' => [
            'edit_posts' => 'edit_fatawa',
            'edit_others_posts' => 'edit_others_fatawa',
            'publish_posts' => 'publish_fatawa',
            'read_private_posts' => 'read_private_fatawa',
            'delete_posts' => 'delete_fatawa'
        ]
    ];
    
    return $types;
}
```

### **Content Type Controller**
```php
<?php

namespace IslamWiki\Extensions\MyExtension\Controllers;

use IslamWiki\Core\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class FatwaController extends Controller
{
    public function index(): Response
    {
        $fatawa = Fatwa::paginate(20);
        
        return $this->view('fatwa/index', [
            'fatawa' => $fatawa
        ]);
    }
    
    public function show(string $slug): Response
    {
        $fatwa = Fatwa::where('slug', $slug)->firstOrFail();
        
        return $this->view('fatwa/show', [
            'fatwa' => $fatwa
        ]);
    }
}
```

---

## 🔌 **Service Integration**

### **Service Provider**
```php
<?php

namespace IslamWiki\Extensions\MyExtension\Providers;

use IslamWiki\Core\Providers\ServiceProvider;
use IslamWiki\Extensions\MyExtension\Services\MyExtensionService;

class MyExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MyExtensionService::class, function ($app) {
            return new MyExtensionService($app);
        });
    }
    
    public function boot(): void
    {
        // Boot extension services
    }
}
```

### **Service Class**
```php
<?php

namespace IslamWiki\Extensions\MyExtension\Services;

class MyExtensionService
{
    private $app;
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function doSomething(): string
    {
        return 'Extension service is working!';
    }
}
```

---

## 🧪 **Testing Extensions**

### **Test Structure**
```
tests/
├── 📁 Unit/                  # Unit tests
├── 📁 Integration/           # Integration tests
├── 📁 Feature/               # Feature tests
└── 📁 TestCase.php           # Base test case
```

### **Test Example**
```php
<?php

namespace IslamWiki\Extensions\MyExtension\Tests\Unit;

use PHPUnit\Framework\TestCase;
use IslamWiki\Extensions\MyExtension\MyExtension;

class MyExtensionTest extends TestCase
{
    public function testExtensionName(): void
    {
        $extension = new MyExtension();
        $this->assertEquals('MyExtension', $extension->getName());
    }
    
    public function testExtensionVersion(): void
    {
        $extension = new MyExtension();
        $this->assertEquals('0.0.1', $extension->getVersion());
    }
}
```

---

## 🚀 **Performance Optimization**

### **Lazy Loading**
```php
public function registerHooks(): void
{
    // Only register hooks when needed
    if ($this->shouldRegisterHooks()) {
        $this->manager->addAction('init', [$this, 'onInit']);
    }
}
```

### **Caching**
```php
public function getData(): array
{
    return $this->cache->remember('my_extension_data', 3600, function () {
        return $this->fetchData();
    });
}
```

### **Asset Optimization**
```php
public function enqueueAssets(): void
{
    // Only load assets when needed
    if ($this->isMyExtensionPage()) {
        $this->assetManager->enqueueStyle('my-extension', 'assets/css/style.css');
        $this->assetManager->enqueueScript('my-extension', 'assets/js/script.js');
    }
}
```

---

## 🔒 **Security Best Practices**

### **Input Validation**
```php
public function validateInput(array $data): array
{
    return [
        'title' => filter_var($data['title'], FILTER_SANITIZE_STRING),
        'content' => filter_var($data['content'], FILTER_SANITIZE_STRING),
        'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL)
    ];
}
```

### **Output Escaping**
```php
// In Twig templates, always escape output
{{ user_input|escape }}

// Or use raw filter only when you trust the content
{{ trusted_content|raw }}
```

### **Permission Checking**
```php
public function canEditContent(User $user, Content $content): bool
{
    return $user->hasPermission('edit_content') || 
           $user->id === $content->user_id;
}
```

---

## 📚 **Documentation**

### **Extension README**
```markdown
# MyExtension

A sample extension for IslamWiki that demonstrates extension development.

## Features
- Feature 1
- Feature 2
- Feature 3

## Installation
1. Copy to extensions/ directory
2. Activate in admin panel
3. Configure settings

## Usage
Describe how to use the extension

## Development
Information for developers
```

### **API Documentation**
Document all public methods and hooks:

```php
/**
 * MyExtension main class
 * 
 * This extension provides sample functionality for IslamWiki.
 * 
 * @package IslamWiki\Extensions\MyExtension
 * @version 0.0.1
 */
class MyExtension implements ExtensionInterface
{
    /**
     * Initialize the extension
     * 
     * @return void
     */
    public function onInit(): void
    {
        // Implementation
    }
}
```

---

## 🚀 **Deployment**

### **Extension Package**
```bash
# Create extension package
tar -czf MyExtension-0.0.1.tar.gz MyExtension/

# Or use Composer
composer create-package islamwiki/my-extension
```

### **Installation**
```bash
# Extract to extensions directory
tar -xzf MyExtension-0.0.1.tar.gz -C extensions/

# Set permissions
chmod -R 755 extensions/MyExtension/
```

---

## 🔍 **Troubleshooting**

### **Common Issues**
1. **Extension not loading**: Check file permissions and namespace
2. **Hooks not working**: Verify hook registration in registerHooks()
3. **Templates not found**: Check template path registration
4. **Database errors**: Verify migration files and table names

### **Debug Mode**
Enable debug mode to see detailed error information:

```php
// In extension class
public function debug(): void
{
    if ($this->isDebugMode()) {
        error_log('MyExtension Debug: ' . print_r($this->getDebugInfo(), true));
    }
}
```

---

## 📞 **Support & Resources**

### **Documentation**
- **Extension API Reference**: Complete API documentation
- **Hook Reference**: Available hooks and usage
- **Template Guide**: Twig template development
- **Database Guide**: Migration and model development

### **Community**
- **Developer Forum**: Ask questions and share code
- **Code Examples**: Sample extensions and snippets
- **Best Practices**: Development guidelines and standards

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Extension System:** WordPress-inspired with Modern PHP 
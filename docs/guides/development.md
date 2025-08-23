# IslamWiki Development Guide

## 🎯 **Overview**

This guide provides comprehensive information for developers working with IslamWiki. It covers the hybrid architecture, development practices, and best practices for building Islamic knowledge platforms.

---

## 🏗️ **Architecture Overview**

### **Hybrid Architecture: MediaWiki + WordPress + Modern PHP**
IslamWiki combines the best features of three powerful systems:

- **MediaWiki**: Content management, versioning, collaborative editing
- **WordPress**: Plugin system, theme system, user experience
- **Modern PHP**: Performance, security, developer experience

### **Core Components**
```
Core Systems:
├── 📁 Simplified Routing     # Modern PHP routing system
├── 📁 Extension System       # WordPress-inspired plugin architecture
├── 📁 Skin System            # WordPress-inspired theme architecture
├── 📁 Content Management     # MediaWiki-inspired content system
├── 📁 Database System        # Multi-database architecture
└── 📁 Security System        # Enterprise-grade security
```

---

## 🚀 **Getting Started**

### **Prerequisites**
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer for dependency management
- Git for version control

### **Installation**
```bash
# Clone the repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Copy configuration
cp LocalSettings.php.example LocalSettings.php

# Configure database
# Edit LocalSettings.php with your database settings

# Run migrations
php scripts/database/migrate.php

# Start development server
php -S localhost:8000 -t public
```

---

## 📁 **Project Structure**

### **Directory Organization**
```
local.islam.wiki/
├── 📁 src/                   # PHP source code
│   ├── 📁 Core/             # Framework core
│   ├── 📁 Http/             # HTTP layer
│   ├── 📁 Models/           # Data models
│   └── 📁 Providers/        # Service providers
├── 📁 resources/             # Frontend assets
│   ├── 📁 assets/           # Framework assets
│   └── 📁 views/            # Twig templates
├── 📁 extensions/            # Extension system
├── 📁 skins/                 # Skin system
├── 📁 public/                # Web entry points
├── 📁 config/                # Configuration files
├── 📁 database/              # Database files
└── 📁 docs/                  # Documentation
```

### **Key Directories Explained**
- **`src/`**: Contains all PHP source code
- **`resources/`**: Contains frontend assets and templates
- **`extensions/`**: Contains extension system
- **`skins/`**: Contains skin system
- **`public/`**: Contains web entry points only

---

## 🔌 **Extension Development**

### **Creating Extensions**
Extensions follow a WordPress-inspired architecture:

```php
<?php

namespace IslamWiki\Extensions\MyExtension;

use IslamWiki\Core\Extensions\ExtensionInterface;

class MyExtension implements ExtensionInterface
{
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
    
    public function registerHooks(): void
    {
        // Register hooks
        $this->manager->addAction('init', [$this, 'onInit']);
    }
}
```

### **Extension Structure**
```
extensions/MyExtension/
├── 📄 MyExtension.php       # Main extension class
├── 📄 extension.json        # Extension metadata
├── 📁 assets/               # CSS, JS, images
├── 📁 templates/            # Twig templates
├── 📁 database/             # Migrations and seeds
├── 📁 src/                  # PHP source code
└── 📁 docs/                 # Extension documentation
```

---

## 🎨 **Skin Development**

### **Creating Skins**
Skins follow a WordPress-inspired theme architecture:

```php
<?php

namespace IslamWiki\Skins\MySkin;

use IslamWiki\Core\Skins\SkinInterface;

class MySkin implements SkinInterface
{
    public function getName(): string
    {
        return 'MySkin';
    }
    
    public function getVersion(): string
    {
        return '0.0.1';
    }
    
    public function getAssets(): array
    {
        return [
            'css' => ['css/style.css'],
            'js' => ['js/main.js']
        ];
    }
}
```

### **Skin Structure**
```
skins/MySkin/
├── 📁 css/                  # Stylesheets
├── 📁 js/                   # JavaScript
├── 📁 templates/            # Twig templates
├── 📁 images/               # Images
├── 📄 skin.json             # Skin configuration
└── 📄 README.md             # Skin documentation
```

---

## 🗄️ **Database Development**

### **Multi-Database Architecture**
IslamWiki uses multiple databases for different content types:

```php
// Main database connection
$mainDb = $this->container->get('database.main');

// Quran database connection
$quranDb = $this->container->get('database.quran');

// Hadith database connection
$hadithDb = $this->container->get('database.hadith');
```

### **Creating Migrations**
```php
<?php

use IslamWiki\Core\Database\Migration;

class CreateMyTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('my_table', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('my_table');
    }
}
```

### **Creating Models**
```php
<?php

namespace IslamWiki\Models;

use IslamWiki\Core\Models\BaseModel;

class MyModel extends BaseModel
{
    protected $table = 'my_table';
    
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

## 🎯 **Content Management**

### **Content Types**
IslamWiki supports various Islamic content types:

- **Articles**: General Islamic content
- **Wiki Pages**: Collaborative content
- **Fatwas**: Islamic rulings
- **Quran**: Complete Quran integration
- **Hadith**: Authenticated hadith collections
- **Sahaba**: Companion biographies
- **Duas**: Islamic supplications

### **Creating Content Types**
```php
<?php

namespace IslamWiki\Extensions\MyContentType;

use IslamWiki\Core\Content\ContentTypeInterface;

class MyContentType implements ContentTypeInterface
{
    public function getName(): string
    {
        return 'my_content';
    }
    
    public function getLabel(): string
    {
        return 'My Content';
    }
    
    public function getFields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'Content',
                'required' => true
            ]
        ];
    }
}
```

---

## 🔐 **Security Development**

### **Security Best Practices**
- **Input Validation**: Always validate user input
- **Output Escaping**: Escape output to prevent XSS
- **Authentication**: Implement proper authentication
- **Authorization**: Check permissions before actions
- **Content Validation**: Validate Islamic content

### **Input Validation Example**
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

### **Permission Checking**
```php
public function canEditContent(User $user, Content $content): bool
{
    return $user->hasPermission('edit_content') || 
           $user->id === $content->user_id;
}
```

---

## 🧪 **Testing**

### **Testing Framework**
IslamWiki uses PHPUnit for testing:

```php
<?php

namespace IslamWiki\Tests\Unit;

use PHPUnit\Framework\TestCase;
use IslamWiki\Extensions\MyExtension\MyExtension;

class MyExtensionTest extends TestCase
{
    public function testExtensionName(): void
    {
        $extension = new MyExtension();
        $this->assertEquals('MyExtension', $extension->getName());
    }
}
```

### **Running Tests**
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/MyExtensionTest.php

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 🚀 **Performance Development**

### **Performance Best Practices**
- **Caching**: Implement proper caching strategies
- **Lazy Loading**: Load content only when needed
- **Asset Optimization**: Optimize CSS, JS, and images
- **Database Optimization**: Optimize database queries
- **CDN Integration**: Use CDN for static assets

### **Caching Example**
```php
public function getData(): array
{
    return $this->cache->remember('my_data', 3600, function () {
        return $this->fetchData();
    });
}
```

### **Asset Optimization**
```php
public function enqueueAssets(): void
{
    // Only load assets when needed
    if ($this->isMyPage()) {
        $this->assetManager->enqueueStyle('my-style', 'css/style.css');
        $this->assetManager->enqueueScript('my-script', 'js/script.js');
    }
}
```

---

## 📚 **Template Development**

### **Twig Templates**
IslamWiki uses Twig for templating:

```twig
{# templates/pages/home.twig #}
{% extends "layouts/default.twig" %}

{% block title %}Home{% endblock %}

{% block content %}
<div class="hero-section">
    <h1>{{ title }}</h1>
    <p>{{ description }}</p>
</div>

{% if featured_content %}
<section class="featured-content">
    {% for item in featured_content %}
    <article class="content-item">
        <h2>{{ item.title }}</h2>
        <p>{{ item.excerpt }}</p>
    </article>
    {% endfor %}
</section>
{% endif %}
{% endblock %}
```

### **Template Inheritance**
```twig
{# templates/layouts/default.twig #}
<!DOCTYPE html>
<html lang="{{ app.locale }}">
<head>
    <title>{% block title %}{{ site.title }}{% endblock %}</title>
    {% block head %}{% endblock %}
</head>
<body>
    {% block content %}{% endblock %}
    {% block scripts %}{% endblock %}
</body>
</html>
```

---

## 🔧 **Configuration**

### **Configuration Files**
- **`LocalSettings.php`**: Main configuration
- **`IslamSettings.php`**: Islamic-specific settings
- **`.env`**: Environment variables

### **Configuration Example**
```php
// LocalSettings.php
$wgDBserver = 'localhost';
$wgDBname = 'islamwiki';
$wgDBuser = 'islamwiki_user';
$wgDBpassword = 'secure_password';

// Islamic-specific settings
$wgQuranDatabase = 'quran_db';
$wgHadithDatabase = 'hadith_db';
$wgSalahTimesAPI = 'custom_api';
```

---

## 📊 **Logging & Debugging**

### **Logging System**
```php
use IslamWiki\Core\Logging\Logger;

$logger = $this->container->get(Logger::class);

$logger->info('User logged in', ['user_id' => $user->id]);
$logger->error('Database connection failed', ['error' => $e->getMessage()]);
```

### **Debug Mode**
```php
// Enable debug mode
$wgDebug = true;
$wgShowDebug = true;

// Debug information
if ($wgDebug) {
    error_log('Debug: ' . print_r($debugInfo, true));
}
```

---

## 🔄 **Version Control**

### **Git Workflow**
```bash
# Create feature branch
git checkout -b feature/my-feature

# Make changes
git add .
git commit -m "Add my feature"

# Push to remote
git push origin feature/my-feature

# Create pull request
# Merge after review
```

### **Commit Messages**
Use conventional commit format:
```
feat: add new content type
fix: resolve authentication issue
docs: update development guide
style: format code according to standards
refactor: improve extension system
test: add unit tests for new feature
```

---

## 📈 **Monitoring & Analytics**

### **Performance Monitoring**
- **Response Times**: Monitor API response times
- **Error Rates**: Track error frequencies
- **Resource Usage**: Monitor memory and CPU usage
- **User Experience**: Track user interactions

### **Health Checks**
```php
public function healthCheck(): array
{
    return [
        'database' => $this->checkDatabase(),
        'cache' => $this->checkCache(),
        'extensions' => $this->checkExtensions(),
        'overall' => 'healthy'
    ];
}
```

---

## 🚀 **Deployment**

### **Production Deployment**
```bash
# Set production environment
export APP_ENV=production

# Optimize for production
composer install --no-dev --optimize-autoloader

# Clear caches
php scripts/clear-cache.php

# Run migrations
php scripts/database/migrate.php

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 var/
```

### **Environment Configuration**
```bash
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://islam.wiki

DB_HOST=production-db-host
DB_NAME=production_db
DB_USER=production_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_DRIVER=redis
```

---

## 🔍 **Troubleshooting**

### **Common Issues**
1. **Database Connection**: Check database credentials
2. **Permission Errors**: Check file and directory permissions
3. **Extension Issues**: Check extension compatibility
4. **Performance Issues**: Check caching and optimization

### **Debug Commands**
```bash
# Check system status
php scripts/system-status.php

# Check database connections
php scripts/database/check-connections.php

# Check extension status
php scripts/extensions/check-status.php

# Clear all caches
php scripts/clear-cache.php
```

---

## 📞 **Support & Resources**

### **Documentation**
- **API Reference**: Complete API documentation
- **Extension Guide**: Extension development guide
- **Skin Guide**: Skin development guide
- **Database Guide**: Database development guide

### **Community**
- **Developer Forum**: Ask questions and share code
- **Code Examples**: Sample code and snippets
- **Best Practices**: Development guidelines
- **Contributing**: How to contribute to the project

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Architecture:** MediaWiki + WordPress + Modern PHP Hybrid 

## 🚀 **Modern Development Practices**

### **Development Workflow**
1. **Git Flow**: Feature branches, pull requests, and semantic versioning
2. **Continuous Integration**: Automated testing and quality checks
3. **Code Review**: Peer review with automated quality gates
4. **Documentation First**: Write documentation before implementation
5. **Test-Driven Development**: Write tests before writing code

### **Modern Development Tools**

#### **Code Quality Tools**
```bash
# PHPStan for static analysis
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse src --level=8

# PHP CodeSniffer for coding standards
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs src --standard=PSR12

# PHP Mess Detector for code complexity
composer require --dev phpmd/phpmd
./vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode

# Infection for mutation testing
composer require --dev infection/infection
./vendor/bin/infection --min-msi=80
```

#### **Testing Tools**
```bash
# PHPUnit for unit testing
composer require --dev phpunit/phpunit
./vendor/bin/phpunit --coverage-html coverage

# Pest for expressive testing
composer require --dev pestphp/pest
./vendor/bin/pest --coverage

# Codeception for acceptance testing
composer require --dev codeception/codeception
./vendor/bin/codecept run acceptance
```

#### **Performance Tools**
```bash
# Blackfire for performance profiling
composer require --dev blackfire/php-sdk

# Xdebug for debugging and profiling
pecl install xdebug

# APCu for local caching
pecl install apcu
```

### **Modern PHP Features Usage**

#### **Type Safety and Modern Syntax**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Services;

use IslamWiki\Core\Interfaces\ServiceInterface;
use IslamWiki\Core\ValueObjects\UserId;
use IslamWiki\Core\ValueObjects\Email;
use IslamWiki\Core\Exceptions\ValidationException;

/**
 * Modern user service with type safety
 */
final readonly class UserService implements ServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Create a new user with validation
     */
    public function createUser(
        string $username,
        Email $email,
        string $password,
        array $roles = []
    ): User {
        // Validate input
        $this->validateUsername($username);
        $this->validatePassword($password);
        
        // Check if user exists
        if ($this->userRepository->findByEmail($email)) {
            throw new ValidationException('User with this email already exists');
        }
        
        // Create user
        $user = User::create(
            username: $username,
            email: $email,
            passwordHash: $this->passwordHasher->hash($password),
            roles: $roles
        );
        
        // Save user
        $this->userRepository->save($user);
        
        // Dispatch event
        $this->eventDispatcher->dispatch(new UserCreatedEvent($user));
        
        return $user;
    }

    /**
     * Find user by ID with type safety
     */
    public function findUser(UserId $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Update user with partial data
     */
    public function updateUser(UserId $id, array $data): User
    {
        $user = $this->findUser($id);
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }
        
        $user->update($data);
        $this->userRepository->save($user);
        
        $this->eventDispatcher->dispatch(new UserUpdatedEvent($user));
        
        return $user;
    }

    /**
     * Delete user with confirmation
     */
    public function deleteUser(UserId $id, bool $confirm = false): bool
    {
        if (!$confirm) {
            throw new ValidationException('Deletion must be confirmed');
        }
        
        $user = $this->findUser($id);
        if (!$user) {
            return false;
        }
        
        $this->userRepository->delete($user);
        $this->eventDispatcher->dispatch(new UserDeletedEvent($user));
        
        return true;
    }
}
```

#### **Value Objects and Domain Modeling**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable User ID value object
 */
final readonly class UserId
{
    public function __construct(
        private int $value
    ) {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('User ID must be positive');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}

/**
 * Immutable Email value object
 */
final readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr(strrchr($this->value, '@'), 1);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### **Repository Pattern Implementation**

#### **Repository Interface**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Repositories;

use IslamWiki\Core\ValueObjects\UserId;
use IslamWiki\Core\Entities\User;
use IslamWiki\Core\Collections\UserCollection;

/**
 * User repository interface
 */
interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findByUsername(string $username): ?User;
    public function findAll(): UserCollection;
    public function save(User $user): void;
    public function delete(User $user): void;
    public function count(): int;
}
```

#### **Repository Implementation**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Repositories;

use IslamWiki\Core\ValueObjects\UserId;
use IslamWiki\Core\ValueObjects\Email;
use IslamWiki\Core\Entities\User;
use IslamWiki\Core\Collections\UserCollection;
use IslamWiki\Core\Database\ConnectionInterface;

/**
 * Database user repository implementation
 */
final class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly ConnectionInterface $connection
    ) {}

    public function findById(UserId $id): ?User
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM mizan_users WHERE id = ?'
        );
        $stmt->execute([$id->getValue()]);
        
        $data = $stmt->fetch();
        return $data ? User::fromArray($data) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM mizan_users WHERE email = ?'
        );
        $stmt->execute([$email->getValue()]);
        
        $data = $stmt->fetch();
        return $data ? User::fromArray($data) : null;
    }

    public function save(User $user): void
    {
        if ($user->getId()) {
            $this->update($user);
        } else {
            $this->insert($user);
        }
    }

    private function insert(User $user): void
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO mizan_users (username, email, password_hash, created_at) VALUES (?, ?, ?, ?)'
        );
        
        $stmt->execute([
            $user->getUsername(),
            $user->getEmail()->getValue(),
            $user->getPasswordHash(),
            $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    private function update(User $user): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE mizan_users SET username = ?, email = ?, updated_at = ? WHERE id = ?'
        );
        
        $stmt->execute([
            $user->getUsername(),
            $user->getEmail()->getValue(),
            $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            $user->getId()->getValue()
        ]);
    }
}
``` 
# IslamWiki Development Standards

## 🎯 **Overview**

This document defines the comprehensive development standards for IslamWiki, ensuring consistency, quality, and adherence to Islamic values throughout the codebase. These standards apply to all aspects of development, from code structure to documentation and deployment.

---

## 🏗️ **Architecture Standards**

### **Core Architecture Principles**
- **Islamic Values**: All systems reflect Islamic principles of excellence, clarity, and community
- **Modern PHP**: PHP 8.1+ with strict typing and modern practices
- **Performance First**: Optimized for speed, efficiency, and scalability
- **Security Focus**: Enterprise-grade security with Islamic content validation
- **Accessibility**: WCAG 2.1 AA compliance for global accessibility
- **Scalability**: Designed to handle growth and increased usage

### **System Architecture**
```
IslamWiki Architecture:
├── 📁 Container (Container) - Core foundation and dependency injection
├── 📁 Security (Security) - Comprehensive security framework
├── 📁 API (Light) - API management and routing system
├── 📁 Logger (Witness) - Logging and error handling
├── 📁 Session (Connection) - Session management
├── 📁 Routing (Journey) - Caching system
├── 📁 Queue (Patience) - Job queue system
├── 📁 Knowledge (Principles) - Knowledge management
├── 📁 Iqra (Read) - Islamic search engine
├── 📁 Bayan (Explanation) - Content formatting
├── 📁 Sabil (Path) - Advanced routing system
├── 📁 Application (Order) - Main application system
├── 📁 Database (Balance) - Database system
├── 📁 Configuration (Management) - Configuration management
├── 📁 Safa (Purity) - CSS framework
└── 📁 Marwa (Excellence) - JavaScript framework
```

---

## 📝 **Code Standards**

### **PHP Standards**

#### **General Requirements**
- **PHP Version**: Minimum PHP 8.1+
- **Strict Types**: Always use `declare(strict_types=1);`
- **PSR Standards**: Follow PSR-12 coding style
- **Error Handling**: Use exceptions, never suppress errors
- **Documentation**: PHPDoc for all public methods and classes

#### **Modern PHP Features**
- **Type Declarations**: Use type hints for all parameters and return types
- **Union Types**: Use union types for flexible parameter handling
- **Named Arguments**: Use named arguments for clarity in complex method calls
- **Attributes**: Use PHP 8 attributes for metadata and annotations
- **Match Expressions**: Use match expressions instead of switch statements
- **Nullsafe Operator**: Use `?->` for safe property access
- **Constructor Property Promotion**: Use constructor property promotion for clean code

#### **Code Structure**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Interfaces\RouteInterface;
use IslamWiki\Core\Exceptions\RouteNotFoundException;

/**
 * Advanced routing system for IslamWiki
 * 
 * @package IslamWiki\Core\Routing
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
final class Routing implements RouteInterface
{
    private array $routes = [];
    private array $middleware = [];

    public function __construct(
        private readonly string $basePath = '',
        private readonly array $defaultMiddleware = []
    ) {
        $this->middleware = $this->defaultMiddleware;
    }

    /**
     * Register a new route
     * 
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self For method chaining
     */
    public function get(string $path, callable|array $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    /**
     * Add middleware to the routing stack
     * 
     * @param callable|string $middleware The middleware function or class
     * @return self For method chaining
     */
    public function addMiddleware(callable|string $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Process the request through middleware and routing
     * 
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     * @throws RouteNotFoundException When route is not found
     */
    public function handle(Request $request): Response
    {
        $path = $request->getPath();
        $method = $request->getMethod();
        
        if (!isset($this->routes[$method][$path])) {
            throw new RouteNotFoundException("Route {$method} {$path} not found");
        }
        
        $handler = $this->routes[$method][$path];
        return $this->processMiddleware($request, $handler);
    }

    /**
     * Process middleware chain
     * 
     * @param Request $request The HTTP request
     * @param callable|array $handler The final handler
     * @return Response The HTTP response
     */
    private function processMiddleware(Request $request, callable|array $handler): Response
    {
        $next = function (Request $request) use ($handler): Response {
            if (is_array($handler)) {
                [$controller, $method] = $handler;
                return (new $controller())->$method($request);
            }
            return $handler($request);
        };

        foreach (array_reverse($this->middleware) as $middleware) {
            $next = function (Request $request) use ($middleware, $next): Response {
                if (is_string($middleware)) {
                    $middleware = new $middleware();
                }
                return $middleware->process($request, $next);
            };
        }

        return $next($request);
    }
}
```

#### **Advanced Design Patterns**
- **Repository Pattern**: Use repositories for data access abstraction
- **Unit of Work**: Implement unit of work for transaction management
- **Command Pattern**: Use commands for complex operations
- **Event Sourcing**: Track all system changes for audit and rollback
- **CQRS**: Separate read and write operations for performance
- **Value Objects**: Use immutable value objects for domain concepts
- **DTOs**: Use Data Transfer Objects for API responses

#### **Composer and Autoloading**
```json
{
    "name": "islamwiki/core",
    "description": "IslamWiki Core Framework",
    "type": "library",
    "license": "AGPL-3.0",
    "require": {
        "php": "^8.1",
        "twig/twig": "^3.0",
        "monolog/monolog": "^3.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.0",
        "squizlabs/php_codesniffer": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "IslamWiki\\Core\\": "src/Core/",
            "IslamWiki\\Extensions\\": "extensions/",
            "IslamWiki\\Skins\\": "skins/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "IslamWiki\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "test-coverage": "phpunit --coverage-html coverage",
        "cs-check": "phpcs",
        "cs-fix": "phpcbf",
        "stan": "phpstan analyse src"
    },
    "config": {
        "sort-packages": true,
        "optimize-autoloader": true
    }
}
```

#### **Interface and Contract Design**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Interfaces;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Middleware interface for request processing
 */
interface MiddlewareInterface
{
    /**
     * Process the request through middleware
     * 
     * @param Request $request The HTTP request
     * @param callable $next The next middleware in the chain
     * @return Response The HTTP response
     */
    public function process(Request $request, callable $next): Response;
}

/**
 * Route interface for routing implementations
 */
interface RouteInterface
{
    /**
     * Handle the incoming request
     * 
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response;
}

/**
 * Container interface for dependency injection
 */
interface ContainerInterface
{
    /**
     * Get a service from the container
     * 
     * @param string $id The service identifier
     * @return mixed The service instance
     */
    public function get(string $id): mixed;

    /**
     * Check if a service exists in the container
     * 
     * @param string $id The service identifier
     * @return bool True if service exists
     */
    public function has(string $id): bool;

    /**
     * Register a service in the container
     * 
     * @param string $id The service identifier
     * @param callable|object $service The service definition
     * @return void
     */
    public function set(string $id, callable|object $service): void;
}
```

#### **Naming Conventions**
- **Classes**: PascalCase with Islamic prefixes
- **Methods**: camelCase
- **Properties**: camelCase with private/protected visibility
- **Constants**: UPPER_SNAKE_CASE
- **Variables**: camelCase
- **Namespaces**: Follow PSR-4 autoloading standards

### **Islamic Terminology Standards**
- **Salah**: Always use "salah" instead of "prayer" (the English translation)
- **Quran**: Use "Quran" (not "Koran" or other variations)
- **Hadith**: Use "Hadith" (not "Ahadith" in English contexts)
- **Hijri**: Use "Hijri" for Islamic calendar (not "Islamic calendar" when referring to the system)
- **Adhan**: Use "Adhan" (not "call to prayer")
- **Qibla**: Use "Qibla" (not "direction of prayer")

---

## 🕌 **Islamic Naming Conventions**

### **Mandatory Islamic Naming**
All core systems, components, and files must use Islamic names:

```php
// ✅ Correct - Using Islamic naming
class ContainerContainer {}           // Container container
class SecurityAuthenticator {}       // Security authenticator
class SabilRouter {}             // Path router
class Application {}        // Order application
class Database {}           // Balance database
class Configuration {}     // Management configuration
class RoutingCache {}             // Journey cache
class Queue {}               // Patience queue
class Knowledge {}           // Principles knowledge
class IqraSearch {}              // Read search
class BayanFormatter {}          // Explanation formatter
class APIAPI {}                // Light API
class LoggerLogger {}            // Witness logger
class SessionSession {}            // Connection session
class SafaTheme {}               // Purity theme
class MarwaComponent {}          // Excellence component

// ❌ Incorrect - Generic naming
class SystemContainer {}         // Generic
class SecurityAuth {}            // Mixed
class Router {}                  // No prefix
class Configuration {}            // No prefix
```

### **File Naming Standards**
```
PHP Files:
├── asas-{component}.php        # Container files
├── aman-{component}.php        # Security files
├── simplified-routing-{component}.php  # Routing files
├── tadbir-{component}.php      # Configuration files
├── rihlah-{component}.php      # Caching files
├── mizan-{component}.php       # Database files
├── usul-{component}.php        # Knowledge files
├── iqra-{component}.php        # Search files
├── bayan-{component}.php       # Formatting files
├── siraj-{component}.php       # API files
├── shahid-{component}.php      # Logging files
├── wisal-{component}.php       # Session files
├── sabr-{component}.php        # Queue files
├── nizam-{component}.php       # Application files
├── safa-{component}.php        # CSS framework files
└── marwa-{component}.php       # JavaScript framework files
```

### **Namespace Standards**
```php
// Core Framework
namespace IslamWiki\Core\Container;      // Container
namespace IslamWiki\Core\Security;       // Security
namespace IslamWiki\Core\Routing;        // Simplified Routing
namespace IslamWiki\Core\Application;    // Application
namespace IslamWiki\Core\Database;       // Database
namespace IslamWiki\Core\Configuration;  // Configuration
namespace IslamWiki\Core\Caching;        // Routing
namespace IslamWiki\Core\Queues;         // Queue
namespace IslamWiki\Core\Knowledge;      // Knowledge
namespace IslamWiki\Core\Search;         // Iqra
namespace IslamWiki\Core\Formatting;     // Bayan
namespace IslamWiki\Core\API;            // API
namespace IslamWiki\Core\Logging;        // Logging
namespace IslamWiki\Core\Sessions;       // Session
namespace IslamWiki\Core\CSS;            // Safa
namespace IslamWiki\Core\JavaScript;     // Marwa

// Extensions
namespace IslamWiki\Extensions\{ExtensionName};

// Skins
namespace IslamWiki\Skins\{SkinName};
```

---

## 🎨 **Frontend Standards**

### **CSS Framework (Safa)**
- **Naming**: Use `safa-` prefix for all framework classes
- **Structure**: Follow BEM methodology with Islamic naming
- **Responsive**: Mobile-first responsive design
- **Accessibility**: High contrast ratios, semantic markup

```css
/* ✅ Correct - Using Safa framework naming */
.safa-button {
    /* Base button styles */
}

.safa-button--primary {
    /* Primary button variant */
}

.safa-button__icon {
    /* Button icon element */
}

.safa-navigation {
    /* Navigation component */
}

.safa-theme--islamic {
    /* Islamic theme variant */
}

/* ❌ Incorrect - Generic naming */
.button {
    /* No prefix */
}

.btn-primary {
    /* Mixed naming */
}
```

### **JavaScript Framework (Marwa)**
- **Naming**: Use `marwa-` prefix for all framework components
- **Progressive Enhancement**: Functionality must work without JavaScript
- **Accessibility**: ARIA support, keyboard navigation
- **Performance**: Lazy loading, efficient event handling

```javascript
// ✅ Correct - Using Marwa framework naming
class MarwaComponent {
    constructor(element) {
        this.element = element;
        this.init();
    }

    init() {
        // Component initialization
    }
}

class MarwaThemeSwitcher extends MarwaComponent {
    switchTheme(theme) {
        // Theme switching logic
    }
}

// ❌ Incorrect - Generic naming
class Component {
    // No prefix
}

class ThemeSwitcher {
    // No prefix
}
```

---

## 🗄️ **Database Standards**

### **Table Naming**
- **Tables**: Use descriptive names with Islamic prefixes
- **Columns**: Use snake_case with clear, descriptive names
- **Indexes**: Use descriptive names with table prefix
- **Foreign Keys**: Use descriptive names with relationship indication

```sql
-- ✅ Correct - Descriptive table naming
CREATE TABLE mizan_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE mizan_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES mizan_users(id)
);

-- ❌ Incorrect - Generic naming
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(50)
);
```

### **Migration Standards**
```php
// ✅ Correct - Using Islamic naming
class QueueMigration_0001_CreateUsersTable extends QueueMigration
{
    public function up(): void
    {
        // Migration logic
    }

    public function down(): void
    {
        // Rollback logic
    }
}

// ❌ Incorrect - Generic naming
class CreateUsersTable extends Migration
{
    // No prefix
}
```

---

## 🔒 **Security Standards**

### **Input Validation**
- **All Input**: Must be validated and sanitized
- **SQL Injection**: Use prepared statements only
- **XSS Prevention**: Output escaping in all templates
- **CSRF Protection**: Token-based protection for all forms

```php
// ✅ Correct - Secure input handling
class SecurityValidator
{
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

// ❌ Incorrect - Insecure input handling
$email = $_POST['email']; // Direct input without validation
$query = "SELECT * FROM users WHERE email = '$email'"; // SQL injection risk
```

### **Authentication & Authorization**
- **Password Hashing**: Use modern hashing algorithms
- **Session Security**: Secure session handling
- **Role-Based Access**: Implement proper RBAC
- **Rate Limiting**: Prevent abuse and attacks

---

## 📚 **Documentation Standards**

### **Code Documentation**
- **PHPDoc**: Required for all public methods and classes
- **Examples**: Include usage examples for complex functionality
- **Parameters**: Document all parameters and return values
- **Exceptions**: Document all possible exceptions

### **API Documentation**
- **OpenAPI/Swagger**: Use for REST API documentation
- **Examples**: Include request/response examples
- **Authentication**: Document authentication methods
- **Error Codes**: Document all possible error responses

---

## 🧪 **Testing Standards**

### **Test Naming**
```php
// ✅ Correct - Using Islamic naming
class LoggerTest_UserAuthentication extends LoggerTestCase
{
    public function testUserCanLoginWithValidCredentials(): void
    {
        // Test logic
    }

    public function testUserCannotLoginWithInvalidCredentials(): void
    {
        // Test logic
    }
}

// ❌ Incorrect - Generic naming
class UserAuthenticationTest extends TestCase
{
    // No prefix
}
```

### **Test Structure**
- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test component interactions
- **Feature Tests**: Test complete user workflows
- **Coverage**: Aim for 80%+ code coverage

---

## 🚀 **Performance Standards**

### **Caching Strategy**
- **Page Cache**: Cache full pages where appropriate
- **Object Cache**: Cache database queries and objects
- **Route Cache**: Cache compiled routes
- **Template Cache**: Cache compiled templates

### **Database Optimization**
- **Indexes**: Proper indexing for all queries
- **Queries**: Optimize database queries
- **Connections**: Use connection pooling
- **Monitoring**: Monitor query performance

---

## 📋 **Code Review Standards**

### **Before Submitting Code**
- [ ] Follows Islamic naming conventions
- [ ] Follows PSR-12 coding style
- [ ] Includes proper documentation
- [ ] Passes all tests
- [ ] No security vulnerabilities
- [ ] Performance considerations addressed
- [ ] Accessibility requirements met

### **During Code Review**
- [ ] Code is readable and maintainable
- [ ] Proper error handling implemented
- [ ] Security best practices followed
- [ ] Performance impact considered
- [ ] Documentation is clear and complete

---

## 📖 **File Organization Standards**

### **Directory Structure**
```
local.islam.wiki/
├── 📁 asas/                    # Core foundation (was: src/)
├── 📁 aman/                    # Security system (was: security/)
├── 📁 simplified-routing/      # Routing system (was: routes/)
├── 📁 nizam/                   # Main application (was: public/)
├── 📁 tadbir/                  # Configuration (was: config/)
├── 📁 rihlah/                  # Caching system (was: cache/)
├── 📁 mizan/                   # Database system (was: database/)
├── 📁 usul/                    # Knowledge management (was: resources/)
├── 📁 sabr/                    # Job queues (was: storage/)
├── 📁 shahid/                  # Logging system (was: logs/)
├── 📁 wisal/                   # Session management (was: sessions/)
├── 📁 iqra/                    # Search engine (was: search/)
├── 📁 bayan/                   # Content formatting (was: formatting/)
├── 📁 siraj/                   # API system (was: api/)
├── 📁 safa/                    # CSS framework (was: css/)
├── 📁 marwa/                   # JavaScript framework (was: js/)
├── 📁 extensions/              # Extension system (keep as is)
├── 📁 skins/                   # Skin system (keep as is)
├── 📁 languages/               # Language files (keep as is)
├── 📁 vendor/                  # Dependencies (keep as is)
└── 📁 docs/                    # Documentation (keep as is)
```

---

## 📄 **License Standards**

### **AGPL-3.0 License Requirements**
- **Source Code**: Must be made available to users
- **Network Use**: Network use triggers source code distribution
- **Modifications**: Modified versions must be licensed under AGPL-3.0
- **Attribution**: Original copyright notices must be preserved

### **License Compliance**
- All source files must include AGPL-3.0 license headers
- Source code must be available to users
- Network use must comply with AGPL-3.0 requirements
- Modifications must preserve license terms

---

## 🔍 **Quality Assurance Standards**

### **Code Quality Tools**
- **PHPStan**: Static analysis for PHP code
- **PHP CS Fixer**: Code style enforcement
- **PHPUnit**: Unit testing framework
- **Psalm**: Type checking and analysis

### **Continuous Integration**
- **Automated Testing**: All tests must pass
- **Code Quality**: Static analysis must pass
- **Security Scanning**: Security vulnerabilities must be resolved
- **Performance Testing**: Performance benchmarks must be met

---

## 📚 **References**

- [Islamic Naming Conventions](../guides/islamic-naming-conventions.md)
- [Islamic Naming Implementation](../guides/islamic-naming-implementation.md)
- [Core Systems Architecture](../architecture/core-systems.md)
- [PSR-12 Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
- [PHP Documentation Standards](https://docs.phpdoc.org/)
- [AGPL-3.0 License](https://www.gnu.org/licenses/agpl-3.0.en.html)

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Development Standards Complete ✅ 
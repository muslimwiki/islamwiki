<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Container, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
# IslamWiki Style Guide

## 🎯 **Overview**

This style guide defines the coding standards, naming conventions, and best practices for developing IslamWiki. Following these guidelines ensures consistency, maintainability, and adherence to Islamic values throughout the codebase.

---

## 🏗️ **Architecture Principles**

### **Core Philosophy**
- **Islamic Values**: All code should reflect Islamic principles of excellence, clarity, and community
- **Modern Standards**: Follow current PHP and web development best practices
- **Performance First**: Optimize for speed, efficiency, and scalability
- **Security Focus**: Implement enterprise-grade security with Islamic content validation
- **Accessibility**: Ensure the platform is accessible to users worldwide

### **Design Patterns**
- **Dependency Injection**: Use the Container container for all service resolution
- **Service-Oriented**: Organize functionality into focused, single-responsibility services
- **Middleware Architecture**: Implement request/response processing through middleware chains
- **Event-Driven**: Use events for loose coupling between components
- **Progressive Enhancement**: Build functionality that works without JavaScript

---

## 📝 **Coding Standards**

### **PHP Standards**

#### **General Rules**
- **PHP Version**: Minimum PHP 8.1+
- **Strict Types**: Always use `declare(strict_types=1);`
- **PSR Standards**: Follow PSR-12 coding style
- **Error Handling**: Use exceptions, never suppress errors
- **Documentation**: PHPDoc for all public methods and classes

#### **Code Structure**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Advanced routing system for IslamWiki
 * 
 * @package IslamWiki\Core\Routing
 * @author IslamWiki Development Team
 */
class Routing
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Register a new route
     */
    public function get(string $path, callable $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    /**
     * Add middleware to the routing stack
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
}
```

#### **Naming Conventions**
- **Classes**: PascalCase with Islamic prefixes
- **Methods**: camelCase
- **Properties**: camelCase with private/protected visibility
- **Constants**: UPPER_SNAKE_CASE
- **Variables**: camelCase

### **Islamic Terminology Standards**
- **Salah**: Always use "salah" instead of "prayer" (the English translation)
- **Quran**: Use "Quran" (not "Koran" or other variations)
- **Hadith**: Use "Hadith" (not "Ahadith" in English contexts)
- **Hijri**: Use "Hijri" for Islamic calendar (not "Islamic calendar" when referring to the system)
- **Adhan**: Use "Adhan" (not "call to prayer")
- **Qibla**: Use "Qibla" (not "direction of prayer")

---

## 🕌 **Islamic Naming Conventions**

### **Core System Naming**
All core systems must use Islamic names as defined in the naming conventions guide:

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

### **File Naming Patterns**
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

### **Namespace Structure**
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

### **Migration Naming**
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

## 📋 **Code Review Checklist**

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

## 📖 **References**

- [Islamic Naming Conventions](../guides/islamic-naming-conventions.md)
- [Islamic Naming Implementation](../guides/islamic-naming-implementation.md)
- [Core Systems Architecture](../architecture/core-systems.md)
- [PSR-12 Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
- [PHP Documentation Standards](https://docs.phpdoc.org/)

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Status:** Style Guide Complete ✅

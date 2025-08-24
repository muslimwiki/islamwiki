# IslamWiki Naming Conventions

## 🎯 **Professional Naming Standards**

IslamWiki follows a hybrid naming convention that combines **professional English directory names** with **unique Arabic class names** for the best of both worlds.

## 📁 **Directory Structure (English for Clarity)**

```
src/Core/
├── Auth/           # Authentication system
├── Session/        # Session management  
├── Logging/        # Logging system
├── Container/      # Dependency injection
├── API/            # API management
├── Knowledge/      # Knowledge system
├── Caching/        # Caching system
├── Queue/          # Queue system
├── Formatter/      # Content formatting
├── Database/       # Database operations
├── Routing/        # URL routing
├── Http/           # HTTP handling
├── View/           # View rendering
├── Error/          # Error handling
├── Extensions/     # Plugin system
├── Configuration/  # Settings management
├── Security/       # Security features
├── Community/      # Community features
├── Islamic/        # Islamic content
└── Support/        # Helper utilities
```

## 🏷️ **Class Names (Arabic for Uniqueness)**

| System | Arabic Name | Meaning           | Purpose              | Directory    |
| ------ | ----------- | ----------------- | -------------------- | ------------ |
| Security   | أمان        | Security/Safety   | Authentication       | `Auth/`      |
| Session  | وصال        | Connection/Link   | Session Management   | `Session/`   |
| Logging | شاهد        | Witness/Testimony | Logging              | `Logging/`   |
| Container   | أساس        | Container/Base   | Dependency Injection | `Container/` |
| API  | سراج        | Lamp/Light        | API Management       | `API/`       |
| Knowledge   | أصول        | Principles/Roots  | Knowledge System     | `Knowledge/` |
| Routing | رحلة        | Journey           | Caching System       | `Caching/`   |
| Queue   | صبر         | Patience          | Queue System         | `Queue/`     |
| Bayan  | بيان        | Explanation       | Content Formatting   | `Formatter/` |

## 💼 **Variable Names (English for Clarity)**

```php
// ✅ GOOD - Use English variable names for clarity
$container = new ContainerContainer();           // Dependency injection container
$auth = new SecuritySecurity();               // Authentication system  
$session = new SessionSession();           // Session management
$logger = new LoggerLogger();           // Logging system
$api = new APIAPI();               // API management
$knowledge = new Knowledge();          // Knowledge system
$cache = new RoutingCaching();            // Caching system
$queue = new Queue();              // Queue system
$formatter = new BayanFormatter();         // Content formatting

// ❌ AVOID - Don't use Arabic variable names
$asas = new ContainerContainer();               // Confusing
$aman = new SecuritySecurity();               // Hard to understand
$wisal = new Session();             // Not clear
```

## 📦 **Namespace Structure**

```php
// ✅ CORRECT - Professional namespace structure
use Security;\Security
use Session;\Session
use Logger;\Logger
use Container;\Container
use API;\API
use Knowledge;\Knowledge
use Caching;\Routing
use Queue;\Queue
use IslamWiki\Core\Formatter\BayanFormatter;
```

## 🔧 **Usage Examples**

### **Service Registration**

```php
// ✅ GOOD - Clear and professional
$container = new ContainerContainer();
$container->singleton('auth', function() {
    return new SecuritySecurity($session, $db);
});
$container->singleton('cache', function() {
    return new Routing($container, $logger, $db);
});
```

### **Controller Construction**

```php
// ✅ GOOD - Professional variable names
class UserController extends Controller
{
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        $this->auth = $container->get(Security::class);
        $this->logger = $container->get(Logger::class);
        $this->cache = $container->get(Routing::class);
    }
}
```

### **Service Provider Registration**

```php
// ✅ GOOD - Clear service registration
class AuthServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(Security::class, function() use ($container) {
            $session = $container->get(Session::class);
            $db = $container->get(Connection::class);
            return new SecuritySecurity($session, $db);
        });
    }
}
```

## 🎨 **Benefits of This Approach**

### **✅ Professional Standards**

- **English directories** - Easy to navigate and understand
- **English variables** - Clear and readable code
- **Consistent structure** - Follows industry conventions

### **✅ Unique Identity**

- **Arabic class names** - Memorable and culturally relevant
- **Meaningful names** - Each name has deep significance
- **Brand differentiation** - Sets IslamWiki apart from other frameworks

### **✅ Developer Experience**

- **Easy to learn** - Familiar directory structure
- **Clear intent** - Variable names explain their purpose
- **Consistent patterns** - Predictable code organization

## 📋 **Implementation Checklist**

### **✅ Directory Structure**

- [x] `src/Core/Auth/` - Authentication system
- [x] `src/Core/Session/` - Session management
- [x] `src/Core/Logging/` - Logging system
- [x] `src/Core/Container/` - Dependency injection
- [x] `src/Core/API/` - API management
- [x] `src/Core/Knowledge/` - Knowledge system
- [x] `src/Core/Caching/` - Caching system
- [x] `src/Core/Queue/` - Queue system
- [x] `src/Core/Formatter/` - Content formatting

### **✅ Class Names**

- [x] `Security` - Authentication class
- [x] `Session` - Session class
- [x] `Logger` - Logging class
- [x] `Container` - Container class
- [x] `API` - API class
- [x] `Knowledge` - Knowledge class
- [x] `Caching` - Caching class
- [x] `Queue` - Queue class
- [x] `BayanFormatter` - Formatter class

### **✅ Variable Names**

- [x] `$container` - Dependency injection container
- [x] `$auth` - Authentication system
- [x] `$session` - Session management
- [x] `$logger` - Logging system
- [x] `$api` - API management
- [x] `$knowledge` - Knowledge system
- [x] `$cache` - Caching system
- [x] `$queue` - Queue system
- [x] `$formatter` - Content formatting

## 🚀 **Best Practices**

### **✅ DO**

- Use English directory names for clarity
- Use English variable names for readability
- Use Arabic class names for uniqueness
- Follow consistent naming patterns
- Document naming conventions clearly

### **❌ DON'T**

- Use Arabic variable names (confusing)
- Mix naming conventions inconsistently
- Use unclear or ambiguous names
- Ignore professional standards
- Create inconsistent patterns

## 📚 **Summary**

IslamWiki's naming convention successfully balances:

1. **Professional Standards** - English directories and variables for clarity
2. **Cultural Identity** - Arabic class names for uniqueness
3. **Developer Experience** - Consistent and predictable patterns
4. **Maintainability** - Clear and organized code structure

This approach ensures that IslamWiki is both **professionally structured** and **culturally distinctive**, providing the best experience for developers while maintaining the unique Islamic identity of the platform. 
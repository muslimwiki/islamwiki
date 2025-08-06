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
| Aman   | أمان        | Security/Safety   | Authentication       | `Auth/`      |
| Wisal  | وصال        | Connection/Link   | Session Management   | `Session/`   |
| Shahid | شاهد        | Witness/Testimony | Logging              | `Logging/`   |
| Asas   | أساس        | Foundation/Base   | Dependency Injection | `Container/` |
| Siraj  | سراج        | Lamp/Light        | API Management       | `API/`       |
| Usul   | أصول        | Principles/Roots  | Knowledge System     | `Knowledge/` |
| Rihlah | رحلة        | Journey           | Caching System       | `Caching/`   |
| Sabr   | صبر         | Patience          | Queue System         | `Queue/`     |
| Bayan  | بيان        | Explanation       | Content Formatting   | `Formatter/` |

## 💼 **Variable Names (English for Clarity)**

```php
// ✅ GOOD - Use English variable names for clarity
$container = new AsasContainer();           // Dependency injection container
$auth = new AmanSecurity();               // Authentication system  
$session = new WisalSession();           // Session management
$logger = new ShahidLogger();           // Logging system
$api = new SirajAPI();               // API management
$knowledge = new UsulKnowledge();          // Knowledge system
$cache = new RihlahCaching();            // Caching system
$queue = new SabrQueue();              // Queue system
$formatter = new BayanFormatter();         // Content formatting

// ❌ AVOID - Don't use Arabic variable names
$asas = new AsasContainer();               // Confusing
$aman = new AmanSecurity();               // Hard to understand
$wisal = new Wisal();             // Not clear
```

## 📦 **Namespace Structure**

```php
// ✅ CORRECT - Professional namespace structure
use IslamWiki\Core\Auth\AmanSecurity;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\API\SirajAPI;
use IslamWiki\Core\Knowledge\UsulKnowledge;
use IslamWiki\Core\Caching\RihlahCaching;
use IslamWiki\Core\Queue\SabrQueue;
use IslamWiki\Core\Formatter\BayanFormatter;
```

## 🔧 **Usage Examples**

### **Service Registration**

```php
// ✅ GOOD - Clear and professional
$container = new AsasContainer();
$container->singleton('auth', function() {
    return new AmanSecurity($session, $db);
});
$container->singleton('cache', function() {
    return new Rihlah($container, $logger, $db);
});
```

### **Controller Construction**

```php
// ✅ GOOD - Professional variable names
class UserController extends Controller
{
    public function __construct(Connection $db, Asas $container)
    {
        parent::__construct($db, $container);
        $this->auth = $container->get(AmanSecurity::class);
        $this->logger = $container->get(ShahidLogger::class);
        $this->cache = $container->get(Rihlah::class);
    }
}
```

### **Service Provider Registration**

```php
// ✅ GOOD - Clear service registration
class AuthServiceProvider
{
    public function register(Asas $container): void
    {
        $container->singleton(AmanSecurity::class, function() use ($container) {
            $session = $container->get(WisalSession::class);
            $db = $container->get(Connection::class);
            return new AmanSecurity($session, $db);
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

- [x] `AmanSecurity` - Authentication class
- [x] `WisalSession` - Session class
- [x] `ShahidLogger` - Logging class
- [x] `Asas` - Container class
- [x] `SirajAPI` - API class
- [x] `UsulKnowledge` - Knowledge class
- [x] `RihlahCaching` - Caching class
- [x] `SabrQueue` - Queue class
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
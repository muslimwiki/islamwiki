# Arabic-Named Core Systems

IslamWiki uses meaningful Arabic names for its core systems, reflecting the cultural and spiritual significance of Islamic knowledge management.

## 🏗️ Core Systems Overview

### Aman (أمان) - Security System
**File:** `src/Core/Auth/Aman.php`

**Meaning:** "Security" or "safety" in Arabic, representing the protective layer that ensures user authentication and authorization.

**Purpose:** Comprehensive authentication and security system for IslamWiki.

**Features:**
- User authentication (login/logout)
- Password management and reset
- Permission-based access control
- User registration and profile management
- Session-based security
- CSRF protection

**Usage:**
```php
use IslamWiki\Core\Auth\Aman;

$aman = new Aman($session, $db);
if ($aman->attempt($username, $password)) {
    // User authenticated successfully
}
```

---

### Wisal (وصال) - Connection Manager
**File:** `src/Core/Session/Wisal.php`

**Meaning:** "Connection" or "link" in Arabic, representing the persistent connection between users and the application.

**Purpose:** Handles secure session management and user connection state.

**Features:**
- Secure session handling
- User session persistence
- CSRF token management
- Session regeneration for security
- Remember-me functionality
- Session data management

**Usage:**
```php
use IslamWiki\Core\Session\Wisal;

$wisal = new Wisal($config);
$wisal->start();
$wisal->login($userId, $username, $isAdmin);
```

---

### Shahid (شاهد) - Witness System
**File:** `src/Core/Logging/Shahid.php`

**Meaning:** "Witness" or "testimony" in Arabic, representing the system that bears witness to all application events and activities.

**Purpose:** Comprehensive logging system for IslamWiki.

**Features:**
- Multiple log levels (debug, info, warning, error, etc.)
- Log file rotation
- Performance logging
- Security event logging
- User action tracking
- API request logging
- Exception handling

**Usage:**
```php
use IslamWiki\Core\Logging\Shahid;

$shahid = new Shahid($logDir, 'debug');
$shahid->info('User logged in', ['user_id' => $userId]);
$shahid->error('Database connection failed', ['error' => $e->getMessage()]);
```

---

### Asas (أساس) - Foundation Container
**File:** `src/Core/Asas.php`

**Meaning:** "Foundation" or "base" in Arabic, representing the foundational layer that holds and manages all application services.

**Purpose:** Dependency injection container for IslamWiki.

**Features:**
- Service registration and resolution
- Singleton and instance management
- Dependency injection
- Service aliases
- Parameter overrides
- Resolving callbacks

**Usage:**
```php
use IslamWiki\Core\Asas;

$asas = new Asas();
$asas->singleton('auth', function() {
    return new Aman($session, $db);
});
$auth = $asas->get('auth');
```

---

### Siraj (سراج) - API Management System
**File:** `src/Core/API/Siraj.php`

**Meaning:** "Lamp" or "light" in Arabic, representing the system that illuminates and guides API interactions.

**Purpose:** Comprehensive API management system for IslamWiki.

**Features:**
- API authentication (session, token, API key)
- Rate limiting with configurable limits
- Response formatting (JSON, XML, HTML)
- Request lifecycle management
- Error handling and logging
- Extensible authenticator and formatter interfaces

**Usage:**
```php
use IslamWiki\Core\API\Siraj;

$siraj = new Siraj($container, $logger, $session);

// Handle API request with full lifecycle management
$response = $siraj->handleRequest($request, function($req) {
    return ['data' => 'API response'];
}, [
    'rate_limit' => 'default',
    'auth_method' => 'session'
]);
```

## 🔧 System Integration

### Service Provider Registration
All systems are properly registered through service providers:

```php
// Aman (Authentication)
$container->singleton('auth', function() {
    return new Aman($session, $db);
});

// Wisal (Session)
$container->singleton('session', function() {
    return new Wisal($config);
});

// Shahid (Logging)
$container->singleton(LoggerInterface::class, function() {
    return new Shahid($logDir, $config['level']);
});

// Siraj (API)
$container->singleton('api', function() {
    return new Siraj($container, $logger, $session);
});
```

### Controller Integration
Controllers use the new systems seamlessly:

```php
class HomeController extends Controller
{
    public function __construct(Connection $db, Asas $container)
    {
        parent::__construct($db, $container);
        $this->aman = $container->get('auth');
        $this->shahid = $container->get(LoggerInterface::class);
    }
    
    public function index(Request $request): Response
    {
        if ($this->aman->check()) {
            $this->shahid->info('Authenticated user accessed home page');
        }
        // ...
    }
}
```

## 🚀 Future Systems

The following systems are planned for implementation:

### Usul (أصول) - Knowledge System
**Meaning:** "Principles" or "roots" in Arabic, especially in Islamic jurisprudence (uṣūl al-fiqh).

**Purpose:** Knowledge engine, ontology, and data modeling system including:
- Qur'anic root systems
- Hadith classifications  
- Category trees
- Schema layers
- Semantic core and knowledge ontology engine

### Rihlah (رحلة) - Caching System
**File:** `src/Core/Caching/Rihlah.php`

**Meaning:** "Journey" in Arabic, representing the system that manages the journey of data through various cache layers for optimal performance.

**Purpose:** Comprehensive caching system for IslamWiki performance optimization.

**Features:**
- Multi-driver caching (Memory, File, Database, Session, Redis)
- Automatic driver selection and fallback
- Cache statistics and monitoring
- Pattern-based cache invalidation
- Cache warm-up functionality
- Real-time performance metrics
- Web-based management dashboard

**Usage:**
```php
use IslamWiki\Core\Caching\Rihlah;

$cache = $container->get('cache');

// Basic caching
$data = $cache->get('key', 'memory');
$cache->set('key', $value, 3600, 'memory');

// Remember pattern
$result = $cache->remember('user:123', function() {
    return $db->select('SELECT * FROM users WHERE id = 123');
}, 1800, 'database');

// Specialized caching
$queries = $cache->rememberQuery('site_stats', function() {
    return $db->select('SELECT COUNT(*) FROM pages');
}, 3600);

$apiResponse = $cache->rememberApiResponse('quran:verses', function() {
    return $api->getVerses();
}, 1800);
```

**Cache Drivers:**
- **MemoryCacheDriver** - APCu-based high-speed caching
- **FileCacheDriver** - File-based persistent caching
- **DatabaseCacheDriver** - Database-based caching with automatic table creation
- **SessionCacheDriver** - User-specific session caching
- **RedisCacheDriver** - High-performance Redis caching with pattern invalidation

### Sabr (صبر) - Queue System
**Meaning:** "Patience" in Arabic.

**Purpose:** Asynchronous job processing and queue management.

### Nida (نداء) - Event Bus
**Meaning:** "Call" or "summon" in Arabic.

**Purpose:** Event-driven architecture and message bus system.

### Waraq (ورق) - File Storage
**Meaning:** "Paper" or "document" in Arabic.

**Purpose:** File storage and document management system.

### Mizan (ميزان) - Config Manager
**Meaning:** "Balance" or "measure" in Arabic.

**Purpose:** Configuration management and system settings.

## 📚 Cultural Significance

The use of Arabic names for core systems reflects:

1. **Cultural Authenticity** - Systems named in the language of Islamic scholarship
2. **Semantic Meaning** - Each name carries deep meaning relevant to its function
3. **Spiritual Connection** - Names that resonate with Islamic values and concepts
4. **Memorability** - Short, meaningful names that are easy to remember
5. **Developer Experience** - Names that provide immediate understanding of purpose

## 🔄 Migration Notes

### From Old Names to New Names
- `AuthManager` → `Aman`
- `SessionManager` → `Wisal`  
- `Logger` → `Shahid`
- `Container` → `Asas`

### Updated References
All references have been updated across:
- Service providers
- Controllers
- Application bootstrap
- Documentation
- Test files

### Backward Compatibility
The new systems maintain the same interfaces and functionality as the old systems, ensuring smooth migration and operation.

---

### Sabr (صبر) - Queue System
**File:** `src/Core/Queue/Sabr.php`

**Meaning:** "Patience" in Arabic, representing the system that patiently processes background tasks and time-consuming operations.

**Purpose:** Comprehensive asynchronous job processing system for IslamWiki.

**Features:**
- Multiple queue drivers (Database, File, Memory, Redis)
- Job types: Email, Notification, Report, Cleanup
- Job management: Push, pop, retry, clear failed jobs
- Queue monitoring dashboard with real-time statistics
- Queue controller for management and monitoring
- Service provider for dependency injection
- Support for job priorities, delays, and timeouts
- Failed job handling with retry mechanisms
- Queue statistics and performance monitoring
- Test job creation for development and testing

**Usage:**
```php
use IslamWiki\Core\Queue\Sabr;

$sabr = new Sabr($container, $logger, $db);
$sabr->email('user@example.com', 'Welcome', 'Welcome to IslamWiki!');
$sabr->notify(123, 'welcome', ['message' => 'Welcome!']);
$sabr->report('user_activity', ['period' => 'daily']);
$sabr->cleanup('temp_files', ['max_age' => 86400]);
``` 
# Islamic Naming Implementation Guide

## 🎯 **Overview**

This document provides a comprehensive list of all files, folders, and components that need to be renamed using Islamic naming conventions, along with specific suggestions for each.

---

## 📁 **Root Directory Renaming**

### **Current → Islamic Names (CORRECTED)**
```
Current Name          → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────
src/                 → asas/                 → Container (Core code)
config/              → tadbir/               → Management/Planning (Configuration)
cache/               → rihlah/               → Journey (Caching)
routes/              → simplified-routing/   → Path/Way (Routing)
public/              → nizam/                → System/Order (Main application)
resources/           → usul/                 → Principles/Roots (Knowledge management)
storage/             → sabr/                 → Patience (Job queues)
logs/                → shahid/               → Witness (Logging)
maintenance/         → tadbir/               → Management (Maintenance)
scripts/             → usul/                 → Principles (Scripts)
database/            → mizan/                → Balance/Scale (Database)
extensions/          → rihlah/               → Journey (Extensions)
skins/               → safa/                 → Purity (CSS framework)
languages/           → bayan/                → Explanation (Content formatting)
vendor/              → tawheed/              → Unity (Dependencies)
docs/                → ilm/                  → Knowledge (Documentation)
```

### **System Purpose Alignment**
```
Core Systems:
├── Container (Container) - Dependency injection container
├── Security (Security) - Security, authentication, authorization
├── API (Light/Lamp) - API management and routing
├── Logging (Witness) - Logging and error handling
├── Session (Connection) - Session management
├── Routing (Journey) - Caching system
├── Queue (Patience) - Job queue system
├── Knowledge (Principles/Roots) - Knowledge management
├── Iqra (Read) - Islamic search engine
├── Bayan (Explanation) - Content formatting system
├── Simplified Routing (Path/Way) - Advanced routing system
├── Application (System/Order) - Main application system
├── Database (Balance/Scale) - Database system
├── Configuration (Management/Planning) - Configuration management
├── Safa (Purity) - CSS framework
└── Marwa (Excellence) - JavaScript framework
```

---

## 🏗️ **Source Code Structure (asas/)**

### **Core Framework Components**
```
asas/
├── 📁 Core/                    # Core framework components
│   ├── 📁 Container/          # Dependency injection
│   │   ├── 📄 Container.php        # Container container
│   │   └── 📄 ContainerInterface.php   # Container interface
│   ├── 📁 Routing/            # Routing system
│   │   ├── 📄 Routing.php             # Path routing
│   │   └── 📄 RouteInterface.php      # Route interface
│   ├── 📁 Http/               # HTTP handling
│   │   ├── 📄 Request.php             # HTTP request
│   │   ├── 📄 Response.php            # HTTP response
│   │   └── 📄 Middleware/             # Middleware components
│   ├── 📁 View/               # Template system
│   │   ├── 📄 TwigRenderer.php        # Template renderer
│   │   └── 📄 ViewInterface.php       # View interface
│   ├── 📁 Error/              # Error handling
│   │   ├── 📄 LoggingErrorHandler.php  # Witness error handler
│   │   └── 📄 ExceptionHandler.php    # Exception handler
│   └── 📁 Logging/            # Logging system
│       ├── 📄 Logger.php        # Witness logger
│       └── 📄 LogInterface.php        # Log interface
├── 📁 Http/                    # HTTP layer
│   ├── 📁 Controllers/        # Request controllers
│   │   ├── 📄 HomeController.php      # Home controller
│   │   ├── 📄 WikiController.php      # Wiki controller
│   │   └── 📄 AuthController.php      # Authentication controller
│   └── 📁 Middleware/         # Request middleware
│       ├── 📄 SecurityMiddleware.php      # Security middleware
│       ├── 📄 SessionMiddleware.php     # Session middleware
│       └── 📄 LocaleMiddleware.php    # Language middleware
├── 📁 Providers/               # Service providers
│   ├── 📄 ContainerServiceProvider.php     # Container service provider
│   ├── 📄 SecurityServiceProvider.php     # Security service provider
│   └── 📄 ViewServiceProvider.php     # View service provider
└── 📁 Models/                  # Data models
    ├── 📄 User.php                    # User model
    ├── 📄 Page.php                    # Page model
    └── 📄 Extension.php               # Extension model
```

---

## ⚙️ **Configuration Structure (tadbir/)**

### **Configuration Files**
```
tadbir/
├── 📄 LocalSettings.php                # Main configuration
├── 📄 IslamSettings.php                # Islamic-specific settings
├── 📄 DatabaseSettings.php             # Database configuration
├── 📄 SecuritySettings.php             # Security configuration
├── 📄 PerformanceSettings.php          # Performance configuration
├── 📄 ExtensionSettings.php            # Extension configuration
├── 📄 FrontendSettings.php             # Frontend configuration
├── 📄 CacheSettings.php                # Cache configuration
├── 📄 LoggingSettings.php              # Logging configuration
└── 📄 EnvironmentSettings.php          # Environment configuration
```

---

## 🎨 **Frontend Asset Structure**

### **CSS Framework (safa/)**
```
safa/
├── 📁 base/                    # Base styles
│   ├── 📄 safa-reset.css              # CSS reset
│   ├── 📄 safa-typography.css         # Typography
│   ├── 📄 safa-layout.css             # Layout utilities
│   └── 📄 safa-colors.css             # Color system
├── 📁 components/              # Component styles
│   ├── 📄 safa-buttons.css            # Button styles
│   ├── 📄 safa-forms.css              # Form styles
│   ├── 📄 safa-navigation.css         # Navigation styles
│   ├── 📄 safa-cards.css              # Card styles
│   ├── 📄 safa-modals.css             # Modal styles
│   └── 📄 safa-tables.css             # Table styles
├── 📁 themes/                  # Theme variations
│   ├── 📄 safa-light.css              # Light theme
│   ├── 📄 safa-dark.css               # Dark theme
│   ├── 📄 safa-islamic.css            # Islamic aesthetic theme
│   └── 📄 safa-ramadan.css            # Ramadan theme
├── 📁 utilities/               # Utility classes
│   ├── 📄 safa-spacing.css            # Spacing utilities
│   ├── 📄 safa-flexbox.css            # Flexbox utilities
│   └── 📄 safa-grid.css               # Grid utilities
└── 📄 safa.css                        # Main framework file
```

### **JavaScript Framework (marwa/)**
```
marwa/
├── 📁 core/                    # Core functionality
│   ├── 📄 marwa.js                    # Main framework
│   ├── 📄 marwa-events.js             # Event system
│   ├── 📄 marwa-utils.js              # Utility functions
│   └── 📄 marwa-config.js             # Configuration
├── 📁 components/              # UI components
│   ├── 📄 marwa-forms.js              # Form handling
│   ├── 📄 marwa-navigation.js         # Navigation
│   ├── 📄 marwa-modals.js             # Modal dialogs
│   ├── 📄 marwa-tabs.js               # Tab system
│   ├── 📄 marwa-accordion.js          # Accordion
│   └── 📄 marwa-carousel.js           # Carousel
├── 📁 themes/                  # Theme functionality
│   ├── 📄 marwa-theme-switcher.js     # Theme switching
│   ├── 📄 marwa-color-schemes.js      # Color schemes
│   └── 📄 marwa-rtl-support.js        # RTL support
├── 📁 accessibility/           # Accessibility features
│   ├── 📄 marwa-screen-reader.js      # Screen reader support
│   ├── 📄 marwa-keyboard-nav.js       # Keyboard navigation
│   └── 📄 marwa-focus-management.js   # Focus management
└── 📄 marwa.js                         # Main framework file
```

---

## 🗄️ **Database Structure (mizan/)**

### **Database Files**
```
mizan/
├── 📁 migrations/              # Database migrations
│   ├── 📄 0001_initial_schema.php     # Initial schema
│   ├── 📄 0002_quran_schema.php       # Quran schema
│   ├── 📄 0003_hadith_schema.php      # Hadith schema
│   └── 📄 0004_extensions_schema.php  # Extensions schema
├── 📁 seeds/                   # Database seeds
│   ├── 📄 user_seeds.php              # User data seeds
│   ├── 📄 content_seeds.php           # Content seeds
│   └── 📄 extension_seeds.php         # Extension seeds
├── 📁 models/                  # Database models
│   ├── 📄 UserModel.php               # User model
│   ├── 📄 PageModel.php               # Page model
│   └── 📄 ExtensionModel.php          # Extension model
└── 📄 database.php                     # Database configuration
```

---

## 🔒 **Security System (aman/)**

### **Security Components**
```
aman/
├── 📁 authentication/           # Authentication system
│   ├── 📄 SecurityAuthenticator.php       # Main authenticator
│   ├── 📄 UserAuthenticator.php       # User authentication
│   └── 📄 TokenAuthenticator.php      # Token authentication
├── 📁 authorization/            # Authorization system
│   ├── 📄 SecurityAuthorizer.php          # Main authorizer
│   ├── 📄 RoleAuthorizer.php          # Role-based authorization
│   └── 📄 PermissionAuthorizer.php    # Permission-based authorization
├── 📁 validation/              # Input validation
│   ├── 📄 SecurityValidator.php           # Main validator
│   ├── 📄 InputValidator.php          # Input validation
│   └── 📄 ContentValidator.php        # Content validation
├── 📁 encryption/              # Encryption system
│   ├── 📄 SecurityEncryptor.php           # Main encryptor
│   ├── 📄 PasswordEncryptor.php       # Password encryption
│   └── 📄 DataEncryptor.php           # Data encryption
└── 📄 security.php                # Main security file
```

---

## 🚀 **Performance System (rihlah/)**

### **Performance Components**
```
rihlah/
├── 📁 caching/                 # Caching system
│   ├── 📄 RoutingCache.php              # Journey cache
│   ├── 📄 PageCache.php                # Page caching
│   ├── 📄 ObjectCache.php              # Object caching
│   └── 📄 RouteCache.php               # Route caching
├── 📁 monitoring/              # Performance monitoring
│   ├── 📄 RoutingMonitor.php            # Journey monitor
│   ├── 📄 PerformanceMonitor.php       # Performance metrics
│   └── 📄 ResourceMonitor.php          # Resource monitoring
├── 📁 optimization/            # Optimization system
│   ├── 📄 RoutingOptimizer.php          # Journey optimizer
│   ├── 📄 DatabaseOptimizer.php        # Database optimization
│   └── 📄 AssetOptimizer.php           # Asset optimization
└── 📄 rihlah-performance.php            # Main performance file
```

---

## 🔄 **Routing System (simplified-routing/)**

### **Routing Components**
```
simplified-routing/
├── 📁 routes/                  # Route definitions
│   ├── 📄 web-routes.php              # Web routes
│   ├── 📄 api-routes.php              # API routes
│   └── 📄 admin-routes.php            # Admin routes
├── 📁 middleware/              # Route middleware
│   ├── 📄 SimplifiedRoutingMiddleware.php  # Main routing middleware
│   ├── 📄 RouteMiddleware.php         # Route-specific middleware
│   └── 📄 ApiMiddleware.php           # API middleware
└── 📄 simplified-routing-router.php    # Main router
```

---

## 📚 **Content System (nizam/)**

### **Content Components**
```
nizam/
├── 📁 content/                 # Content management
│   ├── 📄 ApplicationContent.php             # Main content manager
│   ├── 📄 PageContent.php              # Page content
│   ├── 📄 ArticleContent.php           # Article content
│   └── 📄 MediaContent.php             # Media content
├── 📁 reading/                 # Reading system
│   ├── 📄 ApplicationReader.php              # Main reader
│   ├── 📄 PageReader.php               # Page reader
│   └── 📄 ContentReader.php            # Content reader
├── 📁 delivery/                # Content delivery
│   ├── 📄 ApplicationDelivery.php            # Main delivery system
│   ├── 📄 ContentDelivery.php          # Content delivery
│   └── 📄 MediaDelivery.php            # Media delivery
└── 📄 nizam-content.php                # Main content file
```

---

## 🔍 **Search System (iqra/)**

### **Search Components**
```
iqra/
├── 📁 search/                  # Search engine
│   ├── 📄 IqraSearch.php               # Main search engine
│   ├── 📄 FullTextSearch.php           # Full-text search
│   ├── 📄 SemanticSearch.php           # Semantic search
│   └── 📄 IslamicSearch.php            # Islamic content search
├── 📁 indexing/                # Content indexing
│   ├── 📄 IqraIndexer.php              # Main indexer
│   ├── 📄 ContentIndexer.php           # Content indexing
│   └── 📄 SearchIndexer.php            # Search indexing
├── 📁 discovery/               # Knowledge discovery
│   ├── 📄 IqraDiscovery.php             # Main discovery engine
│   ├── 📄 ContentDiscovery.php         # Content discovery
│   └── 📄 RelatedContent.php           # Related content
└── 📄 iqra-search.php                  # Main search file
```

---

## 📝 **Knowledge Management System (usul/)**

### **Knowledge Components**
```
usul/
├── 📁 knowledge/               # Knowledge management
│   ├── 📄 Knowledge.php            # Main knowledge manager
│   ├── 📄 ContentKnowledge.php         # Content knowledge
│   ├── 📄 UserKnowledge.php            # User knowledge
│   └── 📄 SystemKnowledge.php          # System knowledge
├── 📁 principles/              # Knowledge principles
│   ├── 📄 KnowledgePrinciples.php           # Main principles
│   ├── 📄 ContentPrinciples.php        # Content principles
│   └── 📄 UserPrinciples.php           # User principles
├── 📁 management/              # Knowledge management
│   ├── 📄 KnowledgeManager.php              # Main manager
│   ├── 📄 ContentManager.php           # Content manager
│   └── 📄 UserManager.php              # User manager
└── 📄 usul-knowledge.php               # Main knowledge file
```

---

## 🎨 **Content Formatting System (bayan/)**

### **Formatting Components**
```
bayan/
├── 📁 formatting/              # Content formatting
│   ├── 📄 BayanFormatter.php           # Main formatter
│   ├── 📄 TextFormatter.php             # Text formatting
│   ├── 📄 HtmlFormatter.php             # HTML formatting
│   └── 📄 MarkdownFormatter.php        # Markdown formatting
├── 📁 themes/                  # Formatting themes
│   ├── 📄 BayanTheme.php               # Main theme
│   ├── 📄 LightTheme.php               # Light theme
│   └── 📄 DarkTheme.php                # Dark theme
├── 📁 components/              # Formatting components
│   ├── 📄 BayanComponents.php          # Main components
│   ├── 📄 TextComponents.php            # Text components
│   └── 📄 HtmlComponents.php            # HTML components
└── 📄 bayan-formatter.php               # Main formatting file
```

---

## 📝 **Documentation System (ilm/)**

### **Documentation Structure**
```
ilm/
├── 📁 architecture/             # Architecture documentation
│   ├── 📄 overview.md                   # Architecture overview
│   ├── 📄 core-systems.md               # Core systems
│   └── 📄 hybrid-architecture.md        # Hybrid architecture
├── 📁 guides/                   # User and developer guides
│   ├── 📄 installation.md               # Installation guide
│   ├── 📄 development.md                # Development guide
│   └── 📄 islamic-naming-conventions.md # Naming conventions
├── 📁 api/                      # API documentation
│   ├── 📄 overview.md                   # API overview
│   └── 📄 endpoints.md                  # API endpoints
├── 📁 extensions/               # Extension documentation
│   ├── 📄 development.md                # Extension development
│   └── 📄 api.md                        # Extension API
├── 📁 skins/                    # Skin documentation
│   ├── 📄 development.md                # Skin development
│   └── 📄 customization.md              # Skin customization
└── 📄 README.md                         # Main documentation
```

---

## 🔧 **Implementation Priority**

### **Phase 1: Core Systems (Immediate)**
1. **Rename main directories**:
   - `src/` → `asas/`
   - `config/` → `tadbir/`
   - `cache/` → `rihlah/`

2. **Create new directories**:
   - `safa/` (CSS framework)
   - `marwa/` (JavaScript framework)

### **Phase 2: System Components (Short-term)**
1. **Rename system directories**:
   - `security/` → `aman/`
   - `routes/` → `sabil/`
   - `database/` → `mizan/`

2. **Rename content directories**:
   - `public/` → `nizam/`
   - `resources/` → `usul/`

### **Phase 3: File Names (Medium-term)**
1. **Rename PHP files** with Islamic prefixes
2. **Rename CSS files** with Safa prefix
3. **Rename JavaScript files** with Marwa prefix

### **Phase 4: Code References (Long-term)**
1. **Update all PHP class names**
2. **Update all file references**
3. **Update configuration files**
4. **Update documentation references**

---

## 📋 **Complete File Naming Reference**

### **PHP Files**
```
Core Files:
├── asas-{component}.php        # Container files
├── aman-{component}.php        # Security files
├── simplified-routing-{component}.php  # Routing files
├── tadbir-{component}.php      # Configuration files
├── rihlah-{component}.php      # Caching files
├── tadbir-{component}.php      # Administration files
├── nizam-{component}.php       # Main application files
├── usul-{component}.php        # Knowledge management files
├── siraj-{component}.php       # API files
├── rihlah-{component}.php      # Caching files
├── shahid-{component}.php      # Logging files
├── usul-{component}.php        # Knowledge files
├── sabr-{component}.php        # Queue files
└── wisal-{component}.php       # Session files
```

### **CSS Files**
```
Safa Framework:
├── safa-{component}.css        # Component styles
├── safa-base.css               # Base styles
├── safa-components.css         # Component styles
├── safa-themes.css             # Theme styles
├── safa-utilities.css          # Utility classes
└── safa-islamic.css            # Islamic theme
```

### **JavaScript Files**
```
Marwa Framework:
├── marwa-{component}.js        # Component scripts
├── marwa-core.js               # Core functionality
├── marwa-components.js         # UI components
├── marwa-themes.js             # Theme functionality
├── marwa-utilities.js          # Utility functions
└── marwa-accessibility.js      # Accessibility features
```

---

## 🚨 **Important Notes**

### **Migration Considerations**
1. **Backup everything** before starting migration
2. **Update all references** in code and configuration
3. **Test thoroughly** after each phase
4. **Update documentation** to reflect new names
5. **Maintain backward compatibility** during transition

### **Naming Consistency**
1. **Use consistent prefixes** for all files
2. **Maintain clear relationships** between names and purposes
3. **Document all name meanings** for future reference
4. **Follow established patterns** across the system

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Status:** Islamic Naming Implementation Guide Complete ✅ 
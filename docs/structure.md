# IslamWiki Project Structure Documentation

## 🎯 **Overview**

This document defines the **modern hybrid architecture** for IslamWiki, combining the best features of **MediaWiki** (content management, versioning, collaborative editing) with **WordPress** (ease of use, plugin system, modern development practices) while maintaining **Islamic-first design principles**.

**ALWAYS reference this document before creating, moving, or deleting any files or folders.**

---

## 🏗️ **Architecture Philosophy**

### **Hybrid Approach: MediaWiki + WordPress + Modern PHP**
- **MediaWiki Strengths**: Content versioning, collaborative editing, namespace system, extension framework
- **WordPress Strengths**: Plugin system, theme system, user-friendly admin, modern development practices
- **Modern PHP**: PSR standards, dependency injection, service providers, modern routing
- **Islamic-First**: Built-in Islamic content types, metadata, and features

### **Core Principles**
1. **Content-First**: Powerful content management with version control
2. **Extension-Driven**: Everything is an extension for maximum flexibility
3. **Performance-Optimized**: Built-in caching, asset optimization, database efficiency
4. **Security-First**: Enterprise-grade security with Islamic content validation
5. **Developer-Friendly**: Clean architecture, comprehensive documentation, testing support

---

## 📁 **Root Directory Structure**

```
local.islam.wiki/
├── 📁 backup/              # Backup files (database dumps, file backups)
├── 📁 config/              # Configuration files (app.php, database.php, etc.)
├── 📁 database/            # Database migrations and seeds
├── 📁 docs/                # Comprehensive documentation
├── 📁 extensions/          # Extension system (plugins, modules, themes)
├── 📁 languages/           # Language files (JSON-based, modern approach)
├── 📁 logs/                # Application logs (error logs, access logs)
├── 📁 maintenance/         # Debug and test files (development utilities)
├── 📁 public/              # Web entry points ONLY (minimal, secure)
├── 📁 resources/           # Frontend assets and templates
├── 📁 scripts/             # Utility scripts (setup, maintenance, tools)
├── 📁 skins/               # Skin-specific assets (themes)
├── 📁 src/                 # PHP source code (core framework)
├── 📁 storage/             # Application storage (framework cache, sessions)
├── 📁 var/                 # Variable data (cache, temporary files)
├── 📁 vendor/              # Composer dependencies
├── 📄 LocalSettings.php    # Main configuration (MediaWiki-style)
├── 📄 IslamSettings.php    # Islamic-specific settings override
├── 📄 composer.json        # Dependencies and autoloading
└── 📄 .htaccess            # Apache configuration (root level)
```

---

## 🔧 **Configuration System**

### **Hybrid Configuration Approach**
- **LocalSettings.php**: Main configuration (MediaWiki-style, familiar to wiki users)
- **IslamSettings.php**: Islamic-specific overrides (optional, for customization)
- **Environment Variables**: Modern .env support for deployment flexibility
- **Service Providers**: PHP-based configuration for developers

### **Configuration Priority (Highest to Lowest)**
1. Environment variables (`.env`)
2. `IslamSettings.php` (Islamic overrides)
3. `LocalSettings.php` (main configuration)
4. Default values (hardcoded fallbacks)

---

## 🌐 **Routing System**

### **SabilRouting - Modern PHP Routing**
- **Inline Route Definition**: Routes defined directly in `public/app.php` (no external route files)
- **Controller Support**: Full controller/method routing with dependency injection
- **Middleware Stack**: Comprehensive middleware support for security and functionality
- **Performance**: Route caching and optimization built-in

### **Route Definition Pattern**
```php
// In public/app.php - CORRECT way
$router = new SabilRouting($container);

// Content Routes (MediaWiki-style)
$router->get('/wiki', 'WikiController@index');
$router->get('/wiki/{slug}', 'WikiController@show');
$router->get('/wiki/{slug}/edit', 'WikiController@edit');

// Admin Routes (WordPress-style)
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/extensions', 'AdminController@extensions');

// API Routes (Modern REST)
$router->get('/api/v1/quran', 'Api\QuranController@index');
$router->get('/api/v1/hadith', 'Api\HadithController@index');
```

---

## 📚 **Content Management System**

### **Content Types (Islamic-First)**
- **Articles**: General Islamic content (MediaWiki-style)
- **Wiki Pages**: Collaborative content with version control
- **Fatwas**: Islamic rulings with scholarly verification
- **Quran**: Complete Quran integration with translations
- **Hadith**: Hadith collections with authenticity grading
- **Sahaba**: Companion biographies and stories
- **Duas**: Islamic supplications and salah

### **Content Structure**
```
resources/views/
├── 📁 content/             # Content type templates
│   ├── 📁 articles/        # Article templates
│   ├── 📁 wiki/           # Wiki page templates
│   ├── 📁 fatwas/         # Fatwa templates
│   ├── 📁 quran/          # Quran templates
│   ├── 📁 hadith/         # Hadith templates
│   ├── 📁 sahaba/         # Sahabi templates
│   └── 📁 duas/           # Dua templates
├── 📁 layouts/             # Base layouts
├── 📁 components/          # Reusable components
└── 📁 errors/              # Error pages (404, 500)
```

---

## 🎨 **Frontend Architecture**

### **Asset Organization**
```
resources/
├── 📁 assets/              # Framework assets
│   ├── 📁 css/
│   │   └── safa.css       # Safa CSS Framework (صافا)
│   └── 📁 js/
│       └── zamzam.js      # ZamZam.js Framework (زمزم)
├── 📁 views/               # Twig templates
└── 📁 lang/                # PHP language files (legacy support)
```

### **Skin System (WordPress-style Themes)**
```
skins/
├── 📁 Bismillah/          # Default skin
│   ├── 📁 css/            # Skin-specific CSS
│   ├── 📁 js/             # Skin-specific JavaScript
│   ├── 📁 templates/      # Skin-specific templates
│   └── 📄 skin.json       # Skin configuration
├── 📁 Muslim/             # Alternative skin
└── 📁 CustomSkin/         # User-created skins
```

### **Extension Assets**
```
extensions/
├── 📁 QuranExtension/
│   ├── 📁 assets/         # Extension-specific assets
│   │   ├── 📁 css/
│   │   └── 📁 js/
│   └── 📁 templates/      # Extension-specific templates
└── 📁 HadithExtension/
    ├── 📁 assets/
    └── 📁 templates/
```

---

## 🔌 **Extension System**

### **Extension Types**
- **Content Extensions**: Add new content types (Quran, Hadith, etc.)
- **Functionality Extensions**: Add features (salah times, calendar, etc.)
- **Theme Extensions**: Add new skins and themes
- **Integration Extensions**: Connect to external services

### **Extension Structure**
```
extensions/
├── 📁 {ExtensionName}/
│   ├── 📄 {ExtensionName}.php      # Main extension class
│   ├── 📄 extension.json           # Extension metadata
│   ├── 📁 assets/                  # CSS, JS, images
│   ├── 📁 templates/               # Twig templates
│   ├── 📁 database/                # Migrations and seeds
│   ├── 📁 src/                     # PHP source code
│   └── 📁 docs/                    # Extension documentation
```

---

## 🗄️ **Database Architecture**

### **Multi-Database Support**
- **Main Database**: General wiki content, users, extensions
- **Quran Database**: Complete Quran with translations and recitations
- **Hadith Database**: Hadith collections with authenticity grading
- **Islamic Database**: Islamic-specific content and features

### **Database Structure**
```
database/
├── 📁 migrations/          # Database schema changes
├── 📁 seeds/              # Initial data population
└── 📁 connections/         # Database connection configurations
```

---

## 🚀 **Performance & Caching**

### **Caching Strategy**
- **Page Caching**: Full page caching for static content
- **Object Caching**: Redis/Memcached for dynamic content
- **Asset Caching**: Browser caching for CSS, JS, images
- **Database Caching**: Query result caching and connection pooling

### **Cache Locations**
```
var/
├── 📁 cache/              # Application cache
│   ├── 📁 pages/          # Page cache
│   ├── 📁 objects/        # Object cache
│   └── 📁 assets/         # Asset cache
└── 📁 logs/               # Variable log files

storage/
├── 📁 framework/          # Framework cache
│   ├── 📁 views/          # Compiled Twig templates
│   ├── 📁 cache/          # Framework cache
│   └── 📁 sessions/       # Session files
└── 📁 logs/               # Application logs
```

---

## 🔒 **Security Architecture**

### **Security Layers**
1. **Input Validation**: Comprehensive input sanitization
2. **Output Escaping**: Automatic XSS protection
3. **Authentication**: Multi-factor authentication support
4. **Authorization**: Role-based access control (RBAC)
5. **Content Security**: Islamic content validation
6. **Rate Limiting**: API and form submission protection

### **Security Files**
```
config/
├── 📄 security.php         # Security configuration
├── 📄 auth.php            # Authentication settings
└── 📄 permissions.php      # Permission definitions
```

---

## 🧪 **Development & Testing**

### **Development Structure**
```
maintenance/
├── 📁 tests/              # Test files
│   ├── 📁 Unit/          # Unit tests
│   ├── 📁 Integration/   # Integration tests
│   ├── 📁 web/           # Web tests
│   └── 📁 cli/           # Command line tests
├── 📁 debug/              # Debug utilities
└── 📁 scripts/            # Development scripts
```

### **Testing Framework**
- **PHPUnit**: Unit and integration testing
- **Browser Testing**: Selenium/Playwright for web testing
- **Performance Testing**: Load testing and optimization
- **Security Testing**: Vulnerability scanning and testing

---

## 📋 **Critical Rules - NEVER VIOLATE**

### ❌ **FORBIDDEN ACTIONS**

1. **NEVER put CSS/JS files in `public/`**
2. **NEVER put PHP source code in `resources/`**
3. **NEVER put templates in `src/`**
4. **NEVER put configuration in `public/`**
5. **NEVER put assets in `src/`**
6. **NEVER create external route files** (use SabilRouting inline)

### ✅ **REQUIRED ACTIONS**

1. **ALWAYS put framework assets in `resources/assets/`**
2. **ALWAYS put skin assets in `skins/{SkinName}/`**
3. **ALWAYS put templates in `resources/views/`**
4. **ALWAYS put PHP code in `src/`**
5. **ALWAYS put entry points in `public/`**
6. **ALWAYS put logs in `logs/` or `storage/logs/`**
7. **ALWAYS put backups in `backup/`**
8. **ALWAYS put temporary data in `var/`**
9. **ALWAYS define routes inline in `app.php` using SabilRouting**

---

## 🔍 **Validation Commands**

### **Check Current Structure**
```bash
# Check for misplaced CSS/JS files
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins | grep -v extensions

# Check for PHP files in wrong locations
find . -name "*.php" | grep -v vendor | grep -v src | grep -v maintenance | grep -v scripts | grep -v extensions

# Check public directory (should only have entry points)
ls -la public/

# Verify framework assets
ls -la resources/assets/css/ resources/assets/js/

# Verify skin assets  
ls -la skins/*/css/ skins/*/js/

# Verify all directories exist
ls -la backup/ logs/ var/ storage/
```

### **Validate Structure**
```bash
# Ensure no assets in public
find public/ -name "*.css" -o -name "*.js"

# Ensure frameworks are in resources/assets
ls -la resources/assets/css/ resources/assets/js/

# Ensure skins are properly structured
ls -la skins/*/css/ skins/*/js/

# Check for misplaced files
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins | grep -v extensions

# Check public directory (should only have entry points)
ls -la public/

# Verify all directories exist
ls -la backup/ logs/ var/ storage/
```

---

## 📚 **Reference Documentation**

- **Architecture Overview**: `docs/architecture/overview.md`
- **Development Standards**: `docs/STANDARDS.md`
- **Naming Conventions**: `docs/guides/naming-conventions.md`
- **Extension Development**: `docs/guides/extension-development.md`
- **Skin Development**: `docs/guides/skin-development.md`

---

## 🚨 **Before Any File Operation**

1. **Check this reference document**
2. **Verify current structure**
3. **Follow naming conventions**
4. **Test web access paths**
5. **Update documentation if needed**
6. **Ensure no duplicate files in wrong locations**

---

**Last Updated:** 2025-08-19  
**Version:** 2.0 (Modern Hybrid Architecture)  
**Author:** IslamWiki Development Team 
**Architecture:** MediaWiki + WordPress + Modern PHP Hybrid 
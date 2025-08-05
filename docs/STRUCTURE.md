# IslamWiki Project Structure Documentation

## Overview
This document defines the correct file and directory structure for the IslamWiki project. **ALWAYS reference this document before creating, moving, or deleting any files or folders.**

## Root Directory Structure

```
local.islam.wiki/
├── backup/               # Backup files (database dumps, file backups)
├── config/               # Configuration files
├── database/             # Database migrations and seeds
├── docs/                 # Documentation
├── extensions/           # Extension files
├── logs/                 # Application logs (error logs, access logs)
├── maintenance/          # Debug and test files
├── public/               # Web entry points ONLY
├── resources/            # Frontend assets
├── routes/               # Route definitions
├── scripts/              # Utility scripts
├── skins/                # Skin-specific assets
├── src/                  # PHP source code
├── storage/              # Application storage
├── var/                  # Variable data (cache, temporary files)
├── vendor/               # Composer dependencies
├── LocalSettings.php     # Main configuration
├── IslamSettings.php     # Islamic-specific settings
└── composer.json         # Dependencies
```

## Detailed Structure Rules

### 1. Frontend Assets (`resources/`)

#### Framework Assets
- **Location:** `resources/assets/css/` and `resources/assets/js/`
- **Files:** 
  - `safa.css` - Safa CSS Framework (صافا) - Structural CSS framework
  - `zamzam.js` - ZamZam.js Framework (زمزم) - Custom JavaScript framework
- **Purpose:** Core CSS and JavaScript frameworks
- **Web Access:** `/assets/css/` and `/assets/js/` (via AssetController)

#### Templates
- **Location:** `resources/views/`
- **Files:** `.twig` template files
- **Purpose:** HTML templates for the application
- **Web Access:** Rendered through application

#### Error Pages
- **Location:** `resources/views/errors/`
- **Files:** `401.php`, `404.php`, `500.php`
- **Purpose:** Standalone error pages (must work without Twig)
- **Note:** These remain as PHP files for independence

### 2. Skin Assets (`skins/`)

#### Skin Structure
```
skins/
├── {SkinName}/
│   ├── css/
│   │   └── {skinname}.css
│   ├── js/
│   │   └── {skinname}.js
│   ├── templates/
│   │   └── layout.twig
│   └── skin.json
```

#### Skin Rules
- **Location:** `skins/{SkinName}/`
- **Files:** CSS, JS, templates specific to each skin
- **Naming:** Files should be named `{skinname}.css` and `{skinname}.js`
- **Web Access:** `/skins/{SkinName}/css/` and `/skins/{SkinName}/js/` (via AssetController)

### 3. PHP Source Code (`src/`)

#### Core Components
- **Location:** `src/Core/`
- **Purpose:** Core framework components with Islamic-named systems
- **Structure:**
  - `API/` - Siraj API (سراج) - API management and routing system
  - `Auth/` - Aman Security (أمان) - Comprehensive security, authentication, and authorization
  - `Caching/` - Rihlah Caching (رحلة) - Caching system
  - `Container/` - Asas Container (أساس) - Dependency injection container
  - `Database/` - Mizan Database (ميزان) - Database connection and data integrity
  - `Error/` - Error handling (handled by Shahid)
  - `Extensions/` - Extension system
  - `Configuration/` - Tadbir Configuration (تدبير) - Configuration management and planning
  - `Formatter/` - Bayan Formatter (بيان) - Content formatting
  - `Http/` - HTTP components
  - `Islamic/` - Islamic-specific features
  - `Knowledge/` - Usul Knowledge (أصول) - Knowledge management
  - `Logging/` - Shahid Logging (شاهد) - Logging and error handling system
  - `Queue/` - Sabr Queue (صبر) - Job queue system
  - `Routing/` - Sabil Routing (سبيل) - Advanced routing system
  - `Search/` - Iqra Search (اقرأ) - Islamic search engine
  - `Security/` - Security features (handled by Aman)
  - `Session/` - Wisal Session (وصال) - Session management
  - `Skin/` - Skin management
  - `View/` - View rendering

#### HTTP Components
- **Location:** `src/Http/`
- **Structure:**
  - `Controllers/` - Application controllers
  - `Middleware/` - HTTP middleware

#### Models
- **Location:** `src/Models/`
- **Purpose:** Data models and entities

#### Providers
- **Location:** `src/Providers/`
- **Purpose:** Service providers for dependency injection

#### Skins
- **Location:** `src/Skins/`
- **Purpose:** Skin management classes

### 4. Web Entry Points (`public/`)

#### Allowed Files
- `index.php` - Main entry point
- `app.php` - Application entry point
- `.htaccess` - Apache configuration
- `favicon.ico` - Site favicon
- Entry point scripts (login.php, register.php, etc.)

#### Forbidden in `public/`
- ❌ CSS files
- ❌ JavaScript files
- ❌ Template files
- ❌ PHP source code
- ❌ Configuration files

### 5. Configuration (`config/`)

#### Files
- `app.php` - Application configuration
- `database.php` - Database configuration
- `logging.php` - Logging configuration
- `routes.php` - Route configuration

### 6. Database (`database/`)

#### Structure
- `migrations/` - Database migration files
- `seeds/` - Database seed files

### 7. Extensions (`extensions/`)

#### Structure
```
extensions/
├── {ExtensionName}/
│   ├── {ExtensionName}.php
│   └── extension.json
```

### 8. Scripts (`scripts/`)

#### Purpose
- Database setup scripts
- Utility scripts
- Debug scripts
- Test scripts

### 9. Maintenance (`maintenance/`)

#### Purpose
- Debug files
- Test files
- Development utilities

### 10. Storage (`storage/`)

#### Structure
- `framework/views/` - Compiled Twig templates
- `logs/` - Application logs
- `sessions/` - Session files
- `cache/` - Cache files

### 11. Backup (`backup/`)

#### Purpose
- Database dumps
- File backups
- Configuration backups
- Archive files

### 12. Logs (`logs/`)

#### Purpose
- Application error logs
- Access logs
- Debug logs
- Performance logs

### 13. Variable Data (`var/`)

#### Structure
- `cache/` - Temporary cache files
- `logs/` - Variable log files
- Temporary application data

### 14. Routes (`routes/`)

#### Purpose
- Route definitions and configurations
- API route definitions
- Web route definitions

### 15. Documentation (`docs/`)

#### Structure
- `architecture/` - System architecture documentation
- `components/` - Component documentation
- `controllers/` - Controller documentation
- `features/` - Feature documentation
- `guides/` - User and developer guides
- `layouts/` - Layout documentation
- `models/` - Model documentation
- `plans/` - Development plans and roadmaps
- `releases/` - Release documentation
- `security/` - Security documentation
- `skins/` - Skin documentation
- `systems/` - System documentation
- `testing/` - Testing documentation
- `troubleshooting/` - Troubleshooting guides
- `views/` - View documentation

## Critical Rules - NEVER VIOLATE

### ❌ FORBIDDEN ACTIONS

1. **NEVER put CSS/JS files in `public/`**
2. **NEVER put PHP source code in `resources/`**
3. **NEVER put templates in `src/`**
4. **NEVER put configuration in `public/`**
5. **NEVER put assets in `src/`**

### ✅ REQUIRED ACTIONS

1. **ALWAYS put framework assets in `resources/assets/`**
2. **ALWAYS put skin assets in `skins/{SkinName}/`**
3. **ALWAYS put templates in `resources/views/`**
4. **ALWAYS put PHP code in `src/`**
5. **ALWAYS put entry points in `public/`**
6. **ALWAYS put logs in `logs/` or `storage/logs/`**
7. **ALWAYS put backups in `backup/`**
8. **ALWAYS put temporary data in `var/`**

## Asset Serving

### Framework Assets
- **Storage:** `resources/assets/css/safa.css`
- **Web Access:** `/assets/css/safa.css` (via AssetController)
- **Storage:** `resources/assets/js/zamzam.js`
- **Web Access:** `/assets/js/zamzam.js` (via AssetController)

### Skin Assets
- **Storage:** `skins/Bismillah/css/bismillah.css`
- **Web Access:** `/skins/Bismillah/css/bismillah.css` (via AssetController)

## File Naming Conventions

### CSS/JS Files
- Framework files: `safa.css`, `zamzam.js`
- Skin files: `{skinname}.css`, `{skinname}.js`

### PHP Files
- Controllers: `{Name}Controller.php`
- Models: `{Name}.php`
- Providers: `{Name}ServiceProvider.php`
- Middleware: `{Name}Middleware.php`

### Template Files
- Layouts: `layouts/{name}.twig`
- Pages: `{section}/{name}.twig`
- Components: `components/{name}.twig`

## Validation Checklist

Before creating, moving, or deleting any file, check:

- [ ] Is the file in the correct directory according to this structure?
- [ ] Does the file follow the naming conventions?
- [ ] Is the file accessible via the correct web path?
- [ ] Are there no duplicate files in wrong locations?
- [ ] Does the file serve its intended purpose in the correct location?

## Common Mistakes to Avoid

1. **Putting assets in `public/`** - Use `resources/assets/` instead
2. **Putting source code in `resources/`** - Use `src/` instead
3. **Putting templates in `src/`** - Use `resources/views/` instead
4. **Creating duplicate files** - Check existing locations first
5. **Using wrong file extensions** - Use `.twig` for templates, `.php` for source code

## Reference Commands

### Check Current Structure
```bash
# Check for misplaced CSS/JS files
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins

# Check for PHP files in wrong locations
find . -name "*.php" | grep -v vendor | grep -v src | grep -v maintenance | grep -v scripts

# Check public directory contents
ls -la public/
```

### Validate Structure
```bash
# Ensure no assets in public
find public/ -name "*.css" -o -name "*.js"

# Ensure frameworks are in resources/assets
ls -la resources/assets/css/ resources/assets/js/

# Ensure skins are properly structured
ls -la skins/*/css/ skins/*/js/

# Check for misplaced files
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins

# Check public directory (should only have entry points)
ls -la public/

# Verify all directories exist
ls -la backup/ logs/ var/ storage/
```

---

**Last Updated:** 2025-08-05
**Version:** 1.0
**Author:** IslamWiki Development Team 
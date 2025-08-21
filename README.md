# IslamWiki - Authentic Islamic Knowledge Platform

## 🎯 **Overview**

IslamWiki is a comprehensive Islamic knowledge management platform that combines the best features of MediaWiki, WordPress, and modern PHP frameworks while maintaining Islamic values and principles.

## 🏗️ **Project Structure**

### **📁 Core Directories**

```
local.islam.wiki/
├── 📁 public/              # Web entry points ONLY
│   ├── 📄 index.php        # Main application entry point
│   ├── 📄 app.php          # Alternative entry point
│   ├── 📄 .htaccess        # Apache configuration
│   └── 📄 favicon.ico      # Site favicon
├── 📁 src/                 # PHP source code
├── 📁 resources/           # Frontend assets and templates
├── 📁 skins/               # Skin-specific assets
├── 📁 extensions/          # Extension system
├── 📁 config/              # Configuration files
├── 📁 database/            # Database migrations
├── 📁 languages/           # Language files
├── 📁 storage/             # Application storage
├── 📁 var/                 # Variable data (cache, logs)
└── 📁 vendor/              # Composer dependencies
```

### **📁 Development & Maintenance**

```
local.islam.wiki/
├── 📁 scripts/             # Utility and setup scripts
│   ├── 📁 database/        # Database scripts
│   ├── 📁 hadith_import/   # Hadith import scripts
│   ├── 📁 quran/           # Quran import scripts
│   ├── 📁 templates/       # Template scripts
│   └── 📁 utils/           # Utility scripts
├── 📁 maintenance/         # Maintenance and system scripts
│   ├── 📁 scripts/         # Maintenance scripts
│   └── 📁 update/          # Update scripts
├── 📁 tests/               # All testing files (consolidated)
└── 📁 debug/               # All debug files (consolidated)
```

## 🚀 **Quick Start**

### **1. Start Development Server**
```bash
php -S localhost:8000 public/index.php
```

### **2. Access Application**
- **Main Site**: http://localhost:8000
- **Admin Dashboard**: http://localhost:8000/dashboard/admin
- **User Dashboard**: http://localhost:8000/dashboard/user

## 🔧 **Development**

### **Architecture**
- **Hybrid System**: MediaWiki + WordPress + Modern PHP
- **16 Core Islamic Systems**: Named after Islamic concepts
- **Extension System**: WordPress-inspired plugin architecture
- **Skin System**: WordPress-inspired theme architecture

### **Core Systems**
- **Asas** (Foundation) - Core foundation and services
- **Aman** (Security) - Authentication and authorization
- **Sabil** (Path) - Routing and request handling
- **Nizam** (Order) - Application coordination
- **Mizan** (Balance) - Database management
- **Tadbir** (Management) - Configuration management
- **Safa** (Purity) - CSS framework
- **Marwa** (Excellence) - JavaScript framework

## 🎨 **Recent Major Improvements**

### **✅ Unified Skin System Implementation**
- **Consolidated all visual elements** into a single skin system
- **Eliminated fragmentation** between layouts, templates, and components
- **Professional appearance** across all pages with consistent header/footer
- **WordPress-quality skin management** with Islamic aesthetics

### **✅ Dashboard System Restoration**
- **Admin Dashboard** (`/dashboard/admin`) - Full system management interface
- **User Dashboard** (`/dashboard/user`) - Personalized user experience
- **Proper sidebar navigation** with role-based links
- **Beautiful Islamic theming** with responsive design

### **✅ Header & Navigation Improvements**
- **Logo now links to home page** (`/`) with proper accessibility
- **Unified user menu** with working logout functionality
- **Consistent navigation** across all pages
- **Professional header design** with Islamic aesthetics

### **✅ Project Organization Cleanup**
- **Public folder cleaned** - Only web entry points remain
- **Test files consolidated** in `tests/` directory
- **Debug files consolidated** in `debug/` directory
- **No duplication** between maintenance and scripts folders
- **Clear separation of concerns** for all file types

### **✅ CSS & Styling Improvements**
- **Dashboard-specific styles** properly isolated from main page
- **Responsive design** for all screen sizes
- **Islamic color scheme** with golden accents
- **Smooth animations** and hover effects
- **Professional appearance** throughout the platform

## 📚 **Documentation**

- **Architecture**: `docs/architecture/`
- **Development**: `docs/guides/development.md`
- **Extensions**: `docs/extensions/development.md`
- **Skins**: `docs/skins/development.md`
- **Standards**: `docs/standards/standards.md`

## 🧪 **Testing & Debugging**

### **Test Files**
All test files are consolidated in the `tests/` directory:
```bash
# Run specific tests
php tests/test_name.php

# Run all tests
find tests/ -name "*.php" -exec php {} \;
```

### **Debug Files**
All debug files are consolidated in the `debug/` directory:
```bash
# Run debug scripts
php debug/debug_script.php
```

### **Organization Check**
Run our organization verification script:
```bash
php scripts/organize_project.php
```

## 🚨 **Important Rules**

### **❌ NEVER PUT IN `public/`**
- CSS files
- JavaScript files
- Template files
- PHP source code
- Configuration files
- Test files
- Debug files

### **✅ CORRECT LOCATIONS**
- **Web Entry Points**: `public/` (ONLY)
- **PHP Source Code**: `src/`
- **Templates**: `resources/views/`
- **Skin Assets**: `skins/{SkinName}/`
- **Extension Assets**: `extensions/{Name}/`
- **Test Files**: `tests/`
- **Debug Files**: `debug/`

## 🔒 **Security**

- **Authentication**: Multi-factor authentication support
- **Authorization**: Role-based access control
- **Input Validation**: Comprehensive sanitization
- **Output Security**: XSS protection
- **Content Security**: Islamic content validation

## 📊 **Current Status**

### **✅ Completed Features**
- **Unified Skin System**: All visual elements consolidated
- **Dashboard System**: Admin and user dashboards fully functional
- **Header Navigation**: Logo links to home, unified user menu
- **Project Organization**: Clean, organized file structure
- **CSS Styling**: Professional appearance with Islamic aesthetics
- **Responsive Design**: Works on all screen sizes

### **🎯 Working Systems**
- **Main Page**: Beautiful hero section with Islamic design
- **Admin Dashboard**: Full system management interface
- **User Dashboard**: Personalized user experience
- **Navigation**: Consistent header across all pages
- **User Authentication**: Working login/logout system
- **Skin Management**: Unified visual system

## 📄 **License**

This project is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

---

**Last Updated**: 2025-08-21  
**Version**: 0.0.2.0  
**Author**: IslamWiki Development Team  
**Status**: Production Ready with Unified UI & Clean Organization 🚀

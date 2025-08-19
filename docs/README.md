# IslamWiki Documentation

**Version**: 0.0.1.0  
**Last Updated**: 2025-08-19  
**Author**: IslamWiki Development Team  
**License**: AGPL-3.0

Welcome to the comprehensive documentation for the Islam Wiki project. This documentation covers all aspects of the application architecture, components, and development guidelines.

### ✅ **Latest Updates (v0.0.1.0)**
- **🎯 Documentation Restructuring**: ✅ Complete documentation restructuring with comprehensive README files
- **🏗️ Architecture Corrections**: ✅ Fixed major inconsistencies in system layer organization
- **📁 File Naming Standardization**: ✅ Moved from inconsistent UPPERCASE to consistent lowercase
- **🔌 Extension Versioning Independence**: ✅ New system for extension management
- **🚀 Modern Development Practices**: ✅ Enhanced documentation with modern PHP practices
- **📚 Comprehensive Structure**: ✅ Every directory now has comprehensive README files
- **🔗 Link Integrity**: ✅ All internal links fixed and working properly

### 📋 **Development Status**
- **Database Connection Strategy**: ✅ **COMPLETED** (0.0.0.11) - Separate connections recommended
- **Islamic Database Implementation**: ✅ **COMPLETED** (0.0.0.12) - 39 tables across 4 databases
- **Islamic Authentication**: ✅ **COMPLETED** (0.0.0.12) - Enhanced authentication with scholar verification
- **Islamic Content Management**: ✅ **COMPLETED** (0.0.0.12) - Complete content creation and moderation
- **Quran Integration**: ✅ **COMPLETED** (0.0.0.13) - Complete Quran verse management system
- **Hadith Integration**: ✅ **COMPLETED** (0.0.0.14) - Complete Hadith management system
- **Islamic Calendar Integration**: ✅ **COMPLETED** (0.0.0.15) - Islamic calendar with event management
- **Salah Times Integration**: ✅ **COMPLETED** (0.0.0.16) - Complete salah time system with astronomical algorithms
- **Search & Discovery**: ✅ **COMPLETED** (0.0.0.17) - Comprehensive search across all Islamic content types
- **Configuration System**: ✅ **COMPLETED** (0.0.0.18) - Hybrid configuration system with MediaWiki-inspired structure
- **Documentation Structure**: ✅ Root folder for essential docs, docs/ for specialized content
- **Islamic Core Organization**: ✅ Nested within app/Core/Islamic/ (Option B)
- **Language Files**: ✅ Laravel-style resources/lang instead of MediaWiki i18n
- **Extensions Permissions**: ✅ Per-extension basis permissions
- **API Versioning**: ✅ Separate versioning for all APIs
- **Enhanced Configuration System**: ✅ **COMPLETED** (0.0.0.24) - Advanced configuration management with CLI tools, visual builder, and enhanced API
- **Comprehensive Routing System**: ✅ **COMPLETED** (0.0.0.25) - Complete routing system for all features and API endpoints
- **View Templates Implementation**: ✅ **COMPLETED** (0.0.0.26) - Complete Twig template system for all routes and features
- **Database Integration & Authentication**: ✅ **COMPLETED** (0.0.0.27) - Complete database integration and authentication systems
- **Code Quality & Error Resolution**: ✅ **COMPLETED** (0.0.0.38) - Achieved 100% error-free codebase with comprehensive syntax validation
- **DashboardExtension System**: ✅ **COMPLETED** (0.0.0.61) - Complete role-based dashboard system with personalized user experiences
- **Documentation Restructuring**: ✅ **COMPLETED** (0.0.1.0) - Complete documentation restructuring with comprehensive structure

### 🚧 **Next Phase (0.0.1.1)**
- **Site Restructuring**: Begin major site architecture restructuring
- **Core Systems Implementation**: Implement new architectural foundation
- **Database Restructuring**: Update database structure to match new architecture
- **Extension System Updates**: Modernize extension system architecture

## 📚 Documentation Structure

### Core Documentation
- **[Architecture Overview](architecture/overview.md)** - System architecture and design patterns
- **[Components](components/README.md)** - Core application components
- **[Controllers](controllers/README.md)** - Controller documentation and patterns
- **[Models](models/README.md)** - Data models and database structure
- **[Views](views/README.md)** - Template system and view rendering
- **[Layouts](layouts/README.md)** - Layout system and dashboard interface

### Development Guides
- **[Style Guide](guides/style-guide.md)** - Coding standards and conventions
- **[Islamic Naming Conventions](guides/islamic-naming-conventions.md)** - Complete Islamic naming system for all components
- **[Islamic Terminology Standards](guides/islamic-terminology-standards.md)** - Standard Islamic terms (salah, Quran, Hadith, etc.)
- **[Versioning Strategy](guides/versioning.md)** - Semantic versioning and release process
- **[Security Guidelines](security/README.md)** - Security best practices
- **[Testing Guidelines](testing/README.md)** - Testing strategies and procedures

### Feature Documentation
- **[Feature Summaries](features/README.md)** - Detailed feature implementation summaries
- **[Extension System](extensions/README.md)** - Extension system and development guide

### **Release Documentation**
- **[Release Notes](releases/README.md)** - All version release notes and changelog
- **[Changelog](releases/CHANGELOG.md)** - Complete version history and changes

## 🏗️ Project Organization

### Directory Structure
```
📁 docs/                    # Documentation
├── 📁 plans/              # Development plans and roadmaps
├── 📁 guides/             # User and developer guides
├── 📁 architecture/       # System architecture docs
├── 📁 components/         # Component documentation
├── 📁 security/           # Security documentation
├── 📁 features/           # Feature documentation and summaries
├── 📁 troubleshooting/    # Issue resolution and troubleshooting
├── 📁 deployment/         # Deployment guides
├── 📁 testing/            # Testing documentation
├── 📁 controllers/        # Controller documentation
├── 📁 models/             # Model documentation
├── 📁 views/              # View documentation
├── 📁 layouts/            # Layout documentation
├── 📁 extensions/         # Extension documentation
├── 📁 skins/              # Skin documentation
├── 📁 releases/           # Release documentation
├── 📁 api/                # API documentation
└── 📁 standards/          # Development standards

📁 scripts/                # Utility scripts
├── 📁 database/           # Database migrations and setup
├── 📁 debug/              # Debug and troubleshooting tools
├── 📁 tests/              # Test scripts
└── 📁 utils/              # Utility and maintenance scripts

📁 maintenance/tests/      # Consolidated tests
├── 📁 cli/                # CLI test runners
├── 📁 Unit/               # Unit tests
│   └── 📁 Database/       # Database unit tests
├── 📁 Integration/        # Integration tests
└── 📁 web/                # Web-style test scripts

📁 public/                 # Web root (minimal)
├── index.php              # Main application entry point
├── .htaccess              # Apache configuration
└── (other web-accessible files)

📁 src/                    # Application source code
├── 📁 Core/               # Core framework components
├── 📁 Http/               # HTTP layer (controllers, middleware)
├── 📁 Models/             # Data models
├── 📁 Providers/          # Service providers
└── 📁 resources/          # Application resources
```

### Key Organizational Changes

#### **Documentation Organization**
- **Plans**: All development plans moved to `docs/plans/`
- **Guides**: Comprehensive guides in `docs/guides/`
- **Architecture**: System design docs in `docs/architecture/`
- **Components**: Component-specific docs in `docs/components/`

#### **Script Organization**
- **Database Scripts**: Migration and setup scripts in `scripts/database/`
- **Debug Tools**: Troubleshooting scripts in `scripts/debug/`
- **Test Scripts**: Test utilities in `scripts/tests/`
- **Utilities**: Maintenance scripts in `scripts/utils/`

#### **Test Organization**
- **Unit Tests**: PHPUnit tests in `maintenance/tests/Unit/`
- **Web Tests**: Browser-style test scripts in `maintenance/tests/web/`
- **Integration Tests**: End-to-end tests in `maintenance/tests/Integration/`

#### **Clean Public Directory**
- **Minimal Web Root**: Only essential web-accessible files
- **Security**: Reduced attack surface by moving test files
- **Performance**: Faster directory scanning

## 🚀 Quick Start

### **Getting Started**
1. **Setup**: Follow the [Installation Guide](guides/installation.md)
2. **Development**: Review the [Style Guide](guides/style-guide.md)
3. **Testing**: Use the [Testing Guidelines](testing/README.md)
4. **Deployment**: Follow the [Deployment Guide](deployment/README.md)

## 🔧 Development Workflow

### Code Organization
- **Controllers**: HTTP request handling in `src/Http/Controllers/`
- **Models**: Data layer in `src/Models/`
- **Views**: Templates in `resources/views/`
- **Middleware**: Request processing in `src/Http/Middleware/`

### Testing Strategy
- **Unit Tests**: `maintenance/tests/Unit/` for isolated component testing
- **Web Tests**: `maintenance/tests/web/` for browser-style testing
- **Integration Tests**: End-to-end testing in `maintenance/tests/Integration/`

### Scripts and Utilities
- **Database**: Migration and setup scripts in `scripts/database/`
- **Debug**: Troubleshooting tools in `scripts/debug/`
- **Maintenance**: Utility scripts in `scripts/utils/`

## 📖 Documentation Standards

### File Naming
- Use descriptive names with hyphens: `feature-name.md`
- Group related files in appropriate directories
- Maintain consistent naming across documentation

### Content Structure
- Start with overview and purpose
- Include code examples and usage
- Provide troubleshooting sections
- Link to related documentation

### Version Control
- Keep documentation in sync with code changes
- Update version numbers and changelogs
- Maintain backward compatibility notes

## 🔗 Related Documentation

- **[Main README](../README.md)** - Project overview and quick start
- **[Release Notes](releases/README.md)** - Release documentation and changelog
- **[License](LICENSE.md)** - Project licensing information
- **[Versioning Guide](guides/versioning.md)** - Semantic versioning strategy

---

*Last updated: v0.0.8 - 2025-08-19*

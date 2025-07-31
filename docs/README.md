# IslamWiki Documentation

**Version**: 0.0.16  
**Status**: Development Phase - Prayer Times Integration Complete  
**Last Updated**: 2025-07-30

Welcome to the comprehensive documentation for the Islam Wiki project. This documentation covers all aspects of the application architecture, components, and development guidelines.

### ✅ **Latest Updates**
- **Prayer Times Integration System**: ✅ Complete prayer time system with astronomical algorithms
- **PrayerTime Model**: Full model for prayer time calculations with 7 calculation methods
- **PrayerTimeController**: Complete web and API controller for prayer time functionality
- **Advanced Prayer Calculations**: Astronomical algorithms for accurate prayer times worldwide
- **Multiple Calculation Methods**: Support for MWL, ISNA, EGYPT, MAKKAH, KARACHI, TEHRAN, JAFARI
- **User Location Management**: Save and manage multiple locations with preferences
- **Prayer Time Widgets**: Embeddable widgets for external websites with customization
- **Qibla Direction**: Calculate Qibla direction for any location worldwide
- **Next Prayer Detection**: Automatic detection of the next prayer time
- **Multi-language Support**: English, Arabic, Urdu, Turkish prayer names

### 📋 **Development Status**
- **Database Connection Strategy**: ✅ **COMPLETED** (0.0.11) - Separate connections recommended
- **Islamic Database Implementation**: ✅ **COMPLETED** (0.0.12) - 39 tables across 4 databases
- **Islamic Authentication**: ✅ **COMPLETED** (0.0.12) - Enhanced authentication with scholar verification
- **Islamic Content Management**: ✅ **COMPLETED** (0.0.12) - Complete content creation and moderation
- **Quran Integration**: ✅ **COMPLETED** (0.0.13) - Complete Quran verse management system
- **Hadith Integration**: ✅ **COMPLETED** (0.0.14) - Complete Hadith management system
- **Islamic Calendar Integration**: ✅ **COMPLETED** (0.0.15) - Islamic calendar with event management
- **Prayer Times Integration**: ✅ **COMPLETED** (0.0.16) - Complete prayer time system with astronomical algorithms
- **Documentation Structure**: ✅ Root folder for essential docs, docs/ for specialized content
- **Islamic Core Organization**: ✅ Nested within app/Core/Islamic/ (Option B)
- **Language Files**: ✅ Laravel-style resources/lang instead of MediaWiki i18n
- **Extensions Permissions**: ✅ Per-extension basis permissions
- **API Versioning**: ✅ Separate versioning for all APIs
- **Advanced Features**: 🔄 **Next Implementation** (0.0.17) - Advanced Islamic features and community integration

### 🚧 **Next Phase**
- **Advanced Islamic Features**: Advanced features and community integration (0.0.17)
- **Prayer Time Notifications**: Push notifications for prayer times
- **Audio Adhan**: Audio prayer call integration
- **Mobile App**: Native mobile application development
- **Community Features**: User communities and social features

## 📚 Documentation Structure

### Core Documentation
- **[Architecture Overview](architecture/overview.md)** - System architecture and design patterns
- **[Components](components/README.md)** - Core application components
- **[Controllers](controllers/README.md)** - Controller documentation and patterns
- **[Models](models/README.md)** - Data models and database structure
- **[Views](views/README.md)** - Template system and view rendering

### Development Guides
- **[Style Guide](guides/style-guide.md)** - Coding standards and conventions
- **[Versioning Strategy](guides/versioning.md)** - Semantic versioning and release process
- **[Security Guidelines](security/README.md)** - Security best practices
- **[Testing Guidelines](testing/README.md)** - Testing strategies and procedures

### Feature Documentation
- **[Wiki Pages](features/wiki-pages.md)** - Wiki page system documentation
- **[Authentication](features/auth.md)** - User authentication and authorization
- **[Content Rendering](features/content-rendering.md)** - Content processing and display

### Release Documentation
- **[Release Notes](releases/README.md)** - All version release notes and changelog
- **[Version 0.0.11](releases/RELEASE-NOTES-0.0.11)** - Database Connection Strategy Research
- **[Version 0.0.10](releases/RELEASE-NOTES-0.0.10)** - MediaWiki-Inspired Root Structure

### Deployment & Operations
- **[Deployment Guide](deployment/README.md)** - Production deployment instructions
- **[Database Setup](DATABASE_SETUP.md)** - Database configuration and setup
- **[Session Management](security/session-management.md)** - Session handling and security

### Development Resources
- **[Development Plans](plans/)** - Project roadmaps and planning documents
- **[Initial Project Prompt](Cursor_initial-prompt.md)** - Original project requirements

## 🏗️ Project Organization

### Directory Structure
```
📁 docs/                    # Documentation
├── 📁 plans/              # Development plans and roadmaps
├── 📁 guides/             # User and developer guides
├── 📁 architecture/       # System architecture docs
├── 📁 components/         # Component documentation
├── 📁 security/           # Security documentation
├── 📁 features/           # Feature documentation
├── 📁 deployment/         # Deployment guides
├── 📁 testing/            # Testing documentation
├── 📁 controllers/        # Controller documentation
├── 📁 models/             # Model documentation
├── 📁 views/              # View documentation
├── DATABASE_SETUP.md      # Database setup guide
└── Cursor_initial-prompt.md # Initial project prompt

📁 scripts/                # Utility scripts
├── 📁 database/           # Database migrations and setup
├── 📁 debug/              # Debug and troubleshooting tools
├── 📁 tests/              # Test scripts
└── 📁 utils/              # Utility and maintenance scripts

📁 tests/                  # Test files
├── 📁 Unit/               # Unit tests
│   └── 📁 Database/       # Database unit tests
└── 📁 web/                # Web-based tests

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
- **Unit Tests**: PHPUnit tests in `tests/Unit/`
- **Web Tests**: Browser-based tests in `tests/web/`
- **Integration Tests**: End-to-end tests in `tests/`

#### **Clean Public Directory**
- **Minimal Web Root**: Only essential web-accessible files
- **Security**: Reduced attack surface by moving test files
- **Performance**: Faster directory scanning

## 🚀 Quick Start

1. **Setup**: Follow the [Database Setup](DATABASE_SETUP.md) guide
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
- **Unit Tests**: `tests/Unit/` for isolated component testing
- **Web Tests**: `tests/web/` for browser-based testing
- **Integration Tests**: End-to-end testing in `tests/`

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
- **[CHANGELOG](../CHANGELOG.md)** - Version history and changes
- **[LICENSE](../LICENSE.md)** - Project licensing information
- **[Versioning Guide](guides/versioning.md)** - Semantic versioning strategy

---

*Last updated: v0.0.8 - July 30, 2025*

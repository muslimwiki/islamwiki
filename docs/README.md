# Islam Wiki Documentation

## Current Version: v0.1.2

Welcome to the comprehensive documentation for the Islam Wiki project. This documentation covers all aspects of the application architecture, components, and development guidelines.

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

*Last updated: v0.1.2 - July 30, 2025*

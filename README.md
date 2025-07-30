# IslamWiki

**Version**: 0.0.12  
**Status**: Development Phase - Islamic Database, Authentication & Content Management Complete  
**License**: GNU AGPL v3.0

A modern, secure wiki system built with PHP 8.1+, featuring comprehensive content management, user authentication, and enterprise-level security.

## Version 0.0.12

**What's New:**
- **Islamic Database Implementation**: ✅ Complete implementation of separate database connections for Islamic content
- **Quran Database Schema**: 13 tables for Quran verses, translations, recitations, and tafsir
- **Hadith Database Schema**: 13 tables for Hadith collections, narrators, chains, and commentaries
- **Scholar Database Schema**: 13 tables for scholar verification, credentials, works, and relationships
- **Islamic User Model**: Enhanced user model with Islamic community features and scholar verification
- **Islamic Authentication**: Enhanced authentication with role-based permissions for Islamic content
- **Scholar Verification System**: Complete workflow for verifying Islamic scholars with approval/rejection
- **Islamic Permissions**: 5 Islamic roles (user, moderator, scholar, verified_scholar, admin) with specific permissions
- **Islamic Content Management**: Enhanced content creation with Islamic categorization and templates
- **Content Moderation System**: Complete workflow for approving, rejecting, and requesting revisions
- **Scholar Verification Workflow**: Content verification by Islamic scholars
- **Islamic Content Templates**: 10 specialized templates for different Islamic content types
- **Content Quality Scoring**: Quality assessment system for Islamic content
- **Islamic References & Citations**: Structured reference and citation system
- **Arabic Content Support**: Full Arabic title and content support

**Technical Achievements:**
- **39 Database Tables**: Complete Islamic database infrastructure across 4 separate databases
- **Sub-100ms Performance**: All database connections optimized for fast response times
- **Security Isolation**: Each Islamic content type properly isolated in separate databases
- **Role-Based Access**: Comprehensive permission system for Islamic content management
- **Scholar Verification**: Complete workflow for verifying Islamic scholars and credentials
- **Content Moderation**: Complete approval, rejection, and revision workflow
- **Quality Control**: Content quality scoring and assessment system
- **Arabic Support**: Full Arabic title and content support with proper encoding

## Version 0.0.10

**What's New:**
- **Pure IslamRouter**: Completely removed FastRoute dependency, implemented custom routing solution
- **Project Organization**: Comprehensive reorganization for better maintainability and security
- **Documentation Structure**: All documentation moved to `docs/` with clear categorization
- **Script Organization**: Scripts categorized by purpose (database, debug, tests, utils)
- **Clean Public Directory**: Removed test files from web root for enhanced security
- **Test Organization**: Web tests in `tests/web/`, unit tests in `tests/Unit/`
- **Comprehensive Testing**: ✅ All router features verified and working correctly
- **MediaWiki Structure Planning**: ✅ Complete planning for MediaWiki-inspired structure

### Core Components
- **Application Bootstrap**: Main application entry point and configuration
- **Service Providers**: Modular service registration and management
- **Dependency Injection**: Container-based service management
- **Routing System**: Custom IslamRouter with middleware support ✅ **Fully Tested**
- **Template Engine**: Twig templating with layout inheritance
- **Database Layer**: PDO-based database abstraction with migrations
- **Security System**: Enterprise-level security with CSRF protection

### Key Features
- **Wiki Page System** (v0.0.3): Complete CRUD operations for wiki pages
- **Content Rendering** (v0.0.4): Comprehensive markdown support with syntax highlighting
- **Pages Index** (v0.0.5): Advanced page browsing and management interface
- **Enterprise Security** (v0.0.6): Comprehensive security middleware implementation
- **User Authentication** (v0.0.2): Registration, login, session management
- **Database Foundation** (v0.0.2): Migration system and database setup
- **Pure IslamRouter** (v0.0.8): ✅ **Custom routing solution fully tested and verified**
- **Database Connection Strategy** (v0.0.11): ✅ **Research completed, separate connections recommended**
- **Islamic Database Implementation** (v0.0.12): ✅ **Complete implementation with 39 tables across 4 databases**
- **Islamic Authentication** (v0.0.12): ✅ **Enhanced authentication with scholar verification**
- **Islamic Content Management** (v0.0.12): ✅ **Complete content creation, editing, and moderation system**
- **IslamWiki Structure** (v0.1.0): 🚧 **Planning complete, implementation next**

### Research Status
- **Database Connection Strategy**: ✅ **COMPLETED** (0.0.11) - Separate connections recommended
- **Islamic Database Implementation**: ✅ **COMPLETED** (0.0.12) - 39 tables across 4 databases implemented
- **Islamic Authentication**: ✅ **COMPLETED** (0.0.12) - Enhanced authentication with scholar verification
- **Documentation Structure**: ✅ Root folder for essential docs, docs/ for specialized content
- **Islamic Core Organization**: ✅ Nested within app/Core/Islamic/ (Option B)
- **Language Files**: ✅ Laravel-style resources/lang instead of MediaWiki i18n
- **Extensions Permissions**: ✅ Per-extension basis permissions
- **API Versioning**: ✅ Separate versioning for all APIs
- **Islamic Content Management**: ✅ **COMPLETED** (0.0.12) - Complete content creation, editing, and moderation system
- **Islamic Features**: 🔄 **Next Implementation** (0.0.12 Phase 4) - Quran, Hadith, and Islamic calendar integration
- **Configuration System**: 🔄 **Planned Research** (0.0.13) - Hybrid LocalSettings.php + IslamSettings.php approach
- **API System**: 🔄 **Planned Research** (0.0.14) - Hybrid api.php + specific API files approach

## 🚀 Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/islam-wiki.git
   cd islam-wiki
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Setup database**
   ```bash
   php scripts/database/setup_database.php
   php scripts/database/migrate.php
   php scripts/database/create_sample_data.php
   ```

5. **Start development server**
   ```bash
   php -S localhost:8000 -t public/
   ```

6. **Visit the application**
   Open http://localhost:8000 in your browser

## 🏗️ Architecture

### Core Components
- **Application Bootstrap**: Main application entry point and configuration
- **Service Providers**: Modular service registration and management
- **Dependency Injection**: Container-based service management
- **Routing System**: Custom IslamRouter with middleware support
- **Template Engine**: Twig templating with layout inheritance
- **Database Layer**: PDO-based database abstraction with migrations
- **Security System**: Enterprise-level security with CSRF protection

### Key Features
- **Wiki Page System** (v0.0.3): Complete CRUD operations for wiki pages
- **Content Rendering** (v0.0.4): Comprehensive markdown support with syntax highlighting
- **Pages Index** (v0.0.5): Advanced page browsing and management interface
- **Enterprise Security** (v0.0.6): Comprehensive security middleware implementation
- **User Authentication** (v0.0.2): Registration, login, session management
- **Database Foundation** (v0.0.2): Migration system and database setup

## 📁 Project Organization

### Directory Structure
```
📁 docs/                    # Comprehensive documentation
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

📁 scripts/                # Utility scripts (organized by purpose)
├── 📁 database/           # Database migrations and setup
├── 📁 debug/              # Debug and troubleshooting tools
├── 📁 tests/              # Test utilities
└── 📁 utils/              # Utility and maintenance scripts

📁 tests/                  # Test files
├── 📁 Unit/               # Unit tests
│   └── 📁 Database/       # Database unit tests
└── 📁 web/                # Web-based tests

📁 public/                 # Web root (minimal, secure)
├── index.php              # Main application entry point
├── .htaccess              # Apache configuration
└── (essential web files only)

📁 src/                    # Application source code
├── 📁 Core/               # Core framework components
├── 📁 Http/               # HTTP layer (controllers, middleware)
├── 📁 Models/             # Data models
├── 📁 Providers/          # Service providers
└── 📁 resources/          # Application resources
```

### Key Organizational Benefits
- **Security**: Reduced web-accessible files, test files moved to protected directories
- **Performance**: Cleaner directory structure for faster scanning
- **Maintainability**: Logical grouping of related files and functionality
- **Developer Experience**: Clear organization for easier navigation and discovery

## 🔧 Development

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

## 🛡️ Security Features

### Enterprise Security (v0.0.6)
- **CSRF Protection**: Cross-site request forgery protection on all forms
- **Security Headers**: Enhanced HTTP security headers
- **Input Validation**: Request sanitization and validation
- **Session Security**: Secure session management and cookie handling
- **Rate Limiting**: Protection against abuse and attacks
- **XSS Protection**: Comprehensive cross-site scripting prevention

### Authentication System (v0.0.2)
- **User Registration**: Secure user account creation
- **Login System**: Session-based authentication
- **Password Security**: Secure password hashing and verification
- **Remember Me**: Persistent login functionality
- **Session Management**: Secure HTTP-only cookies

## 📚 Documentation

### Comprehensive Documentation Structure
- **[Architecture Overview](docs/architecture/overview.md)** - System architecture and design patterns
- **[Components](docs/components/README.md)** - Core application components
- **[Controllers](docs/controllers/README.md)** - Controller documentation and patterns
- **[Models](docs/models/README.md)** - Data models and database structure
- **[Views](docs/views/README.md)** - Template system and view rendering

### Development Guides
- **[Style Guide](docs/guides/style-guide.md)** - Coding standards and conventions
- **[Versioning Strategy](docs/guides/versioning.md)** - Semantic versioning and release process
- **[Organization Guide](docs/guides/organization.md)** - Project structure and organization
- **[Security Guidelines](docs/security/README.md)** - Security best practices
- **[Testing Guidelines](docs/testing/README.md)** - Testing strategies and procedures

### Feature Documentation
- **[Wiki Pages](docs/features/wiki-pages.md)** - Wiki page system documentation
- **[Authentication](docs/features/auth.md)** - User authentication and authorization
- **[Content Rendering](docs/features/content-rendering.md)** - Content processing and display

## 🔄 Versioning Strategy

This project follows [Semantic Versioning](https://semver.org/) with a structured development approach:

### Development Stages
- **0.0.x (Core Infrastructure)**: Basic framework, routing, database, authentication
- **0.1.x (Wiki Features)**: Wiki page system, content rendering, user management
- **0.2.x (Advanced Features)**: Search, media, advanced content features
- **0.3.x (Integration Features)**: API, external integrations, advanced functionality
- **1.x.x (Production Ready)**: Fully functional, production-ready application

### Current Status
- **Current Version**: 0.0.8
- **Stage**: Core Infrastructure (0.0.x)
- **Focus**: Framework stability, routing, database, authentication
- **Next Milestone**: Wiki Features (0.1.x) - Wiki page system, content rendering

## 🚀 Deployment

### Production Setup
1. **Environment Configuration**: Set production environment variables
2. **Database Setup**: Run migrations and create production database
3. **Web Server Configuration**: Configure Apache/Nginx for the application
4. **Security Hardening**: Enable HTTPS, configure security headers
5. **Performance Optimization**: Enable caching, optimize database queries

### Development Setup
1. **Local Environment**: Use PHP built-in server for development
2. **Database**: Local MySQL/MariaDB instance
3. **Debug Mode**: Enable debug mode for development
4. **Logging**: File-based logging for debugging

## 🤝 Contributing

### Development Workflow
1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Make your changes**: Follow the style guide and testing guidelines
4. **Test your changes**: Run tests and ensure functionality
5. **Submit a pull request**: Include detailed description of changes

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for any changes
- Follow semantic versioning for releases

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE](LICENSE.md) file for details.

## 🆘 Support

### Getting Help
- **Documentation**: Comprehensive guides in `docs/` directory
- **Issues**: Create an issue on GitHub for bugs or feature requests
- **Discussions**: Use GitHub Discussions for questions and ideas
- **Security**: Report security issues privately to the maintainers

### Development Resources
- **[Changelog](CHANGELOG.md)** - Complete version history and changes
- **[Release Notes](docs/releases/README.md)** - All version release notes and changelog
- **[Database Setup](docs/DATABASE_SETUP.md)** - Database configuration guide
- **[Testing Guidelines](docs/testing/README.md)** - Testing strategies and procedures
- **[Security Guidelines](docs/security/README.md)** - Security best practices

---

**Built with ❤️ for the Islamic community**



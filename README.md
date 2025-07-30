# IslamWiki

A modern, custom wiki system combining the power of MediaWiki's functionality with a WordPress-like dashboard experience, built with PHP, Twig templating, and Alpine.js for lightweight interactivity.

[![AGPL-3.0 License](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

## Version 0.1.2 (2025-07-30)

### ✨ What's New
- **Environment Variable Fixes**: Resolved all "Undefined array key 'APP_ENV'" warnings
- **Application Stability**: Eliminated 500 errors and application crashes
- **Robust Environment Handling**: Enhanced environment variable access across all components
- **Enterprise Security**: Comprehensive security middleware with attack prevention
- **Professional Error Handling**: Enhanced error management with debug information
- **Enhanced Logging**: PSR-3 compliant logging with structured data
- **CSRF Protection**: Cross-site request forgery protection with token validation
- **Rate Limiting**: Configurable request rate limiting to prevent abuse
- **Security Headers**: Comprehensive security headers (CSP, XSS-Protection, etc.)
- **Input Validation**: Sanitization and validation of all user input
- **SQL Injection Protection**: Detection and blocking of SQL injection attempts
- **XSS Protection**: Prevention of cross-site scripting attacks
- **Directory Traversal Protection**: Blocking of path traversal attempts

### 🚀 Key Features

#### 🔒 **Enterprise Security (v0.0.6)**
- **Multi-layered Security Protection**
  - Rate limiting (60 requests/minute, 10 burst/second)
  - Input validation and sanitization
  - SQL injection detection and prevention
  - XSS protection with pattern detection
  - Directory traversal protection
  - Comprehensive security headers (CSP, XSS-Protection, etc.)
- **CSRF Protection**: Token-based protection for all state-changing requests
- **Professional Error Handling**: Robust error management with debug information
- **Enhanced Logging**: PSR-3 compliant system with structured data

#### 📝 **Wiki Page System (v0.1.0+)**
- **Complete Page Management**: Create, view, edit, and delete wiki pages
- **Page Model**: Eloquent-like model with relationships and revision tracking
- **Page Controller**: Full CRUD operations with proper templates
- **View Count Tracking**: Page view analytics with database updates
- **Page Permissions**: Edit, delete, and lock permissions based on user roles
- **Page History**: Revision tracking and history viewing functionality
- **Pages Index**: Complete "View All Pages" functionality with search and filtering
- **Professional Layout**: Grid-based page cards with metadata and actions

#### 🎨 **Content Rendering (v0.1.1+)**
- **Enhanced Markdown Support**: Headers, bold, italic, lists, links, blockquotes, code blocks
- **Syntax Highlighting**: Prism.js integration for beautiful code display
- **Professional Styling**: Enhanced CSS for all rendered content
- **Auto-linking**: Smart URL detection and markdown-style link support
- **Code Blocks**: Language-specific syntax highlighting with proper formatting

#### 🔐 **Authentication & Security (v0.0.2+)**
- **Session Management**: Secure session handling with HTTP-only, SameSite cookies
- **User Authentication**: Registration, login, logout with password hashing
- **Remember Me**: Secure persistent login functionality
- **Authentication Middleware**: Route protection based on user roles
- **Database Foundation**: Complete migration system with proper schema management

#### 🛠️ **Development & Infrastructure**
- **Dynamic Homepage**: Database-driven content with recent pages display
- **Development Tools**: Setup scripts, tests, and comprehensive documentation
- **PSR-7 Compatible**: Standard HTTP request/response handling
- **Dependency Injection**: Clean, testable code architecture
- **Comprehensive Testing**: Unit tests and integration tests

---

## Versioning Strategy

IslamWiki follows **Semantic Versioning** with a logical progression:

### 🔧 **Core Infrastructure (0.0.x)**
- **0.0.1**: Foundation, routing, templating, basic error handling
- **0.0.2**: Authentication system, session management, CSRF protection
- **0.0.6**: Security middleware, error handling, logging
- **0.0.7**: Environment variable fixes, application stability

### 📝 **Wiki Features (0.1.x)**
- **0.1.0**: Wiki Page System (CRUD operations, permissions, history)
- **0.1.1**: Content Rendering (markdown, syntax highlighting, styling)
- **0.1.2**: Pages Index & Browsing (search, filter, pagination)

### 🚀 **Future Progression**
- **0.2.x**: Major features (Quran integration, Hijri calendar, etc.)
- **0.3.x**: Additional major features
- **1.0.0**: Production-ready, feature-complete site

This approach ensures clear separation between core infrastructure, wiki features, and major additions.

---

## Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Web server (Apache/Nginx)
- MySQL/MariaDB 5.7+ or PostgreSQL 10+

### Installation
```bash
# Clone the repository
git clone https://github.com/yourusername/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env with your database configuration

# Set up database
php scripts/setup_database.php

# Set permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/

# Run tests to verify installation
php tests/Unit/Database/IntegrationTest.php

# Test security features
php scripts/test_security_error_handling.php

## Security Features

IslamWiki includes enterprise-level security features to protect against common web vulnerabilities:

### 🔒 Attack Prevention
- **SQL Injection**: Detects and blocks patterns like `union+select`, `drop+table`, `delete+from`
- **XSS Protection**: Prevents script tags, javascript: protocols, and event handlers
- **Directory Traversal**: Blocks `..` and `//` patterns in URLs
- **CSRF Protection**: Token-based protection for all state-changing requests
- **Rate Limiting**: Prevents abuse with configurable request limits

### 🛡️ Security Headers
- **Content Security Policy**: Restricts resource loading to trusted sources
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-XSS-Protection**: Additional XSS protection layer
- **Referrer-Policy**: Controls referrer information
- **Permissions-Policy**: Restricts browser features
- **Strict-Transport-Security**: Enforces HTTPS connections

### 📊 Monitoring & Logging
- **Structured Logging**: PSR-3 compliant with rich context
- **Security Events**: Automatic logging of suspicious activities
- **Performance Tracking**: Request timing and memory usage
- **Error Handling**: Comprehensive exception management
- **Log Rotation**: Automatic log file management

### 🧪 Testing
Run the security test suite to verify all protections:
```bash
php scripts/test_security_error_handling.php
```

This will test:
- SQL injection detection
- XSS pattern blocking
- CSRF token validation
- Rate limiting functionality
- Error handling capabilities
- Logging system operation

# Access the application
# Point your web server to the public/ directory
```

### Development
```bash
# Start development server
php -S localhost:8000 -t public/

# Access the application
open http://localhost:8000
```

---

## Architecture

### Core Components
- **Application**: Main application bootstrap and service container
- **FastRouter**: High-performance routing with dependency injection
- **TwigRenderer**: Server-side template rendering with caching
- **Container**: Dependency injection container for service management
- **Controllers**: Request handling with proper dependency injection

### Frontend
- **Alpine.js**: Lightweight JavaScript framework for interactivity
- **Twig Templates**: Server-side rendering with layouts and inheritance
- **Responsive CSS**: Modern styling with component-based architecture

### Backend
- **PSR-7 HTTP**: Standard HTTP request/response handling
- **Session Management**: Secure session handling with regeneration
- **CSRF Protection**: Token-based form protection
- **Database ORM**: Custom model system with relationships and validation
- **Migration System**: Database schema management with version control
- **Authentication System**: Secure user authentication with password hashing
- **Service Providers**: Modular service registration system
- **File Logging**: Comprehensive error tracking and debugging
- **Error Handling**: Detailed error pages and exception handling

---

## Project Structure

```
islamwiki/
├── public/                 # Web server document root
│   └── index.php          # Application entry point
├── src/                   # Application source code
│   ├── Core/             # Core framework components
│   ├── Http/             # HTTP layer (controllers, middleware)
│   ├── Providers/        # Service providers
│   └── Models/           # Data models
├── database/             # Database migrations and seeders
│   └── migrations/       # Database migration files
├── resources/            # Application resources
│   └── views/           # Twig templates
├── routes/              # Route definitions
├── storage/             # Application storage
│   ├── logs/           # Log files
│   └── framework/      # Framework cache
├── docs/               # Documentation
├── tests/              # Test files
├── scripts/            # Utility scripts
└── config/             # Configuration files
```

---

## Technology Stack

### Backend
- **PHP 8.1+**: Modern PHP with strict typing and PSR standards
- **FastRoute**: High-performance routing with middleware support
- **Twig**: Server-side templating engine with layouts and inheritance
- **PSR-7**: HTTP message interfaces for request/response handling
- **PSR-3**: Logging interface for structured logging
- **Composer**: Dependency management and autoloading

### Security & Error Handling
- **SecurityMiddleware**: Enterprise-level attack prevention
- **ErrorHandlingMiddleware**: Professional error management
- **MiddlewareStack**: Organized middleware execution
- **CSRF Protection**: Token-based form protection
- **Rate Limiting**: Configurable request limiting

### Frontend
- **Alpine.js**: Lightweight JavaScript framework for interactivity
- **Modern CSS**: Responsive design with flexbox/grid
- **Progressive Enhancement**: Works without JavaScript
- **Prism.js**: Syntax highlighting for code blocks

### Development & Infrastructure
- **Dependency Injection**: Clean, testable code architecture
- **Service Providers**: Modular service registration
- **Database Migrations**: Schema version control
- **Comprehensive Testing**: Unit and integration tests
- **Structured Logging**: PSR-3 compliant with context

---

## Features

### ✅ **Implemented (v0.2.3)**
- **Enterprise Security**: Multi-layered attack prevention
  - SQL injection, XSS, and directory traversal protection
  - Rate limiting and input validation
  - Comprehensive security headers
  - CSRF protection with token validation
- **Professional Error Handling**: Robust error management
  - Exception catching and logging
  - Debug information in development
  - User-friendly error pages
  - Performance monitoring
- **Enhanced Logging**: PSR-3 compliant system
  - Structured logging with context
  - Specialized methods (security, performance, user actions)
  - Log rotation with configurable limits
- **Wiki Page System**: Complete page management
  - Create, view, edit, and delete wiki pages
  - Page revision tracking and history
  - View count analytics
  - Page permissions and locking
  - Pages index with search and filtering
- **Content Rendering**: Enhanced markdown support
  - Full markdown syntax support
  - Syntax highlighting with Prism.js
  - Professional styling and auto-linking
- **Authentication System**: Secure user management
  - Registration, login, logout
  - Password hashing and verification
  - Session management with secure cookies
  - Remember me functionality
- **Database Foundation**: Complete migration system
  - Database schema management
  - Migration version control
  - Seeding and testing support
- **Development Infrastructure**
  - PSR-7 compatible HTTP handling
  - Dependency injection container
  - Comprehensive testing suite
  - Alpine.js integration for interactivity
  - Twig templating with layouts
  - Responsive design with modern styling

### 🔄 **Planned (v0.3.0+)**
- **Advanced Search**: Full-text search with filters
- **Rich Text Editor**: WYSIWYG editor for page editing
- **User Profiles**: Detailed user profiles and contribution tracking
- **API Endpoints**: RESTful API for external integration
- **Caching System**: Performance optimization with caching
- **Extensions System**: Plugin and extension framework
- **Themes and Skins**: Customizable appearance
- **Real-time Collaboration**: Live editing and collaboration
- **Media Support**: Image and file upload handling
- **Advanced Permissions**: Role-based access control

---

## Contributing

This project is licensed under the AGPL-3.0 License. Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting pull requests.

### Development Guidelines
- Follow PSR-1 and PSR-12 coding standards
- Write comprehensive tests for all features
- Document all new features and changes
- Use semantic versioning (MAJOR.MINOR.PATCH)
- Maintain backward compatibility when possible
- Follow security best practices
- Use structured logging for debugging

---

## License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

---

## Support

For support and questions:
- Create an issue on GitHub
- Check the [documentation](docs/)
- Review the [changelog](CHANGELOG.md)



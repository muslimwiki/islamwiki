# IslamWiki

A modern, custom wiki system combining the power of MediaWiki's functionality with a WordPress-like dashboard experience, built with PHP, Twig templating, and Alpine.js for lightweight interactivity.

[![AGPL-3.0 License](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

## Version 0.2.0 (2025-07-30)

### ✨ What's New
- **Wiki Page System**: Complete page creation, viewing, and management
- **Page Model**: Eloquent-like model with relationships and revision tracking
- **Page Controller**: Full CRUD operations for wiki pages with proper templates
- **Content Rendering**: Basic wiki text parsing and HTML rendering
- **View Count Tracking**: Page view analytics with database updates
- **Page Permissions**: Edit, delete, and lock permissions based on user roles
- **Page History**: Revision tracking and history viewing functionality
- **Dynamic Homepage**: Recent pages display with excerpts and professional styling

### 🚀 Key Features
- **Wiki Page System**: Complete page creation, viewing, and management
- **Page Model**: Eloquent-like model with relationships and revision tracking
- **Page Controller**: Full CRUD operations for wiki pages with proper templates
- **Content Rendering**: Basic wiki text parsing and HTML rendering
- **View Count Tracking**: Page view analytics with database updates
- **Page Permissions**: Edit, delete, and lock permissions based on user roles
- **Page History**: Revision tracking and history viewing functionality
- **Session Management**: Secure session handling with HTTP-only, SameSite cookies
- **CSRF Protection**: Token-based protection against cross-site request forgery
- **Authentication System**: Secure user authentication with password hashing
- **Database Foundation**: Complete migration system with proper schema management
- **Dynamic Homepage**: Database-driven content with recent pages display
- **Remember Me**: Secure persistent login functionality
- **Development Tools**: Setup scripts, tests, and comprehensive documentation

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
- **PHP 8.1+**: Modern PHP with strict typing
- **FastRoute**: High-performance routing
- **Twig**: Server-side templating engine
- **PSR-7**: HTTP message interfaces
- **Composer**: Dependency management

### Frontend
- **Alpine.js**: Lightweight JavaScript framework
- **Modern CSS**: Responsive design with flexbox/grid
- **Progressive Enhancement**: Works without JavaScript

### Development
- **Error Handling**: Comprehensive logging and debugging
- **Service Providers**: Modular architecture
- **Dependency Injection**: Clean, testable code

---

## Features

### Current (0.1.0)
- ✅ Session management with secure HTTP-only cookies
- ✅ CSRF protection on all forms
- ✅ User authentication (registration, login, logout)
- ✅ Authentication middleware for route protection
- ✅ Database foundation with migration system
- ✅ Dynamic homepage with recent pages display
- ✅ Remember me functionality
- ✅ Secure password hashing and verification
- ✅ Comprehensive testing suite
- ✅ Working homepage with interactive demo
- ✅ Alpine.js integration for lightweight interactivity
- ✅ Twig templating with proper layouts
- ✅ Responsive design with modern styling
- ✅ Comprehensive error handling and logging
- ✅ PSR-7 compatible HTTP handling
- ✅ Dependency injection container

### Planned (0.1.1)
- 🔄 Wiki page creation, editing, and management
- 🔄 Page revision tracking and history
- 🔄 Search functionality with full-text search
- 🔄 Rich text editor for page editing
- 🔄 User profiles and contribution tracking
- 🔄 API endpoints for external integration
- 🔄 Caching system for performance
- 🔄 Extensions and plugins system
- 🔄 Themes and skins
- 🔄 Real-time collaboration features

---

## Contributing

This project is licensed under the AGPL-3.0 License. Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting pull requests.

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests
- Document all new features
- Use semantic versioning
- Maintain backward compatibility

---

## License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

---

## Support

For support and questions:
- Create an issue on GitHub
- Check the [documentation](docs/)
- Review the [changelog](CHANGELOG.md)

---

## Archived Recent Changes

### Version 0.0.5 (Unreleased)
- Enhanced routing system with middleware support
- Improved error handling and logging
- Added database migration system
- Implemented user authentication framework
- Added API endpoints for frontend integration

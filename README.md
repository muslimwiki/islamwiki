# IslamWiki

A modern, custom wiki system combining the power of MediaWiki's functionality with a WordPress-like dashboard experience, built with PHP, Twig templating, and Alpine.js for lightweight interactivity.

[![AGPL-3.0 License](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)

## Version 0.0.1 (2025-07-29)

### ✨ What's New
- **Foundation Complete**: Working homepage and dashboard with modern architecture
- **Alpine.js Integration**: Lightweight frontend interactivity without heavy frameworks
- **Twig Templating**: Server-side rendering with proper layouts and inheritance
- **Responsive Design**: Modern, clean UI with component-based styling
- **Interactive Components**: Counter, messaging, alerts, stats, and watchlist management
- **Comprehensive Error Handling**: Detailed error pages and robust logging system

### 🚀 Key Features
- **Modern Architecture**: PSR-7 compatible HTTP handling with dependency injection
- **FastRouter**: High-performance routing with proper controller instantiation
- **Service Providers**: Modular service registration system
- **File-based Logging**: Comprehensive error tracking and debugging
- **Development Ready**: Hot reloading, debug mode, and detailed error output

---

## Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Web server (Apache/Nginx)
- MySQL/MariaDB (optional for now)

### Installation
```bash
# Clone the repository
git clone https://github.com/yourusername/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env with your configuration

# Set permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/

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
├── resources/            # Application resources
│   └── views/           # Twig templates
├── routes/              # Route definitions
├── storage/             # Application storage
│   ├── logs/           # Log files
│   └── framework/      # Framework cache
├── docs/               # Documentation
└── tests/              # Test files
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

### Current (0.0.1)
- ✅ Working homepage with interactive demo
- ✅ Dashboard with dynamic stats and activity feed
- ✅ Alpine.js integration for lightweight interactivity
- ✅ Twig templating with proper layouts
- ✅ Responsive design with modern styling
- ✅ Comprehensive error handling and logging
- ✅ PSR-7 compatible HTTP handling
- ✅ Dependency injection container

### Planned
- 🔄 User authentication and authorization
- 🔄 Wiki page creation and editing
- 🔄 Search functionality
- 🔄 File uploads and media management
- 🔄 Extensions and plugins system
- 🔄 Themes and skins
- 🔄 API endpoints
- 🔄 Database integration
- 🔄 Caching system
- 🔄 Testing framework

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

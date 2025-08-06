# IslamWiki

**Version:** 0.0.52  
**Release Date:** 2025-08-06  
**Status:** Active Development

A comprehensive Islamic knowledge base and resource center built with modern web technologies.

## 🚀 Features

### Core Features
- **Islamic Content Management**: Complete Quran, Hadith, and Islamic sciences database
- **Advanced Search Engine**: Iqra search with intelligent filtering and relevance
- **User Authentication**: Secure login, registration, and profile management
- **Community Features**: User profiles, contributions, and community interaction
- **Islamic Calendar**: Advanced Islamic calendar with prayer times
- **Modern Islamic Design**: Beautiful, modern interface with Islamic theme and animations
- **Responsive Design**: Mobile-friendly interface with modern UI/UX

### Technical Features
- **Modern Architecture**: MVC pattern with dependency injection
- **REST API**: Complete API for frontend integration
- **Database Migration**: Automated schema management
- **Caching System**: Performance optimization with intelligent caching
- **Security**: CSRF protection, input validation, and secure sessions
- **Logging**: PSR-3 compliant logging system
- **Error Handling**: Comprehensive error management and debugging

### Skin System
- **Dynamic Skin Discovery**: Automatic discovery of skins from `/skins/` directory
- **Multi-Skin Support**: Full support for multiple skins (Bismillah, Muslim)
- **User-Specific Preferences**: Individual user skin settings stored in database
- **Settings Interface**: Comprehensive settings page with skin selection
- **Case-Insensitive Access**: Support for both `Muslim` and `muslim` naming
- **Skin Information**: Detailed metadata and feature information display
- **Responsive Design**: Mobile-friendly layouts for all skins
- **Accessibility**: Skip links, keyboard navigation, focus management
- **Authentication Safe**: Skin middleware doesn't interfere with login process
- **Content Rendering**: Proper content display in main body area for all skins
- **CSS Framework Integration**: Seamless integration with Safa CSS framework
- **URL Parameter Override**: Quick skin switching via URL parameters (`?skin=bismillah`, `?skin=muslim`)
- **Temporary Skin Testing**: Non-persistent skin switching for development and testing
- **Validation & Fallback**: Only accepts valid skin names, falls back to user preference
- **Modern Islamic Theme**: Beautiful blue gradient design with glass morphism effects
- **Advanced Animations**: Smooth transitions, hover effects, and loading animations
- **Professional Typography**: Inter font family with proper hierarchy

### Development Features
- **Dependency Injection**: Container-based service management
- **Service Providers**: Modular service registration
- **Configuration Management**: Environment-based settings
- **Comprehensive Testing**: Automated test suites and debugging tools
- **Documentation**: Extensive documentation and guides

## 🛠️ Technology Stack

- **Backend**: PHP 8.1+ with custom framework
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL with migration system
- **Templating**: Twig template engine
- **CSS Framework**: Safa CSS (custom Islamic framework)
- **JavaScript**: ZamZam.js (lightweight framework)
- **Logging**: PSR-3 compliant Shahid logger
- **Container**: AsasContainer dependency injection container

## 📦 Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependency management)

### Quick Start
1. Clone the repository
2. Install dependencies: `composer install`
3. Configure database in `LocalSettings.php`
4. Run migrations: `php scripts/database/run_migrations.php`
5. Set up virtual host pointing to `public/` directory
6. Access the application at your configured domain

### Development Setup
```bash
# Clone repository
git clone https://github.com/your-username/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Configure environment
cp LocalSettings.php.example LocalSettings.php
# Edit LocalSettings.php with your database credentials

# Run migrations
php scripts/database/run_migrations.php

# Start development server
php -S localhost:8000 -t public/
```

## 🎨 Skins

### Available Skins
- **Bismillah**: Default Islamic-themed skin with traditional design and beautiful gradients
- **Muslim**: Modern skin inspired by Citizen MediaWiki with Islamic aesthetics and proper content rendering

### Skin Management
- **Dynamic Discovery**: New skins automatically appear in settings
- **User Preferences**: Individual skin preferences stored per user
- **Settings API**: RESTful endpoints for skin management
- **Skin Information**: Detailed metadata, features, and dependencies
- **Validation**: Comprehensive skin validation and error handling

### Skin Features
- Responsive design for all devices
- Islamic typography and color schemes
- Accessibility features (WCAG compliant)
- Dark theme support
- Customizable layouts and components
- Animation and gradient support
- Glass morphism effects

## 🔧 Configuration

### Environment Variables
- `APP_DEBUG`: Enable/disable debug mode
- `DB_HOST`: Database host
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `ACTIVE_SKIN`: Default active skin

### LocalSettings.php
Main configuration file for:
- Database settings
- Available skins
- Application settings
- Security configurations

## 📚 Documentation

### User Guides
- [Getting Started](docs/guides/getting-started.md)
- [User Manual](docs/guides/user-manual.md)
- [Contributing Guidelines](docs/guides/contributing.md)

### Developer Documentation
- [Architecture Overview](docs/architecture/overview.md)
- [API Reference](docs/api/reference.md)
- [Skin Development](docs/skins/README.md)
- [Database Schema](docs/database/schema.md)

### Release Notes
- [Current Release](docs/releases/RELEASE-0.0.45.md)
- [All Releases](docs/releases/)

## 🧪 Testing

### Automated Tests
- Unit tests for core functionality
- Integration tests for API endpoints
- Skin compatibility tests
- Performance benchmarks

### Manual Testing
- Debug scripts in `debug/` directory
- Test pages for skin verification
- Settings pages for configuration testing

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](docs/guides/contributing.md) for details.

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive documentation
- Include tests for new features
- Maintain backward compatibility

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- Islamic scholars and researchers for content guidance
- Open source community for tools and libraries
- Contributors and maintainers

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-username/islamwiki/issues)
- **Documentation**: [Project Wiki](https://github.com/your-username/islamwiki/wiki)
- **Community**: [Discussions](https://github.com/your-username/islamwiki/discussions)

---

**IslamWiki** - Empowering Islamic knowledge through technology.



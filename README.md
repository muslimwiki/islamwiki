# IslamWiki - Local Development

A comprehensive Islamic knowledge platform built with modern PHP architecture.

## 🚀 Current Version: 0.0.30

**Latest Release:** December 19, 2024  
**Codename:** "Stable Skins & Secure Login"

### Recent Fixes (v0.0.30)
- ✅ **Fixed skin switching system** - Case sensitivity issues resolved
- ✅ **Fixed login functionality** - Alpine.js interference eliminated
- ✅ **Improved CSRF protection** - Proper token handling implemented
- ✅ **Enhanced middleware stack** - Better error handling and execution

## 🎯 Features

### Core Functionality
- **Islamic Content Management** - Quran, Hadith, and Islamic resources
- **User Authentication** - Secure login and registration system
- **Skin System** - Multiple visual themes with real-time switching
- **Search & Navigation** - Advanced content discovery
- **Community Features** - User profiles and interactions

### Technical Stack
- **Framework:** Custom PHP MVC with dependency injection
- **CSS Framework:** Safa CSS (lightweight, pure CSS)
- **Templating:** Twig templates
- **Database:** MySQL with Islamic content schemas
- **Security:** CSRF protection, session management
- **Frontend:** Alpine.js for interactivity

## 🛠️ Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- Composer

### Installation
```bash
# Clone the repository
git clone <repository-url>
cd local.islam.wiki

# Install dependencies
composer install

# Setup database
php scripts/database/setup_database.php

# Configure web server
# Point document root to public/ directory
```

### Default Credentials
- **Username:** `admin`
- **Password:** `password`

## 🎨 Available Skins

- **Bismillah** - Default Islamic theme
- **BlueSkin** - Modern blue theme
- **GreenSkin** - Fresh green theme

## 📁 Project Structure

```
local.islam.wiki/
├── src/                    # Core application code
│   ├── Core/              # Framework core
│   ├── Http/              # Controllers & Middleware
│   ├── Models/            # Data models
│   └── Skins/             # Skin system
├── resources/             # Views and assets
├── public/               # Web root
├── database/             # Migrations
├── scripts/              # Setup and utilities
└── docs/                 # Documentation
```

## 🔧 Development

### Running Tests
```bash
# Test skin switching
php public/test-skin-switching-safa.php

# Test authentication
php scripts/tests/test_auth.php

# Test database connection
php scripts/tests/test_db.php
```

### Adding New Skins
1. Create skin directory in `skins/`
2. Add `skin.json` configuration
3. Include CSS and JS files
4. Register in `SkinManager`

## 📚 Documentation

- [Architecture Overview](docs/architecture/overview.md)
- [Database Schema](docs/DATABASE_SETUP.md)
- [Skin Development](docs/skins/README.md)
- [API Documentation](docs/ROUTING.md)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the AGPL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details.

## 🆘 Support

For issues and questions:
- Check the [documentation](docs/)
- Review [release notes](docs/releases/)
- Create an issue on GitHub

---

**Built with ❤️ for the Islamic community**



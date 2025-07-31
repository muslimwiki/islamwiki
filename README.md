# IslamWiki

A comprehensive Islamic knowledge platform built with modern web technologies, featuring a modular skin system for easy customization.

## 🚀 Features

### Core Features
- **Wiki System**: Complete wiki functionality with page creation, editing, and history
- **User Management**: Registration, authentication, and profile management
- **Search System**: Advanced search across all content types
- **Islamic Content**: Quran, Hadith, Prayer Times, Islamic Calendar integration
- **Community Features**: User discussions, activity tracking, and collaboration

### 🎨 Skin System
- **Modular Skins**: User-friendly skin system with `/skins/` directory
- **Easy Customization**: JSON-based skin configuration
- **Multiple Skins**: Built-in Bismillah and BlueSkin themes
- **Frontend Switching**: Web interface for skin management
- **Responsive Design**: All skins are mobile-friendly and responsive

### Islamic Features
- **Quran Integration**: Complete Quran verse management with translations
- **Hadith System**: Comprehensive Hadith collections and search
- **Prayer Times**: Accurate prayer time calculations with multiple methods
- **Islamic Calendar**: Hijri calendar with events and date conversion
- **Scholar Verification**: Islamic scholar verification and credential system

## 📦 Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

### Quick Start
```bash
# Clone the repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Copy configuration
cp LocalSettings.example.php LocalSettings.php

# Configure database and other settings
nano LocalSettings.php

# Run database migrations
php scripts/migrate.php

# Start development server
php -S localhost:8000 -t public/
```

## 🎨 Skin System

### Available Skins
- **Bismillah**: Default Islamic-themed skin with modern design
- **BlueSkin**: Beautiful blue-themed skin with clean interface

### Switching Skins
1. **Via LocalSettings**: Edit `LocalSettings.php` and change `$wgActiveSkin`
2. **Via Web Interface**: Use the Settings page to switch skins
3. **Via Environment**: Set `ACTIVE_SKIN` environment variable

### Creating Custom Skins
1. Create a new folder in `/skins/` (e.g., `/skins/MySkin/`)
2. Add `skin.json` configuration file
3. Create CSS and JS files in the skin folder
4. Optionally add custom layout templates

Example skin structure:
```
skins/
├── Bismillah/
│   ├── skin.json
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── templates/
│       └── layout.twig
└── BlueSkin/
    ├── skin.json
    ├── css/
    ├── js/
    └── templates/
```

## 🔧 Configuration

### LocalSettings.php
Main configuration file with settings for:
- Database connections
- Skin system
- Islamic features
- Security settings
- Performance options

### Environment Variables
- `ACTIVE_SKIN`: Active skin name
- `DB_CONNECTION`: Database connection type
- `APP_ENV`: Application environment

## 📚 Documentation

- [Installation Guide](docs/INSTALL.md)
- [Skin System Documentation](docs/skins/README.md)
- [API Documentation](docs/API.md)
- [Developer Guide](docs/developer/README.md)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Skin Development
- Follow the skin system documentation
- Test your skin across different devices
- Ensure accessibility compliance
- Document any new features

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- Built with modern PHP and web technologies
- Inspired by Islamic knowledge sharing traditions
- Designed for the global Islamic community

## 📞 Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-org/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-org/islamwiki/discussions)

---

**Version**: 0.0.28  
**License**: AGPL-3.0-only  
**Status**: Active Development



# IslamWiki

A comprehensive Islamic knowledge platform built with modern PHP architecture.

## 🚀 Current Version: 0.0.32

**Latest Fix**: Resolved critical database connection issue in SettingsController that was preventing proper skin switching and "Active" button display.

## ✨ Features

- **Multi-Skin Support**: BlueSkin, GreenSkin, and Bismillah themes
- **User Authentication**: Secure login and registration system
- **Settings Management**: User preferences and skin selection
- **Database Integration**: Robust user settings and preferences storage
- **Security Features**: CSRF protection and session management
- **Modern Architecture**: PHP MVC with dependency injection

## 🔧 Recent Fixes (v0.0.32)

### Critical Database Connection Issue Resolved
- **Problem**: SettingsController was using incorrect database methods (`prepare()` instead of `first()`/`statement()`)
- **Solution**: Fixed to use proper Connection class methods for database operations
- **Result**: Settings page now correctly displays active skin status and allows proper skin switching

### Technical Improvements
- Replaced direct PDO calls with framework's database abstraction layer
- Implemented proper error handling for database operations
- Cleaned up authentication bypasses and debug code
- Enhanced production security and reliability

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/islamwiki.git
   cd islamwiki
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Set up database**
   ```bash
   php scripts/database/setup_database.php
   ```

5. **Configure web server**
   - Point document root to `/public`
   - Ensure Apache/Nginx is configured for PHP

## 🎨 Skin System

IslamWiki supports multiple visual themes:

- **Bismillah**: Default Islamic-themed skin with modern design
- **BlueSkin**: Clean blue-themed interface
- **GreenSkin**: Green-themed skin with Islamic aesthetics

Users can switch skins through the Settings page, with preferences stored in the database.

## 🔒 Security Features

- **CSRF Protection**: All forms protected against cross-site request forgery
- **Session Management**: Secure session handling with proper authentication
- **Input Validation**: Comprehensive input sanitization and validation
- **Database Security**: Prepared statements and proper connection handling

## 📁 Project Structure

```
islamwiki/
├── src/                    # Application source code
│   ├── Core/              # Core framework components
│   ├── Http/              # HTTP layer (controllers, middleware)
│   ├── Models/            # Data models
│   └── Skins/             # Skin system
├── resources/             # Views and templates
├── public/               # Web-accessible files
├── database/             # Database migrations
└── scripts/              # Utility scripts
```

## 🚀 Development

### Running Locally
```bash
# Start PHP development server
php -S localhost:8000 -t public/

# Or use Apache/Nginx with proper configuration
```

### Database Operations
```bash
# Run migrations
php scripts/migrate.php

# Create sample data
php scripts/database/create_sample_data.php
```

## 📝 Recent Changes

### v0.0.32 (2025-08-01)
- **Fixed**: Critical database connection issue in SettingsController
- **Improved**: Database abstraction layer usage
- **Enhanced**: Production security and reliability

### v0.0.31 (2025-08-01)
- **Fixed**: ZamZam.js framework reactive data binding
- **Resolved**: CSRF token issues and login authentication
- **Improved**: Skin system functionality

### v0.0.30 (2025-08-01)
- **Fixed**: Authentication system and session management
- **Enhanced**: CSRF protection and database integration
- **Added**: Comprehensive debugging tools

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- Built with modern PHP practices and security in mind
- Designed for the Islamic community's knowledge sharing needs
- Open source and community-driven development

---

**IslamWiki** - Empowering Islamic knowledge sharing through modern technology.



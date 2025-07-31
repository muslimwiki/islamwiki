# IslamWiki

[![License: AGPL-3.0-only](https://img.shields.io/badge/License-AGPL--3.0--only-blue.svg)](https://opensource.org/licenses/AGPL-3.0-only)
[![Version](https://img.shields.io/badge/Version-0.0.29-green.svg)](VERSION)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)

A comprehensive Islamic knowledge platform built with modern PHP, featuring user-specific settings, authentication, and a modular skin system.

## 🚀 Features

### 🔐 **User Authentication & Security**
- **Secure Authentication System**: Session-based user authentication with proper security
- **User-Specific Settings**: Individual preferences for each user (skins, themes, etc.)
- **Professional Error Pages**: Beautiful 401 authentication error pages for non-logged-in users
- **Smart Response System**: Detects AJAX/API requests vs browser requests and responds appropriately

### 🎨 **Modular Skin System**
- **Multiple Skins**: Bismillah, BlueSkin, GreenSkin, and more
- **User-Specific Skins**: Each user can have their own skin preference
- **Real-time Switching**: Instant skin changes with visual feedback
- **Customizable Themes**: CSS, JavaScript, and layout customization

### 📚 **Islamic Content Management**
- **Quran Integration**: Complete Quran database with search and navigation
- **Hadith Collections**: Comprehensive hadith database with authentication
- **Islamic Calendar**: Hijri calendar integration with prayer times
- **Community Features**: User contributions and content management

### 🛠️ **Modern Development**
- **PHP 8.3+**: Latest PHP features and performance
- **MVC Architecture**: Clean, maintainable code structure
- **Database Abstraction**: Flexible database layer with migrations
- **Comprehensive Testing**: Extensive test coverage and debugging tools

## 📋 Requirements

- **PHP**: 8.3 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Web Server**: Apache/Nginx with PHP support
- **Extensions**: PDO, JSON, Session, FileInfo

## 🚀 Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/islamwiki.git
cd islamwiki
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Database
```bash
# Copy configuration file
cp LocalSettings.php.example LocalSettings.php

# Edit database settings
nano LocalSettings.php
```

### 4. Run Migrations
```bash
php scripts/migrate.php
```

### 5. Start Development Server
```bash
php -S localhost:8000 -t public/
```

### 6. Access the Application
Open your browser and navigate to `http://localhost:8000`

## 🔧 Configuration

### Database Setup
```php
// LocalSettings.php
$wgDatabaseHost = 'localhost';
$wgDatabaseName = 'islamwiki';
$wgDatabaseUser = 'your_username';
$wgDatabasePassword = 'your_password';
```

### Authentication
```php
// Session configuration
$wgSessionSecret = 'your-secret-key-here';
$wgSessionLifetime = 86400; // 24 hours
```

### Skin Configuration
```php
// Default skin for new users
$wgActiveSkin = 'Bismillah';
```

## 🎨 Available Skins

### Bismillah (Default)
- **Description**: Traditional Islamic theme with elegant typography
- **Features**: Responsive design, prayer time integration
- **Colors**: Gold, green, and white color scheme

### BlueSkin
- **Description**: Modern blue theme with clean interface
- **Features**: Professional appearance, excellent readability
- **Colors**: Blue and white color palette

### GreenSkin
- **Description**: Fresh green theme for easy testing
- **Features**: Distinct visual appearance, comprehensive styling
- **Colors**: Green and white color scheme

## 🔐 Authentication System

### User Registration & Login
- **Secure Registration**: Email verification and password hashing
- **Session Management**: Secure session handling with proper timeouts
- **User Profiles**: Individual user settings and preferences
- **Access Control**: Role-based permissions and security

### Settings Security
- **Authentication Required**: All settings endpoints require user login
- **User Isolation**: Each user's preferences are properly isolated
- **Professional Error Pages**: Beautiful error pages for non-authenticated users
- **Smart Response System**: Appropriate responses for different request types

## 📊 Database Schema

### Core Tables
- **users**: User accounts and authentication
- **user_settings**: Individual user preferences (JSON storage)
- **pages**: Wiki content and articles
- **revisions**: Content version history
- **quran_verses**: Complete Quran database
- **hadith_collections**: Hadith database with authentication

### User Settings
```sql
CREATE TABLE user_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    settings JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_id (user_id)
);
```

## 🧪 Testing

### Test Scripts
```bash
# Test authentication system
php public/test-auth.php

# Test user-specific settings
php public/test-user-settings.php

# Test settings with logged-in user
php public/test-settings-logged-in.php
```

### Test Coverage
- **Authentication**: User login/logout and session management
- **Settings**: User-specific preferences and skin switching
- **Error Handling**: Professional error pages and responses
- **Database**: User settings storage and retrieval

## 📚 Documentation

### Core Documentation
- **[Architecture Overview](docs/architecture/overview.md)**: System architecture and design
- **[Database Schema](docs/DATABASE_SETUP.md)**: Complete database documentation
- **[Routing System](docs/ROUTING.md)**: URL routing and controller system
- **[Security Guide](docs/security/README.md)**: Authentication and security features

### Feature Documentation
- **[Skin System](docs/skins/README.md)**: Complete skin system guide
- **[User Settings](docs/features/user-settings.md)**: User-specific settings system
- **[Error Pages](docs/features/error-pages.md)**: Professional error handling
- **[Testing Guide](docs/testing/README.md)**: Comprehensive testing documentation

### API Documentation
- **[Settings API](docs/api/settings.md)**: User settings endpoints
- **[Authentication API](docs/api/authentication.md)**: Login and session management
- **[Skin API](docs/api/skins.md)**: Skin management endpoints

## 🔒 Security Features

### Authentication & Authorization
- **Session Security**: Secure session management with proper timeouts
- **Password Hashing**: Bcrypt password hashing for user accounts
- **Route Protection**: Authentication middleware for protected endpoints
- **CSRF Protection**: Cross-site request forgery protection

### Data Protection
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input validation and output escaping
- **User Data Isolation**: Proper separation of user preferences
- **Secure Error Handling**: No sensitive information in error messages

## 🚀 Development

### Project Structure
```
islamwiki/
├── src/                    # Core application code
│   ├── Core/              # Framework core components
│   ├── Http/              # HTTP layer (controllers, middleware)
│   ├── Models/            # Database models
│   └── Skins/             # Skin management system
├── resources/             # Views and templates
├── skins/                 # User-facing skins
├── database/              # Database migrations
├── docs/                  # Documentation
└── tests/                 # Test files
```

### Key Components
- **Application**: Main application bootstrap and configuration
- **Container**: Dependency injection container
- **Router**: HTTP routing and middleware system
- **SkinManager**: Skin discovery and management
- **SettingsController**: User settings and authentication

## 📈 Performance

### Optimizations
- **Database Indexing**: Optimized queries with proper indexing
- **Session Caching**: Efficient session management
- **Skin Caching**: Cached skin loading for better performance
- **JSON Storage**: Efficient user preferences storage

### Monitoring
- **Error Logging**: Comprehensive error logging and debugging
- **Performance Metrics**: Database query optimization
- **User Analytics**: Anonymous usage statistics
- **Security Monitoring**: Authentication and access logging

## 🤝 Contributing

### Development Setup
1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Submit** a pull request

### Code Standards
- **PSR-12**: PHP coding standards
- **Documentation**: Comprehensive inline documentation
- **Testing**: Unit and integration tests
- **Security**: Security-first development approach

## 📄 License

This project is licensed under the **GNU Affero General Public License v3.0** - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- **Islamic Scholars**: For guidance on Islamic content and authenticity
- **Open Source Community**: For inspiration and best practices
- **Contributors**: For code contributions and feedback
- **Users**: For testing and feature requests

## 📞 Support

### Getting Help
- **Documentation**: Comprehensive guides and tutorials
- **Issues**: GitHub issues for bug reports and feature requests
- **Discussions**: Community discussions for questions and ideas
- **Security**: Private security reports for vulnerabilities

### Community
- **Contributors**: Join our development team
- **Testing**: Help test new features and improvements
- **Documentation**: Improve guides and tutorials
- **Feedback**: Share ideas and suggestions

---

**Version:** 0.0.29  
**Last Updated:** July 31, 2025  
**License:** AGPL-3.0-only



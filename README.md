# IslamWiki

A comprehensive Islamic knowledge platform built with modern PHP architecture.

## 🚀 Current Version: 0.0.34

**Latest Feature**: Enhanced profile system with privacy controls and customization options.

## ✨ Features

- **📝 Page Creation System**: Complete wiki page creation with Markdown support
- **🏷️ Namespace Support**: Organize pages with namespaces (Help, User, Template, Category)
- **📚 Revision Tracking**: Automatic revision history with user attribution
- **🎨 Multi-Skin Support**: BlueSkin, GreenSkin, and Bismillah themes
- **🔐 User Authentication**: Secure login and registration system
- **⚙️ Settings Management**: User preferences and skin selection
- **💾 Database Integration**: Robust user settings and preferences storage
- **🛡️ Security Features**: CSRF protection and session management
- **🏗️ Modern Architecture**: PHP MVC with dependency injection

## 🔧 Recent Features (v0.0.34)

### Enhanced Profile System
- **Dual Profile System**: Separate public (`/user/{username}`) and private (`/profile`) profile pages
- **Privacy Controls**: Users can control exactly what information is visible on their public profile
- **Profile Customization**: Users can customize how their profile appears to others
- **Real-time Updates**: AJAX functionality for privacy and customization settings
- **Responsive Design**: All profile sections work perfectly on mobile and desktop

### Privacy Controls Tab
- **Profile Visibility**: Public, registered users only, or private profiles
- **Activity Visibility**: Control visibility of recent activity, statistics, and watchlist
- **Data Visibility**: Control visibility of email, join date, and last active status
- **Granular Control**: Fine-grained privacy settings for complete user control

### Profile Customization Tab
- **Profile Information**: Customize display name, bio, location, and website
- **Visual Customization**: Choose from multiple profile themes and layouts
- **Content Preferences**: Featured content and personal profile messages
- **Personal Branding**: Complete control over profile appearance and content

### Technical Improvements
- **User Model Enhancement**: Added `toArray()` and `toJson()` methods for better data handling
- **Route Conflict Resolution**: Fixed conflicting profile routes between controllers
- **Database Integration**: Enhanced user settings storage and retrieval
- **Error Handling**: Improved error handling and validation throughout the profile system
- **Git Management**: Comprehensive .gitignore file with proper file exclusions and security

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

IslamWiki features a robust and flexible skin system:

- **Multiple Themes**: Bismillah (default), BlueSkin, and GreenSkin available
- **User Preferences**: Individual skin selection with database storage
- **Real-time Switching**: Change skins through the Settings page
- **Responsive Design**: All skins optimized for mobile and desktop
- **Islamic Aesthetics**: Beautiful Islamic-themed designs and typography

### Skin Features
- **Bismillah**: Default Islamic-themed skin with purple/indigo gradient
- **BlueSkin**: Clean blue-themed interface with modern design
- **GreenSkin**: Green-themed skin with Islamic aesthetics
- **Custom CSS/JS**: Each skin supports custom styles and scripts
- **Template System**: Flexible Twig-based template system

### Debugging Tools
- **Skin Status**: Access `/skin-system-status.php` for detailed system reporting
- **Comprehensive Testing**: Advanced debugging tools in `/debug/` directory
- **Validation**: Complete skin validation and error reporting
- **Performance Monitoring**: Real-time skin loading and performance metrics



## 🔒 Security Features

- **CSRF Protection**: All forms protected against cross-site request forgery
- **Session Management**: Secure session handling with proper authentication
- **File Security**: Comprehensive .gitignore file protecting sensitive data
- **Session File Protection**: Session files properly excluded from version control
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



# IslamWiki - Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.3.1-blue.svg)](https://github.com/your-username/islamwiki/releases)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-8.0+-purple.svg)](https://php.net)

**IslamWiki** is a comprehensive, open-source Islamic knowledge platform designed to provide authentic Islamic content, resources, and community features in multiple languages.

## 🚀 **Latest Release: 0.0.3.1 - Complete Routing System**

**Version 0.0.3.1** represents a major milestone in the development of IslamWiki, featuring a complete, production-ready routing system with internationalization, authentication, and beautiful user interfaces.

### ✨ **What's New in 0.0.3.1:**

- **🎯 Complete Routing System**: 123 fully functional routes covering all major Islamic content areas
- **🌐 Internationalization**: Full English and Arabic language support with `/en/*` and `/ar/*` routing
- **🎨 Beautiful UI**: Professional HTML templates with modern CSS styling and responsive design
- **🔐 Authentication Ready**: Middleware system for user authentication and admin authorization
- **📱 Controller Architecture**: Clean separation of concerns with SimpleController implementation
- **🛡️ Security Features**: CSRF protection, session management, and secure form handling
- **📊 Dashboard System**: User dashboard with statistics, quick actions, and Islamic resources
- **🔍 Search Functionality**: Advanced search with tips and guidance for Islamic content
- **📚 Wiki System**: Dynamic wiki pages with editing, history, and discussion features
- **👥 Community Features**: Forums, messaging, and user profiles
- **📅 Islamic Calendar**: Calendar system for Islamic events and important dates
- **🕌 Salah Times**: Prayer time calculations with city support
- **📖 Islamic Resources**: Quran, Hadith, Fatwas, and Scholar profiles
- **⚙️ Admin Panel**: Comprehensive admin dashboard for platform management

## 🌟 **Key Features**

### **Core Platform**
- **Multi-language Support**: English and Arabic with easy language switching
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Modern Architecture**: Built with PHP 8.0+, PSR-4 autoloading, and dependency injection
- **Security First**: Authentication, authorization, and CSRF protection built-in

### **Islamic Content**
- **Quran Integration**: Access to Quranic text and translations
- **Hadith Collections**: Authentic hadith with proper sourcing
- **Fatwa System**: Islamic legal opinions and guidance
- **Scholar Profiles**: Information about Islamic scholars and their works
- **Learning Resources**: Educational content for Islamic studies

### **Community Features**
- **User Forums**: Discussion boards for Islamic topics
- **Private Messaging**: Secure communication between users
- **User Profiles**: Personal profiles with activity tracking
- **Bookmarks**: Save and organize favorite content
- **Notifications**: Stay updated with platform activities

### **Administrative Tools**
- **User Management**: Comprehensive user administration
- **Content Moderation**: Tools for maintaining content quality
- **Analytics Dashboard**: Platform usage statistics and insights
- **System Configuration**: Flexible platform settings and customization

## 🏗️ **Architecture**

IslamWiki is built with a modern, scalable architecture:

```
src/
├── Core/                 # Core system components
│   ├── Application.php   # Main application bootstrap
│   ├── Container/        # Dependency injection container
│   ├── Routing/          # Router and route management
│   ├── Http/             # HTTP request/response handling
│   ├── Database/         # Database abstraction layer
│   ├── Logging/          # Logging and error handling
│   └── I18n/            # Internationalization system
├── Http/                 # HTTP layer
│   ├── Controllers/      # Application controllers
│   ├── Middleware/       # Request/response middleware
│   └── Views/            # View templates and rendering
└── Extensions/           # Modular extensions
    ├── QuranExtension/   # Quran functionality
    ├── HadithExtension/  # Hadith collections
    └── DashboardExtension/ # User dashboard
```

## 🚀 **Quick Start**

### **Requirements**
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- Composer for dependency management

### **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/islamwiki.git
   cd islamwiki
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure the environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database and application settings
   ```

4. **Set up the database**
   ```bash
   php scripts/database/setup.php
   ```

5. **Configure your web server**
   - Point document root to `public/` directory
   - Ensure `.htaccess` is enabled for URL rewriting

6. **Access the platform**
   - Open your browser to `http://your-domain.com`
   - You'll be redirected to `/en` (English) by default
   - Switch to Arabic with `/ar` prefix

## 📚 **Documentation**

Comprehensive documentation is available in the `docs/` directory:

- **[Architecture Guide](docs/architecture/)** - System design and architecture
- **[API Reference](docs/api/)** - REST API documentation
- **[Development Guide](docs/development/)** - Contributing to IslamWiki
- **[User Guide](docs/user-guide/)** - Platform usage instructions
- **[Admin Guide](docs/admin-guide/)** - Administrative functions

## 🔧 **Development**

### **Adding New Routes**
```php
// In config/routes.php
$router->get('/en/new-feature', [$controller, 'newFeature']);
$router->post('/en/new-feature', [$controller, 'saveFeature']);
```

### **Creating Controllers**
```php
namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class FeatureController
{
    public function newFeature(Request $request): Response
    {
        return \IslamWiki\Http\Views\View::render('New Feature', 'Content here');
    }
}
```

### **Adding Middleware**
```php
// Apply authentication middleware
$router->get('/en/protected', [$controller, 'protected'])
    ->middleware([$authMiddleware]);
```

## 🌍 **Internationalization**

IslamWiki supports multiple languages through a sophisticated i18n system:

- **Language Detection**: Automatic language detection from URL, session, or browser
- **Route Prefixing**: All routes are prefixed with language codes (`/en/*`, `/ar/*`)
- **Content Localization**: Interface and content available in multiple languages
- **RTL Support**: Full right-to-left language support for Arabic

## 🔒 **Security Features**

- **Authentication**: User login and registration system
- **Authorization**: Role-based access control
- **CSRF Protection**: Cross-site request forgery prevention
- **Input Validation**: Comprehensive input sanitization
- **Session Security**: Secure session management
- **HTTPS Ready**: Full HTTPS support for production

## 📊 **Performance**

- **Route Caching**: Efficient route matching and caching
- **Database Optimization**: Optimized queries and indexing
- **Asset Optimization**: Minified CSS and JavaScript
- **CDN Ready**: Content delivery network support
- **Caching Layers**: Multiple caching strategies

## 🤝 **Contributing**

We welcome contributions from the community! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### **Development Setup**
```bash
# Fork and clone the repository
git clone https://github.com/your-username/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Run tests
php vendor/bin/phpunit

# Start development server
php -S localhost:8000 -t public/
```

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- **Islamic Scholars**: For their guidance and authentic knowledge
- **Open Source Community**: For the amazing tools and libraries
- **Contributors**: Everyone who has helped build IslamWiki
- **Users**: For their feedback and support

## 📞 **Support & Community**

- **Documentation**: [docs.islamwiki.org](https://docs.islamwiki.org)
- **Issues**: [GitHub Issues](https://github.com/your-username/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/islamwiki/discussions)
- **Email**: support@islamwiki.org

## 🔮 **Roadmap**

### **Version 0.0.4.0** (Next Major Release)
- Advanced content management system
- Enhanced search with AI-powered recommendations
- Mobile application (iOS/Android)
- Advanced user roles and permissions
- Content versioning and collaboration

### **Version 0.0.5.0** (Future)
- Machine learning for content recommendations
- Advanced analytics and insights
- API for third-party integrations
- Multi-tenant architecture
- Advanced security features

---

**IslamWiki** - Empowering Islamic knowledge through technology.

*Built with ❤️ for the global Muslim community.*

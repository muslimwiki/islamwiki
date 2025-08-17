# IslamWiki

**Version**: 0.0.61  
**Status**: DashboardExtension Release  
**Last Updated**: 2024-08-17

A comprehensive Islamic knowledge platform built with modern web technologies, featuring Quran and Hadith management, Islamic calendar integration, prayer times, and a role-based dashboard system.

## 🚀 **Latest Release (v0.0.61) - DashboardExtension**

### **New Features**
- **🎯 DashboardExtension**: Complete role-based dashboard system with personalized user experiences
- **👥 Role-Based Dashboards**: Admin, Scholar, Contributor, and User dashboards
- **🧠 Smart Role Detection**: Automatic user role identification and appropriate dashboard rendering
- **📱 Responsive Design**: Mobile-first approach with Islamic-themed styling
- **⚡ Interactive Widgets**: 10+ pre-built widgets for different purposes
- **🔒 Permission System**: Granular access control based on user roles

### **Dashboard System Features**
- **Admin Dashboard**: System administration, user management, content moderation
- **Scholar Dashboard**: Academic tools, research resources, scholarly content
- **Contributor Dashboard**: Content creation tools, contribution tracking
- **User Dashboard**: Learning progress, personalized recommendations, community updates

### **Technical Improvements**
- **Twig Templates**: Role-specific dashboard templates with consistent design
- **Service Architecture**: Clean separation of business logic and presentation
- **Hook System**: Comprehensive integration with IslamWiki framework
- **Performance**: Optimized queries, caching support, and lazy loading

## ✨ **Core Features**

### **Islamic Content Management**
- **📖 Quran Integration**: Complete Quran verse management with translations
- **📜 Hadith Collections**: Comprehensive Hadith management system
- **📅 Islamic Calendar**: Hijri date system with event management
- **🕌 Prayer Times**: Accurate prayer time calculations with astronomical algorithms
- **🔍 Search & Discovery**: Advanced search across all Islamic content types

### **User Management & Authentication**
- **🔐 Islamic Authentication**: Enhanced authentication with scholar verification
- **👥 Role-Based Access**: Admin, Scholar, Contributor, and User roles
- **📊 User Profiles**: Comprehensive user profiles with Islamic information
- **🔒 Security**: Advanced security features and permission management

### **Content & Wiki System**
- **📝 Wiki Pages**: Complete wiki system with Islamic content support
- **🌐 Multi-language**: Arabic and English support with RTL layout
- **📚 Content Categories**: Organized Islamic sciences and topics
- **🤝 Community**: User collaboration and content contribution

## 🏗️ **Architecture**

### **Modern Framework**
- **PHP 8.0+**: Modern PHP with type hints and PSR-12 standards
- **Twig Templates**: Powerful templating engine for views
- **Service Providers**: Clean dependency injection and service management
- **Hook System**: Extensible architecture for plugins and extensions

### **Database Design**
- **Islamic Database**: 39+ tables across 4 specialized databases
- **Content Management**: Efficient storage and retrieval of Islamic content
- **User Management**: Comprehensive user data and relationship management
- **Performance**: Optimized queries and indexing strategies

### **Extension System**
- **DashboardExtension**: Role-based dashboard system (v0.0.1)
- **QuranExtension**: Quran content management and display
- **HadithExtension**: Hadith collections and management
- **EnhancedMarkdown**: Islamic content formatting support
- **GitIntegration**: Version control for content collaboration

## 🚀 **Quick Start**

### **Installation**
```bash
# Clone the repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Configure database
cp .env.example .env
# Edit .env with your database credentials

# Run migrations
php artisan migrate

# Create admin user
php scripts/create_admin_user.php
```

### **Accessing Dashboards**
- **Admin**: `/dashboard` (full system access)
- **Scholar**: `/dashboard` (academic tools)
- **Contributor**: `/dashboard` (content creation)
- **User**: `/dashboard` (learning progress)

## 📚 **Documentation**

### **User Guides**
- **[Dashboard System](docs/extensions/DashboardExtension.md)** - Complete dashboard usage guide
- **[Quran Features](docs/features/quran.md)** - Quran browsing and search
- **[Hadith Collections](docs/features/hadith.md)** - Hadith study and research
- **[Prayer Times](docs/features/prayer-times.md)** - Prayer time calculations

### **Developer Documentation**
- **[Architecture Overview](docs/architecture/overview.md)** - System design and patterns
- **[Extension Development](docs/developer/extension-system.md)** - Building extensions
- **[API Reference](docs/api/README.md)** - REST API documentation
- **[Database Schema](docs/database/README.md)** - Database structure and relationships

### **Extension Documentation**
- **[DashboardExtension](docs/extensions/DashboardExtension.md)** - Role-based dashboard system
- **[QuranExtension](docs/extensions/QuranExtension.md)** - Quran management system
- **[HadithExtension](docs/extensions/HadithExtension.md)** - Hadith management system

## 🔧 **Configuration**

### **Dashboard Configuration**
```php
// Role-based dashboard settings
'admin' => [
    'template' => 'admin_dashboard',
    'widgets' => ['system_overview', 'user_management', 'content_moderation'],
    'permissions' => ['full_access']
],
'scholar' => [
    'template' => 'scholar_dashboard',
    'widgets' => ['academic_tools', 'research_resources'],
    'permissions' => ['content_management', 'academic_features']
]
```

### **Islamic Content Settings**
- **Quran Translations**: Multiple language support
- **Hadith Collections**: Various authentic collections
- **Prayer Times**: Location-based calculations
- **Calendar Events**: Islamic holidays and events

## 🎨 **Themes & Styling**

### **Islamic Design**
- **Color Scheme**: Green (#2d5016), Gold (#d4af37), Cream (#f8f6f0)
- **Typography**: Arabic and Latin font support
- **Layouts**: RTL and LTR layout support
- **Responsive**: Mobile-first design approach

### **Dashboard Themes**
- **Admin Theme**: Professional and functional
- **Scholar Theme**: Academic and research-focused
- **Contributor Theme**: Creative and collaborative
- **User Theme**: Learning and community-oriented

## 🚀 **Development**

### **Contributing**
1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Submit pull request with detailed description

### **Code Standards**
- **PHP**: PSR-12 coding standards
- **Twig**: Consistent template structure
- **CSS**: BEM methodology for class naming
- **JavaScript**: ES6+ with proper error handling

### **Testing**
- **Unit Tests**: Test individual components
- **Integration Tests**: Test dashboard workflows
- **Browser Tests**: Test across different browsers
- **Performance Tests**: Monitor dashboard performance

## 📊 **Performance & Security**

### **Performance Features**
- **Lazy Loading**: Widgets load data on demand
- **Caching**: Dashboard data caching for performance
- **Minimal Queries**: Efficient database queries
- **Responsive Images**: Optimized image loading

### **Security Features**
- **Role-Based Access**: Different dashboards for different user levels
- **Data Isolation**: Users only see authorized data
- **Input Validation**: All user inputs properly validated
- **XSS Protection**: Output properly escaped and sanitized

## 🌟 **Roadmap**

### **Upcoming Features**
- **Advanced Analytics**: User behavior tracking and insights
- **Custom Widgets**: User-configurable dashboard layouts
- **Mobile App**: Native mobile dashboard application
- **AI Recommendations**: Machine learning-based content suggestions
- **Real-time Updates**: WebSocket-based live dashboard updates

### **Performance Improvements**
- **Progressive Web App**: Offline dashboard functionality
- **Advanced Caching**: Redis-based dashboard data caching
- **CDN Integration**: Global content delivery for dashboard assets
- **Asset Bundling**: Optimized CSS and JavaScript delivery

## 🤝 **Community**

### **Support Channels**
- **Documentation**: Comprehensive guides and tutorials
- **Community Forum**: User discussions and support
- **Issue Tracker**: Bug reports and feature requests
- **Developer Chat**: Technical discussions and help

### **Contributors**
- **Core Team**: IslamWiki development team
- **Community Contributors**: Open source contributors
- **Islamic Scholars**: Content verification and guidance
- **Users**: Feedback and feature suggestions

## 📄 **License**

This project is licensed under the [MIT License](LICENSE) - see the LICENSE file for details.

## 🙏 **Acknowledgments**

- **Islamic Scholars**: For content verification and guidance
- **Open Source Community**: For the amazing tools and libraries
- **Contributors**: For their time and expertise
- **Users**: For feedback and continuous improvement

---

**IslamWiki** - Empowering the global Muslim community with authentic Islamic knowledge 🚀

*Built with ❤️ for the Ummah*

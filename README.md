# IslamWiki - Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.2.6-blue.svg)](https://github.com/islamwiki/islamwiki)
[![Status](https://img.shields.io/badge/status-Fully%20Operational-green.svg)](https://github.com/islamwiki/islamwiki)
[![License](https://img.shields.io/badge/license-AGPL--3.0-blue.svg)](LICENSE)

**IslamWiki** is a comprehensive, collaborative platform dedicated to sharing and preserving authentic Islamic knowledge. Built with modern web technologies and Islamic design principles, it provides a beautiful, accessible way for the global Muslim community to learn, contribute, and grow in their Islamic knowledge.

## 🌟 **Current Status: FULLY OPERATIONAL** ✅

**Version 0.0.2.6** represents a complete authentication system and UI overhaul with:
- ✅ **Complete Authentication System** - Login, logout, and registration through AmanSecurity
- ✅ **Conditional Sidebar Rendering** - Different content for logged in vs logged out users
- ✅ **User Preferences System** - Comprehensive settings with display options
- ✅ **Interactive Cog Wheel Menu** - Text size, color theme, and width settings
- ✅ **Profile Management** - User profiles, preferences, and settings
- ✅ **AmanSecurity Extension** - Complete extension architecture for security
- ✅ **Enhanced UI/UX** - Beautiful auth pages, improved sidebar, and responsive design
- ✅ **Smart Layout System** - Content width options with proper centering and sidebar isolation

## 🚀 **Key Features**

### **🔐 Advanced Authentication & Security**
- **AmanSecurity Extension**: Complete authentication system with extension architecture
- **User Management**: Advanced user administration with bulk operations
- **Security Monitoring**: Threat detection, IP blocking, and comprehensive logging
- **Session Management**: Secure authentication with proper session handling
- **User Preferences**: Comprehensive settings for display, language, and personalization

### **🎨 Beautiful Islamic Design**
- **Bismillah Skin**: Complete Islamic-themed user interface
- **Responsive Sidebar**: Navigation with crescent moon icon, search, and profile
- **Islamic Typography**: Amiri and Noto Naskh Arabic fonts
- **Prayer Time Display**: Current time and Hijri calendar integration
- **Professional Layout**: Modern web design with Islamic aesthetics

### **📚 Comprehensive Wiki Functionality**
- **Page Creation**: Intuitive forms for new Islamic content
- **Content Viewing**: Beautifully rendered Markdown with Islamic styling
- **Enhanced Markdown**: Wiki extensions and Islamic content features
- **Content Management**: Edit, delete, and version control capabilities
- **Search & Navigation**: Easy discovery of Islamic knowledge

### **🛡️ Robust System Architecture**
- **Simplified Routing**: Modern, efficient routing system
- **Comprehensive Error Handling**: Beautiful error pages with detailed logging
- **Shahid Logging**: Advanced logging and debugging system
- **Extension System**: Modular architecture for future enhancements
- **Database Migrations**: Version-controlled database schema

### **🌍 Global Accessibility**
- **Multi-language Support**: English, Arabic, Turkish, Urdu, Indonesian, Malay, Persian, Hebrew
- **Responsive Design**: Mobile and desktop optimized
- **Islamic Content Focus**: Quran, Hadith, Islamic history, Fiqh, and more
- **Community Features**: Collaboration tools for scholars and contributors

## 🏗️ **System Architecture**

### **Core Components**
- **NizamApplication**: Main application orchestrator
- **Simplified Routing**: Modern routing system
- **WisalSession**: Advanced session management
- **AmanSecurity**: Comprehensive authentication system
- **ShahidLogger**: Advanced logging and debugging

### **Content Processing**
- **Enhanced Markdown**: Wiki extensions and Islamic syntax
- **Template Engine**: Twig-based templating with skin support
- **Content Rendering**: Beautiful HTML output with Islamic styling
- **Asset Management**: Efficient loading of CSS, JavaScript, and fonts

### **Database & Storage**
- **MySQL/MariaDB**: Robust content storage
- **Active Record Models**: Clean data access patterns
- **Migration System**: Version-controlled database schema
- **Content Versioning**: Full revision history and rollback

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx with URL rewriting
- Composer for dependency management

### **Installation**
```bash
# Clone the repository
git clone https://github.com/islamwiki/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Configure database
cp config/database.example.php config/database.php
# Edit database.php with your credentials

# Run migrations
php scripts/database/migrate.php

# Set up web server
# Point document root to public/ directory
# Ensure skins/ directory is accessible

# Access the platform
# Open http://your-domain/wiki/Main_Page
```

### **Configuration**
- **Database**: Configure MySQL/MariaDB connection
- **Web Server**: Set up URL rewriting for clean URLs
- **Skins**: Ensure skins directory is web-accessible
- **Extensions**: Enable desired extensions in config

## 📱 **User Experience**

### **Main Page**
- **Beautiful Welcome**: Islamic-themed landing page
- **Content Overview**: What you can find on IslamWiki
- **Getting Started**: Guide for new users
- **Featured Content**: Links to Quran, Hadith, Calendar, Prayer Times

### **Content Creation**
- **Enhanced Markdown Editor**: Rich text editing with Islamic features
- **Template System**: Pre-built templates for different content types
- **Category Management**: Organize content by Islamic topics
- **Reference System**: Proper citation and source management

### **Navigation & Search**
- **Smart Sidebar**: Context-aware navigation
- **Search Functionality**: Find Islamic content quickly
- **Category Browsing**: Explore topics systematically
- **Recent Changes**: Stay updated with new content

## 🔧 **Development & Contributing**

### **Code Structure**
```
src/
├── Core/           # Core application components
├── Http/           # HTTP handling and controllers
├── Models/         # Data models and database access
├── Providers/      # Service providers and DI container
└── Services/       # Business logic services

extensions/         # Modular extensions
resources/          # Views, assets, and templates
skins/             # User interface themes
tests/             # Test suite and examples
```

### **Development Workflow**
1. **Fork** the repository
2. **Create** a feature branch
3. **Develop** with Islamic design principles
4. **Test** thoroughly across different devices
5. **Submit** a pull request with detailed description

### **Contributing Guidelines**
- **Islamic Etiquette**: Follow Islamic principles in all interactions
- **Code Quality**: Maintain high standards and comprehensive testing
- **Documentation**: Document all changes and new features
- **Community**: Engage respectfully with other contributors

## 📊 **Performance & Scalability**

### **Current Performance**
- **Fast Page Loading**: Optimized routing and content delivery
- **Efficient Database**: Optimized queries and indexing
- **Asset Optimization**: Compressed CSS and JavaScript
- **Caching System**: Intelligent content and template caching

### **Scalability Features**
- **Modular Architecture**: Easy to extend and maintain
- **Extension System**: Add new features without core changes
- **Database Optimization**: Efficient storage and retrieval
- **CDN Ready**: Easy integration with content delivery networks

## 🔮 **Roadmap & Future**

### **Immediate Goals (0.0.2.4)**
- **Enhanced Wiki Functionality**: Complete edit, delete, history features
- **User Management**: Authentication, profiles, and permissions
- **Content Moderation**: Quality control and review system
- **Advanced Search**: Full-text search with Islamic content indexing

### **Medium Term (0.0.3.x)**
- **Mobile App**: Native mobile applications
- **AI Integration**: Smart content recommendations
- **Community Features**: Forums, discussions, and collaboration
- **Multi-language Content**: Full localization support

### **Long Term Vision**
- **Global Islamic Network**: Connect scholars worldwide
- **Educational Platform**: Islamic learning management system
- **Research Tools**: Advanced Islamic studies resources
- **API Platform**: Open access to Islamic knowledge

## 🤝 **Community & Support**

### **Getting Help**
- **Documentation**: Comprehensive guides and tutorials
- **Community Forum**: Ask questions and share knowledge
- **Issue Tracker**: Report bugs and request features
- **Contributor Chat**: Real-time development discussions

### **Stay Connected**
- **Newsletter**: Updates on new features and releases
- **Social Media**: Follow development progress
- **Community Events**: Participate in Islamic tech meetups
- **Scholar Network**: Connect with Islamic scholars and researchers

## 📄 **License & Legal**

This project is licensed under the **GNU Affero General Public License v3.0** - see the [LICENSE](LICENSE) file for details.

### **Islamic Content Guidelines**
- **Authenticity**: All content must be from reliable Islamic sources
- **Respect**: Follow Islamic etiquette in all interactions
- **Accuracy**: Maintain high standards of Islamic scholarship
- **Inclusivity**: Welcome all Muslims regardless of school of thought

## 🙏 **Acknowledgments**

- **Islamic Scholars**: For guidance and content verification
- **Open Source Community**: For the amazing tools and frameworks
- **Design Community**: For beautiful Islamic-themed interfaces
- **Global Muslim Community**: For inspiration and support

---

**🌙 May Allah guide us all to the straight path and bless our efforts in sharing Islamic knowledge.**

**📧 Contact**: [team@islamwiki.org](mailto:team@islamwiki.org)  
**🌐 Website**: [https://islamwiki.org](https://islamwiki.org)  
**📱 Community**: [https://community.islamwiki.org](https://community.islamwiki.org)

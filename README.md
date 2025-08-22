# 🕌 IslamWiki - Authentic Islamic Knowledge Platform

**Version**: 0.0.2.1  
**Status**: Production Ready with Enhanced Wiki System 🚀  
**Last Updated**: 2025-08-22  

## 🎉 **What's New in Version 0.0.2.1**

### **🌟 Major Improvements**
- **Wiki-Focused Site**: Site now defaults to `/wiki` as the main focus
- **Dynamic Page Creation**: Any `/wiki/{page_name}` shows create option for missing pages
- **Enhanced Search System**: Unified search with IqraSearchExtension
- **Improved UI/UX**: Better readability and professional appearance
- **MediaWiki-Style Behavior**: Professional wiki platform experience

### **🔧 Technical Enhancements**
- **Fixed Routing Issues**: Resolved 404 errors and broken links
- **Improved Templates**: Better styling and responsive design
- **Enhanced Forms**: Pre-filled data and better user experience
- **Performance Optimization**: Faster page loading and better caching

---

## 🏛️ **Platform Overview**

IslamWiki is a **revolutionary hybrid platform** that combines the best features of MediaWiki, WordPress, and modern PHP while maintaining Islamic values and principles. The platform now focuses on **wiki functionality as its core feature**.

### **Core Philosophy**
- **Islamic-First**: Built with Islamic values and content validation
- **Hybrid Architecture**: MediaWiki + WordPress + Modern PHP
- **Performance Focus**: Optimized for speed and scalability
- **User Experience**: Professional, intuitive interface

---

## 🚀 **Current Features**

### **📚 Wiki System (Main Focus)**
- **Dynamic Page Creation**: Visit `/wiki/{page_name}` to create missing pages
- **Page Not Found Views**: Beautiful interfaces for non-existent pages
- **Quick Create Forms**: Inline creation with pre-filled data
- **Category Management**: Organized content structure
- **Template System**: Multiple page templates available

### **🔍 Enhanced Search System**
- **Unified Search**: Single `/search` endpoint for all content
- **IqraSearchExtension**: Advanced Islamic search engine
- **Multiple Content Types**: Wiki, Quran, Hadith, Scholars
- **Smart Filtering**: Category and type-based search
- **Live Suggestions**: Real-time search recommendations

### **🎨 Professional UI/UX**
- **Bismillah Skin**: Beautiful Islamic-themed interface
- **Responsive Design**: Works perfectly on all devices
- **Improved Readability**: High contrast and clear typography
- **Modern Components**: Professional buttons and forms
- **Accessibility**: WCAG 2.1 AA compliance

### **⚙️ System Management**
- **Admin Dashboard**: Complete system administration
- **User Management**: Role-based access control
- **Extension System**: WordPress-style plugin architecture
- **Skin System**: WordPress-style theme management
- **Performance Monitoring**: Built-in optimization tools

---

## 🏗️ **Architecture**

### **Hybrid Approach**
```
IslamWiki = MediaWiki + WordPress + Modern PHP
├── 📁 MediaWiki: Content management, versioning, collaborative editing
├── 📁 WordPress: Plugin system, theme system, user experience  
└── 📁 Modern PHP: Performance, security, developer experience
```

### **Core Systems (16 Islamic-Named)**
- **Asas** (Foundation) - Core foundation and dependency injection
- **Aman** (Security) - Comprehensive security framework
- **Sabil** (Path) - Advanced routing system
- **Nizam** (Order) - Main application system
- **Mizan** (Balance) - Database system
- **Iqra** (Read) - Islamic search engine
- **Bayan** (Explanation) - Content formatting
- **Safa** (Purity) - CSS framework
- **Marwa** (Excellence) - JavaScript framework
- **And 7 more systems...**

---

## 🚀 **Quick Start**

### **1. Access the Platform**
- **Main Site**: Visit `/wiki` (default landing page)
- **Create Pages**: Go to `/wiki/create` or visit any `/wiki/{page_name}`
- **Search Content**: Use the unified `/search` endpoint

### **2. Create Your First Wiki Page**
1. **Visit**: `/wiki/your-page-name`
2. **See**: "Page not found" view with create option
3. **Click**: "Create" button
4. **Fill**: Form with your content
5. **Save**: Your new wiki page

### **3. Use the Search System**
1. **Go to**: `/search`
2. **Enter**: Your search query
3. **Select**: Content type (Wiki, Quran, Hadith, etc.)
4. **View**: Comprehensive search results

---

## 🔧 **Installation & Setup**

### **Requirements**
- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: Required PHP extensions (see composer.json)

### **Quick Installation**
```bash
# Clone the repository
git clone https://github.com/islamwiki/islamwiki.git
cd islamwiki

# Install dependencies
composer install

# Set up environment
cp .env.example .env
# Edit .env with your database credentials

# Run migrations
php scripts/database/run_migrations.php

# Set permissions
chmod -R 755 storage/ var/ logs/
chmod -R 644 .env

# Access your site
# Site will default to /wiki
```

---

## 📚 **Documentation**

### **User Guides**
- **[Wiki User Guide](docs/guides/wiki-user-guide.md)** - How to use the wiki system
- **[Search Guide](docs/guides/search-guide.md)** - Using the search system
- **[Content Creation](docs/guides/content-creation.md)** - Creating and editing content

### **Developer Guides**
- **[Development Guide](docs/guides/development.md)** - Development practices
- **[Extension Development](docs/extensions/development.md)** - Creating extensions
- **[Skin Development](docs/skins/development.md)** - Creating skins
- **[API Documentation](docs/api/overview.md)** - API reference

### **Architecture Documentation**
- **[System Architecture](docs/architecture/overview.md)** - Complete system overview
- **[Core Systems](docs/architecture/core-systems.md)** - 16 Islamic-named systems
- **[Hybrid Architecture](docs/architecture/hybrid-architecture.md)** - Architecture philosophy

---

## 🔌 **Extensions & Skins**

### **Available Extensions**
- **WikiExtension**: Core wiki functionality
- **IqraSearchExtension**: Advanced search engine
- **QuranExtension**: Quran management
- **HadithExtension**: Hadith collections
- **DashboardExtension**: Admin dashboard
- **SafaSkinExtension**: Skin management system

### **Available Skins**
- **Bismillah**: Default Islamic-themed skin
- **Muslim**: Alternative skin option
- **Custom**: User-created skins

---

## 🧪 **Testing & Quality**

### **Code Quality**
- **PHP Standards**: PSR-12 compliance
- **Type Safety**: Strict typing with PHP 8.1+
- **Documentation**: Comprehensive PHPDoc coverage
- **Testing**: PHPUnit testing framework

### **Performance**
- **Response Time**: < 200ms for most pages
- **Caching**: Multi-level caching strategy
- **Database**: Optimized queries and indexing
- **Assets**: Minified and optimized CSS/JS

---

## 🤝 **Contributing**

### **How to Contribute**
1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Submit** a pull request

### **Development Standards**
- Follow Islamic naming conventions
- Use PSR-12 coding standards
- Include comprehensive documentation
- Write tests for new features

---

## 📄 **License**

This project is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

- **Source Code**: Available to all users
- **Network Use**: Triggers source code distribution
- **Modifications**: Must be licensed under AGPL-3.0
- **Attribution**: Original copyright notices preserved

---

## 🆘 **Support & Community**

### **Getting Help**
- **Documentation**: Comprehensive guides available
- **Issues**: Report bugs on GitHub
- **Discussions**: Community discussions
- **Wiki**: Platform documentation

### **Community Resources**
- **Islamic Values**: Built-in content validation
- **Scholar Verification**: Authenticated sources
- **Community Guidelines**: Content moderation tools
- **Educational Focus**: Learning and knowledge sharing

---

## 🎯 **Roadmap**

### **Version 0.0.2.x (Current)**
- ✅ **Wiki System**: Dynamic page creation and management
- ✅ **Search System**: Unified search with IqraSearchExtension
- ✅ **UI/UX**: Professional interface and improved readability
- 🔄 **Performance**: Ongoing optimization and caching

### **Version 0.0.3.x (Next)**
- 📋 **Advanced Wiki Features**: Version control, collaborative editing
- 📋 **Content Management**: Enhanced content creation tools
- 📋 **Community Features**: User contributions and discussions
- 📋 **Mobile Applications**: Mobile-optimized interfaces

### **Version 0.1.x (Future)**
- 📋 **AI Integration**: Machine learning for content recommendations
- 📋 **Blockchain**: Content authenticity verification
- 📋 **Microservices**: Scalable service architecture
- 📋 **Global Deployment**: Multi-region and multi-language support

---

## 📞 **Contact & Links**

- **Website**: [IslamWiki.org](https://islamwiki.org)
- **GitHub**: [github.com/islamwiki/islamwiki](https://github.com/islamwiki/islamwiki)
- **Documentation**: [docs.islamwiki.org](https://docs.islamwiki.org)
- **Community**: [community.islamwiki.org](https://community.islamwiki.org)

---

**🏛️ IslamWiki - Empowering Islamic Knowledge Through Technology**  
**Version 0.0.2.1** | **Status**: Production Ready with Enhanced Wiki System 🚀 | **Focus**: Wiki-First Platform with Professional UI/UX

---

**Last Updated**: 2025-08-22  
**Version**: 0.0.2.1  
**Author**: IslamWiki Development Team  
**License**: AGPL-3.0

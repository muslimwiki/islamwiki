# IslamWiki

A comprehensive Islamic knowledge platform built with modern PHP and Islamic design principles.

## 🚀 Latest Updates (v0.0.59)

### 📚 **Complete Extension Documentation System**

- **Professional Documentation**: All 8 extensions now have comprehensive documentation
- **Technical Architecture**: Detailed internal workings explanation for each extension
- **Complete Version Control**: Strict versioning (0.0.1 → 0.0.2) across all extensions
- **Independent Documentation**: Each extension has complete docs folder for offline use
- **Professional Standards**: README, CHANGELOG, RELEASE notes, and technical docs

### 🔧 **Extension Documentation Status**

All extensions now include:
- **README.md**: Comprehensive overview and usage guide
- **CHANGELOG.md**: Complete version history and change tracking
- **extension.json**: Updated version numbers (0.0.2)
- **docs/ folder**: Professional documentation structure
  - **RELEASE-0.0.2.md**: Detailed release notes
  - **TECHNICAL_ARCHITECTURE.md**: How the extension works internally

### 📋 **Complete Extension List**

| Extension | Version | Status | Documentation |
|-----------|---------|---------|---------------|
| **LanguageSwitch** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **SalahTime** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **HadithExtension** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **QuranExtension** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **HijriCalendar** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **MarkdownDocsViewer** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **GitIntegration** | 0.0.2 | ✅ Complete | ✅ Full docs |
| **EnhancedMarkdown** | 0.0.2 | ✅ Complete | ✅ Full docs |

### 🧭 **Enhanced Navigation System (v0.0.58)**

- **Full-Width Layout**: Header, navigation, and footer now span the full viewport width
- **Comprehensive Navigation**: All available pages included in organized dropdown menus
- **Professional Design**: Clean, modern navigation with Islamic green theme
- **Responsive Layout**: Navigation adapts perfectly to all screen sizes
- **Complete Page Coverage**: Home, Quran, Hadith, Wiki, Sciences, Community, Docs, Bayan, About & Help

### 🎯 **Complete Page Coverage**

- **Home**: Main landing page
- **Quran**: Browse, Search, Juz, Page views with dropdown
- **Hadith**: Browse, Search, Collections with dropdown
- **Wiki**: All Pages, Create, Recent Changes with dropdown
- **Sciences**: Fiqh, Aqeedah, Tasawwuf with dropdown
- **Community**: Hub, Users, Activity, Contribute with dropdown
- **Docs**: Documentation, Architecture, Guides with dropdown
- **Bayan**: Knowledge Graph, Search, Create with dropdown
- **About & Help**: About, Calendar, Prayer Times, Dashboard with dropdown

### 🔐 **Authentication System (v0.0.57)**

- **Login & Register Routes**: Fully functional authentication endpoints working correctly
- **Service Provider Integration**: All required services properly registered and working
- **Container Management**: Fixed dependency injection container issues
- **Session Management**: Working session handling for authenticated users
- **Route Protection**: Proper access control for protected routes

### 🌐 **RTL (Right-to-Left) Language Support (v0.0.57)**

- **Language Toggle Button**: Functional language toggle in header navigation
- **Arabic RTL Layout**: Complete RTL support for Arabic language content
- **Dynamic Direction Switching**: Seamless switching between LTR and RTL
- **Persistent Language Preference**: User language choice saved automatically
- **Mobile RTL Support**: Responsive RTL layout across all devices

### 🛠️ **Technical Improvements**

- **Service Provider Architecture**: Proper registration and boot sequence
- **Container Interface**: Standardized on AsasContainer throughout
- **Error Handling**: Improved error handling and graceful fallbacks
- **Code Quality**: PSR-12 standards and proper type hints

## 🌟 **Features**

### 📚 **Islamic Content**
- **Quran Integration**: Complete Quran text with search and navigation
- **Hadith Database**: Comprehensive hadith collection with authentication
- **Islamic Sciences**: Academic content and research tools
- **Prayer Times**: Real-time prayer time calculations
- **Islamic Calendar**: Advanced Islamic calendar functionality

### 👥 **User Management**
- **Authentication**: ✅ Secure login and registration system (FIXED)
- **User Profiles**: ✅ Personal profile management (WORKING)
- **Settings**: ✅ User preferences and skin customization (WORKING)
- **Community**: User interaction and collaboration
- **Dashboard**: ✅ User dashboard with statistics (WORKING)

### 🎨 **Modern Design**
- **Bismillah Skin**: Beautiful modern Islamic theme with RTL support
- **Responsive Design**: Works perfectly on all devices
- **Islamic Typography**: Professional Arabic and English fonts
- **Smooth Animations**: Modern transitions and effects
- **RTL Layout**: Complete right-to-left text support

### 🔍 **Advanced Search**
- **Iqra Search Engine**: Intelligent Islamic content search
- **Multi-language Support**: Arabic and English search
- **Advanced Filtering**: Content type and category filtering
- **Search Suggestions**: Smart search recommendations

### 🔌 **Extension System**
- **Professional Extensions**: 8 fully documented extensions
- **Modular Architecture**: Easy to add and remove functionality
- **Comprehensive Documentation**: Each extension has complete docs
- **Version Control**: Strict semantic versioning across all extensions
- **Technical Architecture**: Detailed internal workings documentation

## 🛠️ **Technology Stack**

- **Backend**: PHP 8.1+ with custom Islamic-named framework
- **Database**: MySQL with Islamic content schema
- **Frontend**: Modern CSS with Islamic design principles
- **JavaScript**: Custom ZamZam.js framework
- **Templating**: Twig with Islamic theme system
- **Security**: CSRF protection and secure session management
- **Extensions**: Modular extension system with comprehensive documentation

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management

### **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/islamwiki/islamwiki.git
   cd islamwiki
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   ```bash
   cp LocalSettings.example.php LocalSettings.php
   # Edit LocalSettings.php with your database credentials
   ```

4. **Run database migrations**
   ```bash
   php scripts/database/setup_database.php
   ```

5. **Create admin user**
   ```bash
   php scripts/database/create_sample_data.php
   ```

6. **Set up web server**
   - Point document root to `public/` directory
   - Ensure `storage/` directory is writable

### **Default Login**
- **Username**: `admin`
- **Password**: `password`

## 🔌 **Extensions**

IslamWiki features a comprehensive extension system with professional documentation:

### **Core Extensions**
- **LanguageSwitch**: Advanced language switching with RTL support
- **SalahTime**: Comprehensive prayer time calculations
- **HadithExtension**: Complete hadith database and search
- **QuranExtension**: Full Quran text with translations

### **Utility Extensions**
- **HijriCalendar**: Advanced Islamic calendar functionality
- **MarkdownDocsViewer**: Professional markdown rendering
- **GitIntegration**: Version control and collaboration tools
- **EnhancedMarkdown**: Advanced markdown processing

### **Extension Documentation**
Each extension includes:
- **Complete README**: Installation, configuration, and usage
- **Detailed Changelog**: Version history and change tracking
- **Release Notes**: Comprehensive release information
- **Technical Architecture**: Internal workings explanation
- **API Reference**: Complete endpoint documentation

## 🎨 **Skins**

IslamWiki supports multiple skins with Islamic design principles:

### **Bismillah Skin (Default)**
- Modern gradient design
- Professional typography
- Smooth animations
- Responsive layout
- Complete RTL support

### **Muslim Skin**
- MediaWiki-inspired design
- Traditional Islamic elements
- Clean and functional layout

## 🔧 **Development**

### **Project Structure**
```text
islamwiki/
├── src/                    # Core application code
│   ├── Core/              # Framework core components
│   ├── Http/              # HTTP layer (controllers, middleware)
│   ├── Models/            # Data models
│   └── Providers/         # Service providers
├── extensions/            # Extension system
│   ├── LanguageSwitch/    # Language switching extension
│   ├── SalahTime/         # Prayer time extension
│   ├── HadithExtension/   # Hadith database extension
│   ├── QuranExtension/    # Quran text extension
│   ├── HijriCalendar/     # Islamic calendar extension
│   ├── MarkdownDocsViewer/# Markdown viewing extension
│   ├── GitIntegration/    # Version control extension
│   └── EnhancedMarkdown/  # Advanced markdown extension
├── resources/             # Views and assets
├── skins/                # Skin system
├── storage/              # Application storage
├── docs/                 # Documentation
└── public/               # Web server document root
```

### **Key Components**

#### **Islamic-Named Framework**
- **NizamApplication**: Main application class (نظام - System)
- **AsasContainer**: Dependency injection container (أساس - Foundation)
- **SabilRouting**: Routing system (سبيل - Path)
- **AmanSecurity**: Authentication system (أمان - Security)
- **WisalSession**: Session management (وصال - Connection)

#### **Content Management**
- **IqraSearch**: Search engine (إقرأ - Read)
- **BayanFormatter**: Content formatting (بيان - Explanation)
- **RihlahCaching**: Caching system (رحلة - Journey)
- **SabrQueue**: Queue management (صبر - Patience)

#### **Extension System**
- **Modular Architecture**: Easy to add and remove functionality
- **Professional Documentation**: Complete docs for each extension
- **Version Control**: Strict semantic versioning
- **Technical Architecture**: Detailed internal workings

## 📖 **Documentation**

Comprehensive documentation is available in the `docs/` directory:

- [Architecture Overview](docs/architecture/overview.md)
- [Development Guide](docs/developer/)
- [API Documentation](docs/api/)
- [Skin Development](docs/skins/)
- [Deployment Guide](docs/deployment/)
- [Extension Documentation](docs/extensions/)

### **Extension Documentation**
Each extension has complete documentation in its own `docs/` folder:
- **Installation guides** and configuration
- **API reference** and usage examples
- **Technical architecture** and internal workings
- **Performance optimization** and best practices
- **Security implementation** and guidelines

## 🤝 **Contributing**

We welcome contributions from the Islamic community! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### **Extension Development**
- **Follow established patterns** from existing extensions
- **Include comprehensive documentation** in docs folder
- **Follow semantic versioning** (0.0.1, 0.0.2, etc.)
- **Update changelog** and release notes
- **Document technical architecture** and internal workings

## 📄 **License**

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- Islamic scholars and researchers for content guidance
- Open source community for technical inspiration
- Contributors and maintainers for their dedication
- Extension developers for comprehensive documentation

## 📞 **Support**

- **Documentation**: [docs/](docs/)
- **Extension Docs**: Each extension has complete documentation
- **Issues**: [GitHub Issues](https://github.com/islamwiki/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/islamwiki/islamwiki/discussions)

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building Islamic knowledge for the digital age with professional documentation.*

---

## 📊 **Project Status**

- **Current Version**: 0.0.59
- **Extensions**: 8 fully documented extensions
- **Documentation**: Complete professional documentation system
- **Version Control**: Strict semantic versioning across all components
- **Quality**: Professional-grade code and documentation standards

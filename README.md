# IslamWiki

A comprehensive Islamic knowledge platform built with modern PHP and Islamic design principles.

## 🚀 Latest Updates (v0.0.52)

### 🧭 MediaWiki-style Namespaces

- **Special:** `Special:SpecialPages`, `Special:AllPages`
- **Shorthand Redirects:** `Quran:{query}` → `/quran/search?q={query}`, `Hadith:{query}` → `/hadith/search?q={query}`
- **Namespace Manager:** Centralized canonical namespace and alias handling
- **Wiki Redirects:** `/wiki/{slug}` recognizes prefixed titles and redirects accordingly

### ✨ Special Pages (Full-width)
- **Special:SpecialPages**: Modern full-width hub with categories
- **Special:AllPages**: Namespace filters and full-width layout
- **Maintenance Reports**: Placeholders for broken redirects, dead-end pages, etc.

### 🛠️ Technical Enhancements
- **Modular CSS**: Better organized styles with section-specific modules
- **Color System**: New CSS custom properties for consistent theming
- **Animation Framework**: Standardized transitions and effects
- **Grid Layout**: Improved content organization with CSS Grid
- **Responsive Framework**: Enhanced mobile-first design system
- **Calendar Page**: Fixed 500 Internal Server Error - now fully functional
- **Community Page**: Fixed 500 Internal Server Error - now fully functional
- **All Main Pages**: Quran, Hadith, Salah, Calendar, and Community pages all working
- **Error Recovery**: Enhanced error handling prevents application crashes

### ✅ Header Layout Redesign
- **New Header Structure**: Logo, search bar, and auth buttons now on same line
- **Improved Navigation**: Primary navigation moved to secondary bar for better organization
- **Full Width Layout**: All pages now use full available width for better content display
- **Enhanced Search**: Search bar is more prominent and better positioned
- **Better Spacing**: Added proper padding for improved visual balance

### 🎨 UI/UX Improvements
- **Streamlined Design**: Cleaner, more intuitive header layout
- **Better Accessibility**: Improved button and link positioning
- **Mobile Responsive**: Enhanced mobile layout for new header structure
- **Visual Hierarchy**: Better organization of navigation elements

## 🌟 Features

### 📚 Islamic Content
- **Quran Integration**: Complete Quran text with search and navigation
- **Hadith Database**: Comprehensive hadith collection with authentication
- **Islamic Sciences**: Academic content and research tools
- **Prayer Times**: Real-time prayer time calculations
- **Islamic Calendar**: Advanced Islamic calendar functionality

### 👥 User Management
- **Authentication**: Secure login and registration system
- **User Profiles**: Personal profile management
- **Settings**: User preferences and skin customization
- **Community**: User interaction and collaboration

### 🎨 Modern Design
- **Bismillah Skin**: Beautiful modern Islamic theme
- **Responsive Design**: Works perfectly on all devices
- **Islamic Typography**: Professional Arabic and English fonts
- **Smooth Animations**: Modern transitions and effects

### 🔍 Advanced Search
- **Iqra Search Engine**: Intelligent Islamic content search
- **Multi-language Support**: Arabic and English search
- **Advanced Filtering**: Content type and category filtering
- **Search Suggestions**: Smart search recommendations

## 🛠️ Technology Stack

- **Backend**: PHP 8.1+ with custom Islamic-named framework
- **Database**: MySQL with Islamic content schema
- **Frontend**: Modern CSS with Islamic design principles
- **JavaScript**: Custom ZamZam.js framework
- **Templating**: Twig with Islamic theme system
- **Security**: CSRF protection and secure session management

## 🚀 Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management

### Installation

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

### Default Login
- **Username**: `admin`
- **Password**: `password`

## 🎨 Skins

IslamWiki supports multiple skins with Islamic design principles:

### Bismillah Skin (Default)
- Modern gradient design
- Professional typography
- Smooth animations
- Responsive layout

### Muslim Skin
- MediaWiki-inspired design
- Traditional Islamic elements
- Clean and functional layout

## 🔧 Development

### Project Structure
```text
islamwiki/
├── src/                    # Core application code
│   ├── Core/              # Framework core components
│   ├── Http/              # HTTP layer (controllers, middleware)
│   ├── Models/            # Data models
│   └── Providers/         # Service providers
├── resources/             # Views and assets
├── skins/                # Skin system
├── storage/              # Application storage
├── docs/                 # Documentation
└── public/               # Web server document root
```

### Key Components

#### Islamic-Named Framework
- **NizamApplication**: Main application class (نظام - System)
- **AsasContainer**: Dependency injection container (أساس - Foundation)
- **SabilRouting**: Routing system (سبيل - Path)
- **AmanSecurity**: Authentication system (أمان - Security)
- **WisalSession**: Session management (وصال - Connection)

#### Content Management
- **IqraSearch**: Search engine (إقرأ - Read)
- **BayanFormatter**: Content formatting (بيان - Explanation)
- **RihlahCaching**: Caching system (رحلة - Journey)
- **SabrQueue**: Queue management (صبر - Patience)

## 📖 Documentation

Comprehensive documentation is available in the `docs/` directory:

- [Architecture Overview](docs/architecture/overview.md)
- [Development Guide](docs/developer/)
- [API Documentation](docs/api/)
- [Skin Development](docs/skins/)
- [Deployment Guide](docs/deployment/)

## 🤝 Contributing

We welcome contributions from the Islamic community! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Islamic scholars and researchers for content guidance
- Open source community for technical inspiration
- Contributors and maintainers for their dedication

## 📞 Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/islamwiki/islamwiki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/islamwiki/islamwiki/discussions)

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building Islamic knowledge for the digital age.*

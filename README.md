# IslamWiki - Islamic Knowledge Platform

[![Version](https://img.shields.io/badge/version-0.0.60-brightgreen.svg)](https://github.com/islamwiki/islamwiki)
[![Status](https://img.shields.io/badge/status-production%20ready-brightgreen.svg)](https://islamwiki.org)
[![Languages](https://img.shields.io/badge/languages-English%20%7C%20Arabic-blue.svg)](https://islamwiki.org)
[![RTL Support](https://img.shields.io/badge/RTL-Arabic%20Support-orange.svg)](https://islamwiki.org)

**A comprehensive Islamic knowledge platform with complete Arabic language support and RTL layout**

---

## 🌟 **NEW IN VERSION 0.0.60**

### 🎉 **Complete Arabic Language System**
- **100% Arabic UI Translation** - Full interface in Arabic with Islamic terminology
- **Professional RTL Support** - Right-to-left layout for Arabic text
- **Cultural Authenticity** - Respectful Islamic cultural adaptation
- **Seamless Language Switching** - English ↔ Arabic without page refresh

### 🌍 **Internationalization (i18n) & Localization (l10n)**
- **Modern Translation System** - JSON-based translation files
- **Multi-Language Foundation** - Ready for Urdu, Turkish, Indonesian, Malay, Persian, Hebrew
- **Language Service Architecture** - Centralized language management
- **Session-Based Persistence** - User language preference maintained

---

## 🎯 **Overview**

IslamWiki is a comprehensive Islamic knowledge platform designed to serve the global Muslim community. Built with modern web technologies and Islamic cultural sensitivity, it provides a robust foundation for Islamic content, education, and community engagement.

### **Key Features**
- **📚 Islamic Content Management** - Quran, Hadith, Islamic sciences, and community content
- **🌍 Multi-Language Support** - English and Arabic with RTL support
- **🔐 Secure Authentication** - User management with Islamic cultural considerations
- **📱 Responsive Design** - Mobile-first approach for global accessibility
- **⚡ Modern Architecture** - PHP 8+, Twig templates, modern CSS framework
- **🛡️ Security First** - Comprehensive security and privacy protection

---

## 🌍 **Language Support**

### **Currently Available**
| Language | Code | RTL | Status | Coverage |
|----------|------|-----|--------|----------|
| **English** | `en` | ❌ | ✅ **100%** | Complete UI |
| **Arabic** | `ar` | ✅ | ✅ **100%** | Complete UI |

### **Coming Soon**
- **Urdu** (اردو) - RTL support
- **Turkish** (Türkçe) - LTR support  
- **Indonesian** (Bahasa Indonesia) - LTR support
- **Malay** (Bahasa Melayu) - LTR support
- **Persian** (فارسی) - RTL support
- **Hebrew** (עברית) - RTL support

---

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer
- Apache/Nginx web server

### **Installation**
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
php scripts/database/migrate.php

# Create admin user
php scripts/create_admin_user.php

# Start the application
php -S localhost:8000 -t public/
```

### **Language Configuration**
1. **Access Settings** → Language Preference
2. **Select Language** (English/Arabic)
3. **Update Language** - Changes apply immediately
4. **RTL Support** - Arabic automatically switches to right-to-left layout

---

## 🔧 **Architecture**

### **Core Systems**
- **Asas Container** - Dependency injection container (أساس - Foundation)
- **Aman Security** - Authentication and authorization (أمان - Security)
- **Wisal Session** - Session management (وصال - Connection)
- **Siraj API** - API management and routing (سراج - Light/Lamp)
- **Shahid Logging** - Comprehensive logging (شاهد - Witness)

### **Language System**
- **LanguageServiceProvider** - Core language services
- **TranslationService** - JSON-based translation management
- **TwigTranslationExtension** - Template translation functions
- **RTL Support** - Professional right-to-left layout

### **Frontend Framework**
- **Safa CSS** - Islamic-themed CSS framework (صافا - Pure)
- **ZamZam.js** - Reactive JavaScript framework (زمزم - Sacred Well)
- **Twig Templates** - Flexible templating with inheritance
- **Responsive Design** - Mobile-first approach

---

## 📚 **Documentation**

### **User Guides**
- [Language Settings](docs/user-guide/language-settings.md)
- [Getting Started](docs/user-guide/getting-started.md)
- [Content Creation](docs/user-guide/content-creation.md)
- [Community Features](docs/user-guide/community-features.md)

### **Developer Documentation**
- [Architecture Overview](docs/architecture/README.md)
- [Extension Development](docs/developer/extension-system.md)
- [Translation Guide](docs/developer/translation-guide.md)
- [API Reference](docs/api/README.md)

### **Release Notes**
- [Version 0.0.60](docs/releases/RELEASE_NOTES_0.0.60.md) - Arabic i18n/l10n System
- [Version 0.0.50](docs/releases/RELEASE_NOTES_0.0.50.md) - Core Systems
- [Complete History](CHANGELOG.md)

---

## 🌟 **Features**

### **Content Management**
- **Quran Integration** - Complete Quran text with translations
- **Hadith Collection** - Authentic Hadith with verification
- **Islamic Sciences** - Comprehensive Islamic knowledge base
- **Community Wiki** - User-generated Islamic content
- **Scholar Profiles** - Islamic scholar information and verification

### **User Experience**
- **Multi-Language Interface** - English and Arabic with RTL support
- **Responsive Design** - Works on all devices and screen sizes
- **Islamic Design Theme** - Culturally appropriate visual design
- **Accessibility** - WCAG compliant for inclusive access
- **Performance** - Fast loading and smooth user experience

### **Security & Privacy**
- **User Authentication** - Secure login and registration
- **Role-Based Access** - Granular permission system
- **Content Moderation** - Community-driven content quality
- **Privacy Protection** - User data protection and control
- **CSRF Protection** - Cross-site request forgery prevention

---

## 🤝 **Contributing**

We welcome contributions from the global Muslim community and developers worldwide.

### **How to Contribute**
1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Submit** a pull request

### **Translation Contributions**
- **Arabic**: Complete and verified
- **Other Languages**: Welcome community contributions
- **Cultural Sensitivity**: Islamic cultural authenticity required
- **Quality Standards**: Professional translation quality

### **Development Areas**
- **New Languages** - Translation and RTL support
- **Features** - Islamic content and community tools
- **Documentation** - User guides and developer docs
- **Testing** - Quality assurance and bug reports

---

## 📊 **Project Status**

### **Completed Systems** ✅
- **Core Architecture** - Complete foundation
- **Authentication System** - User management and security
- **Content Management** - Wiki, Quran, Hadith integration
- **Multi-Language Support** - English and Arabic with RTL
- **Extension Framework** - Plugin system for features
- **Skin System** - Islamic-themed visual designs
- **API System** - RESTful API with authentication
- **Database System** - Migration and management tools

### **In Progress** 🔄
- **Additional Languages** - Urdu, Turkish, Indonesian support
- **Advanced RTL** - Complex RTL layout improvements
- **Content Localization** - Multi-language content management

### **Planned** 📋
- **AI Integration** - Machine learning for Islamic content
- **Mobile Applications** - Native mobile apps
- **Voice Interface** - Spoken language support
- **Advanced Analytics** - Islamic-specific metrics

---

## 🌍 **Community**

### **Global Reach**
- **Muslim Community** - Serving Muslims worldwide
- **Islamic Scholars** - Academic and religious expertise
- **Developers** - Open source contribution
- **Content Creators** - Islamic knowledge sharing

### **Cultural Values**
- **Islamic Authenticity** - Accurate and verified content
- **Cultural Respect** - Sensitivity to diverse traditions
- **Community Focus** - User-driven development
- **Global Accessibility** - Worldwide access and support

---

## 📄 **License**

This project is licensed under the **AGPL-3.0 License** - see the [LICENSE](LICENSE) file for details.

### **Open Source**
- **Free to Use** - No licensing fees
- **Community Driven** - Open development process
- **Islamic Values** - Aligned with Islamic principles
- **Global Impact** - Serving the worldwide Muslim community

---

## 📞 **Contact & Support**

### **Community Support**
- **GitHub Issues** - Bug reports and feature requests
- **Discussions** - Community questions and answers
- **Documentation** - Comprehensive guides and tutorials
- **Contributing** - How to get involved

### **Development Team**
- **Core Team** - IslamWiki Development Team
- **Contributors** - Global developer community
- **Islamic Scholars** - Content verification and guidance
- **Community Members** - User feedback and testing

---

## 🎉 **Acknowledgments**

- **Islamic Scholars** - For content verification and guidance
- **Open Source Community** - For tools and frameworks
- **Global Muslim Community** - For feedback and support
- **Contributors** - For code, translations, and documentation

---

**IslamWiki** - Knowledge for the Ummah  
**Version:** 0.0.60  
**Status:** ✅ **Production Ready**  
**Languages:** English, Arabic (RTL)  
**License:** AGPL-3.0

*"Seek knowledge from the cradle to the grave" - Prophet Muhammad ﷺ*

# IslamWiki Changelog

## 🎯 **Overview**

This document tracks all changes, improvements, and fixes made to IslamWiki across all versions. It follows the [Keep a Changelog](https://keepachangelog.com/) format and provides detailed information about each release.

---

## 📋 **Changelog Format**

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Features that will be removed in upcoming releases
- **Removed**: Features that have been removed
- **Fixed**: Bug fixes
- **Security**: Security improvements and fixes

---

## [0.0.1.0] - 2025-08-19

### 🎯 **Documentation Restructuring & New Versioning Strategy**

#### **Added**
- **Complete documentation restructuring** with comprehensive README files in every directory
- **New 4-part versioning strategy** (0.0.0.x, 0.0.x.x, 0.x.x.x, x.x.x.x)
- **Architecture corrections** fixing major inconsistencies in system layer organization
- **File naming standardization** moving from inconsistent UPPERCASE to consistent lowercase
- **Extension versioning independence** documentation and guidelines
- **Modern development practices** documentation with PHP 8.1+ features
- **Comprehensive structure** with logical organization and navigation

#### **Changed**
- **Versioning system** from 3-part to 4-part for better phase separation
- **File organization** from inconsistent to standardized structure
- **Architecture documentation** from inconsistent to corrected and aligned
- **All internal links** fixed and working properly
- **Version references** updated throughout all documentation

#### **Fixed**
- **Architecture inconsistencies** between Iqra (search engine) and Bayan (content reading)
- **Broken internal links** in main documentation
- **File naming inconsistencies** throughout the documentation
- **Version numbering inconsistencies** in documentation headers
- **Cross-reference errors** between different documentation sections

#### **Architecture Corrections**
- **Iqra** correctly placed as Islamic search engine in Infrastructure Layer
- **Bayan** correctly placed as content reading/consumption in Presentation Layer
- **System responsibilities** clearly defined and aligned across all layers
- **Data flow** corrected to reflect proper system interactions

#### **Documentation Structure**
- **Every directory** now has comprehensive README.md files
- **Consistent formatting** and Islamic naming conventions throughout
- **Clear navigation** between different documentation sections
- **Professional structure** following modern documentation standards

#### **Versioning Strategy**
- **0.0.0.x**: Testing & bug fixes (0.0.0.62 = QuranUI Enhancement)
- **0.0.1.x**: Restructuring & major changes (0.0.1.0 = Documentation restructuring)
- **0.0.2.x**: Feature development (Quran, Hadith, Forums, etc.)
- **0.1.x.x**: Stabilization (stable architecture)
- **x.x.x.x**: Production releases

#### **Next Phase (0.0.1.1)**
- **Site restructuring** begins to match new architecture
- **Core systems implementation** with Islamic naming conventions
- **Database restructuring** to align with new architecture
- **Extension system modernization** to match new standards

---

## [0.0.0.62] - 2025-08-19

### **Added**
- Extension system implementation
- Dashboard extension
- Enhanced markdown extension
- Git integration extension
- Hadith extension
- Hijri calendar extension
- Markdown docs viewer extension
- Quran extension
- Salah time extension
- Translator extension

### **Changed**
- Enhanced extension management
- Improved extension architecture
- Better extension documentation

### **Fixed**
- Extension loading issues
- Extension compatibility problems
- Extension documentation errors

---

## [0.0.18] - 2025-07-30

### **Added**
- Hybrid configuration system
- LocalSettings.php (MediaWiki-style)
- IslamSettings.php (Islamic overrides)
- Configuration manager class
- Environment variable support
- Configuration validation

### **Changed**
- Configuration loading priority
- Configuration management approach
- Configuration documentation

### **Fixed**
- Configuration conflicts
- Configuration loading errors
- Configuration validation issues

---

## [0.0.17] - 2025-07-29

### **Added**
- Search and discovery system
- Comprehensive search across Islamic content
- Full-text search indexes
- Search analytics and caching
- Advanced filtering and suggestions

### **Changed**
- Search algorithm improvements
- Search performance optimization
- Search result ranking

### **Fixed**
- Search accuracy issues
- Search performance problems
- Search result relevance

---

## [0.0.16] - 2025-07-28

### **Added**
- Salah times integration
- Salah time calculations
- Salah time API
- Salah time widgets
- Salah time customization

### **Changed**
- Salah time accuracy
- Salah time calculation methods
- Salah time display

### **Fixed**
- Salah time calculation errors
- Salah time display issues
- Salah time API problems

---

## [0.0.15] - 2025-07-27

### **Added**
- Islamic calendar integration
- Hijri calendar support
- Gregorian-Hijri conversion
- Calendar widgets
- Calendar customization

### **Changed**
- Calendar calculation accuracy
- Calendar display methods
- Calendar integration

### **Fixed**
- Calendar calculation errors
- Calendar display issues
- Calendar conversion problems

---

## [0.0.14] - 2025-07-26

### **Added**
- Hadith integration system
- Hadith collections
- Hadith authenticity grading
- Hadith search
- Hadith management

### **Changed**
- Hadith data structure
- Hadith search algorithms
- Hadith display methods

### **Fixed**
- Hadith data import issues
- Hadith search problems
- Hadith display errors

---

## [0.0.13] - 2025-07-25

### **Added**
- Quran integration system
- Complete Quran text
- Multiple translations
- Recitation support
- Quran search

### **Changed**
- Quran data structure
- Quran search algorithms
- Quran display methods

### **Fixed**
- Quran data import issues
- Quran search problems
- Quran display errors

---

## [0.0.12] - 2025-07-24

### **Added**
- Islamic database implementation
- Quran database (13 tables)
- Hadith database (13 tables)
- Scholar database (13 tables)
- Wiki database for general content
- Islamic database manager

### **Changed**
- Database architecture
- Database connection management
- Database performance

### **Fixed**
- Database connection issues
- Database performance problems
- Database security issues

---

## [0.0.11] - 2025-07-23

### **Added**
- Database connection strategy
- Separate database connections
- Database connection pooling
- Database performance optimization
- Database security enhancements

### **Changed**
- Database connection approach
- Database performance methods
- Database security measures

### **Fixed**
- Database connection errors
- Database performance issues
- Database security vulnerabilities

---

## [0.0.10] - 2025-07-22

### **Added**
- Enhanced error handling
- 404 error page with Twig
- 500 error page with debug info
- Copy button for debug information
- Error page styling

### **Changed**
- Error handling approach
- Error page design
- Error page functionality

### **Fixed**
- 500 error page debug info display
- Error page styling issues
- Error page functionality problems

---

## [0.0.9] - 2025-07-21

### **Added**
- SabilRouting system
- Inline route definitions
- Controller support
- Middleware stack
- Route caching

### **Changed**
- Routing architecture
- Route definition approach
- Route performance

### **Fixed**
- Routing issues
- Route definition problems
- Route performance issues

---

## [0.0.8] - 2025-07-20

### **Added**
- Twig template system
- Template inheritance
- Template caching
- Template components
- Template layouts

### **Changed**
- Template system approach
- Template performance
- Template functionality

### **Fixed**
- Template loading issues
- Template performance problems
- Template functionality errors

---

## [0.0.7] - 2025-07-19

### **Added**
- Service provider system
- Dependency injection
- Service container
- Service registration
- Service bootstrapping

### **Changed**
- Service architecture
- Service management
- Service performance

### **Fixed**
- Service loading issues
- Service dependency problems
- Service performance issues

---

## [0.0.6] - 2025-07-18

### **Added**
- Authentication system
- User management
- Role-based access control
- Session management
- Security features

### **Changed**
- Authentication approach
- Security measures
- User management

### **Fixed**
- Authentication issues
- Security vulnerabilities
- User management problems

---

## [0.0.5] - 2025-07-17

### **Added**
- Basic content management
- Page creation
- Page editing
- Page history
- Content versioning

### **Changed**
- Content management approach
- Content editing methods
- Content versioning

### **Fixed**
- Content creation issues
- Content editing problems
- Content versioning errors

---

## [0.0.4] - 2025-07-16

### **Added**
- Basic skin system
- Default skin (Bismillah)
- Alternative skin (Muslim)
- Skin switching
- Skin customization

### **Changed**
- Skin architecture
- Skin management
- Skin functionality

### **Fixed**
- Skin loading issues
- Skin switching problems
- Skin customization errors

---

## [0.0.3] - 2025-07-15

### **Added**
- Basic extension system
- Extension loading
- Extension management
- Extension hooks
- Extension API

### **Changed**
- Extension architecture
- Extension management
- Extension functionality

### **Fixed**
- Extension loading issues
- Extension management problems
- Extension functionality errors

---

## [0.0.2] - 2025-07-14

### **Added**
- Basic framework structure
- Core components
- HTTP handling
- Request/Response objects
- Basic routing

### **Changed**
- Framework architecture
- Core component design
- Basic functionality

### **Fixed**
- Framework loading issues
- Core component problems
- Basic functionality errors

---

## [0.0.1] - 2025-07-13

### **Added**
- Initial project setup
- Basic project structure
- Development environment
- Documentation framework
- Version control setup

### **Changed**
- Project initialization
- Basic structure
- Development setup

### **Fixed**
- Initial setup issues
- Structure problems
- Development environment errors

---

## 📚 **Version Information**

### **Version Numbering**
- **Major.Minor.Patch** format
- **Major**: Breaking changes
- **Minor**: New features, backward compatible
- **Patch**: Bug fixes, backward compatible

### **Release Schedule**
- **Patch releases**: Weekly or as needed
- **Minor releases**: Monthly
- **Major releases**: Quarterly or as needed

### **Support Policy**
- **Current version**: Full support
- **Previous version**: Security updates only
- **Older versions**: No support

---

## 🔄 **Migration Guide**

### **Upgrading Between Versions**
- **0.0.x to 0.0.y**: Minor version upgrades
- **0.0.x to 0.1.x**: Minor version upgrades
- **0.x.x to 1.0.x**: Major version upgrade

### **Breaking Changes**
- **0.0.19**: Extension system changes
- **0.0.18**: Configuration system changes
- **0.0.17**: Search system changes
- **0.0.16**: Salah time system changes
- **0.0.15**: Calendar system changes

### **Deprecation Notices**
- **0.0.20**: Some configuration keys will be deprecated
- **0.0.21**: Some extension methods will be deprecated
- **0.0.22**: Some skin methods will be deprecated

---

## 📞 **Support**

### **Documentation**
- **User Guide**: Basic usage instructions
- **Admin Guide**: Administration instructions
- **Developer Guide**: Development information
- **API Reference**: API documentation

### **Community**
- **Forum**: Community support forum
- **GitHub Issues**: Bug reports and feature requests
- **Discord**: Real-time community chat
- **Email Support**: Direct support contact

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Changelog:** Comprehensive Release History 
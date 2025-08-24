# IslamWiki Development and User Guides

## 📚 **Overview**

This directory contains comprehensive guides for developers and users of the IslamWiki platform. These guides cover everything from basic development practices to advanced architectural concepts, all while maintaining Islamic values and modern development standards.

## 🎯 **Guide Categories**

### **Development Guides**
- **[Development Guide](development.md)** - Complete development practices and modern PHP features
- **[Style Guide](style-guide.md)** - Coding standards, conventions, and best practices
- **[Versioning Strategy](versioning.md)** - Semantic versioning and release management
- **[Installation Guide](installation.md)** - Platform installation and setup instructions

### **Islamic Standards Guides**
- **[Islamic Naming Conventions](islamic-naming-conventions.md)** - Complete Islamic naming system for all components
- **[Islamic Naming Implementation](islamic-naming-implementation.md)** - Practical implementation of Islamic naming
- **[Islamic Terminology Standards](islamic-terminology-standards.md)** - Standard Islamic terms and usage

### **Organization and Structure**
- **[Organization Guide](organization.md)** - Project organization and file structure
- **[Naming Conventions](naming-conventions.md)** - General naming conventions and patterns

## 🏗️ **Core Development Topics**

### **Modern PHP Development**
- PHP 8.1+ features and best practices
- Strict typing and type declarations
- SOLID principles and clean architecture
- Dependency injection and service containers
- Comprehensive testing strategies

### **Islamic Naming System**
- 16 core Islamic systems with meaningful names
- File and directory naming conventions
- Class and method naming patterns
- Database naming standards
- Implementation strategies

### **Architecture and Design**
- Hybrid MediaWiki + WordPress + Modern PHP approach
- Clean architecture principles
- Domain-driven design
- Event-driven architecture
- Microservices-ready design

### **Quality Assurance**
- PSR-12 coding standards
- Automated code quality tools
- Comprehensive testing framework
- Code coverage requirements
- Security best practices

## 🔧 **Development Tools**

### **Code Quality Tools**
```bash
# PHPStan for static analysis
./vendor/bin/phpstan analyse src --level=8

# PHP CodeSniffer for coding standards
./vendor/bin/phpcs src --standard=PSR12

# PHP Mess Detector for code complexity
./vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
```

### **Testing Tools**
```bash
# PHPUnit for unit testing
./vendor/bin/phpunit --coverage-html coverage

# Pest for expressive testing
./vendor/bin/pest --coverage

# Codeception for acceptance testing
./vendor/bin/codecept run acceptance
```

## 📋 **Quick Reference**

### **Essential Standards**
- Always use `declare(strict_types=1);`
- Follow PSR-12 coding style
- Use Islamic naming for all components
- Include comprehensive PHPDoc
- Write tests for all functionality

### **Islamic Naming Examples**
```php
// ✅ Correct - Using Islamic naming
class ContainerContainer {}           // Container container
class SecurityAuthenticator {}       // Security authenticator
class SabilRouter {}             // Path router
class IqraSearch {}              // Search engine
class BayanFormatter {}          // Content formatter
```

### **File Naming Patterns**
- **PHP Classes**: `{IslamicName}{Component}.php` (e.g., `IqraSearch.php`)
- **CSS Files**: `safa-{component}.css` (e.g., `safa-navigation.css`)
- **JavaScript**: `marwa-{component}.js` (e.g., `marwa-theme-switcher.js`)
- **Database Tables**: `mizan_{table}` (e.g., `mizan_users`)

## 🚀 **Getting Started**

### **For New Developers**
1. Read the **[Style Guide](style-guide.md)** for coding standards
2. Study the **[Islamic Naming Conventions](islamic-naming-conventions.md)**
3. Review the **[Development Guide](development.md)** for modern practices
4. Check the **[Installation Guide](installation.md)** for setup

### **For Contributors**
1. Follow the **[Versioning Strategy](versioning.md)** for releases
2. Use the **[Organization Guide](organization.md)** for file structure
3. Implement **[Islamic Terminology Standards](islamic-terminology-standards.md)**
4. Follow the **[Style Guide](style-guide.md)** for consistency

## 📖 **Related Documentation**

- **[Architecture Overview](architecture/README.md)** - System architecture
- **[Development Standards](standards/README.md)** - Development standards
- **[Components Documentation](components/README.md)** - Core components
- **[Security Documentation](security/README.md)** - Security practices

## 📄 **License Information**

These guides are licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Guides Documentation Complete ✅ 
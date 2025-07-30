# Project Organization Guide

## Overview

This guide explains the organization structure of the Islam Wiki project and the reasoning behind our file organization decisions.

## 📁 Directory Structure

### Core Application Directories

#### **`src/` - Application Source Code**
```
src/
├── Core/           # Framework core components
│   ├── Application.php
│   ├── Container.php
│   ├── Database/   # Database abstraction layer
│   ├── Error/      # Error handling
│   ├── Http/       # HTTP request/response
│   ├── Logging/    # Logging system
│   ├── Routing/    # Routing system
│   ├── Session/    # Session management
│   ├── Support/    # Support classes
│   └── View/       # View rendering
├── Http/           # HTTP layer
│   ├── Controllers/    # Application controllers
│   └── Middleware/     # Request middleware
├── Models/         # Data models
└── Providers/      # Service providers
```

#### **`public/` - Web Root (Minimal)**
```
public/
├── index.php       # Main application entry point
├── .htaccess       # Apache configuration
└── (essential web files only)
```

**Rationale**: Keep web root minimal for security and performance. All test files moved to `tests/web/`.

#### **`resources/` - Application Resources**
```
resources/
└── views/          # Twig templates
    ├── auth/       # Authentication views
    ├── dashboard/  # Dashboard views
    ├── errors/     # Error pages
    ├── layouts/    # Base layouts
    ├── pages/      # Wiki page views
    └── profile/    # User profile views
```

### Documentation Organization

#### **`docs/` - Comprehensive Documentation**
```
docs/
├── plans/          # Development plans and roadmaps
├── guides/         # User and developer guides
├── architecture/   # System architecture docs
├── components/     # Component documentation
├── security/       # Security documentation
├── features/       # Feature documentation
├── deployment/     # Deployment guides
├── testing/        # Testing documentation
├── controllers/    # Controller documentation
├── models/         # Model documentation
├── views/          # View documentation
├── DATABASE_SETUP.md
└── Cursor_initial-prompt.md
```

**Rationale**: Centralized documentation with clear categorization for easy navigation.

### Scripts and Utilities

#### **`scripts/` - Organized by Purpose**
```
scripts/
├── database/       # Database operations
│   ├── migrate.php
│   ├── setup_database.php
│   └── create_sample_data.php
├── debug/          # Debugging tools
│   ├── debug-framework.php
│   ├── debug-routes.php
│   └── phpinfo.php
├── tests/          # Test utilities
│   └── (test helper scripts)
└── utils/          # Maintenance utilities
    ├── fix_*.php
    ├── check_*.php
    └── update_*.php
```

**Rationale**: Categorized scripts for easier maintenance and discovery.

### Testing Organization

#### **`tests/` - Comprehensive Testing**
```
tests/
├── Unit/           # Unit tests
│   └── Database/   # Database unit tests
└── web/            # Web-based tests
    ├── test_*.php  # Browser-based tests
    └── debug_*.php # Debug test pages
```

**Rationale**: Separated unit tests from web-based tests for different testing strategies.

## 🔄 Organizational Changes Made

### **1. Documentation Consolidation**
- **Before**: Scattered documentation files in root
- **After**: All documentation in `docs/` with clear categorization
- **Benefits**: Easier navigation, better discoverability

### **2. Script Organization**
- **Before**: All scripts in root `scripts/` directory
- **After**: Categorized by purpose (database, debug, tests, utils)
- **Benefits**: Logical grouping, easier maintenance

### **3. Test File Organization**
- **Before**: Test files scattered in `public/` and `routes/`
- **After**: All web tests in `tests/web/`, unit tests in `tests/Unit/`
- **Benefits**: Clear separation, better security (tests not web-accessible)

### **4. Clean Public Directory**
- **Before**: Test and debug files in web root
- **After**: Only essential web-accessible files
- **Benefits**: Improved security, better performance

### **5. Development Plans**
- **Before**: Plan files in project root
- **After**: All plans in `docs/plans/`
- **Benefits**: Centralized planning, better version control

## 🎯 Benefits of New Organization

### **Security Improvements**
- **Reduced Attack Surface**: Test files no longer web-accessible
- **Clean Web Root**: Only essential files in public directory
- **Better Access Control**: Sensitive scripts in protected directories

### **Performance Benefits**
- **Faster Directory Scanning**: Cleaner public directory
- **Reduced Web Server Load**: Fewer files to serve
- **Better Caching**: Cleaner file structure for caching

### **Developer Experience**
- **Logical Grouping**: Related files grouped together
- **Easier Navigation**: Clear directory structure
- **Better Discovery**: Categorized documentation and scripts

### **Maintenance Benefits**
- **Clear Responsibilities**: Each directory has a specific purpose
- **Easier Updates**: Related files grouped for easier updates
- **Better Version Control**: Logical file organization

## 📋 File Organization Rules

### **Documentation Files**
- **Location**: `docs/` directory
- **Naming**: Use hyphens, descriptive names
- **Structure**: Group by topic/feature
- **Examples**: `guides/`, `features/`, `architecture/`

### **Script Files**
- **Location**: `scripts/` with subdirectories by purpose
- **Naming**: Use underscores, descriptive names
- **Categories**: database, debug, tests, utils
- **Examples**: `scripts/database/migrate.php`

### **Test Files**
- **Unit Tests**: `tests/Unit/` for isolated testing
- **Web Tests**: `tests/web/` for browser-based testing
- **Integration Tests**: `tests/` for end-to-end testing
- **Naming**: Use descriptive names with test prefix

### **Web-Accessible Files**
- **Location**: `public/` directory only
- **Content**: Only essential application files
- **Security**: No test or debug files
- **Examples**: `index.php`, `.htaccess`

## 🔧 Migration Guidelines

### **Moving Files**
1. **Identify Purpose**: Determine file's primary function
2. **Choose Location**: Select appropriate directory based on purpose
3. **Update References**: Update any code that references moved files
4. **Test Functionality**: Ensure moved files still work correctly
5. **Update Documentation**: Update any documentation references

### **Updating References**
- **Code References**: Update any `require` or `include` statements
- **Documentation Links**: Update any documentation that links to moved files
- **Configuration**: Update any configuration files that reference moved files
- **Tests**: Update any tests that reference moved files

### **Best Practices**
- **Incremental Changes**: Move files in small batches
- **Test After Each Move**: Ensure functionality is maintained
- **Document Changes**: Update documentation as you go
- **Version Control**: Commit changes frequently

## 📚 Related Documentation

- **[Style Guide](style-guide.md)** - Coding standards and conventions
- **[Versioning Strategy](versioning.md)** - Semantic versioning approach
- **[Testing Guidelines](../testing/README.md)** - Testing strategies
- **[Security Guidelines](../security/README.md)** - Security best practices

---

*Last updated: v0.1.2 - July 30, 2025* 
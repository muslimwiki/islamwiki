# IslamWiki Development Standards

## Overview
This document defines the development standards, conventions, and best practices for the IslamWiki project. **ALWAYS reference this document when making architectural decisions or implementing features.**

## Project Architecture Standards

### 1. Directory Structure Standards

#### Core Directories
- `src/` - **PHP source code only** (controllers, models, services)
- `resources/` - **Frontend assets only** (CSS, JS, templates)
- `public/` - **Web entry points only** (index.php, .htaccess)
- `skins/` - **Skin-specific assets** (CSS, JS, templates per skin)
- `config/` - **Configuration files** (app.php, database.php, etc.)
- `database/` - **Database files** (migrations, seeds)
- `extensions/` - **Extension files** (plugins, modules)
- `scripts/` - **Utility scripts** (setup, maintenance, tools)
- `maintenance/` - **Debug and test files** (development utilities)
- `storage/` - **Application storage** (logs, cache, sessions)
- `routes/` - **Route definitions** (web.php, api.php)
- `docs/` - **Documentation** (guides, references, plans)
- `vendor/` - **Composer dependencies** (third-party libraries)

#### Special Directories
- `backup/` - **Backup files** (database dumps, file backups)
- `logs/` - **Application logs** (error logs, access logs)
- `var/` - **Variable data** (cache, temporary files)

### 2. File Naming Standards

#### PHP Files
- **Controllers:** `{Name}Controller.php` (e.g., `HomeController.php`)
- **Models:** `{Name}.php` (e.g., `User.php`)
- **Providers:** `{Name}ServiceProvider.php` (e.g., `AuthServiceProvider.php`)
- **Middleware:** `{Name}Middleware.php` (e.g., `AuthenticationMiddleware.php`)
- **Interfaces:** `{Name}Interface.php` (e.g., `CacheDriverInterface.php`)
- **Abstract Classes:** `Abstract{Name}.php` (e.g., `AbstractJob.php`)

#### Template Files
- **Layouts:** `layouts/{name}.twig` (e.g., `layouts/base.twig`)
- **Pages:** `{section}/{name}.twig` (e.g., `pages/home.twig`)
- **Components:** `components/{name}.twig` (e.g., `components/header.twig`)
- **Error Pages:** `errors/{code}.php` (e.g., `errors/404.php`)

#### Asset Files
- **Framework CSS:** `safa.css`
- **Framework JS:** `zamzam.js`
- **Skin CSS:** `{skinname}.css` (e.g., `bismillah.css`)
- **Skin JS:** `{skinname}.js` (e.g., `bismillah.js`)

#### Configuration Files
- **Main Config:** `LocalSettings.php`
- **Islamic Config:** `IslamSettings.php`
- **App Config:** `config/app.php`
- **Database Config:** `config/database.php`

### 3. Coding Standards

#### PHP Standards
- **PSR-4 Autoloading:** Follow PSR-4 namespace structure
- **PSR-12 Coding Style:** Use PSR-12 formatting
- **Type Declarations:** Use strict types and type hints
- **Documentation:** PHPDoc comments for all public methods
- **Error Handling:** Use try-catch blocks for exceptions
- **Security:** Always validate and sanitize user input

#### Template Standards
- **Twig Templates:** Use `.twig` extension for templates
- **Template Inheritance:** Use `{% extends %}` for layouts
- **Variable Escaping:** Always escape output with `{{ }}`
- **Security:** Use CSRF tokens in forms
- **Accessibility:** Include ARIA labels and semantic HTML

#### CSS Standards
- **Framework CSS:** Structural styles only (Safa CSS)
- **Skin CSS:** Custom styling per skin
- **CSS Variables:** Use CSS custom properties for theming
- **Responsive Design:** Mobile-first approach
- **Accessibility:** High contrast ratios, focus indicators

#### JavaScript Standards
- **Framework JS:** Core functionality only (ZamZam.js)
- **Skin JS:** Skin-specific interactions
- **ES6+ Features:** Use modern JavaScript features
- **Error Handling:** Try-catch blocks for async operations
- **Accessibility:** Keyboard navigation support

### 4. Security Standards

#### Input Validation
- **Server-side Validation:** Always validate on server
- **Client-side Validation:** Provide immediate feedback
- **SQL Injection Prevention:** Use prepared statements
- **XSS Prevention:** Escape all output
- **CSRF Protection:** Use CSRF tokens in forms

#### Authentication & Authorization
- **Session Management:** Secure session handling
- **Password Hashing:** Use bcrypt or Argon2
- **Role-based Access:** Implement proper RBAC
- **API Security:** Use API keys and rate limiting

#### File Security
- **Upload Validation:** Validate file types and sizes
- **Path Traversal Prevention:** Sanitize file paths
- **Directory Permissions:** Restrict access to sensitive directories
- **HTTPS Only:** Force HTTPS in production

### 5. Database Standards

#### Migration Standards
- **Version Control:** All schema changes via migrations
- **Rollback Support:** Every migration must be reversible
- **Naming Convention:** `{timestamp}_{description}.php`
- **Documentation:** Comment complex migrations

#### Query Standards
- **Prepared Statements:** Use parameterized queries
- **Indexing:** Proper indexes for performance
- **Normalization:** Follow database normalization rules
- **Backup Strategy:** Regular database backups

### 6. Performance Standards

#### Caching Strategy
- **Application Cache:** Cache frequently accessed data
- **Template Cache:** Compile and cache templates
- **Database Cache:** Query result caching
- **CDN Usage:** Use CDN for static assets
- **Asset Optimization:** Minify CSS/JS in production
- **Image Optimization:** Compress and optimize images
- **Lazy Loading:** Implement lazy loading for content
- **Database Optimization:** Optimize slow queries

#### Performance Targets
- **Page Load Time:** < 2 seconds
- **API Response Time:** < 500ms
- **Database Query Time:** < 100ms
- **Cache Hit Rate:** > 90%
- **Uptime:** 99.9% availability

#### Advanced Features
- **Multi-language Support:** Arabic, English, Urdu, etc.
- **RTL Support:** Right-to-left text support
- **Cultural Adaptation:** Cultural-specific features
- **Translation System:** User-contributed translations
- **Localization Testing:** Multi-language testing

### 7. Testing Standards

#### Unit Testing
- **Test Coverage:** Aim for 80%+ code coverage
- **Test Naming:** Descriptive test method names
- **Mock Objects:** Use mocks for external dependencies
- **Test Isolation:** Each test should be independent

#### Integration Testing
- **Database Testing:** Test database interactions
- **API Testing:** Test API endpoints
- **UI Testing:** Test user interface flows
- **Performance Testing:** Load and stress testing

### 8. Documentation Standards

#### Code Documentation
- **PHPDoc Comments:** Document all public methods
- **Inline Comments:** Explain complex logic
- **README Files:** Document setup and usage
- **API Documentation:** Document all API endpoints

#### Project Documentation
- **Architecture Docs:** Document system architecture
- **User Guides:** Create user-friendly guides
- **Developer Guides:** Document development processes
- **Deployment Guides:** Document deployment procedures

### 9. Version Control Standards

#### Git Workflow
- **Branch Naming:** `feature/`, `bugfix/`, `hotfix/` prefixes
- **Commit Messages:** Descriptive commit messages
- **Pull Requests:** Code review for all changes
- **Release Tags:** Semantic versioning for releases

#### File Management
- **Ignore Patterns:** Proper `.gitignore` configuration
- **Large Files:** Use Git LFS for large files
- **Sensitive Data:** Never commit sensitive information
- **Backup Strategy:** Regular repository backups

### 10. Deployment Standards

#### Environment Management
- **Environment Variables:** Use `.env` files for configuration
- **Environment Separation:** Separate dev, staging, production
- **Configuration Management:** Version control configuration
- **Secrets Management:** Secure handling of secrets

#### Deployment Process
- **Automated Deployment:** Use CI/CD pipelines
- **Rollback Strategy:** Ability to rollback deployments
- **Health Checks:** Monitor application health
- **Logging:** Comprehensive logging strategy

## Islamic-Specific Standards

### 1. Content Standards
- **Islamic Accuracy:** Ensure religious content accuracy
- **Scholar Verification:** Verify information with reliable sources
- **Cultural Sensitivity:** Respect diverse Islamic traditions
- **Arabic Support:** Proper Arabic text rendering

### 2. Feature Standards
- **Prayer Times:** Accurate prayer time calculations with multiple methods
- **Islamic Calendar:** Proper Hijri calendar implementation
- **Quran Integration:** Accurate Quran text and translations with tafsir
- **Hadith Integration:** Reliable hadith collections with authentication
- **Scholar Verification:** Comprehensive scholar verification system
- **Islamic Content:** Authentic and verified Islamic content
- **Islamic Analytics:** Islamic-specific metrics and analytics

### 3. User Experience Standards
- **Islamic Design:** Respectful and appropriate design
- **Accessibility:** Ensure accessibility for all users
- **Multilingual Support:** Support for Arabic and other languages
- **Mobile Responsiveness:** Work well on all devices
- **RTL Support:** Right-to-left text support for Arabic
- **Cultural Adaptation:** Cultural-specific features and design
- **Performance:** Fast loading times and smooth interactions

### 4. Islamic-Named Framework Standards

#### Frontend Frameworks
- **Safa CSS Framework (صافا):** Structural CSS framework providing clean, Islamic-themed styling
  - **Purpose:** Base styling and layout system
  - **Location:** `resources/assets/css/safa.css`
  - **Standards:** Structural styles only, no custom styling
  - **Integration:** Seamless integration with skin-specific CSS

- **ZamZam.js Framework (زمزم):** Custom JavaScript framework for Islamic applications
  - **Purpose:** Frontend interactivity and reactive data binding
  - **Location:** `resources/assets/js/zamzam.js`
  - **Features:** Directives (`z-class`, `z-methods`, `z-show`, `z-text`, `z-model`)
  - **Standards:** Core functionality only, skin-specific JS for custom features

#### Backend Systems
- **Asas Container (أساس):** Dependency injection container (Foundation)
  - **Purpose:** Service management and dependency injection
  - **Location:** `src/Core/Container/AsasContainer.php`
  - **Standards:** PSR-11 compliant container implementation

- **Aman Security (أمان):** Comprehensive security, authentication, and authorization system (Security)
  - **Purpose:** User authentication, authorization, security, and access control
  - **Location:** `src/Core/Auth/AmanSecurity.php`
  - **Standards:** Secure authentication, authorization, CSRF protection, and comprehensive security management

- **Siraj API (سراج):** API management and routing system (Light/Lamp)
  - **Purpose:** RESTful API management and versioning
  - **Location:** `src/Core/API/SirajAPI.php`
  - **Standards:** RESTful API with authentication and rate limiting

- **Shahid Logging (شاهد):** Comprehensive logging and error handling system (Witness)
  - **Purpose:** Application logging, debugging, and error handling
  - **Location:** `src/Core/Logging/ShahidLogger.php`
  - **Standards:** PSR-3 compliant logging with multiple levels and comprehensive error handling

- **Wisal Session (وصال):** Session management system (Connection)
  - **Purpose:** User session handling and persistence
  - **Location:** `src/Core/Session/WisalSession.php`
  - **Standards:** Secure session management with CSRF protection

- **Rihlah Caching (رحلة):** Caching system (Journey)
  - **Purpose:** Application caching and performance optimization
  - **Location:** `src/Core/Caching/RihlahCaching.php`
  - **Standards:** Multi-driver caching with database and file support

- **Sabr Queue (صبر):** Job queue system (Patience)
  - **Purpose:** Background job processing and task management
  - **Location:** `src/Core/Queue/SabrQueue.php`
  - **Standards:** Reliable job processing with retry mechanisms

- **Usul Knowledge (أصول):** Knowledge management system (Principles/Roots)
  - **Purpose:** Islamic knowledge organization and classification
  - **Location:** `src/Core/Knowledge/UsulKnowledge.php`
  - **Standards:** Islamic knowledge taxonomy and ontology

- **Iqra Search (اقرأ):** Islamic search engine (Read)
  - **Purpose:** Intelligent search with Islamic content focus
  - **Location:** `src/Core/Search/IqraSearch.php`
  - **Standards:** Semantic search with Islamic content relevance

- **Bayan Formatter (بيان):** Content formatting system (Explanation)
  - **Purpose:** Islamic content formatting and presentation
  - **Location:** `src/Core/Formatter/BayanFormatter.php`
  - **Standards:** Islamic content formatting with proper Arabic support

- **Sabil Routing (سبيل):** Advanced routing system (Path/Way)
  - **Purpose:** HTTP routing and middleware management
  - **Location:** `src/Core/Routing/Sabil.php`
  - **Standards:** PSR-15 compliant routing with middleware support

- **NizamApplication (نظام):** Main application system (System/Order)
  - **Purpose:** Application framework and system orchestration
  - **Location:** `src/Core/NizamApplication.php`
  - **Standards:** Comprehensive system management and coordination

- **Mizan Database (ميزان):** Database system (Balance/Scale)
  - **Purpose:** Database connection and data integrity management
  - **Location:** `src/Core/Database/MizanDatabase.php`
  - **Standards:** PDO-based database operations with transaction support

- **Tadbir Configuration (تدبير):** Configuration management system (Management/Planning)
  - **Purpose:** Configuration management and settings organization
  - **Location:** `src/Core/Configuration/TadbirConfiguration.php`
  - **Standards:** Dot notation access, validation, and environment integration

#### Framework Integration Standards
- **Naming Convention:** All Islamic-named systems follow Arabic naming with English transliteration
- **Documentation:** Each system must have comprehensive documentation
- **Testing:** All systems require unit and integration tests
- **Performance:** Each system must meet performance benchmarks
- **Security:** All systems must follow security best practices
- **Accessibility:** Islamic systems must support accessibility standards

## Quality Assurance Standards

### 1. Code Review
- **Peer Review:** All code must be reviewed
- **Automated Checks:** Use linting and static analysis
- **Security Review:** Security-focused code review
- **Performance Review:** Performance impact assessment

### 2. Testing Requirements
- **Unit Tests:** Test individual components
- **Integration Tests:** Test component interactions
- **User Acceptance Tests:** Test user workflows
- **Security Tests:** Test security vulnerabilities

### 3. Monitoring
- **Error Monitoring:** Monitor application errors
- **Performance Monitoring:** Track application performance
- **Security Monitoring:** Monitor security events
- **User Analytics:** Track user behavior (privacy-compliant)

## Compliance Standards

### 1. Legal Compliance
- **AGPL License:** Follow AGPL-3.0 license requirements
- **Privacy Laws:** Comply with privacy regulations
- **Accessibility Laws:** Meet accessibility requirements
- **Data Protection:** Protect user data appropriately

### 2. Islamic Compliance
- **Religious Guidelines:** Follow Islamic principles
- **Content Moderation:** Appropriate content filtering
- **Community Guidelines:** Respectful community standards
- **Cultural Sensitivity:** Respect diverse traditions
- **Scholar Verification:** Verify information with reliable sources
- **Content Authenticity:** Ensure religious content accuracy
- **Islamic Analytics:** Islamic-specific metrics and tracking

---

**Last Updated:** 2025-08-05
**Version:** 1.0
**Author:** IslamWiki Development Team 
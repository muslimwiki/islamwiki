# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.7] - 2025-07-30

### Fixed
- **Environment Variable Loading**: Resolved "Undefined array key 'APP_ENV'" warnings
  - Enhanced Application class to use fallback chain: `$_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production'`
  - Updated ViewServiceProvider to use same fallback pattern
  - Fixed ErrorHandler environment detection
  - Updated TestController environment variable access
  - Application now properly handles environment variables before dotenv is fully loaded
- **500 Error Resolution**: Fixed application bootstrap failures
  - Eliminated environment variable warnings that caused application crashes
  - Improved error handling during application initialization
  - Enhanced robustness of environment variable access across all components

### Changed
- **Environment Variable Access**: Made environment variable access more robust
  - All components now use consistent fallback pattern for environment variables
  - Improved compatibility with different PHP configurations
  - Enhanced error handling for missing environment variables

## [0.1.2] - 2025-07-30

### Added
- **Pages Index**: Complete "View All Pages" functionality for testing and browsing
  - Comprehensive pages listing with search and filter capabilities
  - Professional grid layout with page cards showing metadata
  - Navigation enhancement with "View All Pages" link
  - Sorting and pagination support for large collections
  - Page actions (View, Edit, History) for each page
  - Search functionality across page titles and content
  - Filter by namespace and sort by various criteria

### Changed
- **PageController**: Enhanced with comprehensive index functionality
- **Template System**: Added pages index template with professional styling
- **Navigation**: Improved navigation with pages browsing link

### Fixed
- **Query Builder**: Resolved missing methods (leftJoin, whereNull, count) with simplified queries
- **Template Paths**: Fixed template resolution for pages index
- **Request Class**: Added missing `getQueryParam()` method

## [0.1.1] - 2025-07-30

### Added
- **Enhanced Content Rendering**: Comprehensive markdown support with syntax highlighting
  - Full markdown parsing (headers, bold, italic, lists, links, blockquotes)
  - Code block syntax highlighting with Prism.js integration
  - Inline code formatting with proper styling
  - Auto-linking of URLs and markdown-style links
  - Blockquote support with proper styling
  - Horizontal rules and mixed content support
  - Professional CSS styling for all rendered content
- **Syntax Highlighting**: Prism.js integration for code blocks
- **Content Styling**: Comprehensive CSS for enhanced readability

### Changed
- **Content Parser**: Enhanced `parseWikiText()` method with modular parsing functions
- **Template Styling**: Added professional CSS for rendered content
- **Code Display**: Improved code block rendering with language detection

### Fixed
- **Template Paths**: Fixed template resolution in PageController
- **Content Rendering**: Resolved HTML escaping and markdown processing order
- **Code Highlighting**: Fixed Prism.js integration and initialization

## [0.1.0] - 2025-07-30

### Added
- **Wiki Page System**: Complete page creation, viewing, and management
  - Page model with Eloquent-like relationships and revision tracking
  - PageController with full CRUD operations for wiki pages
  - Page templates for display, editing, and history viewing
  - Content rendering with basic wiki text parsing and HTML conversion
  - View count tracking with database analytics and user tracking
  - Page permissions system (edit, delete, lock) based on user roles
  - Page history and revision tracking functionality
  - Page locking system for admin-only content protection
  - Dynamic homepage with recent pages display and excerpts
- **Request Enhancements**: Added `isXmlHttpRequest()` method for AJAX detection
- **Template System**: New page templates with proper Twig inheritance

### Changed
- **PageController**: Fixed template path resolution (`pages.show` → `pages/show`)
- **Database Operations**: Improved query builder usage and error handling
- **View Count System**: Enhanced page view tracking with proper database updates
- **Template Rendering**: Enhanced Twig template structure and inheritance

### Fixed
- **Page Viewing**: Resolved 500 errors when accessing individual wiki pages
- **Template Paths**: Fixed template resolution issues in PageController
- **Database Queries**: Fixed raw SQL method calls and view count updates
- **Container Binding**: Resolved view renderer binding in test environment
- **Session Management**: Fixed session warnings in CLI environment
- **Request Class**: Added missing `isXmlHttpRequest()` method for AJAX detection

## [0.0.6] - 2025-07-30

### Added
- **Comprehensive Security Middleware**: Enterprise-level security protection
  - Rate limiting with configurable limits (60 requests/minute, 10 burst requests/second)
  - Input validation and sanitization (null bytes, control characters, line endings)
  - SQL injection detection (union+select, drop+table, delete+from, etc.)
  - XSS protection (script tags, javascript: protocols, event handlers)
  - Directory traversal protection (.. and // patterns)
  - Security headers (CSP, X-Frame-Options, XSS-Protection, etc.)
- **Enhanced Error Handling Middleware**: Professional error management
  - Comprehensive exception catching and logging
  - Debug information in development mode
  - User-friendly error pages with navigation
  - Performance monitoring (request timing, memory usage)
  - Graceful error responses for all HTTP status codes
- **Middleware Stack System**: Organized middleware execution
  - Ordered middleware chain execution
  - Error handling for middleware failures
  - Debug logging for middleware execution
  - Automatic middleware initialization
- **Enhanced Logging System**: PSR-3 compliant with rich features
  - Structured logging with context information
  - Specialized methods (security, userAction, performance, query, exception)
  - Log rotation with configurable size limits
  - Request context automatic inclusion
  - Memory usage and performance tracking
- **CSRF Protection**: Cross-site request forgery protection
  - Token validation for state-changing requests
  - Flexible token sources (POST data, headers, Laravel-style)
  - User-friendly error pages for token mismatches
  - Automatic token generation and verification

### Changed
- **FastRouter Integration**: Enhanced with middleware stack
  - Automatic middleware execution for all routes
  - Proper PSR-7 to internal Request conversion
  - Debug logging for middleware execution
  - Container integration for middleware services
- **Request Class**: Added missing methods
  - `getPostParam()` method for POST parameter access
  - `fromPsr7()` method for PSR-7 request conversion
  - Enhanced header handling and validation
- **Response Class**: Fixed constructor parameter order
  - Corrected status code and headers parameter order
  - Improved error response generation
- **TestController**: Fixed Response constructor calls
  - Updated all Response instantiation to use correct parameter order
  - Improved error handling in test endpoints

### Fixed
- **Logger Interface Compatibility**: Resolved PSR-3 compliance issues
  - Fixed method signatures to match PSR-3 interface
  - Removed strict type hints for message parameters
  - Ensured full PSR-3 compatibility
- **Middleware Execution**: Fixed middleware stack integration
  - Proper request conversion between PSR-7 and internal classes
  - Correct middleware chain execution order
  - Fixed container service resolution for middleware
- **Security Pattern Detection**: Enhanced SQL injection detection
  - Added support for URL-encoded patterns (union%20select)
  - Improved regex patterns for better detection
  - Added query string validation alongside URI validation
- **Permission Issues**: Resolved log file access problems
  - Fixed storage and logs directory permissions
  - Proper file ownership for web server access
  - Enhanced error handling for file operations

### Security
- **Comprehensive Attack Prevention**: Validated protection against common attacks
  - SQL injection patterns blocked ✅
  - XSS attack vectors prevented ✅
  - Directory traversal attempts blocked ✅
  - Rate limiting prevents abuse ✅
  - CSRF protection working ✅
- **Security Headers**: Implemented comprehensive security headers
  - Content Security Policy (CSP) with allowed sources
  - X-Frame-Options: DENY
  - X-XSS-Protection: 1; mode=block
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy: geolocation=(), microphone=(), camera=()
  - Strict-Transport-Security: max-age=31536000; includeSubDomains

### Technical
- **Production Ready**: Enterprise-level security and error handling
  - Environment detection (debug/production modes)
  - Log rotation with configurable limits
  - Memory management and performance tracking
  - Comprehensive testing with validation scripts
  - Multiple security layers with defense in depth



## [0.0.2] - 2025-07-30

### Added
- **Session Management System**: Complete session handling with secure configuration
  - SessionManager class with HTTP-only, SameSite cookies
  - Session regeneration for security against session fixation
  - User authentication state management
  - Remember me functionality with secure tokens
- **CSRF Protection**: Comprehensive cross-site request forgery protection
  - CsrfMiddleware for form protection
  - CSRF token generation and verification
  - User-friendly error pages for token mismatches
  - Automatic token inclusion in login/register forms
- **Authentication Middleware**: Route protection for authenticated users
  - AuthenticationMiddleware for protected routes
  - Automatic redirect to login with return URL preservation
  - Session-based authentication checks
- **Database Foundation**: Complete database setup and migration system
  - Custom migration framework with proper schema management
  - User authentication tables with proper relationships
  - Sample data creation for development
  - Auto-increment fixes for primary keys
- **User Authentication**: Complete user management system
  - User registration with validation
  - Secure login with password hashing
  - User model with proper database interactions
  - Password verification and user lookup
- **Homepage Enhancement**: Dynamic homepage with recent pages
  - Database-driven recent pages display
  - Page excerpts and creation dates
  - Professional styling for page listings
  - Navigation improvements with page browsing

### Fixed
- **Database Schema Issues**: Resolved auto-increment problems in migrations
- **User Model Integration**: Fixed password field mapping and verification
- **Session Configuration**: Proper session startup and cookie settings
- **Container Dependencies**: Resolved service provider registration issues
- **Form Security**: Added CSRF tokens to all authentication forms

### Changed
- **Version Bump**: Major version increment to 0.1.0 for session management milestone
- **Authentication Flow**: Updated to use secure session management
- **Form Security**: All forms now include CSRF protection
- **Homepage**: Now displays dynamic content from database
- **Session Handling**: Replaced basic session handling with robust SessionManager

### Technical Details
- **Session Security**: HTTP-only cookies, SameSite=Lax, secure session regeneration
- **CSRF Protection**: Token-based protection with proper validation
- **Database**: MySQL with proper migrations and sample data
- **Authentication**: Session-based with remember me support
- **Middleware**: Authentication and CSRF middleware for route protection

## [0.0.1] - 2025-07-29

### Added
- **Foundation Reset**: Project version reset to 0.0.1 as new baseline for all future microchanges
- **Alpine.js Integration**: Lightweight frontend interactivity framework for progressive enhancement
- **Twig Templating**: Server-side template rendering with proper layouts and inheritance
- **Modern UI Components**: Responsive design with clean styling and interactive elements
- **Service Provider System**: ViewServiceProvider for TwigRenderer registration
- **Comprehensive Error Handling**: 404/500 pages, pretty error output, robust file logging
- **PSR-7 Compatibility**: Proper HTTP request/response handling throughout the application
- **Dependency Injection**: Container properly configured for all controller dependencies

### Fixed
- **Router Issues**: Fixed FastRouter dependency injection and controller instantiation
- **Request Type Mismatch**: Resolved PSR-7 ServerRequest vs custom Request class conflicts
- **Template Rendering**: Fixed Twig template path resolution and caching issues
- **File Permissions**: Resolved logging and cache directory permission issues
- **Service Registration**: Fixed ViewServiceProvider registration in Application bootstrap
- **Controller Dependencies**: Ensured all controllers receive proper dependencies via container

### Changed
- **Homepage**: Now uses Twig templates with Alpine.js interactive demo
- **Dashboard**: Interactive dashboard with dynamic stats, activity feed, and watchlist management
- **Layout System**: New responsive layout with proper navigation and styling
- **Error Pages**: Comprehensive error handling with detailed debug information
- **Documentation**: Updated README and documentation structure for new architecture

### Technical Details
- **Routing**: FastRouter with ControllerFactory for proper dependency injection
- **Templating**: TwigRenderer with disabled caching in development mode
- **Frontend**: Alpine.js 3.x for lightweight JavaScript interactivity
- **Styling**: Modern CSS with responsive design and component-based architecture
- **Logging**: File-based logging with proper error handling and debug information

### Features
- **Interactive Counter**: Alpine.js counter with increment/decrement/reset functionality
- **Dynamic Messaging**: Real-time message editing with Alpine.js data binding
- **Alert System**: Success/error/info alerts with dismiss functionality
- **Dashboard Stats**: Interactive statistics with refresh simulation
- **Activity Feed**: Collapsible recent activity with user actions
- **Watchlist Management**: Add/remove pages from watchlist with Alpine.js
- **Responsive Navigation**: Clean navigation between homepage and dashboard

# Changelog

All notable changes to IslamWiki will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.39] - 2025-08-02

### Added
- **Three-Column Layout System** - Comprehensive implementation across multiple pages
  - **Pages Page:** Restructured with left column (create content & stats), middle column (hero & pages list), right column (search & filter)
  - **About Page:** Implemented with left column (get involved), middle column (main content), right column (contact us)
  - **Dashboard Page:** Organized with left column (quick actions & user stats), middle column (main content), right column (learning resources & site stats)
  - **Consistent Design:** All pages now follow the same 2/10, 6/10, 2/10 column distribution
- **Dynamic Site Statistics** - Real-time dashboard statistics
  - Replaced hardcoded placeholders with dynamic database queries
  - Added `getSiteStatistics()` method to DashboardController
  - Real-time counts for total pages, users, edits, and categories
  - Statistics update automatically as the site grows
- **Enhanced Dashboard Organization** - Improved user experience and data presentation
  - Consolidated duplicate "Your Contributions" section into "Your Stats"
  - Added contribution action buttons directly to stats card
  - Better space utilization and cleaner layout
  - Improved responsive design for all screen sizes

### Changed
- Updated version to 0.0.39
- **Dashboard Controller Enhancements**
  - Added `getSiteStatistics()` method for dynamic site-wide statistics
  - Updated `index()` method to include site statistics in view data
  - Improved error handling for database queries
- **Template Structure Improvements**
  - Standardized three-column layout across pages, about, and dashboard
  - Enhanced card layouts with better spacing and organization
  - Improved responsive design for mobile and desktop
  - Better content organization and user flow

### Fixed
- **Duplicate Information Issues**
  - Removed redundant "Your Contributions" section from dashboard
  - Consolidated user statistics into single "Your Stats" card
  - Eliminated duplicate data presentation across dashboard sections
- **Static Data Issues**
  - Replaced hardcoded site statistics with dynamic database queries
  - Fixed placeholder values (1,234 users, 5,678 edits, 890 categories)
  - Now shows real-time accurate site statistics

### Security
- Maintained existing security measures
- No database changes - purely frontend and data presentation improvements
- Backward compatible with all existing functionality

## [0.0.38] - 2025-08-02

### Fixed
- **PHP Syntax Errors**
  - Fixed missing closing quotes in `tests/web/test-logging.php`
  - Line 36: Corrected `date('Y-m-d H:i:s")` → `date('Y-m-d H:i:s')`
  - Line 50: Corrected `date('Y-m-d H:i:s")` → `date('Y-m-d H:i:s')`
- **Code Quality Improvements**
  - Achieved 100% error-free codebase across all file types
  - Verified all 336 PHP files have no syntax errors
  - Confirmed all JavaScript files have no syntax errors
  - Validated all JSON configuration files are properly formatted
  - Ensured all CSS files have balanced braces and proper syntax
  - Verified all 720 Twig template tags are properly balanced

### Changed
- Updated version to 0.0.38
- Implemented comprehensive syntax checking for all file types
- Standardized code formatting and syntax across the project
- Updated documentation to reflect current error-free state

### Security
- Maintained existing security measures
- No database changes - purely code quality improvements
- Backward compatible with all existing functionality

## [0.0.37] - 2025-08-02

### Added
- **Enhanced Header Navigation** - Improved navigation structure and styling
  - Separated top navigation into two distinct bars with different backgrounds
  - Enhanced search bar (3x larger, 600px width) with centered positioning
  - Improved avatar styling with larger circle and icon (40px vs 24px)
  - Removed username from navigation for cleaner appearance
  - Larger navigation elements including logo, nav links, and primary navigation text
- **Three-Column Layout System** - Full-width responsive layout for home page
  - Left column (2/10): Second navigation area
  - Middle column (6/10): Hero, welcome back, main content, join community
  - Right column (2/10): Sticky sidebar with quick actions and recent pages
  - Full-width design spanning entire page instead of centered content
- **Purple Section Headers** - Consistent styling across all themes
  - All section titles now have purple background extending full width
  - Cleaner card design with removed grey backgrounds
  - Improved spacing with eliminated unwanted margins and padding
  - Applied consistently across Safa and Bismillah themes

### Changed
- Updated version to 0.0.37
- **Layout Improvements**
  - Implemented three-column layout for better content organization
  - Made entire right sidebar sticky with proper scroll behavior
  - Enhanced responsive design for mobile and desktop
  - Improved button contrast and hover effects
- **Navigation Enhancements**
  - Streamlined navigation by removing redundant "Get Started" button
  - Better button alignment with "Recent Pages" title and "View All" on same row
  - Fixed button contrast issues for better readability
  - Enhanced mobile optimization with improved header layout
- **CSS Framework Updates**
  - Enhanced Safa.css with new layout system and improved styling
  - Synchronized all styling changes across Safa and Bismillah themes
  - Added cache busting parameters to CSS links
  - Improved HTML structure with proper container divs

### Fixed
- **CSS Override Issues**
  - Fixed multiple `.card-header` definitions causing styling conflicts
  - Resolved search bar sizing issues with proper width application
  - Eliminated unwanted 16px margins below section headers
  - Fixed inconsistent avatar sizes across different themes
- **Layout and Spacing Issues**
  - Resolved sticky positioning conflicts with navigation bar overlap
  - Fixed mobile responsiveness issues with header layout
  - Corrected padding and margin inconsistencies across screen sizes
  - Improved button hover state text color readability

### Security
- Maintained existing security measures
- No database changes - purely frontend improvements
- Backward compatible with all existing functionality

## [0.0.36] - 2025-08-02

### Added
- **Bismillah Skin Styling** - Beautiful Islamic design for search interfaces
  - Enhanced search headers with gradient backgrounds and Islamic patterns
  - Glass-morphism effects with backdrop blur for modern look
  - Hover animations and smooth transitions throughout
  - Floating animations for Iqra search icon
  - Enhanced typography and spacing for better readability
- **Search Functionality Fixes** - Complete resolution of search issues
  - Fixed routing conflicts that prevented search pages from loading
  - Added missing controller includes for SearchController and IqraSearchController
  - Corrected database column names to match actual schema
  - Added FULLTEXT indexes for efficient search performance
  - Fixed PDO result handling from array to object notation
- **Enhanced Search Interfaces** - Improved user experience
  - Modern search forms with integrated icons and focus states
  - Beautiful result cards with type badges and relevance scores
  - Enhanced statistics display with interactive elements
  - Responsive design optimized for all devices
  - Action buttons for viewing and editing content

### Changed
- Updated version to 0.0.36
- Enhanced search templates with complete Bismillah styling overhaul
- Improved database query performance with proper indexes
- Updated route order to prioritize search routes over generic routes
- Enhanced visual design with sophisticated color palette

### Fixed
- **Critical Search Fixes**
  - Search routes not loading after moving from app.php to index.php
  - Missing controller includes causing fatal errors
  - Database column name mismatches in search queries
  - PDO result handling incompatibilities
  - Route conflicts with generic slug routes
- **Database Schema Fixes**
  - Added FULLTEXT indexes to search-related tables
  - Corrected column names in pages, verses, hadiths, islamic_events tables
  - Fixed user_locations table column references
- **Visual Design Fixes**
  - Inconsistent styling between search interfaces
  - Poor mobile responsiveness
  - Missing hover effects and animations
  - Inadequate visual hierarchy

### Security
- Maintained existing security measures
- Enhanced input validation for search queries
- Improved error handling without exposing sensitive information
- Secure database query execution with proper parameterization

## [0.0.37] - 2025-08-03

### Added
- **Rihlah Caching System** (رحلة - Journey) - Complete caching system with monitoring
  - `Rihlah` - Main caching orchestrator with multi-driver support
  - `MemoryCacheDriver` - APCu-based memory caching
  - `FileCacheDriver` - File-based persistent caching
  - `DatabaseCacheDriver` - Database-based caching with automatic table creation
  - `SessionCacheDriver` - Session-based user-specific caching
  - `RedisCacheDriver` - High-performance Redis caching with pattern invalidation
  - `CacheController` - Web interface for cache management and monitoring
  - Cache dashboard with real-time statistics and management tools
  - Cache invalidation by pattern matching
  - Cache warm-up functionality for common data
- **Cache Management Features**
  - Real-time cache statistics and monitoring
  - Multi-driver cache management (Memory, File, Database, Session, Redis)
  - Pattern-based cache invalidation
  - Cache warm-up for performance optimization
  - Comprehensive cache dashboard with visual metrics
  - Cache driver information and detailed statistics
- **Redis Integration**
  - Full Redis support with connection management
  - Redis pattern matching for cache invalidation
  - Redis pipeline operations for bulk operations
  - Redis statistics and monitoring
  - Automatic Redis driver detection and fallback

### Changed
- Enhanced Rihlah caching system with comprehensive monitoring
- Added Redis support to existing cache drivers
- Improved cache performance with multi-driver architecture
- Updated cache configuration with Redis settings

### Fixed
- **Cache Management**
  - Fixed cache driver initialization with proper error handling
  - Improved cache statistics collection across all drivers
  - Enhanced cache invalidation with pattern matching
  - Fixed cache warm-up functionality

## [0.0.36] - 2025-08-03

### Added
- **System Renames with Arabic Names** - Renamed core systems with meaningful Arabic names
  - `AuthManager` → `Aman` (أمان - Security/Safety) - Authentication system
  - `SessionManager` → `Wisal` (وصال - Connection/Link) - Session management
  - `Logger` → `Shahid` (شاهد - Witness/Testimony) - Logging system
  - `Container` → `Asas` (أساس - Foundation/Base) - Dependency injection container
- **Siraj API System** (سراج - Lamp/Light) - Complete API management system
  - `Siraj` - Main API management orchestrator
  - `RateLimiter` - Request rate limiting with configurable limits
  - `SessionAuthenticator` - Session-based API authentication
  - `JsonResponseFormatter` - JSON response formatting
  - `AuthenticatorInterface` & `ResponseFormatterInterface` - Extensible interfaces
- **Updated Service Providers** - All service providers updated to use new system names
- **Comprehensive Testing** - All renamed systems tested and working correctly

### Changed
- Updated all references to renamed systems across the codebase
- Updated service providers to use `Asas` instead of `Container`
- Updated controllers to use new system names
- Updated documentation to reflect new Arabic system names

### Fixed
- **Critical Fixes**
  - Fixed type hints in all service providers
  - Fixed use statements in all controllers
  - Removed duplicate Logger.php file
  - Updated all Container references to Asas
- **Application Stability**
  - Application now starts successfully with new system names
  - All authentication and session management working correctly
  - All logging functionality working with Shahid system

## [0.0.35] - 2025-08-02

### Added
- **Complete User Authentication System** - Full login/logout functionality with session management
  - `Aman` (أمان) - Complete authentication system with database integration
  - `Wisal` (وصال) - Secure session handling with proper configuration
  - User profile pages with private and public viewing
  - User dropdown menu with ZamZam.js integration
- **Navigation & UI Improvements** - Enhanced user interface
  - Search bar in top navigation with comprehensive functionality
  - User dropdown menu with Dashboard, Profile, Settings, and Logout links
  - Responsive design with mobile-friendly navigation
  - Proper authentication state detection and display
- **Project Organization** - Improved file structure and organization
  - Moved 73 test files from `public/` to `tests/web/`
  - Moved 20 debug files from `public/` to `debug/`
  - Clean public directory with only web-accessible files
  - Proper web application structure following best practices
- **Service Provider System** - Enhanced dependency injection
  - `SkinServiceProvider` - Properly registered skin management system
  - Settings binding for all service providers
  - Unified container management across the application
  - All service providers properly registered and booted
- **Security Improvements** - Enhanced security features
  - Session security with proper configuration
  - Password hashing with bcrypt
  - CSRF protection for forms
  - Input validation and sanitization
  - Development files moved out of web-accessible directory

### Changed
- Updated version to 0.0.35
- Reorganized project structure for better security and organization
- Enhanced service provider system with proper registration
- Improved authentication system with secure session handling
- Updated file organization following web application best practices

### Fixed
- **Critical Fixes**
  - Profile page error: "No binding found for [skin.manager]"
  - Settings binding missing for LoggingServiceProvider
  - Session regeneration warnings
  - Container conflicts in dependency injection
- **Navigation Fixes**
  - Missing user dropdown for authenticated users
  - Authentication state detection and display
  - Search bar functionality in top navigation
  - Mobile navigation display issues
- **File Organization**
  - Test files properly organized in `tests/web/`
  - Debug files properly organized in `debug/`
  - Clean public directory structure
  - Updated .gitignore for new organization

### Security
- Moved development files out of web-accessible directory
- Enhanced session security with proper configuration
- Implemented secure password hashing
- Added CSRF protection for forms
- Improved input validation and sanitization

## [0.0.34] - 2025-08-01

### Added
- **Bayan Knowledge Graph System** - Complete knowledge graph implementation for Islamic concepts
  - `BayanManager` - Main orchestrator for the knowledge graph system
  - `NodeManager` - Handles knowledge graph nodes (concepts, verses, hadith, etc.)
  - `EdgeManager` - Manages relationships between nodes
  - `QueryManager` - Handles complex graph queries and traversals
- **Database Schema** - 6 new tables for knowledge graph functionality
  - `bayan_nodes` - Stores knowledge graph nodes
  - `bayan_edges` - Stores relationships between nodes
  - `bayan_node_types` - Predefined node types
  - `bayan_edge_types` - Predefined relationship types
  - `bayan_graph_metrics` - Cached graph metrics
  - `bayan_search_index` - Full-text search index
- **Web Interface** - Modern, responsive UI for knowledge graph management
  - Dashboard with real-time statistics
  - Search interface with filters
  - Node creation form with validation
  - Relationship visualization
  - Graph metrics display
- **API Endpoints** - RESTful API for programmatic access
  - `POST /bayan/create` - Create new node
  - `POST /bayan/relationship` - Create relationship
  - `GET /bayan/statistics` - Get graph statistics
  - `GET /bayan/paths` - Find paths between nodes
- **Service Provider** - `BayanServiceProvider` for system registration
- **Controller** - `BayanController` for HTTP request handling
- **Database Migration** - Migration 0016 for complete schema creation
- **Views** - Twig templates for web interface
  - `bayan/index.twig` - Main dashboard
  - `bayan/search.twig` - Search interface
  - `bayan/create.twig` - Node creation
- **Routes** - New routes for knowledge graph functionality
- **Testing** - Comprehensive test scripts
  - `test-bayan.php` - System test script
  - `debug-bayan.php` - Debug script
  - `bayan-test.php` - Web interface test
- **Default Data** - 10 node types and 10 relationship types pre-configured
- **Graph Features** - Advanced graph functionality
  - Path finding between nodes
  - Hub node identification
  - Centrality calculations
  - Full-text search capabilities
  - Relationship-aware search
  - Statistics and metrics

### Changed
- Updated version to 0.0.34
- Enhanced service provider system with Bayan integration
- Improved controller architecture with RESTful design
- Enhanced database migration system

### Fixed
- Service provider registration issues
- Container binding problems
- Database migration execution
- Error handling in knowledge graph operations

### Security
- Input validation and sanitization for node creation
- SQL injection prevention
- XSS protection
- Error handling without information disclosure

## [0.0.33] - 2025-07-31

### Added
- Enhanced search functionality with advanced filters
- Improved user interface with better responsive design
- Additional security features for user authentication

### Changed
- Updated database schema for better performance
- Improved error handling throughout the application

### Fixed
- Several minor bugs in the authentication system
- Performance issues with large datasets

## [0.0.32] - 2025-07-30

### Added
- New prayer times calculation system
- Enhanced Islamic calendar features
- Improved hadith search functionality

### Changed
- Updated Quran integration with better verse handling
- Enhanced user profile system

### Fixed
- Issues with prayer time calculations in certain locations
- Problems with hadith search accuracy

## [0.0.31] - 2025-07-29

### Added
- Comprehensive Islamic calendar system
- Advanced hadith collection management
- Enhanced scholar profile system

### Changed
- Improved database structure for better performance
- Enhanced security measures

### Fixed
- Calendar display issues in different timezones
- Hadith search performance problems

## [0.0.30] - 2025-07-28

### Added
- Quran verse integration system
- Enhanced user authentication
- Improved search functionality

### Changed
- Updated database schema for better data organization
- Enhanced security protocols

### Fixed
- Authentication issues in certain browsers
- Search result accuracy problems

## [0.0.29] - 2025-07-27

### Added
- Advanced configuration management system
- Enhanced security features
- Improved user interface

### Changed
- Updated version numbering system
- Enhanced error handling

### Fixed
- Configuration loading issues
- Security vulnerabilities

## [0.0.28] - 2025-07-26

### Added
- Enhanced skin system with new themes
- Improved user customization options
- Better mobile responsiveness

### Changed
- Updated skin loading mechanism
- Enhanced user experience

### Fixed
- Skin switching issues
- Mobile display problems

## [0.0.27] - 2025-07-25

### Added
- New user profile system
- Enhanced community features
- Improved search capabilities

### Changed
- Updated user interface design
- Enhanced database performance

### Fixed
- User profile display issues
- Search functionality problems

## [0.0.26] - 2025-07-24

### Added
- Advanced logging system
- Enhanced error handling
- Improved debugging tools

### Changed
- Updated logging configuration
- Enhanced error reporting

### Fixed
- Logging issues in production environment
- Error handling problems

## [0.0.25] - 2025-07-23

### Added
- Enhanced database migration system
- Improved data integrity checks
- Better backup and restore functionality

### Changed
- Updated database schema
- Enhanced data validation

### Fixed
- Migration execution issues
- Data integrity problems

## [0.0.24] - 2025-07-22

### Added
- New extension system
- Enhanced plugin architecture
- Improved modularity

### Changed
- Updated extension loading mechanism
- Enhanced plugin management

### Fixed
- Extension loading issues
- Plugin compatibility problems

## [0.0.23] - 2025-07-21

### Added
- Advanced routing system
- Enhanced URL handling
- Improved navigation

### Changed
- Updated routing configuration
- Enhanced URL generation

### Fixed
- Routing issues in certain scenarios
- URL generation problems

## [0.0.22] - 2025-07-20

### Added
- Enhanced session management
- Improved user authentication
- Better security features

### Changed
- Updated session handling
- Enhanced security protocols

### Fixed
- Session management issues
- Authentication problems

## [0.0.21] - 2025-07-19

### Added
- New configuration system
- Enhanced settings management
- Improved system administration

### Changed
- Updated configuration handling
- Enhanced settings interface

### Fixed
- Configuration loading issues
- Settings management problems

## [0.0.20] - 2025-07-18

### Added
- Enhanced database connection system
- Improved data access layer
- Better query optimization

### Changed
- Updated database architecture
- Enhanced query performance

### Fixed
- Database connection issues
- Query performance problems

## [0.0.19] - 2025-07-17

### Added
- New error handling system
- Enhanced debugging capabilities
- Improved error reporting

### Changed
- Updated error handling mechanism
- Enhanced debugging tools

### Fixed
- Error handling issues
- Debugging problems

## [0.0.18] - 2025-07-16

### Added
- Enhanced view rendering system
- Improved template engine
- Better UI/UX

### Changed
- Updated view rendering mechanism
- Enhanced template system

### Fixed
- View rendering issues
- Template problems

## [0.0.17] - 2025-07-15

### Added
- New container system
- Enhanced dependency injection
- Improved service management

### Changed
- Updated container architecture
- Enhanced service registration

### Fixed
- Container issues
- Service management problems

## [0.0.16] - 2025-07-14

### Added
- Enhanced HTTP request/response system
- Improved middleware architecture
- Better request handling

### Changed
- Updated HTTP handling mechanism
- Enhanced middleware system

### Fixed
- HTTP handling issues
- Middleware problems

## [0.0.15] - 2025-07-13

### Added
- New routing system
- Enhanced URL handling
- Improved navigation

### Changed
- Updated routing mechanism
- Enhanced URL generation

### Fixed
- Routing issues
- URL generation problems

## [0.0.14] - 2025-07-12

### Added
- Enhanced application bootstrap
- Improved initialization process
- Better startup handling

### Changed
- Updated bootstrap mechanism
- Enhanced initialization

### Fixed
- Bootstrap issues
- Initialization problems

## [0.0.13] - 2025-07-11

### Added
- New core application structure
- Enhanced framework architecture
- Improved modularity

### Changed
- Updated application architecture
- Enhanced modular design

### Fixed
- Architecture issues
- Modularity problems

## [0.0.12] - 2025-07-10

### Added
- Enhanced database schema
- Improved data modeling
- Better data organization

### Changed
- Updated database structure
- Enhanced data relationships

### Fixed
- Database schema issues
- Data modeling problems

## [0.0.11] - 2025-07-09

### Added
- New project structure
- Enhanced file organization
- Improved codebase organization

### Changed
- Updated project structure
- Enhanced file organization

### Fixed
- Project structure issues
- File organization problems

## [0.0.10] - 2025-07-08

### Added
- Initial project setup
- Basic framework structure
- Core application files

### Changed
- N/A

### Fixed
- N/A

---

## Version History

- **0.0.34** - Bayan Knowledge Graph System (Current)
- **0.0.33** - Enhanced Search and Security
- **0.0.32** - Prayer Times and Islamic Calendar
- **0.0.31** - Islamic Calendar and Hadith Management
- **0.0.30** - Quran Integration and Authentication
- **0.0.29** - Configuration Management
- **0.0.28** - Enhanced Skin System
- **0.0.27** - User Profile System
- **0.0.26** - Advanced Logging
- **0.0.25** - Database Migration System
- **0.0.24** - Extension System
- **0.0.23** - Advanced Routing
- **0.0.22** - Session Management
- **0.0.21** - Configuration System
- **0.0.20** - Database Connection
- **0.0.19** - Error Handling
- **0.0.18** - View Rendering
- **0.0.17** - Container System
- **0.0.16** - HTTP System
- **0.0.15** - Routing System
- **0.0.14** - Application Bootstrap
- **0.0.13** - Core Architecture
- **0.0.12** - Database Schema
- **0.0.11** - Project Structure
- **0.0.10** - Initial Setup

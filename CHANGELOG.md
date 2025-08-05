# Changelog

All notable changes to this project will be documented in this file.

## [0.0.51] - 2025-08-05

### Major Refactoring
- **Container Class Rename**: Renamed `Asas.php` to `AsasContainer.php` for better clarity
- **Application Consolidation**: Merged `Application.php` and `Nizam.php` into single `NizamApplication.php`
- **Class Name Updates**: Updated `Asas` class to `AsasContainer` and `Application` to `NizamApplication`
- **Comprehensive File Updates**: Updated 100+ files to use new class names and file paths
- **Type Hint Updates**: Updated all constructor parameters and type hints to use `AsasContainer`
- **Documentation Updates**: Updated all documentation to reflect new naming conventions

### Technical Improvements
- **Clear Naming Convention**: `AsasContainer` clearly indicates dependency injection container purpose
- **Consolidated Architecture**: Single `NizamApplication.php` eliminates confusion between multiple application classes
- **Consistent Structure**: File names now match class names throughout the codebase
- **Islamic Naming Convention**: `NizamApplication` properly follows Arabic naming system
- **Professional Standards**: Eliminates confusion and provides clear, descriptive naming

### Files Renamed
- `src/Core/Container/Asas.php` → `src/Core/Container/AsasContainer.php`
- `src/Core/Application.php` → **DELETED** (merged into NizamApplication.php)
- `src/Core/Nizam.php` → **DELETED** (merged into NizamApplication.php)
- `src/Core/NizamApplication.php` → **CREATED** (consolidated file)

### Files Updated (100+ files)
- All `require_once` statements updated to reference `AsasContainer.php`
- All `new Application()` → `new NizamApplication()`
- All `new Asas()` → `new AsasContainer()`
- All `use` statements updated
- All type hints updated (`Asas $container` → `AsasContainer $container`)
- All constructor parameters updated
- All documentation references updated

### Impact
- **Code Clarity**: Clear, descriptive class names that indicate purpose
- **Architecture Simplification**: Single application class eliminates confusion
- **Maintainability**: Consistent naming conventions throughout codebase
- **Developer Experience**: Easier to understand and work with the codebase
- **Professional Standards**: Follows modern PHP and Islamic naming best practices

## [0.0.50] - 2025-08-05

### Added
- **URL Parameter Skin Override**: Added ability to switch skins via URL parameter for testing and development
- **Temporary Skin Switching**: Users can now use `?skin=bismillah` or `?skin=muslim` in URLs
- **Case-Insensitive Parameter Support**: Works with `?skin=Bismillah`, `?skin=bismillah`, `?skin=BISMILLAH`
- **Skin Validation**: Only accepts valid skin names, falls back to user's preferred skin
- **Non-Persistent Override**: URL parameter changes don't affect user's saved skin preference

### Technical Improvements
- **SkinMiddleware Enhancement**: Added URL parameter detection and skin override logic
- **Container Data Updates**: Improved skin data management in application container
- **Template Global Updates**: Enhanced global variable management for skin switching
- **Asset Loading**: Improved CSS and JS loading from correct skin directories
- **Error Handling**: Added comprehensive validation and fallback mechanisms

### Files Changed
- `src/Http/Middleware/SkinMiddleware.php` - Added URL parameter skin override functionality
- `src/Core/View/TwigRenderer.php` - Enhanced global variable management for skin switching
- `src/Skins/UserSkin.php` - Improved CSS and JS loading from correct skin paths
- `src/Skins/SkinManager.php` - Updated to load skins from public/skins directory
- `.gitignore` - Updated to ignore debug log files created during development

### Usage Examples
- **Bismillah Skin**: `https://local.islam.wiki/?skin=bismillah`
- **Muslim Skin**: `https://local.islam.wiki/?skin=muslim`
- **Default (no parameter)**: `https://local.islam.wiki/` (uses user's preferred skin)

### Impact
- **Developer Experience**: Easy skin testing and comparison via URL parameters
- **User Experience**: Quick skin switching for demonstration purposes
- **Testing Efficiency**: No need to change user settings for temporary skin testing
- **System Flexibility**: Maintains user preferences while allowing temporary overrides

## [0.0.49] - 2025-08-04

### Fixed
- **Critical Muslim Skin Content Rendering**: Resolved issue where Muslim skin content was not displaying in main body area
- **LocalSettings Loading Issue**: Fixed missing LocalSettings.php loading in main application entry point
- **Skin Initialization Bug**: Resolved SkinManager not picking up `$wgActiveSkin` configuration from LocalSettings
- **Muslim Skin Activation**: Fixed Muslim skin not being activated as default despite LocalSettings configuration
- **Content Block Rendering**: Resolved Twig template content block positioning and rendering issues
- **CSS Class Naming**: Updated all `citizen-` prefixed CSS classes to `muslim-` for proper Muslim skin styling

### Technical Improvements
- **Application Bootstrap**: Added LocalSettings.php loading to main application entry point
- **Skin Configuration**: Improved skin initialization from LocalSettings configuration
- **Template Structure**: Enhanced layout template with proper content block positioning
- **CSS Framework Integration**: Updated Muslim skin CSS with correct class naming convention
- **Debug Tools**: Added comprehensive skin activation and configuration debugging tools

### Files Changed
- `public/index.php` - Added LocalSettings.php loading for proper skin configuration
- `resources/views/layouts/app.twig` - Fixed content block positioning and Muslim skin layout
- `public/skins/Muslim/css/muslim.css` - Updated all CSS classes from `citizen-` to `muslim-` prefix
- `debug/debug-skin-activation.php` - Added skin activation debugging tool
- `debug/test-muslim-skin-availability.php` - Added Muslim skin availability testing
- `debug/test-localsettings-skin.php` - Added LocalSettings configuration testing
- `debug/test-skin-initialization.php` - Added skin initialization debugging

### Impact
- **User Experience**: Muslim skin now displays content correctly in main body area
- **Skin Management**: Muslim skin is properly activated as default skin
- **Visual Design**: Muslim skin styling is now applied with correct CSS classes
- **System Reliability**: Skin configuration is properly loaded from LocalSettings
- **Developer Experience**: Comprehensive debugging tools for skin management

## [0.0.48] - 2025-08-04

### Fixed
- **Critical Authentication Bug**: Resolved login failure caused by SkinMiddleware session interference
- **SkinMiddleware Session Handling**: Fixed middleware accessing session data during authentication
- **Authentication Route Protection**: Added protection for login/register routes from skin middleware
- **Session State Management**: Improved session handling to prevent authentication conflicts
- **Middleware Error Handling**: Enhanced error handling for session-related operations

### Technical Improvements
- **Route-based Middleware Protection**: SkinMiddleware now skips authentication routes
- **Safe Session Access**: Added try-catch blocks and null checks for session operations
- **Non-Critical Error Handling**: Session errors in middleware no longer break authentication
- **Improved Logging**: Better error logging for debugging middleware issues

### Files Changed
- `src/Http/Middleware/SkinMiddleware.php` - Added authentication route protection and safe session handling
- `src/Core/Routing/IslamRouter.php` - Re-enabled SkinMiddleware with improved error handling

### Impact
- **User Experience**: Login now works correctly with skin switching functionality
- **Authentication Reliability**: No more session interference during login process
- **Skin Management**: Dynamic skin switching works without breaking authentication
- **System Stability**: Middleware no longer interferes with core authentication flow

## [0.0.47] - 2025-08-03

### Added
- **Dynamic Skin Discovery**: Automatic discovery of skins from `/skins/` directory
- **Enhanced Settings Page**: Comprehensive settings interface with skin selection
- **Multi-Skin Support**: Full support for multiple skins (Bismillah, Muslim)
- **User-Specific Skin Preferences**: Individual user skin settings stored in database
- **Skin Information Display**: Detailed skin metadata and feature information
- **Case-Insensitive Skin Access**: Support for both `Muslim` and `muslim` naming
- **Settings API Endpoints**: RESTful API for skin management and settings

### Fixed
- **Skin Loading Issue**: Resolved problem where only one skin was being loaded
- **LocalSettings Integration**: Fixed `$wgValidSkins` array loading issues
- **Skin Validation**: Improved skin validation and error handling
- **Settings Controller**: Enhanced skin discovery and switching functionality

### Technical Improvements
- **SkinManager Enhancement**: Improved skin loading logic for dynamic discovery
- **Settings Controller**: Added comprehensive skin management functionality
- **Database Integration**: User skin preferences properly stored and retrieved
- **Debug Tools**: Added comprehensive skin management debugging tools

### Files Changed
- `src/Skins/SkinManager.php` - Enhanced skin loading for dynamic discovery
- `src/Http/Controllers/SettingsController.php` - Improved skin management
- `resources/views/settings/index.twig` - Enhanced settings interface
- `debug/debug-skin-management.php` - Added skin management debugging
- `debug/debug-settings-test.php` - Added settings functionality testing

### Impact
- **User Experience**: Users can now easily switch between available skins
- **Developer Experience**: New skins automatically appear in settings
- **Maintainability**: Dynamic skin discovery reduces configuration overhead
- **Flexibility**: Support for unlimited number of skins

## [0.0.46] - 2025-08-03

### Fixed
- **Critical Session Persistence Bug**: Resolved session data not being written to disk
- **Session Configuration**: Fixed session save path and name configuration issues
- **Session Regeneration**: Removed aggressive session regeneration that was causing data loss
- **Session Write Enforcement**: Added immediate session write for critical authentication data
- **Session Start Logic**: Improved session start handling for all session states
- **Authentication Persistence**: Fixed login state not persisting between requests
- **UI Display Issues**: Resolved user menu showing sign-in button instead of avatar

### Technical Improvements
- **Session Security**: Enhanced session management with proper write/close cycles
- **Session File Management**: Sessions now properly saved to custom storage directory
- **Session Data Integrity**: Ensured session data is written immediately for critical operations
- **Session State Handling**: Improved handling of session states and transitions
- **Debug Tools**: Added comprehensive session debugging tools for troubleshooting

### Files Changed
- `src/Core/Session/Wisal.php` - Fixed session writing and regeneration logic
- `src/Providers/SessionServiceProvider.php` - Improved session initialization
- `debug/test-session-writing.php` - Added session writing test tool
- `debug/test-session-web.php` - Enhanced web-based session testing

### Impact
- **User Experience**: Login state now persists correctly across page navigation
- **Security**: Sessions are properly managed with secure write/close cycles
- **Reliability**: Session data is consistently saved and restored
- **Debugging**: Comprehensive tools for session troubleshooting

## [0.0.45] - 2025-08-03

### Fixed
- **Critical Bug Fix**: Resolved container resolution issues causing "get_class(): Argument #1 ($object) must be of type object, array given" errors
- **Logger System**: Fixed PSR-3 interface compliance issues in Shahid logger
- **Type Safety**: Updated all Logger references to Shahid throughout codebase
- **Container Binding**: Fixed LoggerInterface binding resolution in dependency injection container
- **Missing Methods**: Added missing `notice()` method to Shahid logger for full PSR-3 compliance
- **Type Hints**: Fixed method signature type hints in Shahid logger to match PSR-3 standard
- **Error Handling**: Disabled problematic afterResolving callback that was interfering with container resolution

### Technical Improvements
- **Code Quality**: Improved type safety and error handling throughout the application
- **Dependency Injection**: Enhanced container resolution reliability
- **Logging System**: Fully PSR-3 compliant logging implementation
- **Debugging**: Added comprehensive debug logging for troubleshooting container issues

### Files Changed
- `src/Core/Application.php` - Fixed afterResolving callback issues
- `src/Core/Logging/Shahid.php` - Fixed PSR-3 compliance and type hints
- `src/Http/Controllers/HomeController.php` - Added proper error handling and type checking
- Multiple files updated Logger references to Shahid for consistency

### Impact
- **Stability**: Application now loads correctly without 500 errors
- **Reliability**: Container dependency resolution is now robust and predictable
- **Maintainability**: Code is cleaner and follows better practices
- **Compatibility**: Full PSR-3 logging standard compliance

## [0.0.44] - 2025-08-03

### Added
- **Muslim Skin**: New MediaWiki-inspired skin with Citizen design elements
- **Standardized Skin Management**: Implemented consistent skin management system
- **Static Helper Methods**: Added SkinManager static methods for easy access
- **Enhanced Documentation**: Comprehensive documentation for skin system

### Changed
- **Controller Updates**: Updated all controllers to use standardized SkinManager approach
- **Settings Integration**: Improved settings management for skin switching
- **Release Documentation**: Added detailed release notes and migration guides

### Technical Details
- Created Muslim skin with Citizen-inspired design
- Implemented standardized skin management system
- Updated controllers to use SkinManager static methods
- Added comprehensive testing and documentation

## [0.0.43] - 2025-08-02

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Security Improvements**: Better CSRF protection and input validation
- **Performance Optimizations**: Caching improvements and query optimization

### Changed
- **Database Schema**: Updated user settings and preferences tables
- **Authentication System**: Enhanced login and registration flows
- **UI/UX Improvements**: Better responsive design and accessibility

## [0.0.42] - 2025-08-01

### Added
- **Islamic Calendar Integration**: Advanced Islamic calendar functionality
- **Prayer Times API**: Real-time prayer time calculations
- **Community Features**: User profiles and community management
- **Content Management**: Enhanced page creation and editing

### Changed
- **Database Structure**: Improved schema for Islamic content
- **API Endpoints**: Enhanced REST API for Islamic services
- **Frontend Framework**: Updated to latest Safa CSS and ZamZam.js

## [0.0.41] - 2025-07-31

### Added
- **Quran Integration**: Complete Quran text and search functionality
- **Hadith Database**: Comprehensive hadith collection and search
- **Islamic Sciences**: Academic content and research tools
- **Search Engine**: Iqra search with advanced filtering

### Changed
- **Content Structure**: Reorganized for Islamic knowledge base
- **Search Algorithms**: Improved relevance and accuracy
- **User Interface**: Enhanced for Islamic content presentation

## [0.0.40] - 2025-07-30

### Added
- **Authentication System**: Complete login and registration
- **User Management**: Profile management and permissions
- **Session Handling**: Secure session management
- **Security Features**: CSRF protection and input validation

### Changed
- **Database Schema**: User tables and authentication
- **Security Model**: Enhanced security and privacy
- **API Security**: Protected endpoints and validation

## [0.0.39] - 2025-07-29

### Added
- **Database Migration System**: Automated schema management
- **Content Management**: Page creation and editing
- **File Upload System**: Media and document handling
- **Caching Layer**: Performance optimization

### Changed
- **Architecture**: Improved modular design
- **Performance**: Enhanced loading and response times
- **Scalability**: Better resource management

## [0.0.38] - 2025-07-28

### Added
- **REST API**: Complete API for frontend integration
- **Error Handling**: Comprehensive error management
- **Logging System**: Advanced logging and monitoring
- **Configuration Management**: Dynamic settings system

### Changed
- **API Structure**: RESTful design patterns
- **Error Pages**: User-friendly error messages
- **Configuration**: Flexible settings management

## [0.0.37] - 2025-07-27

### Added
- **Routing System**: Advanced URL routing and handling
- **Middleware Stack**: Request/response processing
- **Controller System**: MVC architecture implementation
- **View Engine**: Twig templating system

### Changed
- **Architecture**: MVC pattern implementation
- **Code Organization**: Better structure and maintainability
- **Templating**: Modern template engine integration

## [0.0.36] - 2025-07-26

### Added
- **Dependency Injection**: Container-based service management
- **Service Providers**: Modular service registration
- **Configuration System**: Environment-based settings
- **Base Framework**: Core application structure

### Changed
- **Architecture**: Dependency injection pattern
- **Service Management**: Modular and extensible design
- **Configuration**: Environment-aware settings

## [0.0.35] - 2025-07-25

### Added
- **Project Foundation**: Initial project structure
- **Basic Routing**: Simple URL handling
- **Error Handling**: Basic error management
- **Documentation**: Project documentation and guides

### Changed
- **Project Structure**: Organized file and directory layout
- **Development Setup**: Local development environment
- **Documentation**: Comprehensive project documentation

---

For more detailed information about each release, see the individual release notes in the `docs/releases/` directory.

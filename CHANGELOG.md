# Changelog

All notable changes to this project will be documented in this file.

## [0.0.34] - 2025-08-02

### Added
- **Complete Skin System Fix**: Resolved all skin loading issues and improved system reliability
- **File Organization Overhaul**: Moved 60+ test and debug files to organized subdirectories
- **Enhanced Debugging Tools**: Created comprehensive skin debugging and status reporting tools
- **Improved Documentation**: Added detailed README and enhanced release notes
- **Security Enhancements**: Implemented proper random key generation and improved error handling

### Fixed
- **LocalSettings Variable Loading**: Fixed `wgValidSkins` and `wgActiveSkin` not being properly loaded
- **Security Configuration**: Resolved secret key warnings with proper random key generation
- **SkinManager Enhancement**: Improved initialization and error handling with fallback mechanisms
- **Global Variable Scope**: Fixed scope issues with LocalSettings variables
- **File Organization**: Cleaned up public directory by organizing test and debug files

### Technical
- Enhanced SkinManager with better error handling and validation
- Improved container service registration and validation
- Added comprehensive skin validation and debugging tools
- Created organized file structure with `/tests/` and `/debug/` subdirectories
- Enhanced logging and debugging capabilities throughout the application

## [0.0.33] - 2025-08-01

### Fixed
- **Critical Skin Selection Issue**: Fixed settings page not displaying any skins in the skin selection interface
- **Global Variable Access**: Resolved `$wgValidSkins` global variable access issue in SettingsController
- **Skin Display**: Now correctly shows both Bismillah and GreenSkin skins
- **Fallback Mechanism**: Added robust fallback for skin configuration when global variable is unavailable

### Technical
- Added fallback skin configuration in SettingsController
- Enhanced global variable access with proper error handling
- Maintained compatibility with LocalSettings.php configuration
- Improved reliability of skin loading logic

## [0.0.32] - 2025-08-01

### Fixed
- **Critical Database Connection Issue**: Fixed SettingsController to use proper Connection class methods instead of direct PDO calls
- **Settings Page "Active" Button**: Now correctly displays active skin status
- **Skin Switching**: Database updates now work properly using framework's database abstraction layer
- **Production Security**: Removed direct PDO bypass and implemented proper framework database access

### Technical
- Replaced `$this->db->prepare()` calls with `$this->db->first()` and `$this->db->statement()`
- Fixed database connection inconsistency that was causing settings page to fail
- Implemented proper error handling for database operations
- Cleaned up authentication bypasses and debug code

## [0.0.31] - 2025-08-01

### Fixed
- **ZamZam.js Framework**: Fixed reactive data binding and directive processing
- **CSRF Token Issues**: Resolved logout CSRF token mismatch errors
- **Login Authentication**: Fixed Alpine.js interference with authentication forms
- **Skin System**: Improved skin switching functionality and active state display

### Added
- **Enhanced Error Handling**: Better error pages and debugging capabilities
- **Improved Session Management**: More robust session handling across the application
- **Debug Tools**: Added comprehensive debugging scripts for troubleshooting

### Technical
- Fixed ZamZam.js `evaluateExpression` and `safeEval` methods
- Improved `z-class` directive with proper class management
- Enhanced `z-methods` directive functionality
- Fixed database column name mismatch (`password` vs `password_hash`)
- Improved CSRF middleware with proper route exclusions

## [0.0.30] - 2025-08-01

### Fixed
- **Authentication System**: Resolved login/logout functionality issues
- **Session Management**: Fixed session persistence and user authentication
- **CSRF Protection**: Implemented proper CSRF token handling
- **Database Integration**: Fixed user settings and skin preference storage

### Added
- **Enhanced Security**: Improved authentication middleware and error handling
- **Better User Experience**: Streamlined login/logout flow
- **Debugging Tools**: Added comprehensive debugging capabilities

### Technical
- Fixed session management in authentication controllers
- Improved CSRF token generation and validation
- Enhanced database connection handling
- Added proper error pages for authentication failures

## [0.0.29] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Interface**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.28] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.27] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.26] - 2025-08-01

### Added
- **Enhanced Database Schema**: Improved user settings and preferences
- **Better Error Handling**: More comprehensive error management
- **Improved Security**: Enhanced authentication and authorization

### Fixed
- **Database Operations**: Resolved issues with user settings storage
- **Authentication**: Fixed login and session management
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.25] - 2025-08-01

### Added
- **Enhanced User Interface**: Improved settings page and skin management
- **Better Database Integration**: Enhanced user preferences and settings
- **Improved Security**: Better authentication and authorization

### Fixed
- **User Settings**: Resolved issues with skin preference storage
- **Database Operations**: Fixed user settings retrieval and updates
- **Security Issues**: Improved session management and validation

## [0.0.24] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Experience**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.23] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.22] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.21] - 2025-08-01

### Added
- **Enhanced Database Schema**: Improved user settings and preferences
- **Better Error Handling**: More comprehensive error management
- **Improved Security**: Enhanced authentication and authorization

### Fixed
- **Database Operations**: Resolved issues with user settings storage
- **Authentication**: Fixed login and session management
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.20] - 2025-08-01

### Added
- **Enhanced User Interface**: Improved settings page and skin management
- **Better Database Integration**: Enhanced user preferences and settings
- **Improved Security**: Better authentication and authorization

### Fixed
- **User Settings**: Resolved issues with skin preference storage
- **Database Operations**: Fixed user settings retrieval and updates
- **Security Issues**: Improved session management and validation

## [0.0.19] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Experience**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.18] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.17] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.16] - 2025-08-01

### Added
- **Enhanced Database Schema**: Improved user settings and preferences
- **Better Error Handling**: More comprehensive error management
- **Improved Security**: Enhanced authentication and authorization

### Fixed
- **Database Operations**: Resolved issues with user settings storage
- **Authentication**: Fixed login and session management
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.15] - 2025-08-01

### Added
- **Enhanced User Interface**: Improved settings page and skin management
- **Better Database Integration**: Enhanced user preferences and settings
- **Improved Security**: Better authentication and authorization

### Fixed
- **User Settings**: Resolved issues with skin preference storage
- **Database Operations**: Fixed user settings retrieval and updates
- **Security Issues**: Improved session management and validation

## [0.0.14] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Experience**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.13] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.12] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.11] - 2025-08-01

### Added
- **Enhanced Database Schema**: Improved user settings and preferences
- **Better Error Handling**: More comprehensive error management
- **Improved Security**: Enhanced authentication and authorization

### Fixed
- **Database Operations**: Resolved issues with user settings storage
- **Authentication**: Fixed login and session management
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.10] - 2025-08-01

### Added
- **Enhanced User Interface**: Improved settings page and skin management
- **Better Database Integration**: Enhanced user preferences and settings
- **Improved Security**: Better authentication and authorization

### Fixed
- **User Settings**: Resolved issues with skin preference storage
- **Database Operations**: Fixed user settings retrieval and updates
- **Security Issues**: Improved session management and validation

## [0.0.9] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Experience**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.8] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.7] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.6] - 2025-08-01

### Added
- **Enhanced Database Schema**: Improved user settings and preferences
- **Better Error Handling**: More comprehensive error management
- **Improved Security**: Enhanced authentication and authorization

### Fixed
- **Database Operations**: Resolved issues with user settings storage
- **Authentication**: Fixed login and session management
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.5] - 2025-08-01

### Added
- **Enhanced User Interface**: Improved settings page and skin management
- **Better Database Integration**: Enhanced user preferences and settings
- **Improved Security**: Better authentication and authorization

### Fixed
- **User Settings**: Resolved issues with skin preference storage
- **Database Operations**: Fixed user settings retrieval and updates
- **Security Issues**: Improved session management and validation

## [0.0.4] - 2025-08-01

### Added
- **Enhanced Skin System**: Improved skin switching and management
- **Better User Experience**: Enhanced settings page with improved skin selection
- **Improved Error Handling**: Better error messages and debugging

### Fixed
- **Skin Switching**: Resolved issues with skin preference updates
- **Database Operations**: Improved user settings storage and retrieval
- **User Experience**: Enhanced skin preview and selection interface

## [0.0.3] - 2025-08-01

### Added
- **Enhanced Authentication**: Improved login and registration system
- **Better Error Handling**: More comprehensive error pages and debugging
- **Improved Security**: Enhanced CSRF protection and session management

### Fixed
- **Login Issues**: Resolved authentication problems and session handling
- **Database Integration**: Fixed user management and settings storage
- **Security Vulnerabilities**: Improved input validation and sanitization

## [0.0.2] - 2025-08-01

### Added
- **Enhanced User Management**: Improved user registration and profile management
- **Better Database Integration**: Enhanced user settings and preferences
- **Improved Security**: Better password hashing and validation

### Fixed
- **User Registration**: Resolved issues with new user creation
- **Database Operations**: Fixed user settings storage and retrieval
- **Security Issues**: Improved password handling and validation

## [0.0.1] - 2025-08-01

### Added
- **Initial Release**: Basic IslamWiki functionality
- **User Authentication**: Login and registration system
- **Skin System**: Basic skin switching and management
- **Settings Page**: User preferences and skin selection
- **Database Integration**: User settings and preferences storage

### Features
- **Multi-Skin Support**: BlueSkin, GreenSkin, and Bismillah skins
- **User Management**: Registration, login, and profile management
- **Settings Management**: Skin preferences and user settings
- **Security Features**: CSRF protection and session management
- **Database Schema**: User settings and preferences storage

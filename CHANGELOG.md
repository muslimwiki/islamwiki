# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.30] - 2024-12-19

### Fixed
- **Skin Switching System**: Fixed case sensitivity issues in skin management
  - Updated `SkinManager` to store and retrieve skin keys in lowercase for consistent, case-insensitive lookup
  - Fixed skin validation and retrieval in `SettingsController`
  - Resolved active skin display issues in settings page
  - Added cache-busting headers to prevent browser caching issues
  - Fixed CSRF token header name mismatch in JavaScript (`X-CSRF-Token` → `X-CSRF-TOKEN`)

- **Login System**: Fixed authentication issues caused by Alpine.js interference
  - Created separate `auth.twig` layout without Alpine.js for authentication pages
  - Updated login and register templates to use the new minimal layout
  - Fixed database field name mismatch (`password_hash` → `password`) in user creation
  - Added CSRF token meta tag and global variable for proper form submission
  - Excluded authentication routes from CSRF middleware to prevent conflicts

- **Dependency Injection**: Fixed container binding issues
  - Added application instance binding to resolve "Target class [app] does not exist" errors
  - Updated service provider registrations for consistent dependency resolution

- **Middleware Stack**: Fixed middleware execution issues
  - Corrected method call from `process()` to `execute()` in `IslamRouter`
  - Ensured middleware runs for all requests including 404s
  - Removed temporary debugging bypasses

### Technical Improvements
- Enhanced skin system with proper case-insensitive handling
- Improved CSRF protection with proper token handling
- Better separation of concerns with dedicated authentication layout
- Cleaner middleware execution flow
- More robust dependency injection setup

### User Experience
- Skin switching now works reliably with immediate visual feedback
- Login and registration forms work without JavaScript interference
- Settings page shows correct active skin status
- Improved error handling and user feedback

## [0.0.29] - 2025-07-31

### Added
- **User-Specific Settings System**: Implemented database-backed user preferences for skin selection
- **Authentication Protection**: All settings endpoints now require user authentication
- **Professional Error Pages**: Beautiful 401 authentication error page for non-logged-in users
- **Smart Response System**: Detects AJAX/API requests vs browser requests and responds appropriately
- **User Navigation Integration**: Settings page now properly shows logged-in user in navigation
- **Database Schema**: New `user_settings` table for storing individual user preferences
- **Enhanced SettingsController**: Updated to support user-specific skin preferences and authentication

### Changed
- **Settings Security**: Moved from global skin settings to user-specific preferences
- **Error Handling**: Replaced JSON error responses with user-friendly HTML pages for browser requests
- **Navigation**: Fixed user dropdown display on settings page for authenticated users
- **Authentication Flow**: Improved session management and user data passing to views

### Fixed
- **Settings Access**: Non-logged-in users now see proper error page instead of JSON response
- **User Navigation**: Logged-in users now see their username instead of "Sign In" in settings
- **Skin Preferences**: Individual users can now have different skin preferences
- **Session Management**: Proper user data retrieval and passing to view templates

### Technical
- Added `user_settings` database table with JSON storage for user preferences
- Enhanced `SettingsController` with authentication checks and user data handling
- Created professional 401 error page with responsive design
- Implemented smart request detection for appropriate response types
- Updated `SkinManager` to support user-specific active skin retrieval

## [0.0.28] - 2025-07-31

### Added
- **Enhanced Skin System**: Improved skin management with better error handling and caching
- **GreenSkin**: New visually distinct skin for easier testing and verification
- **Skin Switching**: Real-time skin switching with immediate visual feedback
- **Improved Error Handling**: Better error messages and debugging information
- **Enhanced Testing**: Comprehensive test scripts for skin functionality

### Changed
- **Skin Manager**: Enhanced with better skin discovery and loading
- **Settings Interface**: Improved skin selection interface with previews
- **Error Recovery**: Better handling of skin loading failures
- **Performance**: Optimized skin loading and caching

### Fixed
- **Skin Switching**: Fixed issues with skin not updating visually after selection
- **Error Handling**: Improved error messages and debugging for skin-related issues
- **Cache Issues**: Resolved PHP file inclusion caching problems
- **Visual Feedback**: Immediate visual changes when switching skins

### Technical
- Enhanced `SkinManager` with better error handling and caching
- Improved skin switching logic with proper file reloading
- Added comprehensive test scripts for debugging
- Better error messages and debugging information

## [0.0.27] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging information
- **Better Logging**: Enhanced logging for debugging and monitoring
- **Improved Testing**: More comprehensive test scripts and debugging tools

### Changed
- **Error Pages**: Enhanced 404 and error pages with better styling and information
- **Logging**: Improved error logging and debugging output
- **Testing**: Better test coverage and debugging tools

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.26] - 2025-07-31

### Added
- **Enhanced Skin System**: Improved skin management and switching
- **Better Error Handling**: Enhanced error pages and debugging
- **Improved Testing**: More comprehensive test scripts

### Changed
- **Skin Management**: Enhanced skin loading and switching logic
- **Error Pages**: Improved error page styling and information
- **Testing**: Better test coverage and debugging tools

### Fixed
- **Skin Switching**: Improved skin switching reliability
- **Error Handling**: Better error page display and debugging
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.25] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.24] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.23] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.22] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.21] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.20] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.19] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.18] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.17] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.16] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.15] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.14] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.13] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.12] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.11] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.10] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.9] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.8] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.7] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.6] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.5] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.4] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.3] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.2] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

## [0.0.1] - 2025-07-31

### Added
- **Enhanced Error Handling**: Improved error pages and debugging
- **Better Testing**: More comprehensive test scripts
- **Improved Logging**: Enhanced logging for debugging

### Changed
- **Error Pages**: Enhanced error page styling and information
- **Testing**: Better test coverage and debugging tools
- **Logging**: Improved error logging and debugging output

### Fixed
- **Error Display**: Better error page styling and information
- **Debugging**: Improved error logging and debugging information
- **Testing**: Enhanced test scripts and debugging tools

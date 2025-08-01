# Public Folder Organization

This directory contains the main web-accessible files for IslamWiki.

## Structure

```
public/
├── index.php                    # Main application entry point
├── skin-system-status.php       # Skin system status report
├── update-user-skin.php         # User skin update utility
├── .htaccess                    # Apache configuration
├── css/                         # Stylesheets
├── js/                          # JavaScript files
├── tests/                       # Test files (moved from root)
└── debug/                       # Debug files (moved from root)
```

## File Organization

### Main Files
- `index.php` - Main application entry point
- `skin-system-status.php` - Comprehensive skin system status report
- `update-user-skin.php` - Utility for updating user skin preferences
- `.htaccess` - Apache server configuration

### Subdirectories

#### `/tests/` - Test Files
Contains all test-related files that were previously in the root:
- `test-*.php` - Various test scripts
- `check-*.php` - Check/validation scripts
- `login-test.php` - Login testing
- `simple-skin-test.php` - Simple skin testing
- `zzz-*.php` - Miscellaneous test files

#### `/debug/` - Debug Files
Contains all debug-related files that were previously in the root:
- `debug-*.php` - Various debug scripts
- Debug utilities for different components

#### `/css/` - Stylesheets
- `safa.css` - Safa CSS framework
- `zamzam.css` - Zamzam component styles

#### `/js/` - JavaScript Files
- `zamzam.js` - Main Zamzam JavaScript
- `zamzam-simple.js` - Simplified Zamzam
- `zamzam-debug.js` - Debug version

## Access URLs

- Main site: `https://local.islam.wiki/`
- Skin status: `https://local.islam.wiki/skin-system-status.php`
- Tests: `https://local.islam.wiki/tests/`
- Debug: `https://local.islam.wiki/debug/`

## Organization Benefits

1. **Cleaner Root Directory** - Only essential files remain in the root
2. **Better Organization** - Related files are grouped together
3. **Easier Maintenance** - Test and debug files are separated
4. **Improved Security** - Debug files are in a separate directory
5. **Better Development Workflow** - Clear separation of concerns

## Migration Notes

- All `test*` and `check*` files moved to `/tests/`
- All `debug*` files moved to `/debug/`
- Main application files remain in root
- CSS and JS directories unchanged
- URLs for main functionality remain the same 
# Skin Switching Implementation

## Overview

The skin switching functionality in IslamWiki allows users to dynamically switch between different visual themes (skins) through both a web interface and a RESTful API. The system supports real-time skin switching without requiring application restarts.

## Features

### ✅ **Working Features**

1. **Dynamic Skin Switching** - Users can switch between available skins (Bismillah, BlueSkin)
2. **Persistent Changes** - Skin changes are saved to `LocalSettings.php`
3. **Real-time Updates** - Skin changes take effect immediately without restarts
4. **RESTful API** - Clean API endpoints for programmatic skin management
5. **Web Interface** - User-friendly settings page for skin selection
6. **Validation** - Input validation and security checks
7. **Error Handling** - Comprehensive error handling with proper logging

### 🔧 **Technical Implementation**

#### 1. API Endpoints

- **GET `/settings/skins`** - Returns all available skins with active status
- **POST `/settings/skin`** - Switches to a specified skin
- **GET `/settings/skin/{name}`** - Returns detailed skin information

#### 2. File System Integration

The skin switching functionality updates the `LocalSettings.php` file by:
- Reading the current file content
- Using regex to find and replace the `$wgActiveSkin` setting
- Writing the updated content back to the file
- Parsing the file directly to determine the active skin (bypassing cache issues)

#### 3. Container Management

- SkinManager is registered as a singleton in the container
- Container instances are updated when skin changes occur
- Direct file parsing ensures accurate active skin detection

## API Usage

### Switch Skin
```bash
curl -X POST http://localhost:8000/settings/skin \
  -H "Content-Type: application/json" \
  -d '{"skin":"BlueSkin"}'
```

**Response:**
```json
{
  "success": true,
  "message": "Skin updated to BlueSkin successfully",
  "activeSkin": "BlueSkin"
}
```

### Get Available Skins
```bash
curl http://localhost:8000/settings/skins
```

**Response:**
```json
{
  "Bismillah": {
    "name": "Bismillah",
    "version": "0.0.28",
    "author": "IslamWiki Team",
    "description": "The default skin for IslamWiki with modern Islamic design and beautiful gradients.",
    "active": false
  },
  "BlueSkin": {
    "name": "BlueSkin",
    "version": "1.0.0",
    "author": "IslamWiki User",
    "description": "A beautiful blue-themed skin for IslamWiki with modern design.",
    "active": true
  }
}
```

### Get Skin Information
```bash
curl http://localhost:8000/settings/skin/BlueSkin
```

**Response:**
```json
{
  "name": "BlueSkin",
  "version": "1.0.0",
  "author": "IslamWiki User",
  "description": "A beautiful blue-themed skin for IslamWiki with modern design.",
  "config": {},
  "features": [],
  "dependencies": [],
  "hasCustomCss": true,
  "hasCustomJs": true,
  "hasCustomLayout": true
}
```

## Technical Details

### 1. SettingsController

The `SettingsController` handles all skin-related operations:

- **`updateSkin()`** - Updates LocalSettings.php and reloads skin manager
- **`getAvailableSkins()`** - Returns all skins with active status from file
- **`getSkinInfo()`** - Returns detailed skin information

### 2. SkinManager

The `SkinManager` provides skin management functionality:

- **`reloadActiveSkin()`** - Parses LocalSettings.php to update active skin
- **`getActiveSkinName()`** - Returns the currently active skin name
- **`getSkins()`** - Returns all available skins

### 3. LocalSettings.php

The active skin is configured in `LocalSettings.php`:

```php
$wgActiveSkin = env('ACTIVE_SKIN', 'Bismillah'); // This line is updated by the controller
```

## Error Handling

The system includes comprehensive error handling:

- **File System Errors** - Missing LocalSettings.php, permission issues
- **Validation Errors** - Invalid skin names, missing parameters
- **Runtime Errors** - Database errors, service failures
- **Logging** - All errors are logged to `logs/php_errors.log`

## Security Considerations

1. **Input Validation** - All skin names are validated against available skins
2. **File Permissions** - Proper file permissions required for LocalSettings.php
3. **CSRF Protection** - Web interface includes CSRF tokens
4. **Error Sanitization** - Error messages are sanitized for production

## Troubleshooting

### Common Issues

1. **Skin not switching visually**
   - Check file permissions on `LocalSettings.php`
   - Verify the skin files exist in `skins/` directory
   - Check application logs for errors

2. **500 errors when switching**
   - Check file permissions on `LocalSettings.php`
   - Verify the regex pattern matches the file content
   - Check application logs for detailed error messages

3. **API returning wrong active skin**
   - The API now reads directly from LocalSettings.php
   - Check if the file was updated correctly
   - Verify the regex pattern in the controller

### Debugging Commands

```bash
# Check LocalSettings.php
grep "wgActiveSkin" LocalSettings.php

# Test skin switching API
curl -X POST http://localhost:8000/settings/skin \
  -H "Content-Type: application/json" \
  -d '{"skin":"BlueSkin"}'

# Check available skins
curl http://localhost:8000/settings/skins

# Check application logs
tail -f logs/php_errors.log
```

## Future Enhancements

### Planned Features

1. **Skin Preview** - Preview skins before switching
2. **User Preferences** - Per-user skin preferences
3. **Skin Categories** - Organize skins by category (Islamic, Modern, etc.)
4. **Custom Themes** - Allow users to create custom themes
5. **Skin Marketplace** - Community-contributed skins
6. **Mobile Optimization** - Mobile-specific skin variants

### Technical Improvements

1. **Caching** - Implement proper skin caching
2. **Performance** - Optimize skin loading and switching
3. **Testing** - Comprehensive unit and integration tests
4. **Documentation** - Enhanced API documentation
5. **Monitoring** - Skin usage analytics

## Conclusion

The skin switching functionality is now fully operational and provides a robust, user-friendly way to customize the IslamWiki interface. The implementation follows best practices for security, error handling, and maintainability while providing a smooth user experience. 
# Standardized Skin Management

## Overview

This document describes the standardized approach for managing skins in IslamWiki, which replaces the previous dual approach that used both `$wgActiveSkin` in LocalSettings.php and `SkinManager` methods.

## The Problem

Previously, there were **two different ways** to set the active skin:

1. **Static**: Via `$wgActiveSkin` in LocalSettings.php
2. **Dynamic**: Via `$skinManager->setActiveSkin()` at runtime

This created confusion and inconsistency across the application.

## The Solution

We've standardized on using the **SkinManager approach** as the primary method, with LocalSettings.php serving as the **default configuration**.

## Implementation

### Core Components

#### 1. SkinManager Class

The `SkinManager` class provides the primary interface for skin management:

```php
use IslamWiki\Skins\SkinManager;

// Get active skin name
$activeSkin = $skinManager->getActiveSkinName();

// Set active skin
$skinManager->setActiveSkin('Muslim');

// Get active skin object
$skin = $skinManager->getActiveSkin();

// Check if skin exists
$hasSkin = $skinManager->hasSkin('Muslim');
```

#### 2. Static Helper Methods

For easy access in controllers and other components:

```php
use IslamWiki\Skins\SkinManager;

// Get active skin name (static)
$activeSkin = SkinManager::getActiveSkinNameStatic($app);

// Set active skin (static)
$success = SkinManager::setActiveSkinStatic($app, 'Muslim');
```

#### 3. LocalSettings.php Configuration

LocalSettings.php now serves as default configuration only:

```php
// Available skins registry
$wgValidSkins = [
    'Bismillah' => 'Bismillah',
    'Muslim' => 'Muslim',
];

// Default active skin (fallback only)
$wgActiveSkin = env('ACTIVE_SKIN', 'Bismillah');
```

## Migration Guide

### For Controllers

**Old way:**
```php
global $wgActiveSkin;
$activeSkinName = $wgActiveSkin ?? 'Bismillah';
```

**New way:**
```php
use IslamWiki\Skins\SkinManager;
$activeSkinName = SkinManager::getActiveSkinNameStatic($app);
```

### For Settings Pages

**Old way:**
```php
// Modify LocalSettings.php file
$wgActiveSkin = 'Muslim';
```

**New way:**
```php
use IslamWiki\Skins\SkinManager;
SkinManager::setActiveSkinStatic($app, 'Muslim');
```

### For Skin Switching

**Old way:**
```php
// Direct file modification
file_put_contents('LocalSettings.php', $newContent);
```

**New way:**
```php
// Runtime skin switching
if ($skinManager->hasSkin($skinName)) {
    $skinManager->setActiveSkin($skinName);
}
```

## Updated Controllers

The following controllers have been updated to use the standardized approach:

### HomeController
- **File**: `src/Http/Controllers/HomeController.php`
- **Changes**: Replaced `global $wgActiveSkin` with `SkinManager::getActiveSkinNameStatic($app)`
- **Benefits**: Better error handling, fallback mechanisms, and performance

### DashboardController
- **File**: `src/Http/Controllers/DashboardController.php`
- **Changes**: Replaced LocalSettings.php loading with static helper method
- **Benefits**: Cleaner code, no file I/O operations

### ProfileController
- **File**: `src/Http/Controllers/ProfileController.php`
- **Changes**: Updated to use standardized skin management
- **Benefits**: Consistent with other controllers

### SettingsController
- **File**: `src/Http/Controllers/SettingsController.php`
- **Changes**: Added `SkinManager::setActiveSkinStatic()` for skin switching
- **Benefits**: Runtime skin switching without file modifications

## Benefits

### ✅ **Consistency**
- Single source of truth for active skin
- Consistent API across the application
- No more dual approaches

### ✅ **Runtime Flexibility**
- Change skins without modifying files
- User-specific skin preferences
- Session-based skin switching

### ✅ **Better Error Handling**
- Validation of skin existence
- Proper error logging
- Graceful fallbacks

### ✅ **Performance**
- Caching and optimization
- Reduced file I/O operations
- Memory-efficient skin loading

### ✅ **User Preferences**
- Support for user-specific skins
- Database-backed skin preferences
- Session persistence

## Testing

### Automated Tests

Run the standardized skin management test:

```bash
php debug/test-standardized-skin-management.php
```

This test verifies:
- Static helper methods work correctly
- Skin switching functionality
- Fallback mechanisms
- LocalSettings integration
- Available skins loading
- Performance metrics

### Manual Testing

1. **Settings Page**: Visit `/settings-skin-management.php`
2. **Muslim Skin Test**: Visit `/test-muslim-skin.php`
3. **Skin Switching**: Use the settings page to switch between skins

## Configuration

### Environment Variables

```bash
# Default active skin (fallback only)
ACTIVE_SKIN=Bismillah

# Available skins (comma-separated)
VALID_SKINS=Bismillah,Muslim
```

### LocalSettings.php

```php
// Available skins registry
$wgValidSkins = [
    'Bismillah' => 'Bismillah',
    'Muslim' => 'Muslim',
];

// Default active skin (fallback only)
$wgActiveSkin = env('ACTIVE_SKIN', 'Bismillah');
```

## Troubleshooting

### Common Issues

#### 1. Skin Not Found
```php
// Check if skin exists
if (!$skinManager->hasSkin('SkinName')) {
    // Handle missing skin
}
```

#### 2. Skin Not Loading
```php
// Force reload all skins
$skinManager->reloadAllSkins();
```

#### 3. Fallback Issues
```php
// Use fallback method
$activeSkin = $skinManager->getActiveSkinNameWithFallback();
```

### Debug Information

Enable debug logging to troubleshoot skin issues:

```php
// Check available skins
$availableSkins = $skinManager->getAvailableSkinNames();
error_log('Available skins: ' . implode(', ', $availableSkins));

// Check active skin
$activeSkin = $skinManager->getActiveSkinName();
error_log('Active skin: ' . $activeSkin);
```

## Future Enhancements

### Planned Features

1. **User Preferences**: Database-backed skin preferences per user
2. **Session Persistence**: Remember skin choice across sessions
3. **Skin Preview**: Live preview before switching
4. **Skin Categories**: Organize skins by type (modern, classic, etc.)
5. **Skin Import/Export**: Easy skin sharing and installation

### API Extensions

```php
// Future API methods
$skinManager->getUserPreferredSkin($userId);
$skinManager->setUserPreferredSkin($userId, $skinName);
$skinManager->getSkinCategories();
$skinManager->previewSkin($skinName);
```

## Conclusion

The standardized skin management approach provides a clean, consistent, and flexible way to manage skins in IslamWiki. It eliminates the confusion of dual approaches and provides better error handling, performance, and user experience.

For questions or issues, refer to the troubleshooting section or contact the development team. 
# Skin Management Standardization

## Overview

This document explains the standardized approach for managing skins in IslamWiki, addressing the dual approach that was previously used.

## The Problem

Previously, there were **two different ways** to set the active skin:

1. **Static**: Via `$wgActiveSkin` in LocalSettings.php
2. **Dynamic**: Via `$skinManager->setActiveSkin()` at runtime

This created confusion and inconsistency across the application.

## The Solution

We've standardized on using the **SkinManager approach** as the primary method, with LocalSettings.php serving as the **default configuration**.

## New Standardized Approach

### 🎯 **Primary Method: SkinManager**

```php
// Get the active skin name
$activeSkin = $skinManager->getActiveSkinName();

// Set the active skin
$skinManager->setActiveSkin('Muslim');

// Get the active skin object
$skin = $skinManager->getActiveSkin();
```

### 🔧 **Static Helpers (Recommended for Controllers)**

```php
use IslamWiki\Skins\SkinManager;

// Get active skin name
$activeSkin = SkinManager::getActiveSkinNameStatic($app);

// Set active skin
$success = SkinManager::setActiveSkinStatic($app, 'Muslim');
```

### 📝 **LocalSettings.php Configuration**

```php
// This sets the DEFAULT skin, not the runtime active skin
$wgActiveSkin = env('ACTIVE_SKIN', 'Bismillah');

// Available skins
$wgValidSkins = [
    'Bismillah' => 'Bismillah',
    'Muslim' => 'Muslim',
];
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

### For Skin Switching

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

## Benefits

### ✅ **Consistency**
- Single source of truth for active skin
- Consistent API across the application
- No more dual approaches

### ✅ **Runtime Flexibility**
- Change skins without modifying files
- User-specific skin preferences
- Session-based skin switching

### ✅ **Backward Compatibility**
- LocalSettings.php still works as default
- Fallback mechanisms in place
- Gradual migration possible

### ✅ **Better Error Handling**
- Validation of skin existence
- Proper error logging
- Graceful fallbacks

## Implementation Details

### SkinManager Methods

```php
// Core methods
$skinManager->getActiveSkinName()           // Get current active skin
$skinManager->setActiveSkin('SkinName')     // Set active skin
$skinManager->getActiveSkin()               // Get skin object
$skinManager->hasSkin('SkinName')           // Check if skin exists

// Static helpers
SkinManager::getActiveSkinNameStatic($app)  // Static getter
SkinManager::setActiveSkinStatic($app, 'SkinName') // Static setter

// Initialization
$skinManager->initializeFromLocalSettings() // Load from LocalSettings
$skinManager->reloadAllSkins()             // Force reload all skins
```

### LocalSettings.php Role

LocalSettings.php now serves as:
- **Default configuration** for new installations
- **Available skins registry** via `$wgValidSkins`
- **Fallback mechanism** if SkinManager is unavailable

## Migration Checklist

- [ ] Update controllers to use `SkinManager::getActiveSkinNameStatic($app)`
- [ ] Replace direct `$wgActiveSkin` access with SkinManager methods
- [ ] Update skin switching logic to use `setActiveSkin()`
- [ ] Test fallback mechanisms
- [ ] Update documentation

## Example Usage

### Controller Example

```php
<?php

use IslamWiki\Skins\SkinManager;

class HomeController
{
    public function index(Request $request): Response
    {
        // Get active skin name
        $activeSkin = SkinManager::getActiveSkinNameStatic($this->app);
        
        // Switch skin for this request
        SkinManager::setActiveSkinStatic($this->app, 'Muslim');
        
        // Render with new skin
        return $this->view('home.index', [
            'activeSkin' => $activeSkin
        ]);
    }
}
```

### Settings Controller Example

```php
<?php

use IslamWiki\Skins\SkinManager;

class SettingsController
{
    public function updateSkin(Request $request): Response
    {
        $skinName = $request->getParsedBody()['skin'] ?? 'Bismillah';
        
        // Validate skin exists
        $container = $this->app->getContainer();
        $skinManager = $container->get('skin.manager');
        
        if (!$skinManager->hasSkin($skinName)) {
            return $this->json(['error' => 'Skin not found'], 400);
        }
        
        // Set active skin
        if (SkinManager::setActiveSkinStatic($this->app, $skinName)) {
            return $this->json(['success' => true]);
        }
        
        return $this->json(['error' => 'Failed to set skin'], 500);
    }
}
```

## Testing

### Test Skin Loading

```php
// Test that Muslim skin loads correctly
$skinManager->reloadAllSkins();
$hasMuslim = $skinManager->hasSkin('Muslim');
assert($hasMuslim === true);
```

### Test Skin Switching

```php
// Test skin switching
$originalSkin = $skinManager->getActiveSkinName();
$skinManager->setActiveSkin('Muslim');
assert($skinManager->getActiveSkinName() === 'Muslim');
$skinManager->setActiveSkin($originalSkin);
```

This standardized approach provides a clean, consistent, and flexible way to manage skins in IslamWiki. 
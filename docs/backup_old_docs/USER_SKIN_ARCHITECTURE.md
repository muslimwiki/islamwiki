# User Skin Architecture

## Overview

The IslamWiki skin system has been restructured to provide a clear separation between backend skin management and user-facing skins. This new architecture makes it easier for users to add and customize skins without touching the source code.

## Architecture Changes

### Before: Source-Based Skins
```
src/Skins/
├── Skin.php                    # Base class
├── SkinManager.php            # Management
├── Bismillah/                 # Skin in source
│   ├── BismillahSkin.php     # PHP class
│   ├── css/
│   ├── js/
│   └── templates/
└── [Other Skins]/            # More source skins
```

### After: User-Facing Skins
```
/skins/                        # User skins directory
├── Bismillah/                 # Default skin
│   ├── skin.json             # Configuration
│   ├── css/
│   ├── js/
│   └── templates/
├── BlueSkin/                  # Example user skin
└── [CustomSkin]/             # User-created skins

src/Skins/                     # Backend system only
├── Skin.php                   # Abstract base class
├── SkinManager.php            # Skin management
├── UserSkin.php               # User skin handler
└── SkinServiceProvider.php    # Service integration
```

## Key Benefits

### 1. User-Friendly
- **No Source Code Changes**: Users can add skins without modifying the application source
- **Simple File Structure**: Just create a folder in `/skins/` with the required files
- **JSON Configuration**: Easy-to-understand configuration format
- **Immediate Activation**: Skins are automatically discovered and available

### 2. Separation of Concerns
- **Backend Logic**: Handled by classes in `src/Skins/`
- **User Skins**: Stored in `/skins/` directory
- **Configuration**: Managed through `LocalSettings.php` or environment variables
- **Assets**: CSS, JS, and templates in skin directories

### 3. Easy Customization
- **Color Schemes**: Modify CSS variables in skin files
- **Layouts**: Create custom Twig templates
- **Functionality**: Add custom JavaScript
- **Dependencies**: Specify external libraries in `skin.json`

## How It Works

### 1. Skin Discovery
The `SkinManager` scans the `/skins/` directory for folders containing `skin.json` files:

```php
private function loadSkins(): void
{
    $skinsPath = $this->app->getBasePath() . '/skins';
    $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
    
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $skinConfigFile = $skinDir . '/skin.json';
        
        if (file_exists($skinConfigFile)) {
            $config = json_decode(file_get_contents($skinConfigFile), true);
            $skin = new UserSkin($config, $skinDir);
            $this->skins[$skinName] = $skin;
        }
    }
}
```

### 2. UserSkin Class
The `UserSkin` class handles user-defined skins from JSON configuration:

```php
class UserSkin extends Skin
{
    private array $jsonConfig;
    
    public function __construct(array $config, string $skinPath)
    {
        $this->jsonConfig = $config;
        $this->setSkinPath($skinPath);
        $this->initializeSkin();
    }
    
    protected function initializeSkin(): void
    {
        $this->name = $this->jsonConfig['name'] ?? 'Unknown';
        $this->version = $this->jsonConfig['version'] ?? '1.0.0';
        $this->author = $this->jsonConfig['author'] ?? 'Unknown';
        $this->description = $this->jsonConfig['description'] ?? 'User-defined skin';
        $this->config = $this->jsonConfig['config'] ?? [];
    }
}
```

### 3. Configuration Integration
Skins are activated through `LocalSettings.php`:

```php
// Active skin configuration
$wgActiveSkin = env('ACTIVE_SKIN', 'Bismillah');

// Skin configuration options
$wgSkinConfig = [
    'enable_animations' => env('SKIN_ANIMATIONS', true),
    'enable_gradients' => env('SKIN_GRADIENTS', true),
    'enable_dark_theme' => env('SKIN_DARK_THEME', false),
];
```

## Creating a User Skin

### Step 1: Create Directory Structure
```bash
mkdir -p skins/MyCustomSkin/{css,js,templates}
```

### Step 2: Create Configuration
Create `skins/MyCustomSkin/skin.json`:
```json
{
    "name": "MyCustomSkin",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "A custom skin for IslamWiki",
    "type": "user-skin",
    "directory": "MyCustomSkin",
    "assets": {
        "css": "css/style.css",
        "js": "js/script.js",
        "layout": "templates/layout.twig"
    },
    "config": {
        "primary_color": "#your-color",
        "enable_animations": true
    },
    "features": ["responsive", "animations"],
    "dependencies": {
        "tailwind": "cdn"
    }
}
```

### Step 3: Add Assets
- **CSS**: `css/bismillah.css` - Your custom styles
- **JavaScript**: `js/bismillah.js` - Your custom functionality
- **Layout**: `templates/layout.twig` - Custom layout (optional)

### Step 4: Activate Skin
Edit `LocalSettings.php`:
```php
$wgActiveSkin = 'MyCustomSkin';
```

## Migration from Old System

### What Changed
1. **Skin Location**: Moved from `src/Skins/` to `/skins/`
2. **Configuration**: Now uses JSON instead of PHP classes
3. **Discovery**: Automatic discovery based on directory structure
4. **Activation**: Through `LocalSettings.php` instead of code

### Migration Steps
1. **Move Existing Skins**: Copy skin folders from `src/Skins/` to `/skins/`
2. **Create JSON Config**: Add `skin.json` to each skin directory
3. **Update References**: Change any hardcoded skin references to use `$wgActiveSkin`
4. **Test**: Verify skins work with the new system

### Example Migration
**Before** (PHP class):
```php
class BismillahSkin extends Skin
{
    protected function initializeSkin(): void
    {
        $this->name = 'Bismillah';
        $this->version = '0.0.28';
        // ...
    }
}
```

**After** (JSON config):
```json
{
    "name": "Bismillah",
    "version": "0.0.28",
    "author": "IslamWiki Team",
    "description": "The default skin for IslamWiki",
    "type": "user-skin",
    "directory": "Bismillah",
    "assets": {
        "css": "css/style.css",
        "js": "js/script.js",
        "layout": "templates/layout.twig"
    }
}
```

## Benefits for Users

### 1. Easy Installation
Users can simply:
1. Download a skin
2. Extract to `/skins/`
3. Set as active in `LocalSettings.php`
4. Done!

### 2. No Code Changes
- No need to modify source code
- No need to understand PHP classes
- No need to restart the application

### 3. Simple Configuration
- JSON format is easy to read and edit
- Clear structure for assets and settings
- Environment variable support

### 4. Version Control Friendly
- Skins are separate from application code
- Easy to backup and restore
- Can be shared independently

## Benefits for Developers

### 1. Clean Architecture
- Clear separation between backend and user skins
- Modular design
- Easy to extend and maintain

### 2. Flexible System
- Supports any skin structure
- Configurable through multiple methods
- Extensible for future features

### 3. Better Testing
- Isolated skin testing
- Easy to mock and test
- Clear test boundaries

## Future Enhancements

### Planned Features
1. **Skin Marketplace**: Browse and install skins
2. **Live Preview**: Preview skins before activation
3. **Skin Builder**: Visual skin creation tool
4. **Theme Editor**: In-browser customization
5. **Plugin System**: Extend skin functionality

### API Extensions
1. **Skin API**: RESTful API for skin management
2. **Hook System**: Customize skin behavior
3. **Event System**: React to skin events
4. **Plugin System**: Extend skin functionality

## Conclusion

The new user skin architecture provides a much more user-friendly and maintainable system. Users can easily add and customize skins without touching the source code, while developers have a clean and extensible backend system to work with.

The separation between backend skin management and user-facing skins makes the system more modular, easier to understand, and more flexible for future enhancements. 
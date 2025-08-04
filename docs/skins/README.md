# IslamWiki Skin System

## Overview

The IslamWiki Skin System provides a modular theming architecture that separates the backend skin management from user-facing skins. This allows users to easily add and customize skins without touching the source code. **New in v0.0.47**: Dynamic skin discovery and comprehensive settings interface. **Fixed in v0.0.48**: Authentication compatibility and session safety. **Fixed in v0.0.49**: Muslim skin content rendering and proper skin activation.

## Architecture

### Backend System (`src/Skins/`)
- **Skin.php**: Abstract base class for all skins
- **SkinManager.php**: Manages skin discovery, loading, and activation
- **UserSkin.php**: Handles user-defined skins from JSON configuration
- **SkinServiceProvider.php**: Integrates the skin system with the application

### User Skins (`/skins/`)
- **Bismillah**: Default skin with Islamic design
- **Muslim**: Modern skin inspired by Citizen MediaWiki
- **Dynamic Discovery**: New skins automatically appear in settings

## Directory Structure

```
/skins/                          # User-facing skins directory
├── Bismillah/                   # Default skin
│   ├── skin.json               # Skin configuration
│   ├── css/
│   │   └── bismillah.css       # Skin CSS
│   ├── js/
│   │   └── bismillah.js        # Skin JavaScript
│   └── templates/
│       └── layout.twig         # Custom layout template
├── Muslim/                      # Modern skin
│   ├── skin.json
│   ├── css/
│   ├── js/
│   └── templates/
└── [CustomSkin]/               # User-created skins

src/Skins/                      # Backend skin system
├── Skin.php                    # Abstract base class
├── SkinManager.php             # Skin management
├── UserSkin.php                # User skin handler
└── SkinServiceProvider.php     # Service integration
```

## Dynamic Skin Discovery

**New Feature**: The skin system now automatically discovers all skins in the `/skins/` directory. New skins will automatically appear in the settings interface without requiring configuration changes.

### How It Works
1. **Automatic Scanning**: The system scans the `/skins/` directory on startup
2. **Configuration Validation**: Each skin's `skin.json` is validated
3. **Settings Integration**: Discovered skins appear in the settings page
4. **User Selection**: Users can switch between any available skin

### Settings Interface
- **Comprehensive Settings Page**: Visit `/settings` to manage skins
- **Skin Information**: View detailed metadata, features, and dependencies
- **User Preferences**: Individual skin preferences stored per user
- **API Endpoints**: RESTful API for skin management
- **Authentication Safe**: Skin middleware doesn't interfere with login process

## Available Skins

### Bismillah (Default)
The **Bismillah** skin is the default skin for IslamWiki, featuring:

#### Features
- **Modern Islamic Design**: Beautiful gradients and Islamic-inspired colors
- **Responsive Layout**: Works perfectly on all devices
- **Glass Morphism**: Modern glass-like effects
- **Dark Theme Support**: Optional dark mode
- **Animations**: Smooth transitions and hover effects
- **Accessibility**: WCAG compliant design

#### Configuration
```json
{
    "name": "Bismillah",
    "version": "0.0.28",
    "author": "IslamWiki Team",
    "description": "The default skin for IslamWiki with modern Islamic design and beautiful gradients.",
    "type": "user-skin",
    "directory": "Bismillah",
    "assets": {
        "css": "css/bismillah.css",
        "js": "js/bismillah.js",
        "layout": "templates/layout.twig"
    },
    "config": {
        "primary_color": "#667eea",
        "secondary_color": "#764ba2",
        "accent_color": "#f093fb",
        "text_color": "#1f2937",
        "background_color": "#f8fafc",
        "card_background": "#ffffff",
        "border_color": "#e5e7eb",
        "enable_animations": true,
        "enable_gradients": true,
        "font_family": "Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif"
    },
    "features": [
        "responsive",
        "dark-theme",
        "animations",
        "gradients",
        "glass-morphism",
        "accessibility"
    ],
    "dependencies": {
        "tailwind": "cdn",
        "zamzamjs": "local",
        "prism": "cdn"
    }
}
```

### Muslim
The **Muslim** skin provides a modern, responsive design inspired by Citizen MediaWiki with proper content rendering:

#### Features
- **Citizen-Inspired Design**: Modern MediaWiki-inspired layout
- **Islamic Aesthetics**: Beautiful Islamic design elements with proper CSS class naming
- **Responsive Design**: Mobile-friendly interface
- **Extension Support**: Enhanced support for extensions
- **Accessibility**: Full accessibility compliance
- **Content Rendering**: Proper content display in main body area
- **CSS Framework Integration**: Seamless integration with Safa CSS framework

#### Configuration
```json
{
    "name": "Muslim",
    "version": "0.0.1",
    "author": "IslamWiki Team",
    "description": "A beautiful, usable, responsive skin inspired by Citizen MediaWiki skin with Islamic design elements.",
    "type": "user-skin",
    "directory": "Muslim",
    "assets": {
        "css": "css/muslim.css",
        "js": "js/muslim.js",
        "layout": "templates/layout.twig"
    },
    "config": {
        "primary_color": "#2c5aa0",
        "secondary_color": "#4a90e2",
        "accent_color": "#f39c12",
        "text_color": "#2c3e50",
        "background_color": "#ecf0f1",
        "card_background": "#ffffff",
        "border_color": "#bdc3c7",
        "enable_animations": true,
        "enable_gradients": true,
        "font_family": "Roboto, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif"
    },
    "features": [
        "responsive",
        "dark-theme",
        "animations",
        "gradients",
        "glass-morphism",
        "accessibility",
        "extension-support"
    ],
    "dependencies": {
        "tailwind": "cdn",
        "zamzamjs": "local",
        "prism": "cdn"
    }
}
```

## Settings Management

### Settings Page
Visit `/settings` to access the comprehensive settings interface:

#### Appearance Tab
- **Skin Selection**: Choose from all available skins
- **Skin Information**: View detailed metadata and features
- **Theme Options**: Configure animations, gradients, and dark theme
- **Live Preview**: See skin changes immediately

#### Account Tab
- **Profile Management**: Update user information
- **Security Settings**: Change password and security preferences
- **Account Actions**: Export data, delete account

#### Privacy Tab
- **Privacy Settings**: Control profile visibility and data sharing
- **Cookie Preferences**: Manage cookie settings
- **Data Management**: Download or delete personal data

#### Notifications Tab
- **Notification Preferences**: Configure email and browser notifications
- **Email Settings**: Choose which emails to receive
- **Schedule Settings**: Set notification frequency and quiet hours

### API Endpoints
- `GET /settings` - Settings page
- `POST /settings/skin` - Update user's skin preference
- `GET /settings/skins` - Get available skins
- `GET /settings/skin/{name}` - Get skin information

### User Preferences
- **Individual Settings**: Each user has their own skin preference
- **Database Storage**: Preferences stored in `user_settings` table
- **Session Persistence**: Settings persist across sessions
- **Fallback System**: Default to global skin if no user preference

## Creating a New Skin

### Step 1: Create Skin Directory
```bash
mkdir -p skins/MyCustomSkin/{css,js,templates}
```

### Step 2: Create Skin Configuration
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
        "secondary_color": "#your-color",
        "enable_animations": true,
        "enable_gradients": true
    },
    "features": [
        "responsive",
        "animations",
        "gradients"
    ],
    "dependencies": {
        "tailwind": "cdn",
        "zamzamjs": "local"
    }
}
```

### Step 3: Create CSS File
Create `skins/MyCustomSkin/css/mycustomskin.css`:
```css
:root {
    --primary-color: #your-color;
    --secondary-color: #your-color;
    /* Define your CSS variables */
}

/* Your custom styles */
body {
    font-family: 'Your Font', sans-serif;
    background-color: var(--background-color);
}

/* Add your custom CSS here */
```

### Step 4: Create JavaScript File
Create `skins/MyCustomSkin/js/mycustomskin.js`:
```javascript
// Your custom JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize your skin functionality
    console.log('MyCustomSkin loaded!');
});
```

### Step 5: Create Layout Template (Optional)
Create `skins/MyCustomSkin/templates/layout.twig`:
```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}IslamWiki{% endblock %}</title>
    
    <!-- Your custom fonts and external CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Your+Font:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Skin CSS -->
    <style>
        {{ skin_css|raw }}
    </style>
</head>
<body>
    <!-- Your custom layout -->
    <header>
        <!-- Your header content -->
    </header>
    
    <main>
        {% block content %}{% endblock %}
    </main>
    
    <footer>
        <!-- Your footer content -->
    </footer>
    
    <!-- Skin JavaScript -->
    <script>
        {{ skin_js|raw }}
    </script>
</body>
</html>
```

### Step 6: Automatic Discovery
Once you create your skin, it will automatically appear in the settings page. No additional configuration required!

## Activating a Skin

### Method 1: Settings Page (Recommended)
1. Visit `/settings`
2. Go to the "Appearance" tab
3. Select your desired skin
4. Click "Select Skin"
5. The change takes effect immediately

### Method 2: LocalSettings Configuration
Edit `LocalSettings.php`:
```php
// Set the active skin
$wgActiveSkin = 'MyCustomSkin';

// Optional: Configure skin features
$wgSkinConfig = [
    'enable_animations' => true,
    'enable_gradients' => true,
    'enable_dark_theme' => false,
];
```

### Method 3: Environment Variables
Set in your `.env` file:
```env
ACTIVE_SKIN=MyCustomSkin
SKIN_ANIMATIONS=true
SKIN_GRADIENTS=true
SKIN_DARK_THEME=false
```

## Using Skins Programmatically

### Get Active Skin
```php
use IslamWiki\Skins\SkinManager;

$skinManager = $app->getContainer()->get('skin.manager');
$activeSkin = $skinManager->getActiveSkin();

echo $activeSkin->getName(); // "Bismillah"
echo $activeSkin->getVersion(); // "0.0.28"
```

### Get All Available Skins
```php
$availableSkins = $skinManager->getSkins();
foreach ($availableSkins as $skinName => $skin) {
    echo $skinName . ': ' . $skin->getDescription() . "\n";
}
```

### Switch Active Skin
```php
$skinManager->setActiveSkin('BlueSkin');
```

### Get User-Specific Skin
```php
$userId = 1;
$userSkin = $skinManager->getActiveSkinForUser($userId);
echo $userSkin->getName(); // User's preferred skin
```

## View Helpers

The skin system provides several Twig helpers:

### `skin_css`
Outputs the CSS content of the active skin:
```twig
<style>
    {{ skin_css|raw }}
</style>
```

### `skin_js`
Outputs the JavaScript content of the active skin:
```twig
<script>
    {{ skin_js|raw }}
</script>
```

### `skin_name`
Returns the name of the active skin:
```twig
<p>Current skin: {{ skin_name }}</p>
```

### `skin_metadata`
Returns metadata about the active skin:
```twig
{% set metadata = skin_metadata() %}
<p>Skin: {{ metadata.name }} v{{ metadata.version }}</p>
<p>Author: {{ metadata.author }}</p>
```

### `available_skins`
Returns a list of all available skins:
```twig
{% set skins = available_skins() %}
<select name="skin">
    {% for skinName, skin in skins %}
        <option value="{{ skinName }}" {% if skinName == skin_name %}selected{% endif %}>
            {{ skin.name }}
        </option>
    {% endfor %}
</select>
```

### `skin_asset`
Generates a URL for a skin asset:
```twig
<link rel="stylesheet" href="{{ skin_asset('css/bismillah.css') }}">
<script src="{{ skin_asset('js/bismillah.js') }}"></script>
```

### `skin_has_custom_layout`
Checks if the active skin has a custom layout:
```twig
{% if skin_has_custom_layout() %}
    {% include skin_layout_path() %}
{% else %}
    {% include 'layouts/app.twig' %}
{% endif %}
```

## Skin Configuration

### CSS Variables
Skins can define CSS custom properties:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --text-color: #1f2937;
    --background-color: #f8fafc;
    --card-background: #ffffff;
    --border-color: #e5e7eb;
}
```

### JavaScript Features
Skins can include custom JavaScript functionality:
- User dropdown menus
- Smooth scrolling
- Loading states
- Hover effects
- Intersection Observer animations
- Keyboard navigation
- Search functionality
- Theme toggles
- Copy-to-clipboard
- Lazy loading
- Form validation
- Mobile menu toggles

## Best Practices

### 1. Skin Organization
- Keep skin files organized in subdirectories
- Use descriptive names for CSS classes
- Follow BEM methodology for CSS
- Use CSS custom properties for theming

### 2. Performance
- Minimize CSS and JavaScript file sizes
- Use CDN resources when possible
- Implement lazy loading for images
- Optimize animations for performance

### 3. Accessibility
- Ensure proper color contrast ratios
- Provide keyboard navigation support
- Include ARIA labels and roles
- Test with screen readers

### 4. Responsive Design
- Use mobile-first approach
- Test on various screen sizes
- Ensure touch-friendly interfaces
- Optimize for different devices

### 5. Browser Compatibility
- Test on multiple browsers
- Use progressive enhancement
- Provide fallbacks for older browsers
- Consider polyfills when needed

## Testing

### Test Your Skin
```bash
php debug/debug-skin-management.php
php debug/debug-settings-test.php
```

### Manual Testing
1. Create your skin in `/skins/`
2. Visit `/settings` to see it in the skin selection
3. Select your skin to test it
4. Test on different devices and browsers

## Troubleshooting

### Common Issues

#### Skin Not Loading
- Check that `skin.json` exists and is valid JSON
- Verify all asset files exist
- Check file permissions
- Run debug scripts to identify issues

#### CSS Not Applying
- Ensure CSS file path is correct in `skin.json`
- Check for CSS syntax errors
- Verify CSS is being loaded in the page

#### JavaScript Errors
- Check browser console for errors
- Verify JavaScript file path in `skin.json`
- Test JavaScript functionality step by step

#### Layout Issues
- Check if custom layout template exists
- Verify Twig syntax in layout files
- Test with default layout first

#### Settings Not Working
- Check if user is logged in
- Verify database connection
- Check user_settings table exists
- Run debug scripts to identify issues

### Debug Mode
Enable debug mode to see detailed error messages:
```php
// In LocalSettings.php
$wgDebug = true;
```

### Debug Tools
Use the provided debug scripts:
```bash
# Test skin management
php debug/debug-skin-management.php

# Test settings functionality
php debug/debug-settings-test.php

# Test detailed skin loading
php debug/debug-skin-loading-detailed.php
```

## Contributing

### Adding New Skins
1. Create your skin in `/skins/`
2. Follow the naming conventions
3. Include proper documentation
4. Test thoroughly
5. Submit for review

### Improving Existing Skins
1. Fork the repository
2. Make your improvements
3. Test on multiple devices
4. Submit a pull request

### Reporting Issues
1. Check existing issues first
2. Provide detailed reproduction steps
3. Include browser and device information
4. Attach screenshots if relevant
5. Run debug scripts and include output

## Future Enhancements

### Planned Features
- **Skin Marketplace**: Browse and install skins
- **Live Preview**: Preview skins before activation
- **Skin Builder**: Visual skin creation tool
- **Theme Editor**: In-browser theme customization
- **Skin Templates**: Pre-built skin templates
- **Advanced Customization**: More configuration options

### API Extensions
- **Skin API**: RESTful API for skin management
- **Plugin System**: Extend skin functionality
- **Hook System**: Customize skin behavior
- **Event System**: React to skin events

## License

This skin system is part of IslamWiki and is licensed under the GNU Affero General Public License v3.0.

## Support

For support with the skin system:
- Check the documentation
- Search existing issues
- Create a new issue with details
- Join the community discussions 
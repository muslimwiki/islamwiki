# IslamWiki Skin System

## 🎨 **Overview**

The IslamWiki skin system provides a flexible, WordPress-inspired theme architecture that allows developers to create beautiful, Islamic-themed skins for the platform. Built on the **Safa CSS Framework** and **Marwa JavaScript Framework**, skins provide consistent styling and functionality while maintaining Islamic values and aesthetics.

---

## 🏗️ **Architecture Overview**

### **Skin System Components**
```
Skin System Architecture:
├── 📁 Safa (CSS Framework) - Purity and cleanliness in styling
├── 📁 Marwa (JavaScript Framework) - Excellence in interactivity
├── 📁 Application (Application System) - Order and organization
├── 📁 Configuration (Configuration) - Management and planning
└── 📁 Bayan (Formatting) - Explanation and presentation
```

### **Core Principles**
- **Islamic Aesthetics**: Beautiful, culturally appropriate designs
- **Responsive Design**: Mobile-first, accessible to all devices
- **Progressive Enhancement**: Works without JavaScript
- **Performance Focus**: Optimized for speed and efficiency
- **Accessibility**: WCAG 2.1 AA compliance
- **Customization**: Easy to customize and extend

---

## 🕌 **Islamic Naming Conventions**

### **Skin File Naming**
All skin files must follow Islamic naming conventions:

```php
// ✅ Correct - Using Islamic naming
class SafaSkin_Bismillah extends SafaSkin
{
    public function getSkinName(): string
    {
        return 'Bismillah';
    }
    
    public function getSkinTheme(): string
    {
        return 'islamic';
    }
}

// ❌ Incorrect - Generic naming
class BismillahSkin extends Skin
{
    // No prefix
}
```

### **Skin Directory Structure**
```
skins/
├── 📁 {SkinName}/
│   ├── 📄 {SkinName}Skin.php        # Main skin class
│   ├── 📄 skin.json                 # Skin configuration
│   ├── 📁 safa/                     # CSS framework files
│   │   ├── 📄 safa-base.css         # Base styles
│   │   ├── 📄 safa-components.css   # Component styles
│   │   ├── 📄 safa-themes.css       # Theme variations
│   │   └── 📄 safa-utilities.css    # Utility classes
│   ├── 📁 marwa/                     # JavaScript framework files
│   │   ├── 📄 marwa-core.js          # Core functionality
│   │   ├── 📄 marwa-components.js    # UI components
│   │   └── 📄 marwa-themes.js        # Theme functionality
│   ├── 📁 templates/                  # Twig templates
│   │   ├── 📄 base.twig              # Base template
│   │   ├── 📄 home.twig              # Home page template
│   │   └── 📄 page.twig              # Page template
│   ├── 📁 images/                     # Skin-specific images
│   │   ├── 📄 logo.png                # Skin logo
│   │   ├── 📄 favicon.ico             # Favicon
│   │   └── 📄 background.jpg          # Background image
│   └── 📁 assets/                     # Additional assets
       ├── 📁 fonts/                   # Custom fonts
       ├── 📁 icons/                   # Icon sets
       └── 📁 media/                   # Media files
```

---

## 🎨 **Safa CSS Framework Integration**

### **CSS Framework Structure**
The **Safa CSS Framework** provides the foundation for all skins:

```css
/* ✅ Correct - Using Safa framework naming */
.safa-layout {
    /* Layout utilities */
}

.safa-typography {
    /* Typography system */
}

.safa-colors {
    /* Color system */
}

.safa-components {
    /* Component styles */
}

.safa-themes {
    /* Theme variations */
}

.safa-utilities {
    /* Utility classes */
}

/* ❌ Incorrect - Generic naming */
.layout {
    /* No prefix */
}

.typography {
    /* No prefix */
}
```

### **Theme System**
```css
/* Islamic Theme */
.safa-theme--islamic {
    --primary-color: #2E7D32;      /* Islamic green */
    --secondary-color: #1B5E20;    /* Dark green */
    --accent-color: #FFD700;       /* Gold accent */
    --text-color: #212121;         /* Dark text */
    --background-color: #FAFAFA;   /* Light background */
}

/* Ramadan Theme */
.safa-theme--ramadan {
    --primary-color: #1976D2;      /* Ramadan blue */
    --secondary-color: #0D47A1;    /* Dark blue */
    --accent-color: #FF6B6B;       /* Red accent */
    --text-color: #FFFFFF;         /* White text */
    --background-color: #1A237E;   /* Dark background */
}

/* Light Theme */
.safa-theme--light {
    --primary-color: #2196F3;      /* Material blue */
    --secondary-color: #1976D2;    /* Dark blue */
    --accent-color: #FF9800;       /* Orange accent */
    --text-color: #212121;         /* Dark text */
    --background-color: #FFFFFF;   /* White background */
}

/* Dark Theme */
.safa-theme--dark {
    --primary-color: #64B5F6;      /* Light blue */
    --secondary-color: #42A5F5;    /* Blue */
    --accent-color: #FFB74D;       /* Light orange */
    --text-color: #FFFFFF;         /* White text */
    --background-color: #121212;   /* Dark background */
}
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **JavaScript Framework Structure**
The **Marwa JavaScript Framework** provides interactive functionality:

```javascript
// ✅ Correct - Using Marwa framework naming
class MarwaSkinManager {
    constructor(skinName) {
        this.skinName = skinName;
        this.currentTheme = 'islamic';
        this.init();
    }
    
    init() {
        this.setupThemeSwitcher();
        this.setupResponsiveNavigation();
        this.setupAccessibility();
    }
    
    setupThemeSwitcher() {
        // Theme switching functionality
    }
    
    setupResponsiveNavigation() {
        // Responsive navigation
    }
    
    setupAccessibility() {
        // Accessibility features
    }
}

// ❌ Incorrect - Generic naming
class SkinManager {
    // No prefix
}
```

### **Component System**
```javascript
// Theme Switcher Component
class MarwaThemeSwitcher extends MarwaComponent {
    constructor(element) {
        super(element);
        this.themes = ['islamic', 'ramadan', 'light', 'dark'];
        this.init();
    }
    
    init() {
        this.createThemeSelector();
        this.bindEvents();
    }
    
    createThemeSelector() {
        // Create theme selector UI
    }
    
    bindEvents() {
        // Bind theme switching events
    }
    
    switchTheme(theme) {
        // Switch to selected theme
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        this.saveThemePreference(theme);
    }
}
```

---

## 🔧 **Skin Development**

### **Creating a New Skin**
1. **Create Skin Directory**: Create directory in `skins/` folder
2. **Skin Class**: Extend `SafaSkin` base class
3. **Configuration**: Create `skin.json` configuration file
4. **Templates**: Create Twig templates
5. **Styles**: Use Safa CSS framework
6. **Scripts**: Use Marwa JavaScript framework
7. **Testing**: Test across different devices and themes

### **Skin Class Example**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Skins\Bismillah;

use IslamWiki\Core\Skins\SafaSkin;

/**
 * Bismillah Skin - Beautiful Islamic-themed skin
 * 
 * @package IslamWiki\Skins\Bismillah
 * @author IslamWiki Development Team
 */
class SafaSkin_Bismillah extends SafaSkin
{
    public function getSkinName(): string
    {
        return 'Bismillah';
    }
    
    public function getSkinDescription(): string
    {
        return 'Beautiful Islamic-themed skin with modern design';
    }
    
    public function getSkinVersion(): string
    {
        return '1.0.0';
    }
    
    public function getSkinAuthor(): string
    {
        return 'IslamWiki Development Team';
    }
    
    public function getSupportedThemes(): array
    {
        return ['islamic', 'ramadan', 'light', 'dark'];
    }
    
    public function getDefaultTheme(): string
    {
        return 'islamic';
    }
    
    public function getSkinAssets(): array
    {
        return [
            'css' => [
                'safa-base.css',
                'safa-components.css',
                'safa-themes.css',
                'safa-utilities.css'
            ],
            'js' => [
                'marwa-core.js',
                'marwa-components.js',
                'marwa-themes.js'
            ]
        ];
    }
}
```

### **Skin Configuration (skin.json)**
```json
{
    "name": "Bismillah",
    "description": "Beautiful Islamic-themed skin with modern design",
    "version": "1.0.0",
    "author": "IslamWiki Development Team",
    "license": "AGPL-3.0",
    "themes": [
        "islamic",
        "ramadan",
        "light",
        "dark"
    ],
    "defaultTheme": "islamic",
    "responsive": true,
    "accessibility": true,
    "rtlSupport": true,
    "features": [
        "theme-switching",
        "responsive-navigation",
        "accessibility-tools",
        "customizable-layout"
    ]
}
```

---

## 🎯 **Skin Features**

### **Core Features**
- **Theme Switching**: Multiple theme variations
- **Responsive Design**: Mobile-first approach
- **Accessibility**: WCAG 2.1 AA compliance
- **RTL Support**: Right-to-left language support
- **Customization**: Easy to customize and extend
- **Performance**: Optimized for speed and efficiency

### **Islamic Features**
- **Islamic Aesthetics**: Beautiful, culturally appropriate designs
- **Islamic Themes**: Islamic, Ramadan, and other cultural themes
- **Cultural Sensitivity**: Respectful of Islamic values
- **Community Focus**: Designed for community collaboration

### **Technical Features**
- **Twig Templates**: Flexible template system
- **CSS Framework**: Safa CSS framework integration
- **JavaScript Framework**: Marwa JavaScript framework integration
- **Asset Management**: Efficient asset loading and caching
- **Extension Support**: Easy integration with extensions

---

## 📱 **Responsive Design**

### **Breakpoint System**
```css
/* Mobile First Approach */
.safa-container {
    width: 100%;
    padding: 1rem;
}

/* Tablet */
@media (min-width: 768px) {
    .safa-container {
        max-width: 720px;
        margin: 0 auto;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .safa-container {
        max-width: 960px;
    }
}

/* Large Desktop */
@media (min-width: 1280px) {
    .safa-container {
        max-width: 1200px;
    }
}
```

### **Navigation System**
```javascript
// Responsive Navigation Component
class MarwaResponsiveNavigation extends MarwaComponent {
    constructor(element) {
        super(element);
        this.mobileBreakpoint = 768;
        this.init();
    }
    
    init() {
        this.setupMobileMenu();
        this.bindEvents();
    }
    
    setupMobileMenu() {
        // Setup mobile navigation menu
    }
    
    bindEvents() {
        // Bind navigation events
    }
    
    toggleMobileMenu() {
        // Toggle mobile menu visibility
    }
}
```

---

## ♿ **Accessibility Features**

### **WCAG 2.1 AA Compliance**
- **Keyboard Navigation**: Full keyboard navigation support
- **Screen Reader Support**: ARIA labels and descriptions
- **High Contrast**: High contrast mode support
- **Focus Management**: Clear focus indicators
- **Semantic HTML**: Proper semantic markup

### **Accessibility Components**
```javascript
// Accessibility Manager Component
class MarwaAccessibilityManager extends MarwaComponent {
    constructor() {
        super();
        this.init();
    }
    
    init() {
        this.setupHighContrast();
        this.setupFontScaling();
        this.setupFocusIndicators();
    }
    
    setupHighContrast() {
        // High contrast mode
    }
    
    setupFontScaling() {
        // Font scaling functionality
    }
    
    setupFocusIndicators() {
        // Focus indicators
    }
}
```

---

## 🔧 **Customization & Extension**

### **Custom CSS Variables**
```css
/* Custom CSS Variables */
:root {
    --custom-primary-color: #2E7D32;
    --custom-secondary-color: #1B5E20;
    --custom-accent-color: #FFD700;
    --custom-font-family: 'Amiri', serif;
    --custom-border-radius: 8px;
    --custom-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Custom Component Styles */
.safa-button--custom {
    background-color: var(--custom-primary-color);
    border-radius: var(--custom-border-radius);
    box-shadow: var(--custom-shadow);
    font-family: var(--custom-font-family);
}
```

### **Custom JavaScript Components**
```javascript
// Custom Component Example
class MarwaCustomComponent extends MarwaComponent {
    constructor(element) {
        super(element);
        this.init();
    }
    
    init() {
        // Custom initialization logic
    }
    
    // Custom methods
    customMethod() {
        // Custom functionality
    }
}
```

---

## 📚 **Documentation & Resources**

### **Development Guides**
- [Skin Development Guide](development.md)
- [CSS Framework Guide](../architecture/core-systems.md#safa-css-framework)
- [JavaScript Framework Guide](../architecture/core-systems.md#marwa-javascript-framework)
- [Template System Guide](../architecture/core-systems.md)

### **Reference Materials**
- [Islamic Naming Conventions](../guides/islamic-naming-conventions.md)
- [Style Guide](../guides/style-guide.md)
- [API Documentation](../api/overview.md)
- [Extension Development](../extensions/development.md)

---

## 📄 **License Information**

This skin system is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

### **AGPL-3.0 License Requirements**
- **Source Code**: Must be made available to users
- **Network Use**: Network use triggers source code distribution
- **Modifications**: Modified versions must be licensed under AGPL-3.0
- **Attribution**: Original copyright notices must be preserved

### **License Compliance**
- All skins must include AGPL-3.0 license headers
- Source code must be available to users
- Network use must comply with AGPL-3.0 requirements
- Modifications must preserve license terms

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Skin System Documentation Complete ✅ 
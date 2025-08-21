# SafaSkinExtension

## 🎯 **Overview**

The **SafaSkinExtension** is a revolutionary unified skin management system for IslamWiki that consolidates all visual elements (layouts, templates, components) into a single, cohesive system. This extension transforms IslamWiki from a fragmented template system into a unified, professional skin management platform.

---

## 🌟 **Key Features**

### **Unified Visual System**
- **Consolidated Structure**: All layouts, components, and pages within skins
- **Professional Appearance**: Consistent header, navigation, and footer across all pages
- **Easy Maintenance**: All visual elements in one place per skin
- **Better Performance**: Skin-specific asset loading and optimization

### **WordPress-Style Theme System**
- **Skin Management**: Easy skin switching and management
- **Asset Optimization**: CSS/JS optimization and bundling
- **Template Engine**: Skin-aware template rendering
- **Asset Manager**: Intelligent asset loading and caching

### **Islamic Aesthetics**
- **Islamic Design**: Built-in Islamic aesthetic themes
- **Cultural Sensitivity**: Respectful and appropriate visual design
- **Accessibility**: WCAG 2.1 AA compliance
- **Responsive Design**: Mobile-first approach

---

## 🏗️ **Architecture**

### **Core Services**
```
SafaSkinExtension/
├── 📁 Services/
│   ├── 📄 SkinManager.php      # Active skin management
│   ├── 📄 TemplateEngine.php   # Skin-aware template rendering
│   ├── 📄 AssetManager.php     # Asset loading and optimization
│   └── 📄 SkinRegistry.php     # Skin discovery and registration
├── 📄 SafaSkinExtension.php    # Main extension class
├── 📄 extension.json           # Extension metadata
└── 📄 README.md                # This documentation
```

### **Service Responsibilities**

#### **SkinManager**
- Manages active skins and skin switching
- Handles skin configuration and settings
- Provides skin asset information
- Manages skin validation and statistics

#### **TemplateEngine**
- Resolves template paths to use skin system
- Processes templates with skin-specific data
- Manages template caching and optimization
- Validates skin template structure

#### **AssetManager**
- Loads skin-specific CSS and JavaScript
- Optimizes assets for production
- Manages asset enqueuing and delivery
- Provides asset statistics and monitoring

#### **SkinRegistry**
- Discovers available skins automatically
- Registers and validates skin configurations
- Manages skin metadata and dependencies
- Provides skin compatibility checking

---

## 🚀 **Installation**

### **1. Install Extension**
```bash
# Copy to extensions directory
cp -r SafaSkinExtension extensions/

# Set permissions
chmod -R 755 extensions/SafaSkinExtension/
```

### **2. Activate Extension**
```php
// In your LocalSettings.php or configuration
$wgExtensions[] = 'SafaSkinExtension';
```

### **3. Configure Skins**
```php
// Configure available skins
$wgValidSkins = [
    'Bismillah' => 'Bismillah',
    'Muslim' => 'Muslim'
];

// Set default skin
$wgDefaultSkin = 'Bismillah';
```

---

## 🎨 **Skin Development**

### **Skin Structure**
```
skins/
├── 📁 {SkinName}/
│   ├── 📄 skin.json           # Skin configuration
│   ├── 📁 layouts/            # Page structures
│   │   ├── 📄 base.twig       # Base page layout
│   │   ├── 📄 dashboard.twig  # Dashboard layout
│   │   └── 📄 content.twig    # Content page layout
│   ├── 📁 components/         # Reusable UI parts
│   │   ├── 📄 header.twig     # Header component
│   │   ├── 📄 navigation.twig # Navigation component
│   │   └── 📄 footer.twig     # Footer component
│   ├── 📁 pages/              # Page-specific templates
│   │   ├── 📄 home.twig       # Homepage
│   │   └── 📄 wiki.twig       # Wiki pages
│   ├── 📁 css/                # Skin-specific styles
│   │   └── 📄 {skin}.css      # Main skin CSS
│   ├── 📁 js/                 # Skin-specific JavaScript
│   │   └── 📄 {skin}.js       # Main skin JS
│   └── 📁 assets/             # Images, fonts, etc.
```

### **Skin Configuration (skin.json)**
```json
{
  "name": "Bismillah",
  "version": "0.0.28",
  "description": "Default Islamic-themed skin with traditional design",
  "author": "IslamWiki Team",
  "assets": {
    "css": ["css/bismillah.css", "css/quran.css"],
    "js": "js/bismillah.js"
  },
  "config": {
    "primary_color": "#4F46E5",
    "secondary_color": "#7C3AED",
    "accent_color": "#A855F7"
  },
  "dependencies": {
    "php": ">=8.1",
    "islamwiki": ">=0.0.19"
  }
}
```

---

## 🔧 **Usage**

### **Basic Skin Management**
```php
// Get skin manager service
$skinManager = $container->get('skin.manager');

// Get active skin
$activeSkin = $skinManager->getActiveSkin();

// Switch skins
$skinManager->setActiveSkin('Muslim');

// Get skin assets
$assets = $skinManager->getActiveSkinAssets();
```

### **Template Rendering**
```php
// Get template engine service
$templateEngine = $container->get('skin.template_engine');

// Resolve template path
$templatePath = $templateEngine->resolveTemplatePath('layouts/base.twig');

// Check if template exists
if ($templateEngine->templateExists('layouts/base.twig')) {
    // Template exists in skin
}
```

### **Asset Management**
```php
// Get asset manager service
$assetManager = $container->get('skin.asset_manager');

// Enqueue skin assets
$assetManager->enqueueSkinAssets();

// Get asset statistics
$stats = $assetManager->getAssetStats();
```

---

## 🎛️ **Configuration**

### **Extension Settings**
```php
// Skin system configuration
$wgSafaSkinConfig = [
    'default_skin' => 'Bismillah',
    'enable_asset_optimization' => true,
    'enable_template_caching' => true,
    'skin_auto_discovery' => true
];
```

### **Skin-Specific Settings**
```php
// Individual skin configuration
$wgSkinConfigs = [
    'Bismillah' => [
        'theme' => 'islamic',
        'color_scheme' => 'traditional',
        'layout' => 'standard'
    ],
    'Muslim' => [
        'theme' => 'modern',
        'color_scheme' => 'contemporary',
        'layout' => 'minimal'
    ]
];
```

---

## 🔌 **Hooks and Filters**

### **Available Hooks**
```php
// Action hooks
add_action('init', 'onInit');
add_action('template_render', 'onTemplateRender');
add_action('asset_enqueue', 'onAssetEnqueue');

// Filter hooks
add_filter('template_path', 'filterTemplatePath');
add_filter('skin_assets', 'filterSkinAssets');
```

### **Hook Usage**
```php
// Register skin change hook
add_action('skin_changed', function($oldSkin, $newSkin) {
    // Handle skin change
    error_log("Skin changed from {$oldSkin} to {$newSkin}");
});

// Filter template paths
add_filter('template_path', function($template) {
    // Modify template path
    return 'skins/custom/' . $template;
});
```

---

## 📊 **Performance Features**

### **Asset Optimization**
- **CSS Minification**: Automatic CSS optimization
- **JavaScript Minification**: JavaScript optimization
- **Asset Bundling**: Combine multiple files
- **Lazy Loading**: Load assets as needed

### **Caching Strategy**
- **Template Caching**: Compiled template caching
- **Asset Caching**: Browser and server caching
- **Path Resolution Caching**: Template path caching
- **Metadata Caching**: Skin metadata caching

### **Performance Monitoring**
```php
// Get performance statistics
$templateStats = $templateEngine->getCacheStats();
$assetStats = $assetManager->getAssetStats();
$skinStats = $skinManager->getSkinStats();
```

---

## 🧪 **Testing**

### **Unit Tests**
```bash
# Run extension tests
./vendor/bin/phpunit extensions/SafaSkinExtension/tests/
```

### **Test Coverage**
```php
// Test skin switching
public function testSkinSwitching(): void
{
    $skinManager = $this->container->get('skin.manager');
    
    $result = $skinManager->setActiveSkin('Muslim');
    $this->assertTrue($result);
    
    $activeSkin = $skinManager->getActiveSkinName();
    $this->assertEquals('Muslim', $activeSkin);
}
```

---

## 🔒 **Security**

### **Security Features**
- **Input Validation**: All skin inputs validated
- **Path Security**: Secure template path resolution
- **Asset Security**: Secure asset loading
- **Access Control**: Skin management permissions

### **Best Practices**
- Validate all skin configurations
- Sanitize template content
- Use secure asset paths
- Implement proper access controls

---

## 🚨 **Troubleshooting**

### **Common Issues**

#### **Skin Not Loading**
```bash
# Check skin directory permissions
chmod -R 755 skins/Bismillah/

# Verify skin.json exists
ls -la skins/Bismillah/skin.json

# Check extension activation
grep -r "SafaSkinExtension" config/
```

#### **Templates Not Found**
```bash
# Verify template structure
ls -la skins/Bismillah/layouts/
ls -la skins/Bismillah/components/
ls -la skins/Bismillah/pages/

# Check template paths
php -r "echo file_exists('skins/Bismillah/layouts/base.twig') ? 'OK' : 'Missing';"
```

#### **Assets Not Loading**
```bash
# Check asset files
ls -la skins/Bismillah/css/
ls -la skins/Bismillah/js/

# Verify web access
curl -I http://localhost/skins/Bismillah/css/bismillah.css
```

### **Debug Mode**
```php
// Enable debug logging
$wgSafaSkinConfig['debug'] = true;

// Check logs
tail -f logs/safa-skin.log
```

---

## 📚 **API Reference**

### **SkinManager Methods**
```php
// Core methods
getAvailableSkins(): array
getSkin(string $name): ?array
getActiveSkinName(): ?string
setActiveSkin(string $name): bool
hasActiveSkin(): bool

// Asset methods
getSkinAssets(string $name): array
getActiveSkinAssets(): array

// Validation methods
validateSkin(string $name): bool
getSkinConfiguration(string $name): array
```

### **TemplateEngine Methods**
```php
// Template methods
resolveTemplatePath(string $template): string
templateExists(string $template): bool
getTemplateContent(string $template): ?string
getAvailableTemplates(): array

// Processing methods
processTemplate(string $template, array $data): void
validateTemplateStructure(): array

// Cache methods
clearCache(): void
getCacheStats(): array
```

### **AssetManager Methods**
```php
// Asset methods
enqueueSkinAssets(array $assets = []): void
enqueueStyle(string $filename, string $skinPath = ''): void
enqueueScript(string $filename, string $skinPath = ''): void

// Rendering methods
renderStyles(): string
renderScripts(): string

// Optimization methods
optimizeAssets(): array
getAssetStats(): array
```

### **SkinRegistry Methods**
```php
// Registry methods
discoverSkins(): void
registerSkin(string $name, array $config): bool
unregisterSkin(string $name): bool

// Information methods
getRegisteredSkins(): array
getSkin(string $name): ?array
getSkinInfo(string $name): ?array

// Validation methods
isSkinRegistered(string $name): bool
isSkinCompatible(string $name): bool
getCompatibleSkins(): array
```

---

## 🔮 **Future Enhancements**

### **Planned Features**
- **Skin Marketplace**: Community-created skins
- **Advanced Customization**: CSS/JS editor for users
- **Layout Builder**: Drag-and-drop layout creation
- **Component Library**: Reusable component system
- **Theme Engine**: Advanced theming capabilities

### **Performance Improvements**
- **Asset Compression**: Advanced compression algorithms
- **CDN Integration**: Global content delivery
- **Service Worker**: Progressive Web App support
- **Advanced Caching**: Multi-level caching strategy

---

## 📞 **Support & Resources**

### **Documentation**
- **Extension Development**: [Extension Development Guide](../development.md)
- **Skin Development**: [Skin Development Guide](../../skins/development.md)
- **API Reference**: [API Documentation](../../api/overview.md)

### **Community**
- **Developer Forum**: Ask questions and share code
- **Code Examples**: Sample skins and extensions
- **Best Practices**: Development guidelines

### **Contributing**
- **Bug Reports**: Report issues and bugs
- **Feature Requests**: Suggest new features
- **Code Contributions**: Submit pull requests
- **Documentation**: Help improve documentation

---

## 📄 **License**

This extension is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.1  
**Author:** IslamWiki Development Team  
**Extension:** SafaSkinExtension - Unified Skin Management System  
**Status:** Development Complete ✅ 
# IslamWiki Layout System - Now Part of Unified Skin System

## 🎯 **Overview**

**IMPORTANT UPDATE**: The layout system has been consolidated into the unified skin system. Layouts are now managed as part of individual skins, not as a separate system.

**New Location**: `skins/{SkinName}/layouts/`

---

## 🏗️ **New Architecture: Layouts Within Skins**

### **Layout System Integration**
```
Unified Skin System:
├── 📁 Skins (Everything Visual)
│   ├── 📁 {SkinName}/             # Individual skin (e.g., Bismillah)
│   │   ├── 📁 layouts/            # Page structures (THIS IS WHERE LAYOUTS LIVE NOW)
│   │   │   ├── 📄 base.twig       # Base page layout
│   │   │   ├── 📄 dashboard.twig  # Dashboard layout
│   │   │   ├── 📄 content.twig    # Content page layout
│   │   │   └── 📄 auth.twig       # Authentication layout
│   │   ├── 📁 components/         # Reusable UI parts
│   │   ├── 📁 pages/              # Page-specific templates
│   │   ├── 📁 css/                # Skin-specific styles
│   │   ├── 📁 js/                 # Skin-specific JavaScript
│   │   └── 📁 assets/             # Images, fonts, icons
│   └── 📁 Other skins...
└── 📁 Extensions (Functionality only)
```

---

## 🔄 **Migration Status**

### **What Changed:**
- ❌ **Old System**: `resources/views/layouts/` (separate system)
- ✅ **New System**: `skins/{SkinName}/layouts/` (unified with skins)

### **Files Moved:**
- `resources/views/layouts/base.twig` → `skins/Bismillah/layouts/base.twig`
- `resources/views/layouts/dashboard.twig` → `skins/Bismillah/layouts/dashboard.twig`
- `resources/views/layouts/app.twig` → `skins/Bismillah/layouts/app.twig`
- `resources/views/layouts/auth.twig` → `skins/Bismillah/layouts/auth.twig`
- `resources/views/layouts/debug.twig` → `skins/Bismillah/layouts/debug.twig`

---

## 🎨 **Layout Categories (Now Within Skins)**

### **1. Base Layouts**
- **Main Layout**: Primary application layout
- **Admin Layout**: Administrative interface layout
- **API Layout**: API response layout
- **Minimal Layout**: Minimal layout for specific pages

### **2. Dashboard Layouts**
- **User Dashboard**: User account and profile dashboard
- **Admin Dashboard**: Administrative control panel
- **Scholar Dashboard**: Islamic scholar interface
- **Contributor Dashboard**: Content contributor interface

### **3. Page Layouts**
- **Content Layout**: Wiki page and article layout
- **Search Layout**: Search results and discovery layout
- **Profile Layout**: User profile and settings layout
- **Extension Layout**: Extension-specific page layouts

---

## 📝 **New Layout Implementation**

### **Layout Location (Updated):**
```twig
{# New location: skins/Bismillah/layouts/base.twig #}
<!DOCTYPE html>
<html lang="{{ app.locale }}" dir="{{ app.direction }}" class="safa-theme--{{ app.theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{% block meta_description %}IslamWiki - Islamic Knowledge Platform{% endblock %}">
    <title>{% block title %}IslamWiki{% endblock %}</title>
    
    {# Safa CSS Framework #}
    <link rel="stylesheet" href="{{ asset('safa/safa-base.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-components.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-themes.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-utilities.css') }}">
    
    {# Skin-specific CSS #}
    <link rel="stylesheet" href="{{ asset('skins/' ~ activeSkin ~ '/css/' ~ activeSkin ~ '.css') }}">
    
    {# Page-specific CSS #}
    {% block stylesheets %}{% endblock %}
</head>
<body class="safa-body">
    {# Skip to Content Link for Accessibility #}
    <a href="#main-content" class="safa-skip-link">Skip to main content</a>
    
    {# Header Component #}
    {% include 'skins/' ~ activeSkin ~ '/components/header.twig' %}
    
    {# Main Navigation Component #}
    {% include 'skins/' ~ activeSkin ~ '/components/navigation.twig' %}
    
    {# Main Content #}
    <main id="main-content" class="safa-main" role="main">
        {# Breadcrumbs Component #}
        {% if breadcrumbs is defined %}
            {% include 'skins/' ~ activeSkin ~ '/components/breadcrumbs.twig' %}
        {% endif %}
        
        {# Page Content #}
        {% block content %}{% endblock %}
    </main>
    
    {# Footer Component #}
    {% include 'skins/' ~ activeSkin ~ '/components/footer.twig' %}
    
    {# Marwa JavaScript Framework #}
    <script src="{{ asset('marwa/marwa-core.js') }}" defer></script>
    <script src="{{ asset('marwa/marwa-components.js') }}" defer></script>
    <script src="{{ asset('marwa/marwa-themes.js') }}" defer></script>
    
    {# Skin-specific JavaScript #}
    <script src="{{ asset('skins/' ~ activeSkin ~ '/js/' ~ activeSkin ~ '.js') }}" defer></script>
    
    {# Page-specific JavaScript #}
    {% block javascripts %}{% endblock %}
</body>
</html>
```

---

## 🔧 **Using Layouts in New System**

### **Template References (Updated):**
```php
// OLD WAY (separate system)
$template = 'layouts/base.twig';

// NEW WAY (unified skin system)
$template = 'skins/' . $activeSkin . '/layouts/base.twig';
```

### **Controller Usage:**
```php
class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = $this->pageService->getPage($slug);
        
        // Use skin-specific layout
        return $this->view('skins/' . $this->getActiveSkin() . '/layouts/content.twig', [
            'page' => $page,
            'activeSkin' => $this->getActiveSkin()
        ]);
    }
}
```

---

## 🎨 **Safa CSS Framework Integration**

### **Layout CSS Classes (Updated):**
```css
/* ✅ Correct - Using Safa framework naming */
.safa-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.safa-header {
    height: var(--safa-header-height);
    background: var(--safa-theme-primary);
    color: white;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.safa-main {
    flex: 1;
    display: flex;
}

.safa-sidebar {
    width: var(--safa-sidebar-width);
    background: var(--safa-theme-cream);
    border-right: 1px solid var(--safa-theme-accent);
}

.safa-content {
    flex: 1;
    max-width: var(--safa-content-max-width);
    margin: 0 auto;
    padding: 2rem;
}

.safa-footer {
    height: var(--safa-footer-height);
    background: var(--safa-theme-primary);
    color: white;
}
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **Layout JavaScript (Updated):**
```javascript
// Layout initialization within skin context
class MarwaLayout {
    constructor(skinName) {
        this.skinName = skinName;
        this.init();
    }
    
    init() {
        this.setupSkipLinks();
        this.setupResponsiveNavigation();
        this.setupThemeSwitcher();
        this.setupAccessibility();
    }
    
    setupResponsiveNavigation() {
        // Handle mobile navigation for specific skin
        const navToggle = document.querySelector(`.${this.skinName}-nav-toggle`);
        const nav = document.querySelector(`.${this.skinName}-main-nav`);
        
        if (navToggle && nav) {
            navToggle.addEventListener('click', () => {
                nav.classList.toggle(`${this.skinName}-main-nav--open`);
            });
        }
    }
}

// Initialize for current skin
document.addEventListener('DOMContentLoaded', () => {
    const activeSkin = document.documentElement.dataset.skin || 'Bismillah';
    new MarwaLayout(activeSkin);
});
```

---

## 🔒 **Security & Accessibility (Updated)**

### **Security Features:**
- **CSRF Protection**: Token-based form protection
- **XSS Prevention**: Output escaping and sanitization
- **Content Security Policy**: CSP headers for security
- **Secure Headers**: Security-focused HTTP headers

### **Accessibility Features:**
- **Skip Links**: Skip to main content navigation
- **ARIA Labels**: Proper ARIA labeling and roles
- **Keyboard Navigation**: Full keyboard navigation support
- **Screen Reader Support**: Screen reader compatibility
- **High Contrast**: High contrast mode support

---

## 📚 **Updated Documentation References**

### **Related Documentation:**
- **[Unified Skin System](../skins/unified-system.md)** - Complete unified system guide
- **[Skin Development](../skins/development.md)** - How to develop skins with layouts
- **[Template Extension System Plan](../architecture/template-extension-system-plan.md)** - Implementation plan
- **[SafaSkinExtension](../extensions/SafaSkinExtension.md)** - Extension documentation

### **Migration Resources:**
- **[Migration Guide](../skins/migration.md)** - How to migrate existing layouts
- **[File Organization](../guides/organization.md)** - New file organization structure

---

## 🧪 **Testing Layouts (Updated)**

### **Layout Testing (New Context):**
```php
class LayoutTest extends TestCase
{
    public function testLayoutRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = [
            'theme' => 'islamic',
            'activeSkin' => 'Bismillah'
        ];
        
        // Test skin-specific layout
        $html = $renderer->render('skins/Bismillah/layouts/base.twig', $data);
        
        $this->assertStringContains('safa-theme--islamic', $html);
        $this->assertStringContains('safa-body', $html);
        $this->assertStringContains('safa-header', $html);
    }
    
    public function testSkinSpecificLayout(): void
    {
        $skinManager = new SkinManager();
        $skinManager->setActiveSkin('Bismillah');
        
        $layout = $skinManager->getActiveSkin()->getLayout('base');
        $this->assertEquals('skins/Bismillah/layouts/base.twig', $layout);
    }
}
```

---

## 📖 **Additional Resources**

### **Development Resources:**
- **[Style Guide](../guides/style-guide.md)** - Updated coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide
- **[Testing Guidelines](../testing/README.md)** - Updated testing strategies

---

## 🚨 **Important Notes**

### **Breaking Changes:**
1. **Template Paths**: All layout template paths have changed
2. **File Locations**: Layouts are now within skin directories
3. **References**: Update all template references in code
4. **Documentation**: Old documentation is outdated

### **Migration Required:**
- **Update Controllers**: Change template path references
- **Update Views**: Change include/extends paths
- **Update Tests**: Update test template paths
- **Update Documentation**: Remove old system references

---

**Last Updated:** 2025-08-19  
**Version:** 2.0 (Unified Skin System)  
**Author:** IslamWiki Development Team  
**Status:** Layouts Now Part of Unified Skin System ✅  
**Migration:** Required - Update all template references 
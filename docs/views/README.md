# IslamWiki Views System - Now Part of Unified Skin System

## 🎯 **Overview**

**IMPORTANT UPDATE**: The views system has been consolidated into the unified skin system. Views are now managed as part of individual skins, not as a separate system.

**New Location**: `skins/{SkinName}/pages/` and `skins/{SkinName}/components/`

---

## 🏗️ **New Architecture: Views Within Skins**

### **Views System Integration**
```
Unified Skin System:
├── 📁 Skins (Everything Visual)
│   ├── 📁 {SkinName}/             # Individual skin (e.g., Bismillah)
│   │   ├── 📁 layouts/            # Page structures
│   │   ├── 📁 components/         # Reusable UI parts (THIS IS WHERE COMPONENTS LIVE NOW)
│   │   │   ├── 📄 header.twig     # Header component
│   │   │   ├── 📄 navigation.twig # Navigation component
│   │   │   ├── 📄 footer.twig     # Footer component
│   │   │   └── 📄 sidebar.twig    # Sidebar component
│   │   ├── 📁 pages/              # Page-specific templates (THIS IS WHERE PAGES LIVE NOW)
│   │   │   ├── 📄 home.twig       # Homepage
│   │   │   ├── 📄 wiki.twig       # Wiki pages
│   │   │   ├── 📄 search.twig     # Search results
│   │   │   ├── 📄 profile.twig    # User profile
│   │   │   └── 📄 settings.twig   # User settings
│   │   ├── 📁 css/                # Skin-specific styles
│   │   ├── 📁 js/                 # Skin-specific JavaScript
│   │   └── 📁 assets/             # Images, fonts, icons
│   └── 📁 Other skins...
└── 📁 Extensions (Functionality only)
```

---

## 🔄 **Migration Status**

### **What Changed:**
- ❌ **Old System**: `resources/views/` (separate system)
- ✅ **New System**: `skins/{SkinName}/pages/` and `skins/{SkinName}/components/` (unified with skins)

### **Files Moved:**
- `resources/views/pages/*.twig` → `skins/Bismillah/pages/*.twig`
- `resources/views/components/*.twig` → `skins/Bismillah/components/*.twig`
- `resources/views/auth/*.twig` → `skins/Bismillah/pages/auth/*.twig`
- `resources/views/dashboard/*.twig` → `skins/Bismillah/pages/dashboard/*.twig`
- `resources/views/errors/*.twig` → `skins/Bismillah/pages/errors/*.twig`

---

## 🎨 **View Categories (Now Within Skins)**

### **1. Page Views**
- **Content Pages**: Wiki pages, articles, search results
- **User Pages**: Profiles, settings, dashboards
- **System Pages**: Errors, maintenance, admin pages
- **Extension Pages**: Extension-specific page templates

### **2. Component Views**
- **Navigation Components**: Headers, footers, sidebars
- **Form Components**: Input fields, buttons, validation
- **Display Components**: Cards, lists, tables, modals
- **Interactive Components**: Dropdowns, tooltips, notifications

### **3. Layout Views**
- **Base Layouts**: Foundation page structures
- **Specialized Layouts**: Dashboard, admin, content layouts
- **Responsive Layouts**: Mobile-first design patterns

---

## 📝 **New View Implementation**

### **View Location (Updated):**
```twig
{# New location: skins/Bismillah/pages/home.twig #}
{% extends 'skins/' ~ activeSkin ~ '/layouts/base.twig' %}

{% block title %}IslamWiki - Islamic Knowledge Platform{% endblock %}

{% block content %}
<div class="safa-homepage">
    <!-- Hero Section -->
    <section class="safa-hero">
        <div class="safa-container">
            <h1 class="safa-hero__title">Welcome to IslamWiki</h1>
            <p class="safa-hero__subtitle">Discover Islamic knowledge, wisdom, and guidance</p>
            <div class="safa-hero__actions">
                <a href="/search" class="safa-btn safa-btn--primary">Search Knowledge</a>
                <a href="/about" class="safa-btn safa-btn--secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Featured Content -->
    <section class="safa-featured">
        <div class="safa-container">
            <h2 class="safa-section__title">Featured Content</h2>
            <div class="safa-content-grid">
                {% for content in featuredContent %}
                <div class="safa-content-card">
                    <h3 class="safa-content-card__title">{{ content.title }}</h3>
                    <p class="safa-content-card__excerpt">{{ content.excerpt }}</p>
                    <a href="{{ content.url }}" class="safa-content-card__link">Read More</a>
                </div>
                {% endfor %}
            </div>
        </div>
    </section>
</div>
{% endblock %}
```

### **Component Usage (Updated):**
```twig
{# New location: skins/Bismillah/components/header.twig #}
<header class="safa-header" role="banner">
    <div class="safa-container">
        <div class="safa-header__content">
            <!-- Logo -->
            <div class="safa-header__logo">
                <a href="/" class="safa-logo">
                    <span class="safa-logo__icon">📚</span>
                    <span class="safa-logo__text">IslamWiki</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="safa-header__nav" role="navigation" aria-label="Main navigation">
                <ul class="safa-nav__list">
                    <li class="safa-nav__item">
                        <a href="/" class="safa-nav__link">Home</a>
                    </li>
                    <li class="safa-nav__item">
                        <a href="/wiki" class="safa-nav__link">Wiki</a>
                    </li>
                    <li class="safa-nav__item">
                        <a href="/quran" class="safa-nav__link">Quran</a>
                    </li>
                    <li class="safa-nav__item">
                        <a href="/hadith" class="safa-nav__link">Hadith</a>
                    </li>
                </ul>
            </nav>

            <!-- User Menu -->
            <div class="safa-header__user">
                {% if user %}
                    <div class="safa-user-menu">
                        <span class="safa-user-menu__name">{{ user.name }}</span>
                        <a href="/profile" class="safa-user-menu__link">Profile</a>
                        <a href="/logout" class="safa-user-menu__link">Logout</a>
                    </div>
                {% else %}
                    <a href="/login" class="safa-btn safa-btn--primary">Login</a>
                {% endif %}
            </div>
        </div>
    </div>
</header>
```

---

## 🔧 **Using Views in New System**

### **Template References (Updated):**
```php
// OLD WAY (separate system)
$template = 'pages/home.twig';
$component = 'components/header.twig';

// NEW WAY (unified skin system)
$template = 'skins/' . $activeSkin . '/pages/home.twig';
$component = 'skins/' . $activeSkin . '/components/header.twig';
```

### **Controller Usage:**
```php
class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredContent = $this->contentService->getFeaturedContent();
        
        // Use skin-specific page template
        return $this->view('skins/' . $this->getActiveSkin() . '/pages/home.twig', [
            'featuredContent' => $featuredContent,
            'activeSkin' => $this->getActiveSkin()
        ]);
    }
}

class ComponentController extends Controller
{
    public function header(): Response
    {
        $user = $this->getCurrentUser();
        
        // Use skin-specific component template
        return $this->view('skins/' . $this->getActiveSkin() . '/components/header.twig', [
            'user' => $user,
            'activeSkin' => $this->getActiveSkin()
        ]);
    }
}
```

---

## 🎨 **Safa CSS Framework Integration**

### **View CSS Classes (Updated):**
```css
/* ✅ Correct - Using Safa framework naming */
.safa-homepage {
    min-height: 100vh;
}

.safa-hero {
    background: linear-gradient(135deg, var(--safa-theme-primary), var(--safa-theme-secondary));
    color: white;
    padding: 4rem 0;
    text-align: center;
}

.safa-hero__title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.safa-hero__subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.safa-hero__actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.safa-content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.safa-content-card {
    background: var(--safa-card-background);
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: var(--safa-shadow-md);
    transition: transform 0.2s ease;
}

.safa-content-card:hover {
    transform: translateY(-2px);
}
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **View JavaScript (Updated):**
```javascript
// View initialization within skin context
class MarwaHomepage {
    constructor(skinName) {
        this.skinName = skinName;
        this.init();
    }
    
    init() {
        this.setupHeroAnimation();
        this.setupContentCards();
        this.setupSearchFunctionality();
    }
    
    setupHeroAnimation() {
        const hero = document.querySelector('.safa-hero');
        if (hero) {
            // Add entrance animation
            hero.classList.add('safa-hero--animated');
        }
    }
    
    setupContentCards() {
        const cards = document.querySelectorAll('.safa-content-card');
        cards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.classList.contains('safa-content-card__link')) {
                    // Handle card click
                    this.handleCardClick(e.target.href);
                }
            });
        });
    }
    
    setupSearchFunctionality() {
        const searchForm = document.querySelector('.safa-search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSearch(e.target);
            });
        }
    }
}

// Initialize for current skin
document.addEventListener('DOMContentLoaded', () => {
    const activeSkin = document.documentElement.dataset.skin || 'Bismillah';
    new MarwaHomepage(activeSkin);
});
```

---

## 🔒 **Security & Accessibility (Updated)**

### **Security Features:**
- **CSRF Protection**: Token-based form protection
- **XSS Prevention**: Output escaping and sanitization
- **Input Validation**: Comprehensive input validation
- **Access Control**: Role-based access control

### **Accessibility Features:**
- **Semantic HTML**: Proper HTML structure and semantics
- **ARIA Labels**: Comprehensive ARIA labeling
- **Keyboard Navigation**: Full keyboard accessibility
- **Screen Reader Support**: Screen reader compatibility
- **High Contrast**: High contrast mode support

---

## 📚 **Updated Documentation References**

### **Related Documentation:**
- **[Unified Skin System](../skins/unified-system.md)** - Complete unified system guide
- **[Skin Development](../skins/development.md)** - How to develop skins with views
- **[Template Extension System Plan](../architecture/template-extension-system-plan.md)** - Implementation plan
- **[SafaSkinExtension](../extensions/SafaSkinExtension.md)** - Extension documentation

### **Migration Resources:**
- **[Migration Guide](../skins/migration.md)** - How to migrate existing views
- **[File Organization](../guides/organization.md)** - New file organization structure

---

## 🧪 **Testing Views (Updated)**

### **View Testing (New Context):**
```php
class ViewTest extends TestCase
{
    public function testHomepageRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = [
            'featuredContent' => [
                ['title' => 'Test Content', 'excerpt' => 'Test excerpt', 'url' => '/test']
            ],
            'activeSkin' => 'Bismillah'
        ];
        
        // Test skin-specific page template
        $html = $renderer->render('skins/Bismillah/pages/home.twig', $data);
        
        $this->assertStringContains('safa-homepage', $html);
        $this->assertStringContains('safa-hero', $html);
        $this->assertStringContains('Test Content', $html);
    }
    
    public function testComponentRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = [
            'user' => ['name' => 'Test User'],
            'activeSkin' => 'Bismillah'
        ];
        
        // Test skin-specific component template
        $html = $renderer->render('skins/Bismillah/components/header.twig', $data);
        
        $this->assertStringContains('safa-header', $html);
        $this->assertStringContains('Test User', $html);
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
1. **Template Paths**: All view template paths have changed
2. **File Locations**: Views are now within skin directories
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
**Status:** Views Now Part of Unified Skin System ✅  
**Migration:** Required - Update all template references 
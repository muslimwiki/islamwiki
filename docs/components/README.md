# IslamWiki Components System - Now Part of Unified Skin System

## 🎯 **Overview**

**IMPORTANT UPDATE**: The components system has been consolidated into the unified skin system. Components are now managed as part of individual skins, not as a separate system.

**New Location**: `skins/{SkinName}/components/`

---

## 🏗️ **New Architecture: Components Within Skins**

### **Components System Integration**
```
Unified Skin System:
├── 📁 Skins (Everything Visual)
│   ├── 📁 {SkinName}/             # Individual skin (e.g., Bismillah)
│   │   ├── 📁 layouts/            # Page structures
│   │   ├── 📁 components/         # Reusable UI parts (THIS IS WHERE COMPONENTS LIVE NOW)
│   │   │   ├── 📄 header.twig     # Header component
│   │   │   ├── 📄 navigation.twig # Navigation component
│   │   │   ├── 📄 footer.twig     # Footer component
│   │   │   ├── 📄 sidebar.twig    # Sidebar component
│   │   │   ├── 📄 breadcrumbs.twig # Breadcrumb component
│   │   │   ├── 📄 search.twig     # Search component
│   │   │   ├── 📄 user-menu.twig  # User menu component
│   │   │   ├── 📄 pagination.twig # Pagination component
│   │   │   └── 📄 modal.twig      # Modal component
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
- ❌ **Old System**: `resources/views/components/` (separate system)
- ✅ **New System**: `skins/{SkinName}/components/` (unified with skins)

### **Files Moved:**
- `resources/views/components/header.twig` → `skins/Bismillah/components/header.twig`
- `resources/views/components/navigation.twig` → `skins/Bismillah/components/navigation.twig`
- `resources/views/components/footer.twig` → `skins/Bismillah/components/footer.twig`
- `resources/views/components/sidebar.twig` → `skins/Bismillah/components/sidebar.twig`
- `resources/views/components/breadcrumbs.twig` → `skins/Bismillah/components/breadcrumbs.twig`
- `resources/views/components/search.twig` → `skins/Bismillah/components/search.twig`

---

## 🎨 **Component Categories (Now Within Skins)**

### **1. Navigation Components**
- **Header**: Site header with logo and main navigation
- **Navigation**: Primary navigation menu
- **Footer**: Site footer with links and information
- **Sidebar**: Side navigation and widgets
- **Breadcrumbs**: Page location navigation

### **2. Interactive Components**
- **Search**: Search input and results
- **User Menu**: User account and settings
- **Pagination**: Page navigation controls
- **Modal**: Popup dialogs and overlays
- **Dropdown**: Expandable menu items

### **3. Display Components**
- **Cards**: Content display cards
- **Lists**: Ordered and unordered lists
- **Tables**: Data display tables
- **Forms**: Input forms and validation
- **Alerts**: Notification and status messages

---

## 📝 **New Component Implementation**

### **Component Location (Updated):**
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

### **Component Usage (Updated):**
```twig
{# New location: skins/Bismillah/components/search.twig #}
<div class="safa-search" data-marwa-component="search">
    <form class="safa-search__form" action="/search" method="GET">
        <div class="safa-search__input-group">
            <input type="text" 
                   name="q" 
                   class="safa-search__input" 
                   placeholder="Search Islamic knowledge..."
                   value="{{ query|default('') }}"
                   aria-label="Search query">
            <button type="submit" class="safa-search__button" aria-label="Search">
                <span class="safa-search__icon">🔍</span>
            </button>
        </div>
        
        <!-- Search suggestions (enhanced with JavaScript) -->
        <div class="safa-search__suggestions" data-marwa-feature="suggestions"></div>
    </form>
</div>
```

---

## 🔧 **Using Components in New System**

### **Template References (Updated):**
```php
// OLD WAY (separate system)
$component = 'components/header.twig';
$component = 'components/navigation.twig';

// NEW WAY (unified skin system)
$component = 'skins/' . $activeSkin . '/components/header.twig';
$component = 'skins/' . $activeSkin . '/components/navigation.twig';
```

### **Include Usage:**
```twig
{# OLD WAY (separate system) #}
{% include 'components/header.twig' %}

{# NEW WAY (unified skin system) #}
{% include 'skins/' ~ activeSkin ~ '/components/header.twig' %}
```

### **Controller Usage:**
```php
class ComponentController extends Controller
{
    public function header(): Response
    {
        $user = $this->getCurrentUser();
        $navigation = $this->getNavigationItems();
        
        // Use skin-specific component template
        return $this->view('skins/' . $this->getActiveSkin() . '/components/header.twig', [
            'user' => $user,
            'navigation' => $navigation,
            'activeSkin' => $this->getActiveSkin()
        ]);
    }
    
    public function search(): Response
    {
        $suggestions = $this->searchService->getSuggestions();
        
        // Use skin-specific component template
        return $this->view('skins/' . $this->getActiveSkin() . '/components/search.twig', [
            'suggestions' => $suggestions,
            'activeSkin' => $this->getActiveSkin()
        ]);
    }
}
```

---

## 🎨 **Safa CSS Framework Integration**

### **Component CSS Classes (Updated):**
```css
/* ✅ Correct - Using Safa framework naming */
.safa-header {
    background: var(--safa-theme-primary);
    color: white;
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.safa-header__content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: var(--safa-content-max-width);
    margin: 0 auto;
    padding: 0 2rem;
}

.safa-header__logo {
    display: flex;
    align-items: center;
}

.safa-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
}

.safa-logo__icon {
    font-size: 2rem;
}

.safa-header__nav {
    flex: 1;
    margin: 0 2rem;
}

.safa-nav__list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 2rem;
}

.safa-nav__link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.2s ease;
}

.safa-nav__link:hover {
    opacity: 0.8;
}

.safa-header__user {
    display: flex;
    align-items: center;
}

.safa-user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.safa-user-menu__name {
    color: white;
    font-weight: 500;
}

.safa-user-menu__link {
    color: white;
    text-decoration: none;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.safa-user-menu__link:hover {
    opacity: 1;
}
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **Component JavaScript (Updated):**
```javascript
// Component initialization within skin context
class MarwaHeader {
    constructor(skinName) {
        this.skinName = skinName;
        this.init();
    }
    
    init() {
        this.setupMobileNavigation();
        this.setupUserMenu();
        this.setupSearchFunctionality();
    }
    
    setupMobileNavigation() {
        const navToggle = document.querySelector(`.${this.skinName}-nav-toggle`);
        const nav = document.querySelector(`.${this.skinName}-header__nav`);
        
        if (navToggle && nav) {
            navToggle.addEventListener('click', () => {
                nav.classList.toggle(`${this.skinName}-header__nav--open`);
                navToggle.setAttribute('aria-expanded', 
                    nav.classList.contains(`${this.skinName}-header__nav--open`).toString());
            });
        }
    }
    
    setupUserMenu() {
        const userMenu = document.querySelector(`.${this.skinName}-user-menu`);
        const userToggle = document.querySelector(`.${this.skinName}-user-toggle`);
        
        if (userToggle && userMenu) {
            userToggle.addEventListener('click', () => {
                userMenu.classList.toggle(`${this.skinName}-user-menu--open`);
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!userToggle.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.remove(`${this.skinName}-user-menu--open`);
                }
            });
        }
    }
    
    setupSearchFunctionality() {
        const searchForm = document.querySelector(`.${this.skinName}-search__form`);
        const searchInput = document.querySelector(`.${this.skinName}-search__input`);
        const suggestions = document.querySelector(`.${this.skinName}-search__suggestions`);
        
        if (searchInput && suggestions) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                if (query.length > 2) {
                    this.fetchSuggestions(query, suggestions);
                } else {
                    suggestions.innerHTML = '';
                }
            });
        }
    }
    
    async fetchSuggestions(query, container) {
        try {
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            container.innerHTML = data.suggestions.map(suggestion => 
                `<div class="safa-search__suggestion">${suggestion}</div>`
            ).join('');
        } catch (error) {
            console.error('Failed to fetch suggestions:', error);
        }
    }
}

// Initialize for current skin
document.addEventListener('DOMContentLoaded', () => {
    const activeSkin = document.documentElement.dataset.skin || 'Bismillah';
    new MarwaHeader(activeSkin);
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
- **[Skin Development](../skins/development.md)** - How to develop skins with components
- **[Template Extension System Plan](../architecture/template-extension-system-plan.md)** - Implementation plan
- **[SafaSkinExtension](../extensions/SafaSkinExtension.md)** - Extension documentation

### **Migration Resources:**
- **[Migration Guide](../skins/migration.md)** - How to migrate existing components
- **[File Organization](../guides/organization.md)** - New file organization structure

---

## 🧪 **Testing Components (Updated)**

### **Component Testing (New Context):**
```php
class ComponentTest extends TestCase
{
    public function testHeaderComponentRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = [
            'user' => ['name' => 'Test User'],
            'navigation' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Wiki', 'url' => '/wiki']
            ],
            'activeSkin' => 'Bismillah'
        ];
        
        // Test skin-specific component template
        $html = $renderer->render('skins/Bismillah/components/header.twig', $data);
        
        $this->assertStringContains('safa-header', $html);
        $this->assertStringContains('Test User', $html);
        $this->assertStringContains('Home', $html);
        $this->assertStringContains('Wiki', $html);
    }
    
    public function testSearchComponentRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = [
            'query' => 'test query',
            'activeSkin' => 'Bismillah'
        ];
        
        // Test skin-specific component template
        $html = $renderer->render('skins/Bismillah/components/search.twig', $data);
        
        $this->assertStringContains('safa-search', $html);
        $this->assertStringContains('test query', $html);
        $this->assertStringContains('Search Islamic knowledge', $html);
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
1. **Template Paths**: All component template paths have changed
2. **File Locations**: Components are now within skin directories
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
**Status:** Components Now Part of Unified Skin System ✅  
**Migration:** Required - Update all template references 
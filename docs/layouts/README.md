# IslamWiki Layout System

## 🎯 **Overview**

This directory contains documentation for the layout system of IslamWiki, which provides the structural foundation for all pages, implements the dashboard interface, and ensures consistent user experience across the platform.

---

## 🏗️ **Layout Architecture**

### **Layout Hierarchy**
```
Layout System:
├── 📁 Base Layouts - Foundation layout templates
├── 📁 Dashboard Layouts - Administrative and user dashboards
├── 📁 Page Layouts - Content page layouts
├── 📁 Form Layouts - Form and input layouts
├── 📁 Error Layouts - Error page layouts
└── 📁 Email Layouts - Email notification layouts
```

### **Layout Responsibilities**
- **Page Structure**: Define overall page structure and organization
- **Navigation**: Provide consistent navigation and user interface
- **Responsiveness**: Ensure mobile-first responsive design
- **Accessibility**: Implement WCAG 2.1 AA compliance
- **Theme Support**: Support multiple Islamic themes

---

## 🔧 **Layout Categories**

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

## 📝 **Layout Implementation**

### **Base Layout Structure**
```twig
{# Main Application Layout #}
<!DOCTYPE html>
<html lang="{{ app.locale }}" dir="{{ app.direction }}" class="safa-theme--{{ app.theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{% block meta_description %}IslamWiki - Islamic Knowledge Platform{% endblock %}">
    <title>{% block title %}IslamWiki{% endblock %}</title>
    
    {# Favicon and Icons #}
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    
    {# Safa CSS Framework #}
    <link rel="stylesheet" href="{{ asset('safa/safa-base.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-components.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-themes.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-utilities.css') }}">
    
    {# Custom Fonts #}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {# Page-specific CSS #}
    {% block stylesheets %}{% endblock %}
</head>
<body class="safa-body">
    {# Skip to Content Link for Accessibility #}
    <a href="#main-content" class="safa-skip-link">Skip to main content</a>
    
    {# Header #}
    <header class="safa-header" role="banner">
        {% include 'components/header.twig' %}
    </header>
    
    {# Main Navigation #}
    <nav class="safa-main-nav" role="navigation" aria-label="Main navigation">
        {% include 'components/navigation.twig' %}
    </nav>
    
    {# Main Content #}
    <main id="main-content" class="safa-main" role="main">
        {# Breadcrumbs #}
        {% if breadcrumbs is defined %}
            {% include 'components/breadcrumbs.twig' %}
        {% endif %}
        
        {# Page Content #}
        {% block content %}{% endblock %}
    </main>
    
    {# Footer #}
    <footer class="safa-footer" role="contentinfo">
        {% include 'components/footer.twig' %}
    </footer>
    
    {# Marwa JavaScript Framework #}
    <script src="{{ asset('marwa/marwa-core.js') }}" defer></script>
    <script src="{{ asset('marwa/marwa-components.js') }}" defer></script>
    <script src="{{ asset('marwa/marwa-themes.js') }}" defer></script>
    
    {# Page-specific JavaScript #}
    {% block javascripts %}{% endblock %}
</body>
</html>
```

### **Dashboard Layout Example**
```twig
{# Dashboard Layout #}
{% extends 'layouts/base.twig' %}

{% block content %}
<div class="safa-dashboard">
    {# Sidebar Navigation #}
    <aside class="safa-dashboard__sidebar" role="complementary">
        <nav class="safa-dashboard-nav" aria-label="Dashboard navigation">
            <ul class="safa-dashboard-nav__list">
                <li class="safa-dashboard-nav__item">
                    <a href="{{ path('dashboard.overview') }}" class="safa-dashboard-nav__link">
                        <span class="safa-dashboard-nav__icon">📊</span>
                        <span class="safa-dashboard-nav__text">Overview</span>
                    </a>
                </li>
                <li class="safa-dashboard-nav__item">
                    <a href="{{ path('dashboard.content') }}" class="safa-dashboard-nav__link">
                        <span class="safa-dashboard-nav__icon">📝</span>
                        <span class="safa-dashboard-nav__text">Content</span>
                    </a>
                </li>
                <li class="safa-dashboard-nav__item">
                    <a href="{{ path('dashboard.users') }}" class="safa-dashboard-nav__link">
                        <span class="safa-dashboard-nav__icon">👥</span>
                        <span class="safa-dashboard-nav__text">Users</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    {# Main Dashboard Content #}
    <div class="safa-dashboard__main">
        <div class="safa-dashboard__header">
            <h1 class="safa-dashboard__title">{% block dashboard_title %}Dashboard{% endblock %}</h1>
            <div class="safa-dashboard__actions">
                {% block dashboard_actions %}{% endblock %}
            </div>
        </div>
        
        <div class="safa-dashboard__content">
            {% block dashboard_content %}{% endblock %}
        </div>
    </div>
</div>
{% endblock %}
```

---

## 🎨 **Safa CSS Framework Integration**

### **Layout CSS Classes**
```css
/* ✅ Correct - Using Safa framework naming */
.safa-body {
    /* Body styles */
}

.safa-header {
    /* Header styles */
}

.safa-main-nav {
    /* Main navigation */
}

.safa-main {
    /* Main content area */
}

.safa-footer {
    /* Footer styles */
}

.safa-dashboard {
    /* Dashboard layout */
}

.safa-dashboard__sidebar {
    /* Dashboard sidebar */
}

.safa-dashboard__main {
    /* Dashboard main content */
}

/* ❌ Incorrect - Generic naming */
.body {
    /* No prefix */
}

.header {
    /* No prefix */
}
```

### **Responsive Design**
```css
/* Mobile First Approach */
.safa-dashboard {
    display: flex;
    flex-direction: column;
}

.safa-dashboard__sidebar {
    order: 2;
}

.safa-dashboard__main {
    order: 1;
}

/* Tablet and Desktop */
@media (min-width: 768px) {
    .safa-dashboard {
        flex-direction: row;
    }
    
    .safa-dashboard__sidebar {
        order: 1;
        width: 250px;
        flex-shrink: 0;
    }
    
    .safa-dashboard__main {
        order: 2;
        flex: 1;
    }
}
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **Layout JavaScript**
```javascript
// Layout initialization
class MarwaLayout {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupSkipLinks();
        this.setupResponsiveNavigation();
        this.setupThemeSwitcher();
        this.setupAccessibility();
    }
    
    setupSkipLinks() {
        // Handle skip to content links
        const skipLinks = document.querySelectorAll('.safa-skip-link');
        skipLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView();
                }
            });
        });
    }
    
    setupResponsiveNavigation() {
        // Handle mobile navigation
        const navToggle = document.querySelector('.safa-nav-toggle');
        const nav = document.querySelector('.safa-main-nav');
        
        if (navToggle && nav) {
            navToggle.addEventListener('click', () => {
                nav.classList.toggle('safa-main-nav--open');
            });
        }
    }
}
```

---

## 🔒 **Security & Accessibility**

### **Security Features**
- **CSRF Protection**: Token-based form protection
- **XSS Prevention**: Output escaping and sanitization
- **Content Security Policy**: CSP headers for security
- **Secure Headers**: Security-focused HTTP headers

### **Accessibility Features**
- **Skip Links**: Skip to main content navigation
- **ARIA Labels**: Proper ARIA labeling and roles
- **Keyboard Navigation**: Full keyboard navigation support
- **Screen Reader Support**: Screen reader compatibility
- **High Contrast**: High contrast mode support

---

## 📚 **Layout Documentation**

### **Available Layouts**
- **[Base Layouts](base/README.md)** - Foundation layouts
- **[Dashboard Layouts](dashboard/README.md)** - Dashboard interfaces
- **[Page Layouts](page/README.md)** - Content page layouts
- **[Form Layouts](form/README.md)** - Form layouts
- **[Error Layouts](error/README.md)** - Error page layouts

### **Layout Development**
- **[Layout Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🧪 **Testing Layouts**

### **Layout Testing**
```php
class LayoutTest extends TestCase
{
    public function testLayoutRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = ['theme' => 'islamic'];
        
        $html = $renderer->render('layouts/base.twig', $data);
        
        $this->assertStringContains('safa-theme--islamic', $html);
        $this->assertStringContains('safa-body', $html);
        $this->assertStringContains('safa-header', $html);
    }
}
```

### **Accessibility Testing**
- **ARIA Compliance**: Test ARIA labels and roles
- **Keyboard Navigation**: Test keyboard accessibility
- **Screen Reader**: Test screen reader compatibility
- **Color Contrast**: Test color contrast ratios

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Views Documentation](../views/README.md)** - Template system
- **[Components Documentation](../components/README.md)** - UI components

### **Development Resources**
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Layouts Documentation Complete ✅ 
# IslamWiki Views & Templates

## 🎯 **Overview**

This directory contains documentation for the view and template system of IslamWiki. Views use the Twig templating engine to render HTML, implement Islamic naming conventions, and provide a flexible, secure template system.

---

## 🏗️ **View Architecture**

### **Template Hierarchy**
```
Template System:
├── 📁 Layouts - Base layout templates
├── 📁 Pages - Page-specific templates
├── 📁 Components - Reusable UI components
├── 📁 Forms - Form templates and validation
├── 📁 Errors - Error page templates
└── 📁 Emails - Email notification templates
```

### **View Responsibilities**
- **Template Rendering**: Render HTML from Twig templates
- **Data Binding**: Bind controller data to templates
- **Component Reuse**: Provide reusable UI components
- **Security**: Implement output escaping and CSRF protection
- **Internationalization**: Support multiple languages

---

## 🔧 **Template Categories**

### **1. Layout Templates**
- **Base Layout**: Main application layout
- **Admin Layout**: Administrative interface layout
- **API Layout**: API response layout
- **Email Layout**: Email notification layout

### **2. Page Templates**
- **Home Page**: Main homepage template
- **Wiki Pages**: Content page templates
- **User Pages**: User profile and account templates
- **Search Results**: Search result display templates

### **3. Component Templates**
- **Navigation**: Site navigation components
- **Forms**: Form input and validation components
- **Cards**: Content card components
- **Modals**: Modal dialog components

---

## 📝 **Template Implementation**

### **Basic Template Structure**
```twig
{# Base Layout Template #}
<!DOCTYPE html>
<html lang="{{ app.locale }}" dir="{{ app.direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}IslamWiki{% endblock %}</title>
    
    {# Safa CSS Framework #}
    <link rel="stylesheet" href="{{ asset('safa/safa-base.css') }}">
    <link rel="stylesheet" href="{{ asset('safa/safa-components.css') }}">
    
    {# Marwa JavaScript Framework #}
    <script src="{{ asset('marwa/marwa-core.js') }}" defer></script>
</head>
<body class="safa-theme--{{ app.theme }}">
    {# Header Navigation #}
    {% include 'components/navigation.twig' %}
    
    {# Main Content #}
    <main class="safa-main">
        {% block content %}{% endblock %}
    </main>
    
    {# Footer #}
    {% include 'components/footer.twig' %}
</body>
</html>
```

### **Component Template Example**
```twig
{# Navigation Component #}
<nav class="safa-navigation" role="navigation" aria-label="Main navigation">
    <div class="safa-container">
        <div class="safa-navigation__brand">
            <a href="{{ path('home') }}" class="safa-brand">
                <img src="{{ asset('images/logo.png') }}" alt="IslamWiki" class="safa-brand__logo">
                <span class="safa-brand__text">IslamWiki</span>
            </a>
        </div>
        
        <div class="safa-navigation__menu">
            <ul class="safa-menu">
                <li class="safa-menu__item">
                    <a href="{{ path('home') }}" class="safa-menu__link">Home</a>
                </li>
                <li class="safa-menu__item">
                    <a href="{{ path('quran') }}" class="safa-menu__link">Quran</a>
                </li>
                <li class="safa-menu__item">
                    <a href="{{ path('hadith') }}" class="safa-menu__link">Hadith</a>
                </li>
                <li class="safa-menu__item">
                    <a href="{{ path('salah-times') }}" class="safa-menu__link">Salah Times</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

---

## 🎨 **Safa CSS Framework Integration**

### **CSS Class Naming**
```css
/* ✅ Correct - Using Safa framework naming */
.safa-navigation {
    /* Navigation styles */
}

.safa-navigation__brand {
    /* Brand section */
}

.safa-navigation__menu {
    /* Menu section */
}

.safa-menu {
    /* Menu list */
}

.safa-menu__item {
    /* Menu item */
}

.safa-menu__link {
    /* Menu link */
}

/* ❌ Incorrect - Generic naming */
.navigation {
    /* No prefix */
}

.menu-item {
    /* Mixed naming */
}
```

### **Theme System**
```twig
{# Theme Switching #}
<div class="safa-theme-switcher">
    <button class="safa-button" data-theme="islamic">Islamic</button>
    <button class="safa-button" data-theme="ramadan">Ramadan</button>
    <button class="safa-button" data-theme="light">Light</button>
    <button class="safa-button" data-theme="dark">Dark</button>
</div>
```

---

## 🚀 **Marwa JavaScript Framework Integration**

### **JavaScript Component Integration**
```twig
{# JavaScript Component Initialization #}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Marwa components
    new MarwaThemeSwitcher('.safa-theme-switcher');
    new MarwaNavigation('.safa-navigation');
    new MarwaSearch('.safa-search');
});
</script>
```

### **Progressive Enhancement**
```twig
{# Progressive Enhancement Example #}
<div class="safa-search" data-marwa-component="search">
    <form class="safa-search__form" action="{{ path('search') }}" method="GET">
        <input type="text" name="q" class="safa-search__input" placeholder="Search IslamWiki...">
        <button type="submit" class="safa-search__button">Search</button>
    </form>
    
    {# Enhanced search with JavaScript #}
    <div class="safa-search__suggestions" data-marwa-feature="suggestions"></div>
</div>
```

---

## 🔒 **Security Features**

### **Output Escaping**
```twig
{# Automatic Output Escaping #}
<h1>{{ page.title|escape }}</h1>
<div class="content">{{ page.content|raw }}</div>

{# CSRF Protection #}
<form method="POST" action="{{ path('page.update') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- Form fields -->
</form>
```

### **Input Validation**
```twig
{# Form Validation Display #}
{% if errors %}
    <div class="safa-alert safa-alert--error">
        <ul class="safa-alert__list">
            {% for error in errors %}
                <li class="safa-alert__item">{{ error|escape }}</li>
            {% endfor %}
        </ul>
    </div>
{% endif %}
```

---

## 📚 **Template Documentation**

### **Available Templates**
- **[Layout Templates](layouts/README.md)** - Base layout templates
- **[Page Templates](pages/README.md)** - Page-specific templates
- **[Component Templates](components/README.md)** - Reusable components
- **[Form Templates](forms/README.md)** - Form templates
- **[Error Templates](errors/README.md)** - Error page templates

### **Template Development**
- **[Template Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🧪 **Testing Templates**

### **Template Testing**
```php
class TemplateTest extends TestCase
{
    public function testTemplateRendersCorrectly(): void
    {
        $renderer = new TwigRenderer();
        $data = ['title' => 'Test Page'];
        
        $html = $renderer->render('pages/test.twig', $data);
        
        $this->assertStringContains('Test Page', $html);
        $this->assertStringContains('safa-theme', $html);
    }
}
```

### **Component Testing**
- **Template Rendering**: Test template output
- **Data Binding**: Test data interpolation
- **Security Features**: Test output escaping
- **Accessibility**: Test ARIA compliance

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Controllers Documentation](../controllers/README.md)** - Request handling
- **[Models Documentation](../models/README.md)** - Data models

### **Development Resources**
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Views Documentation Complete ✅ 
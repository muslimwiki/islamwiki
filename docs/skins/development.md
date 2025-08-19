# IslamWiki Skin Development Guide

## 🎯 **Overview**

IslamWiki's skin system follows a **WordPress-inspired theme architecture** combined with **modern web development practices**. Skins allow you to customize the appearance and user experience of the platform while maintaining clean separation of concerns.

---

## 🏗️ **Skin Architecture**

### **Design Principles**
- **Responsive**: Mobile-first design approach
- **Accessible**: WCAG 2.1 AA compliance
- **Performance**: Optimized asset loading
- **Customizable**: Easy customization options
- **Maintainable**: Clean, organized code structure

### **Skin Types**
```
Skin Categories:
├── 📁 Default Skins          # Built-in platform skins
├── 📁 Custom Skins           # User-created skins
├── 📁 Responsive Skins       # Mobile-optimized skins
├── 📁 Accessibility Skins    # High-contrast, screen reader friendly
└── 📁 Islamic Skins          # Islamic-themed designs
```

---

## 📁 **Skin Structure**

### **Standard Skin Layout**
```
skins/
├── 📁 {SkinName}/
│   ├── 📁 css/                # Skin-specific styles
│   │   ├── 📄 style.css       # Main stylesheet
│   │   ├── 📄 responsive.css  # Responsive styles
│   │   └── 📄 print.css       # Print styles
│   ├── 📁 js/                 # Skin-specific scripts
│   │   ├── 📄 main.js         # Main JavaScript
│   │   └── 📄 custom.js       # Custom functionality
│   ├── 📁 templates/          # Skin-specific templates
│   │   ├── 📁 layouts/        # Layout templates
│   │   ├── 📁 pages/          # Page templates
│   │   ├── 📁 components/     # Component templates
│   │   └── 📁 partials/       # Partial templates
│   ├── 📁 images/             # Skin-specific images
│   │   ├── 📁 icons/          # Icon files
│   │   ├── 📁 backgrounds/    # Background images
│   │   └── 📁 logos/          # Logo files
│   ├── 📁 fonts/              # Custom fonts
│   ├── 📄 skin.json           # Skin configuration
│   ├── 📄 screenshot.png      # Skin preview image
│   └── 📄 README.md           # Skin documentation
```

### **Required Files**
- **CSS Directory**: Main stylesheets
- **JavaScript Directory**: Interactive functionality
- **Templates Directory**: Twig template files
- **Skin Configuration**: Metadata and settings

---

## 🎨 **Skin Development**

### **1. Create Skin Directory**
```bash
mkdir -p skins/MySkin/{css,js,templates/{layouts,pages,components,partials},images/{icons,backgrounds,logos},fonts}
```

### **2. Create Skin Configuration**
```json
{
  "name": "MySkin",
  "version": "0.0.1",
  "description": "A custom skin for IslamWiki",
  "author": "Your Name",
  "license": "GPL-3.0",
  "requires": {
    "islamwiki": ">=0.0.19"
  },
  "category": "custom",
  "tags": ["responsive", "modern", "islamic"],
  "homepage": "https://github.com/yourname/MySkin",
  "screenshot": "screenshot.png",
  "features": [
    "responsive",
    "accessibility",
    "customization",
    "rtl-support"
  ]
}
```

### **3. Create Main Stylesheet**
```css
/* css/style.css */
:root {
  /* CSS Custom Properties */
  --primary-color: #2c5aa0;
  --secondary-color: #f8f9fa;
  --text-color: #333;
  --link-color: #007bff;
  --border-color: #dee2e6;
  --success-color: #28a745;
  --warning-color: #ffc107;
  --error-color: #dc3545;
  
  /* Typography */
  --font-family-base: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  --font-family-heading: 'Amiri', serif;
  --font-size-base: 16px;
  --line-height-base: 1.6;
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 3rem;
  
  /* Breakpoints */
  --breakpoint-sm: 576px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 992px;
  --breakpoint-xl: 1200px;
}

/* Reset and Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family-base);
  font-size: var(--font-size-base);
  line-height: var(--line-height-base);
  color: var(--text-color);
  background-color: var(--secondary-color);
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-family-heading);
  margin-bottom: var(--spacing-md);
  color: var(--primary-color);
}

h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
h5 { font-size: 1.25rem; }
h6 { font-size: 1rem; }

/* Links */
a {
  color: var(--link-color);
  text-decoration: none;
  transition: color 0.3s ease;
}

a:hover {
  color: darken(var(--link-color), 10%);
  text-decoration: underline;
}

/* Buttons */
.btn {
  display: inline-block;
  padding: var(--spacing-sm) var(--spacing-md);
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--primary-color);
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn:hover {
  background-color: darken(var(--primary-color), 10%);
  transform: translateY(-1px);
}

/* Layout Components */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.header {
  background-color: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: var(--spacing-md) 0;
}

.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.main-content {
  padding: var(--spacing-xl) 0;
}

.footer {
  background-color: var(--primary-color);
  color: white;
  padding: var(--spacing-lg) 0;
  margin-top: var(--spacing-xl);
}
```

### **4. Create Responsive Styles**
```css
/* css/responsive.css */
/* Mobile First Approach */

/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {
  .container {
    max-width: 540px;
  }
}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
  .container {
    max-width: 720px;
  }
  
  .nav {
    flex-direction: row;
  }
  
  .mobile-menu-toggle {
    display: none;
  }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
  .container {
    max-width: 960px;
  }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
  .container {
    max-width: 1140px;
  }
}

/* Mobile Navigation */
@media (max-width: 767px) {
  .nav {
    flex-direction: column;
  }
  
  .nav-menu {
    display: none;
  }
  
  .nav-menu.active {
    display: block;
  }
  
  .mobile-menu-toggle {
    display: block;
  }
}
```

### **5. Create Main JavaScript**
```javascript
// js/main.js
class MySkin {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupMobileMenu();
        this.setupSmoothScrolling();
        this.setupLazyLoading();
        this.setupAccessibility();
    }
    
    setupMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const menu = document.querySelector('.nav-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', () => {
                menu.classList.toggle('active');
                toggle.setAttribute('aria-expanded', 
                    menu.classList.contains('active').toString());
            });
        }
    }
    
    setupSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    setupAccessibility() {
        // Skip to content link
        const skipLink = document.querySelector('.skip-link');
        if (skipLink) {
            skipLink.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector('#main-content');
                if (target) {
                    target.focus();
                    target.scrollIntoView();
                }
            });
        }
        
        // Focus management
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });
    }
}

// Initialize skin when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new MySkin();
});
```

---

## 🎨 **Template Development**

### **Layout Template**
```twig
{# templates/layouts/default.twig #}
<!DOCTYPE html>
<html lang="{{ app.locale }}" dir="{{ app.direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{{ site.title }}{% endblock %}</title>
    
    <!-- Meta tags -->
    <meta name="description" content="{% block description %}{{ site.description }}{% endblock %}">
    <meta name="keywords" content="{% block keywords %}{{ site.keywords }}{% endblock %}">
    <meta name="author" content="{{ site.author }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="{% block og_title %}{{ site.title }}{% endblock %}">
    <meta property="og:description" content="{% block og_description %}{{ site.description }}{% endblock %}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ app.request.uri }}">
    <meta property="og:image" content="{% block og_image %}{{ site.logo }}{% endblock %}">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/print.css') }}" media="print">
    
    <!-- Custom fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    
    {% block head %}{% endblock %}
</head>
<body class="skin-{{ app.skin.name }}">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Header -->
    <header class="header" role="banner">
        <div class="container">
            <nav class="nav" role="navigation" aria-label="Main navigation">
                <div class="nav-brand">
                    <a href="{{ path('home') }}" class="brand-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ site.title }}" width="150" height="50">
                    </a>
                </div>
                
                <button class="mobile-menu-toggle" aria-expanded="false" aria-controls="nav-menu">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="hamburger"></span>
                </button>
                
                <div class="nav-menu" id="nav-menu">
                    {% include 'partials/navigation.twig' %}
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Main content -->
    <main id="main-content" class="main-content" role="main">
        {% block content %}{% endblock %}
    </main>
    
    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            {% include 'partials/footer.twig' %}
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="{{ asset('js/main.js') }}"></script>
    {% block scripts %}{% endblock %}
</body>
</html>
```

### **Page Template**
```twig
{# templates/pages/home.twig #}
{% extends "layouts/default.twig" %}

{% block title %}Home - {{ parent() }}{% endblock %}

{% block content %}
<div class="hero-section">
    <div class="container">
        <h1 class="hero-title">Welcome to IslamWiki</h1>
        <p class="hero-subtitle">Your comprehensive source for Islamic knowledge</p>
        <div class="hero-actions">
            <a href="{{ path('wiki') }}" class="btn btn-primary">Explore Wiki</a>
            <a href="{{ path('quran') }}" class="btn btn-secondary">Read Quran</a>
        </div>
    </div>
</div>

<section class="featured-content">
    <div class="container">
        <h2>Featured Content</h2>
        <div class="content-grid">
            {% for article in featured_articles %}
            <article class="content-card">
                <div class="card-image">
                    {% if article.image %}
                    <img src="{{ article.image }}" alt="{{ article.title }}" loading="lazy">
                    {% endif %}
                </div>
                <div class="card-content">
                    <h3 class="card-title">
                        <a href="{{ path('article', {slug: article.slug}) }}">{{ article.title }}</a>
                    </h3>
                    <p class="card-excerpt">{{ article.excerpt }}</p>
                    <div class="card-meta">
                        <span class="author">By {{ article.author }}</span>
                        <span class="date">{{ article.created_at|date('M j, Y') }}</span>
                    </div>
                </div>
            </article>
            {% endfor %}
        </div>
    </div>
</section>
{% endblock %}
```

---

## 🔧 **Skin Customization**

### **Customization Options**
```json
{
  "customization": {
    "colors": {
      "primary": "#2c5aa0",
      "secondary": "#f8f9fa",
      "accent": "#28a745"
    },
    "typography": {
      "font-family": "Amiri, serif",
      "font-size": "16px"
    },
    "layout": {
      "container-width": "1200px",
      "sidebar-position": "right"
    }
  }
}
```

### **Customization Interface**
```php
<?php

namespace IslamWiki\Skins\MySkin;

class SkinCustomizer
{
    public function getCustomizationOptions(): array
    {
        return [
            'colors' => [
                'primary' => [
                    'label' => 'Primary Color',
                    'type' => 'color',
                    'default' => '#2c5aa0'
                ],
                'secondary' => [
                    'label' => 'Secondary Color',
                    'type' => 'color',
                    'default' => '#f8f9fa'
                ]
            ],
            'typography' => [
                'font-family' => [
                    'label' => 'Font Family',
                    'type' => 'select',
                    'options' => [
                        'Amiri' => 'Amiri (Arabic)',
                        'Segoe UI' => 'Segoe UI',
                        'Arial' => 'Arial'
                    ],
                    'default' => 'Amiri'
                ]
            ]
        ];
    }
}
```

---

## ♿ **Accessibility Features**

### **WCAG 2.1 AA Compliance**
- **Keyboard Navigation**: Full keyboard support
- **Screen Reader Support**: Proper ARIA labels
- **Color Contrast**: Sufficient color contrast ratios
- **Focus Management**: Clear focus indicators
- **Skip Links**: Skip to content functionality

### **Accessibility Implementation**
```css
/* Accessibility styles */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.skip-link {
  position: absolute;
  top: -40px;
  left: 6px;
  background: #000;
  color: white;
  padding: 8px;
  text-decoration: none;
  z-index: 1000;
}

.skip-link:focus {
  top: 6px;
}

/* Focus indicators */
.keyboard-navigation *:focus {
  outline: 2px solid var(--primary-color);
  outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
  :root {
    --primary-color: #000;
    --secondary-color: #fff;
    --text-color: #000;
    --border-color: #000;
  }
}
```

---

## 🌐 **RTL Support**

### **RTL Stylesheet**
```css
/* css/rtl.css */
[dir="rtl"] {
  /* Text alignment */
  text-align: right;
  
  /* Margins and paddings */
  margin-left: 0;
  margin-right: var(--spacing-md);
  
  /* Floats */
  float: right;
  
  /* Border radius */
  border-radius: 0 4px 4px 0;
}

[dir="rtl"] .nav-menu {
  margin-left: 0;
  margin-right: auto;
}

[dir="rtl"] .content-grid {
  direction: rtl;
}
```

### **RTL Template Support**
```twig
{# Check direction in templates #}
{% if app.direction == 'rtl' %}
  <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
{% endif %}

{# Use direction-aware classes #}
<div class="content {{ app.direction == 'rtl' ? 'rtl' : 'ltr' }}">
  <!-- Content -->
</div>
```

---

## 🚀 **Performance Optimization**

### **Asset Optimization**
- **CSS Minification**: Compress stylesheets
- **JavaScript Minification**: Compress scripts
- **Image Optimization**: Compress and optimize images
- **Font Loading**: Optimize font loading
- **Lazy Loading**: Implement lazy loading for images

### **Performance Implementation**
```javascript
// Lazy loading for images
const imageObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const img = entry.target;
      img.src = img.dataset.src;
      img.classList.remove('lazy');
      observer.unobserve(img);
    }
  });
});

// Critical CSS inlining
function inlineCriticalCSS() {
  const criticalCSS = `
    /* Critical CSS here */
  `;
  
  const style = document.createElement('style');
  style.textContent = criticalCSS;
  document.head.appendChild(style);
}
```

---

## 🧪 **Testing & Quality Assurance**

### **Testing Checklist**
- [ ] **Cross-browser Testing**: Test in major browsers
- [ ] **Responsive Testing**: Test on different screen sizes
- [ ] **Accessibility Testing**: Test with screen readers
- [ ] **Performance Testing**: Test loading times
- [ ] **User Testing**: Test with real users

### **Testing Tools**
- **Browser DevTools**: Chrome, Firefox, Safari
- **Responsive Testing**: Browser responsive mode
- **Accessibility Testing**: axe-core, WAVE
- **Performance Testing**: Lighthouse, PageSpeed Insights
- **Cross-browser Testing**: BrowserStack, Sauce Labs

---

## 📚 **Documentation**

### **Skin README**
```markdown
# MySkin

A custom skin for IslamWiki with modern design and accessibility features.

## Features
- Responsive design
- Accessibility compliance
- RTL support
- Customization options
- Performance optimized

## Installation
1. Copy to skins/ directory
2. Activate in admin panel
3. Customize settings

## Customization
Describe customization options

## Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License
GPL-3.0
```

---

## 🚀 **Deployment**

### **Skin Package**
```bash
# Create skin package
tar -czf MySkin-0.0.1.tar.gz MySkin/

# Or use Composer
composer create-package islamwiki/my-skin
```

### **Installation**
```bash
# Extract to skins directory
tar -xzf MySkin-0.0.1.tar.gz -C skins/

# Set permissions
chmod -R 755 skins/MySkin/
```

---

## 🔍 **Troubleshooting**

### **Common Issues**
1. **Styles not loading**: Check file paths and permissions
2. **JavaScript errors**: Check browser console for errors
3. **Responsive issues**: Test on different screen sizes
4. **Accessibility problems**: Use accessibility testing tools

### **Debug Mode**
Enable debug mode to see detailed information:

```php
// In skin class
public function debug(): void
{
    if ($this->isDebugMode()) {
        error_log('MySkin Debug: ' . print_r($this->getDebugInfo(), true));
    }
}
```

---

## 📞 **Support & Resources**

### **Documentation**
- **Skin API Reference**: Complete API documentation
- **Template Guide**: Twig template development
- **CSS Guide**: Styling and theming
- **JavaScript Guide**: Interactive functionality

### **Community**
- **Developer Forum**: Ask questions and share code
- **Code Examples**: Sample skins and snippets
- **Best Practices**: Development guidelines and standards

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Skin System:** WordPress-inspired with Modern Web Standards 
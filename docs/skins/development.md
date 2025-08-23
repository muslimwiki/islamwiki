# Skin Development Guide

## Overview

IslamWiki uses a modular skin system where each skin has its own directory containing CSS, JavaScript, and template files. The current active skin is **Bismillah**, which provides a beautiful Islamic-themed interface.

## CSS Architecture

### **Global Styles (`bismillah.css`)**
The main CSS file contains all global styles that apply across the entire site:

- **Layout Components**: Sidebar, header, footer, main content
- **Common Elements**: Buttons, forms, cards, utilities
- **CSS Variables**: Color scheme, spacing, typography
- **Responsive Design**: Global breakpoints and media queries

### **Page-Specific Styles (`/pages/` directory)**
Individual CSS files for specific page types that extend the global styles:

```
/skins/Bismillah/css/pages/
├── main-page.css      # Main page specific styles
├── settings.css       # Settings page styles
└── dashboard.css      # Dashboard page styles
```

### **CSS Loading System**
- **Global CSS**: Always loaded via `app.twig`
- **Page CSS**: Loaded via `{% block page_css %}` in individual templates
- **Asset Routing**: CSS files served through `/skins/{skin}/css/` routes

## File Structure

```
/skins/Bismillah/
├── css/
│   ├── bismillah.css          # Global styles
│   └── pages/                 # Page-specific styles
│       ├── main-page.css
│       ├── settings.css
│       └── dashboard.css
├── js/
│   └── bismillah.js          # Skin JavaScript
├── templates/                 # Skin-specific templates
└── assets/                   # Images, fonts, etc.
```

## CSS Organization Principles

### **1. Global vs. Page-Specific**
- **Global**: Styles that apply to multiple pages (sidebar, header, footer)
- **Page-Specific**: Styles unique to a single page type
- **No Duplication**: Each style rule appears in only one place

### **2. CSS Variables**
Use the predefined Islamic color scheme:

```css
:root {
    --islamic-blue: #17203D;
    --islamic-gold: #d4af37;
    --islamic-dark-blue: #17203D;
    --islamic-cream: #f8f6f0;
    --islamic-white: #ffffff;
    --islamic-green: #059669;
    --spacing-sm: 1rem;
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
    --radius-md: 0.5rem;
    --transition: all 0.3s ease;
}
```

### **3. Responsive Design**
Follow mobile-first approach with these breakpoints:

```css
/* Mobile first */
.element { /* Base styles */ }

/* Tablet */
@media (min-width: 768px) {
    .element { /* Tablet styles */ }
}

/* Desktop */
@media (min-width: 1024px) {
    .element { /* Desktop styles */ }
}
```

## Creating New Page Styles

### **1. Create CSS File**
Create a new CSS file in `/skins/Bismillah/css/pages/`:

```css
/* ===== NEW PAGE SPECIFIC STYLES ===== */
/* This file extends bismillah.css with new page specific styles */

.new-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.new-page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.new-page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--islamic-dark-blue);
    margin-bottom: 1rem;
}
```

### **2. Add Route**
Add a route in `config/routes.php`:

```php
$app->get('/skins/Bismillah/css/pages/new-page.css', function($request) {
    $filePath = dirname(__DIR__) . '/skins/Bismillah/css/pages/new-page.css';
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        return new \IslamWiki\Core\Http\Response(200, [
            'Content-Type' => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff'
        ], $content);
    }
    return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
});
```

### **3. Load in Template**
Use `{% block page_css %}` in your Twig template:

```twig
{% extends 'layouts/app.twig' %}

{% block title %}New Page - IslamWiki{% endblock %}

{% block page_css %}
<link rel="stylesheet" href="/skins/Bismillah/css/pages/new-page.css?v={{ 'now'|date('U') }}&v=0.0.2.5">
{% endblock %}

{% block content %}
<!-- Your page content here -->
{% endblock %}
```

## Best Practices

### **1. CSS Naming Conventions**
- Use descriptive class names: `.hero-section`, `.featured-card`
- Follow BEM methodology for complex components
- Use kebab-case for class names

### **2. Organization**
- Group related styles together with clear comments
- Use consistent spacing and indentation
- Keep CSS files focused and single-purpose

### **3. Performance**
- Minimize CSS specificity conflicts
- Use CSS variables for consistent values
- Optimize selectors for performance

### **4. Responsiveness**
- Design mobile-first
- Use relative units (rem, em, %) when possible
- Test on multiple device sizes

## Common Patterns

### **Card Components**
```css
.card {
    background: var(--islamic-white);
    border: 1px solid var(--islamic-blue);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
```

### **Section Layouts**
```css
.section {
    padding: 4rem 2rem;
    background: var(--islamic-white);
}

.section-container {
    max-width: 1400px;
    margin: 0 auto;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--islamic-dark-blue);
    margin-bottom: 2rem;
    text-align: center;
}
```

### **Grid Layouts**
```css
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
```

## Troubleshooting

### **Common Issues**

1. **CSS Not Loading**
   - Check route is properly defined in `config/routes.php`
   - Verify file path is correct
   - Check browser console for 404 errors

2. **Styles Not Applying**
   - Ensure CSS file is loaded via `{% block page_css %}`
   - Check CSS specificity (use browser dev tools)
   - Verify CSS variables are defined

3. **Responsive Issues**
   - Test breakpoints in browser dev tools
   - Ensure mobile-first approach is followed
   - Check media query syntax

### **Debugging Tools**
- **Browser Dev Tools**: Inspect elements and CSS
- **CSS Validator**: Check for syntax errors
- **Performance Tab**: Monitor CSS loading times

## Future Enhancements

### **Planned Features**
- **CSS Minification**: Compressed CSS for production
- **Critical CSS**: Inline critical styles for above-the-fold content
- **Theme System**: Multiple color schemes
- **Advanced Animations**: Enhanced hover effects

### **Performance Optimizations**
- **Lazy Loading**: CSS loaded as needed
- **Enhanced Caching**: Better browser caching strategies
- **CSS Splitting**: Load only necessary styles per page

---

**Version:** 0.0.2.5  
**Last Updated:** January 20, 2025  
**Author:** IslamWiki Development Team 
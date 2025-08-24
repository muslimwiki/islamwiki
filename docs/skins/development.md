# Skin Development Guide

## Overview

IslamWiki uses a **unified core skin system** where skin management is handled directly by the core architecture. The current active skin is **Bismillah**, which provides a beautiful Islamic-themed interface. All skin functionality has been consolidated into the core, providing enhanced capabilities and better performance.

## 🏗️ **Core Architecture**

### **Enhanced Core Services**
IslamWiki now provides comprehensive skin management through core services:

- **`skin.manager`** - Enhanced skin management with discovery and configuration
- **`skin.registry`** - Skin discovery, registration, and metadata management
- **`skin.assets`** - Asset management for CSS, JavaScript, and images
- **`skin.templates`** - Template engine for skin customization

### **Service Registration**
All skin services are automatically registered in the core container:

```php
// Services available in container
$skinManager = $container->get('skin.manager');
$skinRegistry = $container->get('skin.registry');
$assetManager = $container->get('skin.assets');
$templateEngine = $container->get('skin.templates');
```

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
├── home.css           # Home page specific styles (updated naming)
├── settings.css       # Settings page styles
└── dashboard.css      # Dashboard page styles
```

### **CSS Loading System**
- **Global CSS**: Always loaded via `app.twig`
- **Page CSS**: Loaded via `{% block page_css %}` in individual templates
- **Asset Routing**: CSS files served directly by web server for optimal performance

## File Structure

```
/skins/Bismillah/
├── css/
│   ├── bismillah.css          # Global styles
│   └── pages/                 # Page-specific styles
│       ├── home-page.css
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
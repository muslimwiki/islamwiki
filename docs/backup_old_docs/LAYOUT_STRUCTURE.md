# Layout Structure Documentation

## Overview

IslamWiki uses a hierarchical layout system with two main layouts designed for different purposes. This document explains when and how to use each layout.

## Layout Files

### 1. `layouts/app.twig` - Primary Application Layout

**Purpose**: Main layout for all regular application pages
**Usage**: 99% of pages in the system

**Features**:
- Complete Bismillah skin integration
- Full header with navigation
- Footer with site information
- RTL language toggle
- Responsive design
- Complete styling framework

**When to Use**:
- ✅ Quran pages
- ✅ Hadith pages
- ✅ User profile pages
- ✅ Community pages
- ✅ Search pages
- ✅ Any page that needs full site navigation

**Example Usage**:
```twig
{% extends "layouts/app.twig" %}

{% block content %}
    <!-- Your page content here -->
{% endblock %}
```

### 2. `layouts/debug.twig` - Minimal Debug Layout

**Purpose**: Minimal layout for special cases and debugging
**Usage**: Debug pages and minimal utility pages

**Features**:
- Minimal HTML structure
- Basic styling
- RTL language toggle
- No complex navigation
- Lightweight and fast

**When to Use**:
- ✅ Debug pages
- ✅ Maintenance reports
- ✅ Error pages
- ✅ Simple utility pages
- ✅ Pages that need minimal overhead

**Example Usage**:
```twig
{% extends "layouts/debug.twig" %}

{% block content %}
    <!-- Simple content without complex navigation -->
{% endblock %}
```

## Layout Hierarchy

```
layouts/
├── app.twig      ← Primary layout (most pages)
└── debug.twig    ← Minimal debug layout (special cases)
```

**Note**: The `debug.twig` layout is specifically designed for debug pages, maintenance reports, and minimal utility pages. It's not a "base" layout that other layouts extend from.

## Migration from Old Layouts

### Previously Used Layouts (Now Removed):
- ❌ `layouts/main.twig` - **DELETED** (was redundant with app.twig)

### Current Layouts:
- ✅ `layouts/app.twig` - **USE THIS** for regular pages
- ✅ `layouts/debug.twig` - **USE THIS** for minimal pages

## How to Choose the Right Layout

### Use `app.twig` When:
- You need full site navigation
- You want consistent header/footer
- You're building a regular page
- You need RTL support
- You want responsive design

### Use `debug.twig` When:
- You need minimal HTML structure
- You're building a debug page
- You want lightweight output
- You don't need complex navigation
- You're building a utility page

## RTL Support

Both layouts include the global RTL toggle component:

```twig
{% include 'components/rtl-toggle.twig' %}
```

This ensures RTL functionality is available regardless of which layout you choose.

## Customization

### Customizing `app.twig`:
```twig
{% extends "layouts/app.twig" %}

{% block head %}
    <!-- Add custom CSS/JS -->
    <link rel="stylesheet" href="/custom.css">
{% endblock %}

{% block content %}
    <!-- Your page content -->
{% endblock %}

{% block scripts %}
    <!-- Add custom JavaScript -->
    <script src="/custom.js"></script>
{% endblock %}
```

### Customizing `debug.twig`:
```twig
{% extends "layouts/debug.twig" %}

{% block styles %}
    <!-- Add custom styles -->
    <style>
        .custom-style { color: red; }
    </style>
{% endblock %}

{% block content %}
    <!-- Your minimal content -->
{% endblock %}
```

## Best Practices

1. **Default Choice**: Use `app.twig` for most pages
2. **Minimal Pages**: Use `debug.twig` only when you need minimal structure
3. **Consistency**: Stick to one layout per page type
4. **RTL Support**: Both layouts support RTL - no need to worry about this
5. **Performance**: `debug.twig` is faster but has fewer features

## Troubleshooting

### Common Issues:

1. **Layout Not Found**
   - Ensure the layout file exists
   - Check the path is correct
   - Verify file permissions

2. **Styling Issues**
   - `app.twig` includes full Bismillah skin
   - `debug.twig` has minimal styling
   - Add custom CSS in the appropriate block

3. **RTL Not Working**
   - Both layouts include RTL toggle
   - Check that the component is properly included
   - Verify JavaScript is loading

### Debug Mode:

Enable debug logging to see which layout is being used:

```php
// In your controller
$this->view->addGlobal('debug', true);
```

## Examples

### Regular Page (Quran):
```twig
{% extends "layouts/app.twig" %}

{% block title %}Quran - {{ surah.name }}{% endblock %}

{% block content %}
    <div class="quran-content">
        <h1>{{ surah.name }}</h1>
        <!-- Quran content -->
    </div>
{% endblock %}
```

### Debug Page:
```twig
{% extends "layouts/debug.twig" %}

{% block title %}Debug Information{% endblock %}

{% block content %}
    <div class="debug-info">
        <h1>Debug Information</h1>
        <pre>{{ debug_data|json_encode(constant('JSON_PRETTY_PRINT')) }}</pre>
    </div>
{% endblock %}
```

### Maintenance Report:
```twig
{% extends "layouts/debug.twig" %}

{% block title %}Maintenance Report{% endblock %}

{% block content %}
            <div class="maintenance-report">
            <h1>System Maintenance</h1>
            <table>
                <!-- Maintenance data -->
            </table>
        </div>
{% endblock %}
```

## Summary

- **Use `app.twig`** for 99% of pages (default choice)
- **Use `debug.twig`** only for minimal/special pages
- **Both support RTL** through the global component
- **`main.twig` is deleted** (was redundant)
- **Choose based on your needs**: full features vs. minimal overhead 
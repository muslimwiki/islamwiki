# Layout Architecture

## Overview

IslamWiki uses a flexible layout system that provides both consistency and customization:

- **`app.twig`** - General layout for all pages (default)
- **`auth.twig`** - Authentication pages layout
- **`PAGENAME.twig`** - Page-specific layouts for custom styling (future)

## Layout Files

### `app.twig` - General Layout (Default)

**Purpose**: Standard layout used by most pages in the application.

**Features**:
- Header with navigation
- User menu and search
- Skin CSS loading through skin system
- Standard footer and scripts

**Usage**:
```twig
{% extends "layouts/app.twig" %}
```

**Used by**:
- Dashboard pages
- Content pages (Quran, Hadith, etc.)
- Settings and profile pages
- Most application pages



### `auth.twig` - Authentication Layout

**Purpose**: Specialized layout for authentication pages.

**Features**:
- Clean, focused design for login/register
- Minimal navigation
- Authentication-specific styling

**Usage**:
```twig
{% extends "layouts/auth.twig" %}
```

**Used by**:
- Login pages
- Registration pages
- Password reset pages

## Layout Hierarchy

```
layouts/
├── app.twig          # General layout (default)
├── auth.twig         # Authentication specific
└── [future layouts]  # Page-specific layouts as needed
```

## Creating Page-Specific Layouts

When a page needs custom styling or layout, create a new `PAGENAME.twig` file:

### Example: Settings Page Layout

```twig
<!-- layouts/settings.twig -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Settings - IslamWiki{% endblock %}</title>
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Settings-specific CSS -->
    <link rel="stylesheet" href="/skins/Bismillah/css/settings.css">
    
    <!-- Custom styles -->
    {% block styles %}{% endblock %}
</head>
<body>
    <!-- Settings-specific layout -->
    <div class="settings-container">
        {% block content %}{% endblock %}
    </div>
    
    {% block scripts %}{% endblock %}
</body>
</html>
```

### Usage in Settings Page

```twig
<!-- views/settings/index.twig -->
{% extends "layouts/settings.twig" %}

{% block title %}Settings - IslamWiki{% endblock %}

{% block content %}
    <!-- Settings page content -->
{% endblock %}
```

## CSS Loading Strategy

### General Pages (`app.twig`)
- **Safa CSS Framework**: `<link rel="stylesheet" href="/css/safa.css">` (includes ZamZam.js utilities)
- **Skin CSS**: `<link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">`
- **ZamZam.js**: `<script defer src="/js/zamzam.js"></script>`
- Consistent styling across all general pages
- Framework utilities and skin styling properly separated

### Page-Specific Layouts
- **Safa CSS Framework**: `<link rel="stylesheet" href="/css/safa.css">` (includes ZamZam.js utilities)
- **Skin CSS**: `<link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">`
- Custom styling for specific page types
- Can override skin system for special cases

## Best Practices

1. **Use `app.twig` by default** for most pages
2. **Create page-specific layouts** only when custom styling is needed
3. **Keep layouts focused** - each layout should serve a specific purpose
4. **Maintain consistency** - page-specific layouts should still feel part of the application
5. **Document custom layouts** - explain why a custom layout was needed

## Migration Guide

### From Old System to New Layout Architecture

**Before**:
```twig
{% extends "layouts/app.twig" %}
```

**After** (same for most pages):
```twig
{% extends "layouts/app.twig" %}
```

**After** (for home page):
```twig
{% extends "layouts/index.twig" %}
```

**After** (for auth pages):
```twig
{% extends "layouts/auth.twig" %}
```

## Future Extensions

The layout system is designed to be extensible:

- **New page types** can get their own layouts
- **Custom skins** can have page-specific variations
- **A/B testing** can use different layouts
- **Mobile-specific** layouts can be added

## File Structure

```
resources/views/layouts/
├── app.twig          # General layout
├── index.twig        # Home page layout
├── auth.twig         # Auth pages layout
└── [future].twig     # Page-specific layouts
``` 
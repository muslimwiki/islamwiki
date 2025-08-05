# Static Data System

## Overview

The Static Data System provides a centralized way to manage global static data and skin-specific components across different skins. This eliminates code duplication and provides true skin flexibility while maintaining consistent structure.

## Architecture

### Core Components

1. **StaticDataManager** (`src/Core/Skin/StaticDataManager.php`)
   - Manages global static data
   - Handles skin-specific component data
   - Provides centralized data access

2. **StaticDataServiceProvider** (`src/Providers/StaticDataServiceProvider.php`)
   - Registers the StaticDataManager with the container
   - Provides helper functions for data access
   - Sets up global variables for templates

3. **Base Layout** (`resources/views/layouts/base.twig`)
   - Uses the static data system
   - Dynamically loads skin-specific components
   - Provides fallback components

## Global Static Data

The system provides the following global data:

### Site Information
```php
$site = [
    'name' => 'IslamWiki',
    'tagline' => 'Your comprehensive source for Islamic knowledge and community',
    'version' => '0.0.44',
    'url' => 'https://local.islam.wiki',
    'email' => 'contact@islam.wiki',
];
```

### Navigation
```php
$navigation = [
    'main' => [
        ['url' => '/', 'label' => 'Home', 'icon' => '🏠'],
        ['url' => '/pages', 'label' => 'Browse', 'icon' => '📚'],
        // ... more navigation items
    ],
    'secondary' => [
        ['url' => '/search', 'label' => 'Search', 'icon' => '🔍'],
        // ... secondary navigation
    ],
    'user' => [
        ['url' => '/dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
        // ... user menu items
    ],
];
```

### Footer Data
```php
$footer = [
    'sections' => [
        'main' => [
            'title' => 'IslamWiki',
            'description' => 'Your comprehensive source...',
            'links' => [
                ['url' => '/about', 'label' => 'About Us'],
                // ... more links
            ],
        ],
        // ... more sections
    ],
    'bottom' => [
        'copyright' => '© 2025 IslamWiki. All rights reserved.',
        'license' => 'Licensed under AGPL-3.0-only',
        'links' => [
            ['url' => '/sitemap', 'label' => 'Sitemap'],
            // ... more links
        ],
    ],
];
```

### Features Configuration
```php
$features = [
    'search' => [
        'enabled' => true,
        'placeholder' => 'Search Islamic knowledge...',
        'action' => '/iqra-search',
    ],
    'user_menu' => [
        'enabled' => true,
        'dropdown' => true,
    ],
    'breadcrumbs' => [
        'enabled' => true,
    ],
    'pagination' => [
        'enabled' => true,
        'per_page' => 20,
    ],
];
```

## Skin-Specific Components

Each skin can define its own components:

### Component Structure
```php
$components = [
    'header' => [
        'template' => 'skins/Muslim/components/header.twig',
        'data' => [
            'logo' => ['icon' => '🕌', 'text' => 'IslamWiki', 'url' => '/'],
            'search' => ['enabled' => true, 'placeholder' => '...'],
            'navigation' => $navigation['main'],
            'user_menu' => ['enabled' => true, 'dropdown' => true],
        ],
    ],
    'footer' => [
        'template' => 'skins/Muslim/components/footer.twig',
        'data' => $footer,
    ],
    'sidebar' => [
        'template' => 'skins/Muslim/components/sidebar.twig',
        'data' => [
            'navigation' => $navigation['secondary'],
            'quick_links' => [...],
        ],
    ],
    'breadcrumbs' => [
        'template' => 'skins/Muslim/components/breadcrumbs.twig',
        'data' => ['enabled' => true, 'separator' => '>'],
    ],
    'pagination' => [
        'template' => 'skins/Muslim/components/pagination.twig',
        'data' => ['enabled' => true, 'per_page' => 20],
    ],
];
```

## Usage

### In Controllers

```php
// Get static data manager
$staticDataManager = $this->container->get('static.data');

// Get all static data
$staticData = $staticDataManager->getStaticData();

// Get specific component
$headerComponent = $staticDataManager->getComponent('header');

// Check if feature is enabled
if ($staticDataManager->isFeatureEnabled('search')) {
    // Show search functionality
}
```

### In Templates

```twig
{# Access global data #}
<h1>{{ site_info.name }}</h1>
<p>{{ site_info.tagline }}</p>

{# Access navigation #}
{% for item in navigation.main %}
    <a href="{{ item.url }}">{{ item.icon }} {{ item.label }}</a>
{% endfor %}

{# Access components #}
{% if components.header %}
    {% include components.header.template with components.header.data %}
{% endif %}

{# Check features #}
{% if features.search.enabled %}
    {# Show search form #}
{% endif %}
```

### Helper Functions

The system provides helper functions:

```php
// Get static data
$data = static_data('navigation.main');

// Get navigation
$nav = get_navigation('main');

// Get site info
$siteName = get_site_info('name');

// Get footer
$footer = get_footer();

// Check feature
if (is_feature_enabled('search')) {
    // Feature is enabled
}

// Get component
$header = get_component('header');

// Get social links
$social = get_social_links();
```

## Creating Skin Components

### 1. Create Component Template

Create a template in your skin's components directory:

```twig
{# skins/YourSkin/components/header.twig #}
<header class="your-skin-header">
    <div class="header-content">
        <a href="{{ logo.url }}" class="logo">
            <span class="logo-icon">{{ logo.icon }}</span>
            <span class="logo-text">{{ logo.text }}</span>
        </a>
        
        <nav class="main-nav">
            {% for item in navigation %}
                <a href="{{ item.url }}" class="nav-link">
                    {{ item.icon }} {{ item.label }}
                </a>
            {% endfor %}
        </nav>
    </div>
</header>
```

### 2. Update StaticDataManager

Add your skin to the `getSkinLogo()` method:

```php
private function getSkinLogo(string $skinName): string
{
    $logos = [
        'Muslim' => '🕌',
        'Bismillah' => '📖',
        'YourSkin' => '🎨', // Add your skin
        'default' => '🏠',
    ];
    
    return $logos[$skinName] ?? $logos['default'];
}
```

### 3. Component Data

The component data is automatically provided by the StaticDataManager. You can access:

- `logo`: Logo information (icon, text, url)
- `navigation`: Navigation items
- `search`: Search configuration
- `user_menu`: User menu configuration
- Any other data passed to the component

## Benefits

1. **Centralized Data Management**: All static data is managed in one place
2. **Skin Flexibility**: Each skin can have its own components while sharing data
3. **No Code Duplication**: Navigation, footer, and other elements are defined once
4. **Easy Maintenance**: Changes to navigation or site info only need to be made in one place
5. **Consistent Structure**: All skins use the same data structure
6. **Dynamic Components**: Components can be enabled/disabled per skin
7. **Fallback Support**: Default components are provided if skin-specific ones don't exist

## Migration Guide

### From Old System

1. **Update Layouts**: Change from `layouts/app.twig` to `layouts/base.twig`
2. **Update Templates**: Use the new helper functions and global variables
3. **Create Components**: Move skin-specific layout code to component templates
4. **Update Controllers**: Use `renderWithSkin()` and pass static data

### Example Migration

**Old Template:**
```twig
{% extends 'layouts/app.twig' %}
<nav>
    <a href="/">Home</a>
    <a href="/pages">Pages</a>
</nav>
```

**New Template:**
```twig
{% extends 'layouts/base.twig' %}
{# Navigation is handled by the header component #}
{% block content %}
    <!-- Your page content here -->
{% endblock %}
```

## Testing

Use the test page at `/debug/debug-static-data-test.php` to verify the system is working correctly.

## Future Enhancements

1. **Database Integration**: Store static data in database for admin editing
2. **Caching**: Implement caching for static data
3. **Dynamic Updates**: Allow real-time updates to static data
4. **Component Library**: Create a library of reusable components
5. **Skin Marketplace**: Allow third-party skins to use the system 
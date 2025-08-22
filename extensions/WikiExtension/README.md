# WikiExtension

## 🎯 **Overview**

WikiExtension is a comprehensive wiki system for IslamWiki that provides unified content management, collaborative editing, and knowledge organization. It consolidates all wiki functionality into a single, extensible extension.

## 🏗️ **Features**

### **Core Wiki Functionality**
- **Page Management**: Create, edit, and manage wiki pages
- **Categories**: Organize content with flexible categorization
- **Search**: Full-text search across all wiki content
- **History**: Complete version history and revision tracking
- **Collaborative Editing**: Real-time collaborative editing
- **Permissions**: Role-based access control for pages

### **Content Organization**
- **Unified System**: Single content management system
- **Islamic Categories**: Pre-configured Islamic content categories
- **Featured Pages**: Highlight important content
- **Recent Updates**: Track latest changes
- **Page Templates**: Reusable content structures

### **User Experience**
- **Modern Interface**: Clean, responsive design
- **Search Suggestions**: Intelligent search with suggestions
- **Category Navigation**: Easy content discovery
- **Mobile Optimized**: Responsive design for all devices
- **Islamic Aesthetics**: Beautiful Islamic-themed design

## 📁 **Structure**

```
extensions/WikiExtension/
├── 📄 WikiExtension.php          # Main extension class
├── 📄 extension.json             # Extension metadata
├── 📄 README.md                  # This documentation
├── 📁 assets/                    # Frontend assets
│   ├── 📁 css/                   # Stylesheets
│   │   └── 📄 wiki.css          # Main wiki styles
│   └── 📁 js/                    # JavaScript
│       └── 📄 wiki.js           # Main wiki functionality
└── 📁 templates/                 # Twig templates
    └── 📄 index.twig            # Wiki homepage
```

## 🔧 **Installation**

### **1. Extension Registration**
The WikiExtension is automatically loaded by the IslamWiki extension system.

### **2. Route Configuration**
Routes are configured in the main routing system:

```php
// Wiki routes
$app->get('/wiki', [$wikiController, 'index']);
$app->get('/wiki/{slug}', [$wikiController, 'show']);
$app->get('/wiki/{slug}/edit', [$wikiController, 'edit']);
$app->post('/wiki/{slug}', [$wikiController, 'update']);
$app->get('/wiki/category/{category}', [$wikiController, 'category']);
$app->get('/wiki/search', [$wikiController, 'search']);
$app->get('/wiki/history', [$wikiController, 'history']);
```

### **3. Template Integration**
Templates extend the main app layout:

```twig
{% extends "layouts/app.twig" %}
{% block title %}Wiki - IslamWiki{% endblock %}
{% block content %}
    <!-- Wiki content here -->
{% endblock %}
```

## 🎨 **Styling**

### **CSS Classes**
- **`.wiki-container`**: Main wiki container
- **`.wiki-header`**: Wiki page header
- **`.wiki-content`**: Main content area
- **`.wiki-page`**: Individual page content
- **`.wiki-actions`**: Action buttons
- **`.wiki-categories`**: Category navigation
- **`.wiki-search`**: Search functionality
- **`.wiki-results`**: Search results

### **Button Styles**
- **`.wiki-btn`**: Base button class
- **`.wiki-btn-primary`**: Primary action buttons
- **`.wiki-btn-secondary`**: Secondary action buttons
- **`.wiki-btn-outline`**: Outline style buttons

## 🚀 **Usage**

### **Basic Wiki Page**
```twig
<div class="wiki-container">
    <div class="wiki-header">
        <h1>{{ page.title }}</h1>
        <p>{{ page.description }}</p>
    </div>
    
    <div class="wiki-content">
        <div class="wiki-page">
            {{ page.content|raw }}
        </div>
        
        <div class="wiki-actions">
            <a href="/wiki/{{ page.slug }}/edit" class="wiki-btn wiki-btn-primary">
                <span>✏️</span>
                Edit Page
            </a>
        </div>
    </div>
</div>
```

### **Category Navigation**
```twig
<div class="wiki-categories">
    <a href="/wiki/category/islamic-history" class="wiki-category">Islamic History</a>
    <a href="/wiki/category/quran-studies" class="wiki-category">Quran Studies</a>
    <a href="/wiki/category/hadith-sciences" class="wiki-category">Hadith Sciences</a>
</div>
```

### **Search Functionality**
```twig
<div class="wiki-search">
    <input type="text" placeholder="Search wiki pages..." class="search-input">
</div>

<div class="wiki-results">
    <!-- Search results will be populated here -->
</div>
```

## 🔌 **Hooks**

The WikiExtension provides several hooks for integration:

- **`WikiPageCreate`**: Fired when a new wiki page is created
- **`WikiPageUpdate`**: Fired when a wiki page is updated
- **`WikiPageDelete`**: Fired when a wiki page is deleted

### **Hook Usage**
```php
// Register a hook
$this->hookManager->register('WikiPageCreate', function($data) {
    // Handle page creation
    error_log('New wiki page created: ' . $data['title']);
});
```

## 📱 **Responsive Design**

The WikiExtension is fully responsive and mobile-optimized:

- **Mobile-First**: Designed for mobile devices first
- **Flexible Grids**: Responsive grid layouts
- **Touch-Friendly**: Optimized for touch interfaces
- **Adaptive Typography**: Scales appropriately for all screen sizes

## 🎨 **Customization**

### **Theme Customization**
CSS variables can be customized for different themes:

```css
:root {
    --wiki-primary-color: #4F46E5;
    --wiki-secondary-color: #7C3AED;
    --wiki-text-color: #1F2937;
    --wiki-background-color: #F9FAFB;
}
```

### **Template Overrides**
Templates can be overridden by creating custom versions in your skin:

```
skins/YourSkin/templates/wiki/
├── 📄 index.twig
├── 📄 show.twig
└── 📄 edit.twig
```

## 🧪 **Testing**

### **Manual Testing**
1. Navigate to `/wiki` to see the wiki homepage
2. Test search functionality
3. Test category navigation
4. Verify responsive design on mobile devices

### **Browser Testing**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## 📚 **Dependencies**

- **IslamWiki Core**: >= 0.0.2.0
- **Twig**: Template engine
- **Modern CSS**: CSS Grid, Flexbox, CSS Variables
- **Vanilla JavaScript**: No external JS dependencies

## 🔮 **Future Enhancements**

### **Planned Features**
- **Advanced Search**: Full-text search with filters
- **Page Templates**: Pre-built page structures
- **Collaborative Editing**: Real-time co-editing
- **Page Comments**: Discussion system
- **Page Watchlist**: Follow page changes
- **Advanced Permissions**: Granular access control

### **Integration Plans**
- **QuranExtension**: Quran content integration
- **HadithExtension**: Hadith content integration
- **SalahExtension**: Prayer time integration
- **CalendarExtension**: Islamic calendar integration

## 📞 **Support**

For support and questions about the WikiExtension:

- **Documentation**: Check this README and the main IslamWiki documentation
- **Issues**: Report bugs and feature requests through the issue tracker
- **Community**: Join the IslamWiki community discussions

## 📄 **License**

This extension is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

---

**Version**: 0.0.2.1  
**Author**: IslamWiki Development Team  
**Status**: Active Development  
**Last Updated**: 2025-01-20 
# Bismillah Skin - Condensed Navigation System

## Version: 0.0.2.2

### Overview
The Bismillah Skin is a beautiful, Islamic-themed skin for IslamWiki that features a condensed left navigation system. This design maximizes content space while providing easy access to all navigation features through a narrow 60px sidebar.

### Key Features

#### 🎨 **Condensed Left Sidebar (60px width)**
- **Fixed positioning** for consistent navigation
- **Islamic color scheme** with green accents
- **Icon-based navigation** for space efficiency
- **Hover effects** and smooth transitions

#### 🔍 **Interactive Search System**
- **Global search** accessible via Ctrl+K
- **Beautiful bismillah display** in search overlay
- **Namespace filtering** capabilities
- **Keyboard shortcuts** for power users

#### 📱 **Hamburger Menu System**
- **Main navigation** accessible via Ctrl+M
- **Organized sections** for different content types
- **Islamic content** quick access
- **Responsive design** for all devices

#### ⚙️ **Display Preferences**
- **Text size** adjustment (Small to Extra Large)
- **Color themes** (Light, Dark, Auto)
- **Content width** options (Standard, Wide, Full)
- **Local storage** for user preferences

#### 🔔 **Notifications Panel**
- **Real-time alerts** and updates
- **Mark all as read** functionality
- **Notification preferences** access
- **Badge system** for unread count

#### 🧭 **Navigation Toggle**
- **Context-aware** navigation options
- **Quick access** to related content
- **Dynamic menu** based on current page

#### 👤 **User Profile System**
- **User information** display
- **Profile actions** and settings
- **Logout functionality**
- **Keyboard shortcut** (Alt+P)

### Technical Implementation

#### File Structure
```
skins/Bismillah/
├── css/
│   ├── condensed-navigation.css    # Core sidebar styles
│   ├── bismillah-main.css         # Main skin styles
│   └── bismillah.css              # Legacy skin styles
├── js/
│   ├── condensed-navigation.js    # Core navigation logic
│   ├── search-overlay.js          # Search functionality
│   ├── settings-menu.js           # Display preferences
│   ├── notifications.js           # Notifications system
│   └── profile-menu.js            # User profile management
└── templates/
    └── condensed-layout.twig      # Alternative layout template
```

#### CSS Architecture
- **CSS Variables** for consistent theming
- **Modular structure** for easy customization
- **Responsive design** with mobile-first approach
- **Islamic aesthetics** with proper Arabic font support

#### JavaScript Architecture
- **ES6 Classes** for modular code organization
- **Event-driven** architecture for interactivity
- **Local storage** integration for user preferences
- **Keyboard shortcuts** for enhanced UX

### Keyboard Shortcuts

| Shortcut | Action |
|----------|---------|
| `Ctrl+K` | Open search overlay |
| `Ctrl+M` | Open main menu |
| `Escape` | Close any open overlay |
| `Alt+P` | Open profile menu |

### Browser Support
- **Modern browsers** (Chrome 80+, Firefox 75+, Safari 13+)
- **ES6 support** required for JavaScript functionality
- **CSS Grid and Flexbox** for layout
- **Font Awesome 6.4.0** for icons

### Installation & Setup

#### 1. File Placement
Ensure all skin files are placed in the `skins/Bismillah/` directory for proper serving.

#### 2. CSS Integration
Include the CSS files in your main layout:
```html
<link rel="stylesheet" href="/skins/Bismillah/css/condensed-navigation.css">
<link rel="stylesheet" href="/skins/Bismillah/css/bismillah-main.css">
```

#### 3. JavaScript Integration
Include the JavaScript files before the closing body tag:
```html
<script src="/skins/Bismillah/js/condensed-navigation.js"></script>
<script src="/skins/Bismillah/js/search-overlay.js"></script>
<script src="/skins/Bismillah/js/settings-menu.js"></script>
<script src="/skins/Bismillah/js/notifications.js"></script>
<script src="/skins/Bismillah/js/profile-menu.js"></script>
```

#### 4. HTML Structure
Add the condensed sidebar structure to your layout:
```html
<div class="condensed-sidebar">
    <!-- Sidebar content -->
</div>
<div class="main-content-wrapper">
    <!-- Main content -->
</div>
```

### Customization

#### Color Scheme
Modify CSS variables in `condensed-navigation.css`:
```css
:root {
    --sidebar-bg: #f8f9fa;
    --icon-color: #6c757d;
    --icon-hover-bg: #e9ecef;
    --logo-bg: #2d5016;
}
```

#### Icon Customization
Replace Font Awesome icons with custom ones:
```html
<div class="sidebar-icon">
    <i class="your-custom-icon"></i>
</div>
```

#### Layout Adjustments
Modify sidebar width and positioning:
```css
:root {
    --sidebar-width: 60px; /* Adjust as needed */
}
```

### Testing

#### Test Page
Use the provided test page: `/test-navigation.html`

#### Manual Testing Checklist
- [ ] Sidebar icons display correctly
- [ ] Hover effects work smoothly
- [ ] Keyboard shortcuts function
- [ ] Menu overlays open/close properly
- [ ] Responsive design on mobile
- [ ] User preferences save correctly

### Performance Considerations

#### Asset Optimization
- **CSS minification** for production
- **JavaScript bundling** for reduced HTTP requests
- **Image optimization** for icons and logos
- **Font loading** optimization

#### Caching Strategy
- **Version parameters** for cache busting
- **Local storage** for user preferences
- **CDN usage** for external resources

### Future Enhancements

#### Planned Features
- **Dark mode** toggle
- **Customizable** sidebar icons
- **Advanced search** filters
- **Notification** sound effects
- **Accessibility** improvements

#### Integration Opportunities
- **User authentication** system
- **Content management** system
- **Analytics** integration
- **Multi-language** support

### Troubleshooting

#### Common Issues

**Icons not displaying:**
- Check Font Awesome CDN connection
- Verify CSS file paths
- Clear browser cache

**JavaScript errors:**
- Check browser console for errors
- Verify file paths and permissions
- Ensure ES6 support

**Layout issues:**
- Check CSS file loading
- Verify HTML structure
- Test responsive breakpoints

#### Debug Mode
Enable debug logging in JavaScript:
```javascript
// Add to any JS file
console.log('Debug: Component initialized');
```

### Support & Contributing

#### Development Team
- **Lead Developer:** IslamWiki Development Team
- **Version:** 0.0.2.2
- **Last Updated:** Current

#### Contributing Guidelines
1. Follow existing code style
2. Test thoroughly before submitting
3. Update documentation for new features
4. Maintain backward compatibility

#### Bug Reports
Please report issues with:
- Browser version and OS
- Steps to reproduce
- Console error messages
- Screenshots if applicable

---

**Bismillah Skin** - Bringing Islamic aesthetics to modern web design.

*"بِسْمِ اللهِ الرَّحْمَنِ الرَّحِيم"* 
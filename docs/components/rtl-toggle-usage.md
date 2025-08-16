# Global RTL Toggle Component Usage

## Overview

The Global RTL Toggle Component (`components/rtl-toggle.twig`) provides consistent right-to-left (RTL) language switching functionality across all skins and layouts in IslamWiki. This component is designed to be skin-agnostic and will work with any theme.

## Features

- **Global Functionality**: Works across all skins and layouts
- **Persistent Storage**: Remembers language preference in localStorage
- **Event System**: Dispatches custom events for skins to listen to
- **Responsive Design**: Adapts to different screen sizes
- **Accessibility**: Proper ARIA labels and keyboard support
- **Synchronization**: Keeps multiple RTL toggles in sync

## Basic Usage

### 1. Include the Component

Simply include the component in any Twig template:

```twig
{% include 'components/rtl-toggle.twig' %}
```

### 2. Example in Header

```twig
<div class="header-actions">
    {% include 'components/rtl-toggle.twig' %}
    <a href="/contribute" class="contribute-btn">Contribute</a>
</div>
```

### 3. Example in Navigation

```twig
<nav class="main-navigation">
    <ul class="nav-menu">
        <li><a href="/">Home</a></li>
        <li><a href="/quran">Quran</a></li>
        <li><a href="/hadith">Hadith</a></li>
    </ul>
    <div class="nav-actions">
        {% include 'components/rtl-toggle.twig' %}
    </div>
</nav>
```

## Styling

The component comes with built-in styling that works with any skin. However, you can customize it using CSS variables or by overriding specific classes.

### CSS Customization

```css
/* Customize the RTL toggle button */
.rtl-toggle-btn {
    background: var(--your-primary-color);
    border-color: var(--your-border-color);
    color: var(--your-text-color);
}

/* Customize hover states */
.rtl-toggle-btn:hover {
    background: var(--your-hover-color);
    transform: var(--your-hover-transform);
}
```

### RTL-Aware Styling

The component automatically handles RTL layout adjustments:

```css
/* Your skin's RTL styles will automatically apply */
[dir="rtl"] .your-component {
    text-align: right;
    flex-direction: row-reverse;
}
```

## JavaScript Integration

### Listening for Language Changes

Skins can listen for language change events:

```javascript
document.addEventListener('languageChanged', function(event) {
    const { language, direction } = event.detail;
    
    if (direction === 'rtl') {
        // Apply RTL-specific logic
        document.body.classList.add('rtl-mode');
    } else {
        // Apply LTR-specific logic
        document.body.classList.remove('rtl-mode');
    }
});
```

### Programmatic Language Control

```javascript
// Set language programmatically
window.setGlobalLanguage('ar'); // Arabic/RTL
window.setGlobalLanguage('en'); // English/LTR

// Toggle language
window.toggleGlobalLanguage();
```

## HTML Structure

The component generates this HTML structure:

```html
<div class="global-rtl-toggle" id="globalRtlToggle" data-lang="en">
    <button class="rtl-toggle-btn" onclick="toggleGlobalLanguage()" title="Toggle RTL/LTR Language">
        <span class="rtl-icon">🌐</span>
        <span class="rtl-text">العربية</span>
    </button>
</div>
```

## CSS Classes

### Main Classes

- `.global-rtl-toggle` - Main container
- `.rtl-toggle-btn` - The toggle button
- `.rtl-icon` - Language icon
- `.rtl-text` - Language text

### State Classes

- `[data-lang="en"]` - English mode
- `[data-lang="ar"]` - Arabic mode
- `.rtl` - RTL body class
- `.ltr` - LTR body class

## Responsive Behavior

The component automatically adapts to different screen sizes:

- **Desktop**: Full button with icon and text
- **Tablet**: Compact button with smaller text
- **Mobile**: Icon-only button for space efficiency

## Browser Support

- **Modern Browsers**: Full support with all features
- **Legacy Browsers**: Graceful degradation
- **Mobile Browsers**: Touch-optimized interactions

## Accessibility

- **Keyboard Navigation**: Tab and Enter key support
- **Screen Readers**: Proper ARIA labels and descriptions
- **High Contrast**: Works with high contrast themes
- **Focus Indicators**: Clear focus states

## Integration Examples

### 1. Minimal Integration

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Skin</title>
    <link rel="stylesheet" href="/skins/MySkin/style.css">
</head>
<body>
    <header>
        <h1>My Skin</h1>
        {% include 'components/rtl-toggle.twig' %}
    </header>
    
    <main>
        {% block content %}{% endblock %}
    </main>
</body>
</html>
```

### 2. Advanced Integration

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Advanced Skin</title>
    <link rel="stylesheet" href="/skins/AdvancedSkin/style.css">
</head>
<body>
    <header class="skin-header">
        <div class="header-content">
            <div class="header-left">
                <h1>Advanced Skin</h1>
            </div>
            <div class="header-right">
                <nav class="header-nav">
                    <a href="/">Home</a>
                    <a href="/about">About</a>
                </nav>
                {% include 'components/rtl-toggle.twig' %}
                <div class="user-menu">
                    <a href="/login">Login</a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="skin-main">
        {% block content %}{% endblock %}
    </main>
    
    <script>
        // Listen for language changes
        document.addEventListener('languageChanged', function(event) {
            const { language, direction } = event.detail;
            console.log(`Language changed to ${language} (${direction})`);
            
            // Apply skin-specific RTL adjustments
            if (direction === 'rtl') {
                document.body.classList.add('skin-rtl');
            } else {
                document.body.classList.remove('skin-rtl');
            }
        });
    </script>
</body>
</html>
```

## Troubleshooting

### Common Issues

1. **Component Not Loading**
   - Ensure the path to `components/rtl-toggle.twig` is correct
   - Check that Twig includes are enabled

2. **Styling Conflicts**
   - The component uses specific CSS classes to avoid conflicts
   - Use CSS specificity or `!important` if needed

3. **JavaScript Errors**
   - Ensure the component script loads after DOM is ready
   - Check browser console for error messages

### Debug Mode

Enable debug logging:

```javascript
// Add this before including the component
window.ISLAMWIKI_DEBUG = true;
```

## Best Practices

1. **Always Include**: Include the RTL toggle in every layout
2. **Consistent Placement**: Place it in a consistent location across pages
3. **Accessibility**: Don't hide the toggle behind complex interactions
4. **Testing**: Test RTL functionality with Arabic content
5. **Performance**: The component is lightweight and won't impact page load

## Migration from Skin-Specific RTL

If you're migrating from a skin-specific RTL implementation:

1. **Remove Old Code**: Delete skin-specific RTL toggle code
2. **Include Component**: Add `{% include 'components/rtl-toggle.twig' %}`
3. **Update Event Listeners**: Use the new `languageChanged` event
4. **Test Functionality**: Verify RTL switching works correctly
5. **Clean Up**: Remove unused CSS and JavaScript

## Support

For issues or questions about the Global RTL Toggle Component:

- Check this documentation
- Review the component source code
- Test with different skins and layouts
- Report bugs through the project issue tracker 
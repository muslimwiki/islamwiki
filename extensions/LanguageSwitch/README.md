# LanguageSwitch Extension

A comprehensive language switching system for IslamWiki with support for Arabic (RTL) and English (LTR) languages, including **full Arabic text translation** of the interface. This extension provides seamless switching between English and Arabic with complete text translation, not just layout changes.

## 🌟 **New Feature: Arabic Text Translation**

**The LanguageSwitch extension now includes a comprehensive Arabic translation plugin that translates the entire interface to Arabic, not just changes the text direction!**

### What Gets Translated
- **Navigation Items**: Home, Quran, Hadith, Sciences, Community, etc.
- **Dropdown Menus**: All submenu items and options
- **Page Content**: Headings, buttons, labels, and text
- **Form Elements**: Input placeholders, button text, labels
- **Dynamic Content**: Content added via JavaScript
- **Error Messages**: System notifications and alerts
- **Status Messages**: Success, warning, and info messages

## Features

- **Language Switching**: Seamless switching between English and Arabic
- **Full Text Translation**: Complete Arabic translation of interface text
- **RTL Support**: Full right-to-left layout support for Arabic
- **Persistent Storage**: Language preference saved in localStorage
- **Beautiful UI**: Islamic-themed design with smooth animations
- **Accessibility**: Full keyboard navigation and screen reader support
- **Mobile Responsive**: Optimized for all device sizes
- **Extensible**: Easy to add more languages
- **Dynamic Translation**: Automatically translates new content as it's added

## Supported Languages

### Current Languages
- **English (en)**: Left-to-right (LTR) layout with English text
- **Arabic (ar)**: Right-to-left (RTL) layout with **full Arabic translation**

### Future Languages
The extension is designed to easily support additional languages including:
- Urdu (ur)
- Turkish (tr)
- Indonesian (id)
- Malay (ms)
- Persian (fa)
- Hebrew (he)

## Installation

1. Place the `LanguageSwitch` folder in your `extensions/` directory
2. The extension will be automatically loaded by the IslamWiki extension system
3. The language switch component will appear in the header navigation
4. **Arabic translations are automatically loaded when needed**

## Usage

### Basic Language Switching
The language switch appears as a button in the header with:
- Current language flag and name
- Dropdown arrow indicator
- Hover to reveal language options

### Language Options
Click the language switch button to see:
- Available languages with flags
- Native language names
- RTL indicator for right-to-left languages
- Current language highlighted

### What Happens When You Switch to Arabic
1. **Text Direction**: Page switches to right-to-left (RTL) layout
2. **Interface Translation**: All navigation items, buttons, and text are translated to Arabic
3. **Layout Changes**: Navigation menus, forms, and content automatically adjust for RTL
4. **Font Support**: Arabic text uses proper Arabic typography
5. **Persistent**: Your choice is remembered across page visits

### Keyboard Navigation
- **Tab**: Navigate to language switch
- **Enter/Space**: Open/close dropdown
- **Arrow Keys**: Navigate language options
- **Escape**: Close dropdown

## Configuration

The extension can be configured in `extension.json`:

```json
{
    "config": {
        "defaultLanguage": "en",
        "supportedLanguages": ["en", "ar"],
        "enableLanguageDetection": true,
        "enableLanguagePersistence": true,
        "enableRTLSupport": true,
        "enableLanguageMenu": true,
        "enableLanguageIcons": true,
        "enableArabicTranslations": true
    }
}
```

## Integration

### Including in Templates
Replace the old RTL toggle with the new language switch:

```twig
<!-- Old RTL toggle -->
{% include 'components/rtl-toggle.twig' %}

<!-- New language switch with translations -->
{% include 'extensions/LanguageSwitch/language-switch.twig' %}
```

### Custom Events
The extension dispatches custom events for integration:

```javascript
document.addEventListener('languageChanged', (e) => {
    console.log('Language changed to:', e.detail.language);
    console.log('Direction:', e.detail.direction);
    console.log('Is RTL:', e.detail.isRTL);
    console.log('Translations applied:', e.detail.translationsApplied);
});
```

## Arabic Translation System

### How It Works
1. **Text Detection**: The system scans the page for translatable text
2. **Translation Mapping**: English text is mapped to Arabic equivalents
3. **Dynamic Application**: Translations are applied to all matching elements
4. **RTL Layout**: Page direction and layout automatically adjust
5. **Content Monitoring**: New content is automatically translated as it's added

### Translation Coverage
The Arabic translation plugin includes **200+ translations** covering:

- **Navigation & Menus**: Complete menu system translation
- **Islamic Terms**: Quran, Hadith, Salah, Islamic sciences
- **User Interface**: Buttons, forms, messages, notifications
- **Common Actions**: Save, edit, delete, search, filter
- **Status Messages**: Success, error, warning, info
- **Form Elements**: Labels, placeholders, validation messages
- **Help & Support**: User guides, FAQ, contact information

### Adding New Translations
To add new Arabic translations:

```javascript
// Add to the ARABIC_TRANSLATIONS object
const ARABIC_TRANSLATIONS = {
    'New Text': 'النص الجديد',
    'Another Term': 'مصطلح آخر',
    // ... existing translations
};
```

## Styling

The extension includes comprehensive CSS with:
- Islamic theme colors and gradients
- Smooth transitions and animations
- RTL layout support
- Mobile responsive design
- High contrast mode support
- Reduced motion support

### CSS Variables
The extension uses CSS custom properties for theming:

```css
:root {
    --islamic-green: #2d5016;
    --islamic-gold: #d4af37;
    --islamic-dark-green: #1a3009;
    --islamic-cream: #f8f6f0;
    --islamic-white: #ffffff;
}
```

## JavaScript API

The extension provides a global `EnhancedLanguageSwitch` class:

```javascript
// Get current language
const currentLang = window.EnhancedLanguageSwitch.getCurrentLanguage();

// Check if current language is RTL
const isRTL = window.EnhancedLanguageSwitch.isCurrentLanguageRTL();

// Switch language programmatically
window.EnhancedLanguageSwitch.switchLanguage('ar');

// Access Arabic plugin directly
const arabicPlugin = window.EnhancedLanguageSwitch.arabicPlugin;
if (arabicPlugin) {
    arabicPlugin.activate(); // Force Arabic mode
    arabicPlugin.deactivate(); // Return to English
}
```

## Testing

### Demo Pages
- **Main Demo**: `extensions/LanguageSwitch/arabic-demo.html`
- **Test Page**: `extensions/LanguageSwitch/test.html`

### Testing Instructions
1. **Load the demo page** to see translations in action
2. **Switch to Arabic** and observe text translation
3. **Check RTL layout** for proper Arabic display
4. **Test persistence** by refreshing the page
5. **Verify translations** of navigation and content

## Adding New Languages

To add a new language:

1. **Update the extension configuration** in `extension.json`:
```json
"supportedLanguages": ["en", "ar", "ur", "tr"]
```

2. **Add language data** in `LanguageSwitch.php`:
```php
private function getLanguageName(string $code): string
{
    $names = [
        'en' => 'English',
        'ar' => 'Arabic',
        'ur' => 'Urdu',
        'tr' => 'Turkish'
    ];
    
    return $names[$code] ?? $code;
}
```

3. **Add RTL support** if needed:
```php
private function isLanguageRTL(string $code): bool
{
    $rtlLanguages = ['ar', 'ur', 'fa', 'he'];
    return in_array($code, $rtlLanguages);
}
```

4. **Create translation file** similar to `arabic-translations.js`

## Browser Support

- **Modern Browsers**: Full support (Chrome 60+, Firefox 55+, Safari 12+)
- **Legacy Browsers**: Graceful degradation with basic functionality
- **Mobile Browsers**: Full responsive support
- **Screen Readers**: Complete accessibility support

## Performance

- **Lightweight**: Minimal JavaScript footprint (~25KB total)
- **Efficient**: Uses event delegation for optimal performance
- **Caching**: Language preferences cached in localStorage
- **Lazy Loading**: Arabic translations loaded only when needed
- **Smart Translation**: Only translates text that has available translations

## Security

- **XSS Protection**: All user input properly sanitized
- **CSRF Protection**: No sensitive data transmitted
- **Content Security Policy**: Compatible with strict CSP settings
- **Input Validation**: Language codes validated against allowed list

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Translation Contributions
To contribute Arabic translations:
1. Review existing translations in `arabic-translations.js`
2. Add missing translations following the established format
3. Ensure proper Arabic grammar and Islamic terminology
4. Test translations in the demo pages

## License

This extension is part of IslamWiki and follows the same licensing terms.

## Support

For support and questions:
- Check the documentation
- Review the code comments
- Test with the demo pages
- Open an issue on the repository
- Contact the IslamWiki development team

## Changelog

### Version 0.0.2
- **NEW**: Complete Arabic text translation system
- **NEW**: 200+ Arabic translations for interface elements
- **NEW**: Dynamic content translation with MutationObserver
- **NEW**: Translation status indicators
- **IMPROVED**: Enhanced language switching with translation support
- **IMPROVED**: Better RTL layout handling
- **ADDED**: Demo pages for testing translations

### Version 0.0.1
- Initial release
- English and Arabic language support
- RTL layout support
- Beautiful Islamic-themed UI
- Full accessibility features
- Mobile responsive design 
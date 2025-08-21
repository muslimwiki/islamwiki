# WikiMarkupExtension

## 🎯 **Overview**

The WikiMarkupExtension provides MediaWiki-style syntax support for IslamWiki, enabling users to create rich, structured content using familiar wiki markup conventions. This extension enhances the existing markdown system with powerful wiki features while maintaining compatibility with Islamic content requirements.

## ✨ **Features**

### **Internal Links**
- **Basic Links**: `[[Page Name]]` - Creates links to other wiki pages
- **Display Text**: `[[Page Name|Display Text]]` - Links with custom display text
- **Smart URL Generation**: Automatically converts page names to URL-friendly slugs
- **CSS Classes**: Automatic CSS class assignment based on content type (Quran, Hadith, Scholar, etc.)

### **Templates**
- **Basic Templates**: `{{TemplateName}}` - Inserts template content
- **Parameter Support**: `{{TemplateName|param1|param2}}` - Templates with parameters
- **Named Parameters**: `{{TemplateName|key=value|another=value}}` - Named parameter support
- **Placeholder Rendering**: Currently shows template placeholders (Phase 2 will implement full template engine)

### **Headers**
- **Level 1**: `= Header =` → `<h1>Header</h1>`
- **Level 2**: `== Header ==` → `<h2>Header</h2>`
- **Level 3**: `=== Header ===` → `<h3>Header</h3>`

### **Lists**
- **Unordered Lists**: `* Item` → `<ul><li>Item</li></ul>`
- **Ordered Lists**: `# Item` → `<ol><li>Item</li></ol>`
- **Definition Lists**: `; Term : Definition` → `<dl><dt>Term</dt><dd>Definition</dd></dl>`

## 🚀 **Installation**

### **Automatic Installation**
The WikiMarkupExtension is automatically loaded by the IslamWiki extension system when placed in the `extensions/` directory.

### **Manual Verification**
1. Check that the extension directory exists: `extensions/WikiMarkupExtension/`
2. Verify the extension.json file is valid
3. Confirm the extension loads without errors
4. Test wiki markup parsing with sample content

## ⚙️ **Configuration**

### **Extension Configuration**
```json
{
    "config": {
        "enable_wiki_markup": true,
        "parse_internal_links": true,
        "parse_templates": true,
        "parse_headers": true,
        "parse_lists": true
    }
}
```

### **Configuration Options**
- **`enable_wiki_markup`**: Master switch for all wiki markup features
- **`parse_internal_links`**: Enable/disable internal link parsing
- **`parse_templates`**: Enable/disable template parsing
- **`parse_headers`**: Enable/disable header parsing
- **`parse_lists`**: Enable/disable list parsing

## 📝 **Usage Examples**

### **Creating Internal Links**
```markdown
# Islamic Sciences

This page covers various [[Islamic Sciences]] including:

* [[Fiqh]] - Islamic jurisprudence
* [[Aqeedah]] - Islamic creed and beliefs
* [[Tasawwuf]] - Islamic spirituality

For more information, see [[Islamic Education|Islamic Education System]].
```

### **Using Templates**
```markdown
# Quran Study Guide

{{QuranVerse|surah=2|ayah=255|translation=en}}

{{HadithCitation|collection=bukhari|book=1|hadith=1}}

{{ScholarProfile|name=ibn-taymiyyah}}
```

### **Structured Content**
```markdown
= Main Title =

== Section 1 ==

=== Subsection 1.1 ===

* First point
* Second point
* Third point

== Section 2 ==

# First ordered item
# Second ordered item
# Third ordered item

; Term : Definition
; Another Term : Another Definition
```

## 🔧 **Technical Details**

### **Processing Pipeline**
1. **Content Input**: Raw content with wiki markup
2. **Header Parsing**: Convert `=== Header ===` to HTML headers
3. **List Parsing**: Convert `*` and `#` to HTML lists
4. **Link Resolution**: Convert `[[Page]]` to HTML links
5. **Template Processing**: Convert `{{Template}}` to HTML placeholders
6. **HTML Output**: Final processed HTML ready for display

### **Performance Features**
- **Content Caching**: Parsed content is cached to avoid re-processing
- **Configurable Caching**: Cache TTL and enable/disable options
- **Memory Management**: Automatic cache cleanup and memory monitoring

### **Integration Points**
- **ContentParse Hook**: Processes content before markdown rendering
- **ContentPostRender Hook**: Finalizes HTML output after processing
- **EnhancedMarkdown Extension**: Works alongside existing markdown support

## 🧪 **Testing**

### **Test the Parser**
```bash
cd extensions/WikiMarkupExtension
php test_parser.php
```

### **Sample Test Content**
```php
$testContent = "
# Test Page

This is a test page with [[Internal Link]] and [[Another Page|Display Text]].

## Features

* List item 1
* List item 2
* List item 3

=== Subsection ===

{{Template|param1|param2}}

# Ordered list
# Second item
# Third item
";
```

## 🔮 **Future Enhancements (Phase 2)**

### **Template Engine**
- Full template system with parameter substitution
- Nested template support
- Template caching and optimization
- Template editor interface

### **Advanced Features**
- Category system integration
- Talk page functionality
- User contribution tracking
- Advanced search capabilities

## 📚 **API Reference**

### **WikiMarkupParser Class**
```php
class WikiMarkupParser
{
    public function parse(string $content): string
    public function postProcess(string $html): string
    public function clearCache(): void
    public function getCacheStats(): array
}
```

### **WikiMarkupExtension Class**
```php
class WikiMarkupExtension extends Extension
{
    public function onContentParse(string &$content, array $context = []): void
    public function onContentPostRender(string &$html, array $context = []): void
    public function getInfo(): array
}
```

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Extension Not Loading**
- Check that `extension.json` is valid JSON
- Verify the extension directory structure
- Check error logs for initialization errors

#### **Markup Not Parsing**
- Ensure `enable_wiki_markup` is set to `true`
- Check that specific parsing features are enabled
- Verify hook registration in logs

#### **Performance Issues**
- Check cache configuration
- Monitor memory usage with `getCacheStats()`
- Consider disabling unused parsing features

### **Debug Mode**
Enable debug logging to see detailed processing information:
```php
error_log('Wiki markup parsing completed: ' . strlen($originalContent) . ' -> ' . strlen($content) . ' chars');
```

## 📄 **License**

This extension is licensed under the AGPL-3.0 License.

## 👥 **Contributing**

1. Follow the existing code style and conventions
2. Add tests for new features
3. Update documentation for any changes
4. Ensure backward compatibility

## 🔗 **Related Extensions**

- **EnhancedMarkdown**: Works alongside this extension for markdown processing
- **QuranExtension**: Provides Quran-specific content and templates
- **HadithExtension**: Provides Hadith-specific content and templates

---

**Version**: 0.0.1.0  
**Author**: IslamWiki Development Team  
**Status**: Phase 1 Complete ✅ - Ready for Production 🚀


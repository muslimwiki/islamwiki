# WikiMarkupExtension - Comprehensive MediaWiki Markup Support

**Version**: 0.0.1.3  
**Status**: Enhanced with Comprehensive MediaWiki Markup & Edit Functionality 🚀  
**Last Updated**: 2025-01-20  

## 🎯 **Overview**

The WikiMarkupExtension provides **comprehensive MediaWiki-style markup support** for IslamWiki, including both **WikiMarkup** and **Markdown** languages. This extension enables users to create rich, formatted content using familiar MediaWiki syntax while providing a professional editing experience with live preview and auto-save functionality.

## 🌟 **Key Features**

### **✅ Dual Language Support**
- **WikiMarkup**: Full MediaWiki syntax support
- **Markdown**: Standard Markdown syntax support
- **Format Switching**: Easy switching between languages
- **Unified Parsing**: Single parser handles both formats

### **✅ Comprehensive MediaWiki Syntax**
- **Headers**: `= H1 =`, `== H2 ==`, `=== H3 ===`
- **Emphasis**: `'''bold''`, `''italic''`, `<del>strikethrough</del>`
- **Lists**: `* unordered`, `# ordered`, `; definition : term`
- **Links**: `[[Page]]`, `[[Page|Display Text]]`
- **Tables**: `{| |} |- | ||` syntax
- **Media**: `[[Image:file.jpg|Caption]]`
- **Categories**: `[[Category:Name]]`
- **Math**: `<math>formula</math>`
- **Code**: `<source lang="php">code</source>`
- **Templates**: `{{Template|param1|param2}}`

### **✅ Professional Editing Experience**
- **Rich Text Editor**: Toolbar with formatting buttons
- **Live Preview**: Real-time content preview
- **Auto-save**: Automatic content saving
- **Syntax Help**: Built-in syntax reference
- **Format Validation**: Content format checking
- **Edit History**: Track content changes

### **✅ Islamic Content Templates**
- **Quran**: `{{quran|surah|ayah|translation|tafsir}}`
- **Hadith**: `{{hadith|collection|book|number|narrator|grade}}`
- **Scholar**: `{{scholar|name|era|school|works}}`
- **Hijri**: `{{hijri|date|format|locale}}`
- **Prayer**: `{{prayer|location|city|date|timezone}}`
- **Fatwa**: `{{fatwa|scholar|topic|date|source}}`

---

## 🚀 **Installation & Setup**

### **Automatic Installation**
The WikiMarkupExtension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that the extension is loaded in the admin panel
2. Verify that wiki markup parsing is working
3. Test the edit functionality on existing pages
4. Confirm that templates are rendering correctly

### **Configuration**
```json
{
    "config": {
        "enable_wiki_markup": true,
        "enable_markdown": true,
        "default_format": "wikimarkup",
        "enable_edit_functionality": true,
        "auto_save_interval": 30000,
        "show_edit_button": true,
        "show_source_button": true,
        "enable_live_preview": true
    }
}
```

---

## 📝 **Usage Examples**

### **Creating New Pages**
1. Navigate to `/wiki/{page_name}` for any page
2. Click "Create this page" if it doesn't exist
3. Choose your preferred format (WikiMarkup or Markdown)
4. Use the rich editor toolbar for formatting
5. Preview your content in real-time
6. Save your page

### **Editing Existing Pages**
1. Navigate to any existing wiki page
2. Click the "Edit" button
3. Modify content using the rich editor
4. Switch between WikiMarkup and Markdown formats
5. Use live preview to see changes
6. Save your modifications

---

## 🎨 **WikiMarkup Syntax Reference**

### **Headers**
```wikimarkup
= Main Title =
== Section Title ==
=== Subsection Title ===
==== Sub-subsection Title ====
===== Minor Section =====
====== Detail Section ======
```

### **Text Formatting**
```wikimarkup
'''Bold text'''
''Italic text''
'''<em>Bold and italic</em>'''
<del>Strikethrough text</del>
<u>Underlined text</u>
<code>Inline code</code>
<pre>Preformatted block</pre>
```

### **Lists**
```wikimarkup
* Unordered list item 1
* Unordered list item 2
  * Nested item 2.1
  * Nested item 2.2

# Ordered list item 1
# Ordered list item 2
  # Nested item 2.1
  # Nested item 2.2

; Definition term
: Definition description
: Another description
```

### **Links**
```wikimarkup
[[Internal Page]]
[[Internal Page|Display Text]]
[[Category:Page Category]]
[[Image:filename.jpg|Caption]]
[[File:document.pdf|Download Link]]
```

### **Tables**
```wikimarkup
{| class="wikitable"
|+ Table Caption
|-
! Header 1 !! Header 2 !! Header 3
|-
| Cell 1 || Cell 2 || Cell 3
|-
| Cell 4 || Cell 5 || Cell 6
|}
```

### **Templates**
```wikimarkup
{{Infobox|Title|Content}}
{{quran|2|255|English}}
{{hadith|bukhari|1|1}}
{{image|photo.jpg|Beautiful sunset|alt text|large|right}}
{{math|E = mc^2|block|large|red}}
```

---

## 📝 **Markdown Syntax Reference**

### **Headers**
```markdown
# Main Title
## Section Title
### Subsection Title
#### Sub-subsection Title
##### Minor Section
###### Detail Section
```

### **Text Formatting**
```markdown
**Bold text**
*Italic text*
***Bold and italic***
~~Strikethrough text~~
`Inline code`
```

### **Code Blocks**
```markdown
```php
function hello() {
    echo "Hello, World!";
}
```

```python
def hello():
    print("Hello, World!")
```
```

### **Lists**
```markdown
- Unordered list item 1
- Unordered list item 2
  - Nested item 2.1
  - Nested item 2.2

1. Ordered list item 1
2. Ordered list item 2
   1. Nested item 2.1
   2. Nested item 2.2
```

### **Links and Images**
```markdown
[Link text](https://example.com)
![Alt text](image.jpg "Image title")
```

---

## 🏗️ **Template System**

### **Built-in Templates**

#### **Islamic Content Templates**
- **`{{quran|surah|ayah|translation|tafsir}}`**
  - Displays Quran verses with references
  - Supports multiple translations
  - Optional tafsir (exegesis) content

- **`{{hadith|collection|book|number|narrator|grade}}`**
  - Shows hadith citations
  - Includes authenticity grading
  - Narrator information

- **`{{scholar|name|era|school|works}}`**
  - Islamic scholar references
  - Historical context
  - Major works listing

#### **Media Templates**
- **`{{image|file|caption|alt|size|align|link}}`**
  - Image display with options
  - Caption and alt text support
  - Size and alignment control

- **`{{gallery|images|caption|style|perrow}}`**
  - Image gallery creation
  - Customizable layout
  - Responsive design

#### **Layout Templates**
- **`{{infobox|title|content|style|width}}`**
  - Information boxes
  - Customizable styling
  - Flexible width options

- **`{{quote|text|author|source|date|style}}`**
  - Quotation blocks
  - Attribution support
  - Styling options

#### **Math Templates**
- **`{{math|formula|display|size|color}}`**
  - Mathematical formulas
  - Inline and block display
  - Customizable appearance

### **Creating Custom Templates**
1. Navigate to `/wiki/Template:TemplateName`
2. Create the template page
3. Use parameter placeholders: `{{1}}`, `{{2}}`, etc.
4. Add named parameters: `{{param_name}}`
5. Include HTML and wiki markup
6. Save and test your template

---

## 🔧 **Editor Features**

### **Rich Text Toolbar**
- **Formatting**: Bold, italic, underline, strikethrough
- **Headers**: H1, H2, H3 creation
- **Lists**: Unordered, ordered, definition lists
- **Links**: Internal and external link insertion
- **Media**: Image and file insertion
- **Tables**: Table creation and editing
- **Code**: Code block insertion
- **Math**: Mathematical formula insertion

### **Live Preview**
- **Real-time rendering**: See changes as you type
- **Format switching**: Preview in different formats
- **Responsive design**: Mobile-friendly preview
- **Template rendering**: See template output immediately

### **Auto-save**
- **Automatic saving**: Content saved every 30 seconds
- **Draft recovery**: Recover unsaved changes
- **Version tracking**: Track content modifications
- **Conflict resolution**: Handle simultaneous edits

### **Syntax Help**
- **Format-specific help**: WikiMarkup vs Markdown
- **Template reference**: Built-in template guide
- **Syntax examples**: Practical usage examples
- **Best practices**: Content creation tips

---

## 🎯 **Use Cases**

### **Content Creation**
- **Articles**: Rich, formatted articles with MediaWiki syntax
- **Documentation**: Technical documentation with code examples
- **Tutorials**: Step-by-step guides with images and formatting
- **Reference**: Structured reference materials

### **Islamic Content**
- **Quran Studies**: Verse references and translations
- **Hadith Collections**: Authenticated hadith citations
- **Scholar Biographies**: Islamic scholar information
- **Islamic History**: Historical events and figures

### **Educational Content**
- **Course Materials**: Structured learning content
- **Study Guides**: Comprehensive study resources
- **Practice Exercises**: Interactive learning materials
- **Assessment Tools**: Quiz and test creation

---

## 🚀 **Performance Features**

### **Caching System**
- **Template caching**: Fast template rendering
- **Parser caching**: Efficient content parsing
- **Asset optimization**: Optimized CSS/JS loading
- **Memory management**: Efficient resource usage

### **Optimization**
- **Lazy loading**: Load content as needed
- **Compression**: Gzip content compression
- **CDN ready**: Content delivery network support
- **Mobile optimization**: Responsive design

---

## 🔒 **Security Features**

### **Content Validation**
- **Input sanitization**: Clean user input
- **XSS protection**: Prevent cross-site scripting
- **CSRF protection**: Security token validation
- **Format validation**: Content format checking

### **Access Control**
- **Edit permissions**: User-based editing rights
- **Content moderation**: Community review system
- **Version control**: Track all content changes
- **Rollback protection**: Prevent malicious edits

---

## 🧪 **Testing & Quality Assurance**

### **Automated Testing**
- **Unit tests**: Individual component testing
- **Integration tests**: Component interaction testing
- **Parser tests**: Markup parsing validation
- **Template tests**: Template rendering verification

### **Quality Metrics**
- **Code coverage**: 90%+ test coverage target
- **Performance benchmarks**: Sub-100ms parsing times
- **Memory usage**: Efficient memory utilization
- **Error handling**: Comprehensive error management

---

## 📚 **API Reference**

### **Core Methods**
```php
// Parse content
$parsed = $extension->parseContent($content, 'wikimarkup');

// Get edit form
$editForm = $extension->getEditForm($title, $content, $format);

// Get available templates
$templates = $extension->getAvailableTemplates();

// Get template info
$info = $extension->getTemplateInfo('templateName');
```

### **Hook Integration**
```php
// Content parsing hook
$hookManager->register('ContentParse', [$extension, 'onContentParse'], 10);

// Page edit hook
$hookManager->register('PageEdit', [$extension, 'onPageEdit'], 10);

// Page save hook
$hookManager->register('PageSave', [$extension, 'onPageSave'], 10);
```

---

## 🔮 **Future Enhancements**

### **Planned Features**
- **Advanced templates**: Conditional logic and loops
- **Plugin system**: Third-party template extensions
- **AI assistance**: Smart content suggestions
- **Collaborative editing**: Real-time co-editing
- **Version comparison**: Visual diff tools

### **Integration Plans**
- **Math rendering**: LaTeX and MathML support
- **Code execution**: Interactive code blocks
- **Media processing**: Advanced image handling
- **Export system**: PDF and document export

---

## 📞 **Support & Resources**

### **Documentation**
- **User Guide**: Complete usage instructions
- **Developer Guide**: API and integration details
- **Template Reference**: Built-in template documentation
- **Syntax Guide**: Comprehensive markup reference

### **Community**
- **User Forum**: Community support and discussion
- **Template Library**: Community-created templates
- **Examples Gallery**: Showcase of usage examples
- **Contribution Guide**: How to contribute

---

## 🎉 **Conclusion**

The WikiMarkupExtension transforms IslamWiki into a **professional, MediaWiki-compatible platform** that provides:

1. **✅ Comprehensive Markup Support**: Full MediaWiki syntax with Markdown compatibility
2. **✅ Professional Editing**: Rich text editor with live preview and auto-save
3. **✅ Islamic Content**: Built-in templates for Islamic content management
4. **✅ Performance**: Optimized parsing and caching for fast content rendering
5. **✅ Security**: Comprehensive security features and content validation
6. **✅ Extensibility**: Template system for custom content types
7. **✅ User Experience**: Intuitive interface with comprehensive help

This extension positions IslamWiki as a **premier platform** for Islamic knowledge management, combining the power of MediaWiki with the simplicity of modern web applications!

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.1.3  
**Author:** IslamWiki Development Team  
**Status:** Enhanced with Comprehensive MediaWiki Markup & Edit Functionality 🚀

## 🎨 **Enhanced MediaWiki-Style Interface**

The WikiMarkupExtension now provides a **professional, MediaWiki-style editing experience** that closely matches the look and feel of platforms like MuslimWiki.

### **Professional Editor Features**

#### **🎯 Editor Header**
- **Page Title**: Clear display of "Creating [PageName]" or "Editing [PageName]"
- **Subtitle**: Shows "From IslamWiki" for new pages, "Current revision" for edits
- **Format Selector**: Choose between WikiMarkup and Markdown formats

#### **📝 Instructional Messages**
- **New Page**: Helpful guidance for creating new pages
- **Edit Page**: Clear instructions for editing existing content
- **Help Links**: Direct links to syntax help and documentation

#### **🛠️ Professional Toolbar**
- **Formatting Tools**: Bold, Italic, Underline, Strikethrough
- **Content Tools**: Links, Images, Galleries, Indentation
- **Structure Tools**: Headers (H1, H2, H3), Lists (Bullet, Numbered, Definition)
- **Advanced Tools**: Tables, Code, Math formulas
- **Special Features**: Signatures, Timestamps, Comments, NoWiki tags
- **Character Sets**: Special characters, symbols, and diacritics
- **Help System**: Syntax help, Template help, Category help
- **Preview Controls**: Show preview, toggle live preview

#### **📝 Enhanced Editor**
- **Large Text Area**: 25 rows for comfortable content creation
- **Character Count**: Real-time character count with warnings
- **Auto-save**: Automatic saving every 30 seconds
- **Tab Support**: Proper tab key handling for indentation
- **Keyboard Shortcuts**: Ctrl+B (Bold), Ctrl+I (Italic)

#### **📋 Summary & Options**
- **Summary Field**: Optional description of changes (200 character limit)
- **Watch Page**: Option to watch the page for changes
- **Minor Edit**: Mark edits as minor changes
- **License Notice**: Clear Creative Commons licensing information

#### **🎯 Action Buttons**
- **Primary Action**: "Create page" (new) or "Save page" (edit)
- **Preview**: Show preview of changes
- **Changes**: View difference from current version
- **Draft**: Save work in progress locally
- **Cancel**: Return without saving

### **🎨 Visual Design**

#### **Professional Styling**
- **Clean Layout**: Modern, responsive design with proper spacing
- **Color Scheme**: Professional blue theme with clear visual hierarchy
- **Typography**: Readable fonts with proper contrast
- **Responsive Design**: Works perfectly on all screen sizes

#### **Toolbar Design**
- **Grouped Controls**: Logical grouping of related tools
- **Hover Effects**: Interactive feedback on all buttons
- **Dropdown Menus**: Organized advanced features
- **Icon Support**: Visual indicators for better usability

#### **Form Elements**
- **Professional Inputs**: Clean, modern form controls
- **Status Indicators**: Clear feedback for auto-save and actions
- **Validation**: Real-time character count and limits
- **Accessibility**: Proper labels and keyboard navigation

### **🚀 Advanced Features**

#### **Auto-save System**
- **Automatic Saving**: Saves content every 30 seconds
- **Local Storage**: Preserves work in browser storage
- **Status Updates**: Clear indication of save status
- **Conflict Prevention**: Prevents data loss during editing

#### **Preview System**
- **Live Preview**: Real-time preview of content
- **Toggle Control**: Show/hide preview as needed
- **Scroll Integration**: Smooth scrolling to preview area
- **Format Support**: Preview both WikiMarkup and Markdown

#### **Keyboard Shortcuts**
- **Ctrl+B**: Bold text
- **Ctrl+I**: Italic text
- **Tab**: Insert 4 spaces for indentation
- **Escape**: Cancel current operation

#### **Help System**
- **Contextual Help**: Format-specific syntax guidance
- **Template Examples**: Real examples of template usage
- **Category Help**: Clear category syntax instructions
- **Quick Reference**: Essential syntax at your fingertips

### **📱 Responsive Design**

#### **Mobile Optimization**
- **Touch-Friendly**: Proper button sizes for mobile devices
- **Responsive Layout**: Adapts to different screen sizes
- **Mobile Toolbar**: Optimized toolbar for small screens
- **Touch Gestures**: Support for mobile interactions

#### **Cross-Platform**
- **Desktop**: Full-featured interface with all tools
- **Tablet**: Optimized layout for medium screens
- **Mobile**: Streamlined interface for small screens
- **All Browsers**: Consistent experience across platforms

### **🔧 Technical Implementation**

#### **CSS Framework**
- **Professional Styling**: Clean, modern CSS with proper organization
- **CSS Variables**: Consistent color scheme and spacing
- **Responsive Grid**: CSS Grid and Flexbox for layouts
- **Animation Support**: Smooth transitions and hover effects

#### **JavaScript Enhancement**
- **Modular Code**: Well-organized, maintainable JavaScript
- **Event Handling**: Proper event delegation and management
- **Local Storage**: Client-side data persistence
- **Error Handling**: Graceful fallbacks and user feedback

#### **Performance Features**
- **Lazy Loading**: Load features as needed
- **Efficient Updates**: Minimal DOM manipulation
- **Memory Management**: Proper cleanup and optimization
- **Caching**: Local storage for better performance

### **🎯 User Experience**

#### **Professional Feel**
- **MediaWiki Familiarity**: Users familiar with MediaWiki will feel at home
- **Intuitive Interface**: Clear, logical organization of tools
- **Visual Feedback**: Immediate response to user actions
- **Error Prevention**: Clear warnings and confirmations

#### **Accessibility**
- **Keyboard Navigation**: Full keyboard support
- **Screen Reader**: Proper ARIA labels and descriptions
- **High Contrast**: Clear visual hierarchy and contrast
- **Focus Management**: Proper focus indicators and management

#### **Learning Curve**
- **Progressive Disclosure**: Advanced features in dropdowns
- **Contextual Help**: Help available when needed
- **Examples**: Real examples for all syntax
- **Tooltips**: Clear descriptions for all tools

This enhanced interface transforms the WikiMarkupExtension into a **professional-grade wiki editing platform** that rivals the best MediaWiki installations while maintaining the Islamic aesthetic and values of IslamWiki.


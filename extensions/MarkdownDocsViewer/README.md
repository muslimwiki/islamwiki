# MarkdownDocsViewer Extension

An advanced markdown rendering system for IslamWiki that provides enhanced markdown processing, syntax highlighting, real-time preview, and comprehensive documentation viewing capabilities.

## 🌟 **Features**

### **Advanced Markdown Rendering**
- **Multiple markdown processors** (Parsedown, CommonMark, Markdown)
- **Custom extensions** and syntax support
- **Real-time preview** with live rendering
- **Template system** for consistent styling
- **Multi-language support** for international content

### **Enhanced Syntax Highlighting**
- **Multiple programming languages** support
- **Customizable themes** and color schemes
- **Code block execution** capabilities
- **Line numbering** and syntax validation
- **Language detection** and auto-highlighting

### **Advanced Search Functionality**
- **Full-text search** within markdown content
- **Semantic search** with keyword expansion
- **Search result highlighting** and ranking
- **Advanced filtering** by content type and metadata
- **Search suggestions** and auto-completion

### **Export and Publishing**
- **Multiple export formats** (PDF, HTML, DOCX, EPUB)
- **Customizable templates** for different outputs
- **Batch processing** for multiple documents
- **Quality control** and validation
- **Version control** integration

### **Admin and Management**
- **Advanced admin interface** for content management
- **User permission system** for content access
- **Content versioning** and history tracking
- **Bulk operations** for content management
- **Analytics and reporting** tools

## 🚀 **Installation**

### **Automatic Installation**
The MarkdownDocsViewer extension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that markdown rendering is working correctly
2. Verify that syntax highlighting is functioning
3. Test real-time preview system
4. Confirm that search functionality works
5. Test admin interface functionality

## ⚙️ **Configuration**

### **Basic Configuration**
```json
{
    "config": {
        "enableMarkdownRendering": true,
        "enableSyntaxHighlighting": true,
        "enableRealTimePreview": true,
        "enableSearchFunctionality": true,
        "enableExportFeatures": true,
        "enableTemplateSystem": true,
        "defaultMarkdownProcessor": "parsedown",
        "supportedProcessors": ["parsedown", "commonmark", "markdown"],
        "enableCodeHighlighting": true,
        "supportedLanguages": ["php", "javascript", "python", "sql", "html", "css"],
        "enableCustomExtensions": true,
        "enableAdminInterface": true,
        "enableAPIEndpoints": true,
        "enableCaching": true,
        "defaultTheme": "default",
        "supportedThemes": ["default", "dark", "light", "islamic"]
    }
}
```

### **Markdown Processors**
- **Parsedown**: Fast and feature-rich markdown parser
- **CommonMark**: Standard-compliant markdown processor
- **Markdown**: Traditional markdown processing
- **Custom**: User-defined markdown processors

## 📱 **Usage**

### **Basic Markdown Rendering**
```twig
{% include 'extensions/MarkdownDocsViewer/templates/markdown-viewer.twig' %}
```

### **Markdown Editor with Preview**
```twig
{% include 'extensions/MarkdownDocsViewer/templates/markdown-editor.twig' %}
```

### **Syntax Highlighting**
```twig
{% include 'extensions/MarkdownDocsViewer/templates/syntax-highlighting.twig' %}
```

### **Search Interface**
```twig
{% include 'extensions/MarkdownDocsViewer/templates/search-interface.twig' %}
```

## 🔧 **API Reference**

### **Markdown Rendering API**
```php
use IslamWiki\Extensions\MarkdownDocsViewer\Services\MarkdownRenderer;

$renderer = new MarkdownRenderer();
$html = $renderer->render($markdown, $options);
$metadata = $renderer->extractMetadata($markdown);
```

### **Search API**
```php
use IslamWiki\Extensions\MarkdownDocsViewer\Services\SearchService;

$searchService = new SearchService();
$results = $searchService->search($query, $filters);
$suggestions = $searchService->getSuggestions($query);
```

### **Export API**
```php
use IslamWiki\Extensions\MarkdownDocsViewer\Services\ExportService;

$exportService = new ExportService();
$pdf = $exportService->exportToPDF($markdown, $template);
$html = $exportService->exportToHTML($markdown, $theme);
```

## 🏗️ **Technical Architecture**

### **How the Extension Works**

#### **1. Extension Bootstrap Process**
The extension follows a structured initialization process:
1. **Dependency Loading**: Register required services and dependencies
2. **Configuration Loading**: Load extension settings and preferences
3. **Hook Registration**: Register with IslamWiki's hook system
4. **Resource Setup**: Initialize CSS, JavaScript, and templates
5. **Service Initialization**: Start core business logic services

#### **2. Markdown Processing Pipeline**
The extension implements a sophisticated markdown processing pipeline:
- **Content Parsing**: Parse markdown content with selected processor
- **Extension Processing**: Apply custom extensions and syntax
- **Template Rendering**: Apply templates and styling
- **Output Generation**: Generate final HTML or other formats

#### **3. Multi-Layer Caching System**
The extension implements a sophisticated caching strategy:
- **Content Cache**: Cache rendered markdown content
- **Search Cache**: Cache search results and indexes
- **Template Cache**: Cache processed templates
- **Asset Cache**: Cache CSS, JavaScript, and other assets

#### **4. Search and Indexing System**
Advanced search functionality with multiple algorithms:
- **Full-text Indexing**: Index markdown content for fast searching
- **Semantic Search**: Understand content meaning and context
- **Fuzzy Matching**: Handle spelling variations and typos
- **Result Ranking**: Intelligent result ordering and relevance

## 🎨 **Customization**

### **CSS Customization**
```css
/* Custom markdown viewer styling */
.markdown-viewer {
    background: var(--islamic-cream);
    border: 2px solid var(--islamic-green);
    border-radius: var(--radius-lg);
}

/* Custom syntax highlighting */
.syntax-highlighting {
    background: var(--islamic-dark-green);
    color: var(--islamic-white);
    border-radius: var(--radius-md);
}
```

### **Template Customization**
Copy templates from `templates/` to your theme directory and modify as needed.

### **Extension Customization**
```php
// Custom markdown extension
class CustomMarkdownExtension
{
    public function process($content)
    {
        // Custom markdown processing logic
        return $processedContent;
    }
}
```

## 🧪 **Testing**

### **Test Checklist**
- [ ] Markdown rendering accuracy
- [ ] Syntax highlighting functionality
- [ ] Real-time preview system
- [ ] Search functionality and accuracy
- [ ] Export functionality for all formats
- [ ] Admin interface operations
- [ ] Performance with large documents
- [ ] Caching system effectiveness
- [ ] API endpoint reliability

### **Testing Tools**
- **Markdown validator** for syntax checking
- **Syntax highlighting tester** for language support
- **Search accuracy tester** for query validation
- **Export quality validator** for output verification

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Markdown Not Rendering**
- Check if extension is properly loaded
- Verify markdown processor configuration
- Check template paths and includes
- Review error logs for specific issues

#### **Syntax Highlighting Not Working**
- Verify syntax highlighting is enabled
- Check language support configuration
- Review CSS and JavaScript loading
- Test with different programming languages

#### **Search Not Functioning**
- Check search indexing configuration
- Verify search service is running
- Review search algorithm settings
- Test with simple queries first

### **Debug Mode**
Enable debug logging in the extension configuration:
```json
{
    "config": {
        "enableDebugLogging": true,
        "logLevel": "DEBUG"
    }
}
```

## 📚 **Documentation**

### **Available Resources**
- **README.md**: This file with basic information
- **CHANGELOG.md**: Complete version history
- **docs/**: Comprehensive documentation folder
  - **TECHNICAL_ARCHITECTURE.md**: Complete technical documentation (to be created)
  - **RELEASE-0.0.2.md**: Detailed release notes (to be created)
  - **INSTALLATION.md**: Installation guide (to be created)
  - **CONFIGURATION.md**: Configuration guide (to be created)
  - **API_REFERENCE.md**: Complete API documentation (to be created)
  - **TROUBLESHOOTING.md**: Troubleshooting guide (to be created)
  - **EXAMPLES.md**: Usage examples (to be created)

### **Code Documentation**
- **Inline comments**: Detailed code documentation
- **PHPDoc blocks**: Complete API documentation
- **Example code**: Working examples for common use cases
- **Architecture diagrams**: Visual system documentation

## 🔮 **Future Plans**

### **Upcoming Features**
- **Real-time collaborative editing** with conflict resolution
- **Advanced markdown extensions** with custom syntax
- **Integration with external processors** for enhanced functionality
- **Advanced table and data visualization** capabilities
- **Mathematical formula rendering** with LaTeX support

### **Long-term Goals**
- **AI-powered content analysis** with machine learning
- **Advanced content recommendation** system
- **Global markdown database** with community contributions
- **Educational platform** for markdown learning

### **Technical Roadmap**
- **Microservices architecture** for better scalability
- **Event-driven architecture** for real-time updates
- **Advanced caching strategies** for large documents
- **Machine learning integration** for intelligent features

## 🤝 **Contributing**

We welcome contributions to improve the MarkdownDocsViewer extension:

1. **Report issues** with detailed descriptions and steps to reproduce
2. **Suggest improvements** for markdown processing and display
3. **Contribute code** for bug fixes and enhancements
4. **Submit pull requests** for new features and improvements

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### **Code Standards**
- Follow PSR-12 coding standards
- Include comprehensive tests
- Update documentation for changes
- Follow semantic versioning

## 📞 **Support**

### **Getting Help**
- **Documentation**: Check the docs folder first
- **Issue reporting**: Use GitHub issues for bugs
- **Community support**: Contact IslamWiki community
- **Development team**: Contact the development team

### **Contact Information**
- **GitHub**: [MarkdownDocsViewer Extension Repository](https://github.com/islamwiki/MarkdownDocsViewer)
- **Documentation**: [Extension Documentation](https://islamwiki.org/extensions/MarkdownDocsViewer)
- **Community**: [IslamWiki Community Forum](https://community.islamwiki.org)

## 📄 **License**

This extension is part of IslamWiki and follows the same licensing terms.

## 🙏 **Acknowledgments**

- **Markdown community** for markdown standards and tools
- **Open source contributors** for various markdown processors
- **Syntax highlighting libraries** for code formatting
- **IslamWiki community** for testing and feedback

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building advanced documentation tools for the digital age.* 
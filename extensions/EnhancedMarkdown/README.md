# EnhancedMarkdown Extension

An advanced markdown enhancement system for IslamWiki that provides custom syntax, enhanced table rendering, mathematical formula support, advanced code blocks, and comprehensive markdown processing capabilities for Islamic content.

## 🌟 **Features**

### **Advanced Markdown Extensions**
- **Custom syntax** and processors for Islamic content
- **Enhanced table rendering** with sorting and filtering
- **Mathematical formula support** with LaTeX and MathML
- **Advanced code block features** with syntax highlighting
- **Real-time preview** with live markdown rendering

### **Islamic Content Syntax**
- **Quran verse references** with automatic formatting
- **Hadith citations** with scholarly attribution
- **Islamic date formatting** with Hijri calendar support
- **Prayer time integration** with location-based data
- **Scholar references** with biographical information

### **Enhanced Table System**
- **Sortable columns** with multiple algorithms
- **Advanced filtering** with search capabilities
- **Responsive design** for all device sizes
- **Custom styling** with Islamic themes
- **Export functionality** for various formats

### **Mathematical Formula Support**
- **LaTeX rendering** with comprehensive support
- **MathML output** for accessibility
- **Inline and block formulas** with proper spacing
- **Custom mathematical symbols** for Islamic sciences
- **Formula validation** and error handling

### **Advanced Code Blocks**
- **Multiple programming languages** support
- **Syntax highlighting** with customizable themes
- **Line numbering** and code folding
- **Code execution** capabilities for interactive content
- **Language detection** and auto-highlighting

## 🚀 **Installation**

### **Automatic Installation**
The EnhancedMarkdown extension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that markdown enhancements are working correctly
2. Verify that Islamic syntax is functioning
3. Test table rendering and functionality
4. Confirm that mathematical formulas display properly
5. Test code block features and syntax highlighting

## ⚙️ **Configuration**

### **Basic Configuration**
```json
{
    "config": {
        "enableIslamicSyntax": true,
        "enableArabicSupport": true,
        "enableTemplates": true,
        "defaultEditor": "markdown",
        "syntaxHighlighting": true,
        "autoSave": true,
        "previewMode": "split"
    }
}
```

### **Islamic Syntax Configuration**
```json
{
    "islamicSyntax": {
        "quran": {
            "pattern": "{{quran:surah:ayah}}",
            "example": "{{quran:2:255}}",
            "description": "Quran verse reference"
        },
        "hadith": {
            "pattern": "{{hadith:collection:book:number}}",
            "example": "{{hadith:bukhari:1:1}}",
            "description": "Hadith citation"
        },
        "hijri": {
            "pattern": "{{hijri:YYYY-MM-DD}}",
            "example": "{{hijri:1445-03-15}}",
            "description": "Islamic date"
        },
        "prayer": {
            "pattern": "{{prayer-times:location:city}}",
            "example": "{{prayer-times:location:mecca}}",
            "description": "Prayer times"
        },
        "scholar": {
            "pattern": "{{scholar:name}}",
            "example": "{{scholar:ibn-taymiyyah}}",
            "description": "Scholar reference"
        }
    }
}
```

## 📱 **Usage**

### **Basic Islamic Syntax**
```markdown
# Islamic Content Example

## Quran Reference
{{quran:2:255}} - The Verse of the Throne

## Hadith Citation
{{hadith:bukhari:1:1}} - Beginning of Sahih Bukhari

## Islamic Date
Today is {{hijri:1445-03-15}} in the Hijri calendar

## Prayer Times
{{prayer-times:location:mecca}} - Prayer times for Makkah

## Scholar Reference
{{scholar:ibn-taymiyyah}} - Ibn Taymiyyah's works
```

### **Enhanced Tables**
```markdown
| Name | Age | City | Status |
|------|-----|------|--------|
| Ahmad | 25 | Makkah | Active |
| Fatima | 30 | Madinah | Active |
| Omar | 35 | Jeddah | Inactive |

[sortable:true]
[filterable:true]
[exportable:true]
```

### **Mathematical Formulas**
```markdown
## Mathematical Formulas

### Inline Formula
The golden ratio is $\phi = \frac{1 + \sqrt{5}}{2}$

### Block Formula
$$
\int_{-\infty}^{\infty} e^{-x^2} dx = \sqrt{\pi}
$$

### Islamic Mathematics
The area of a circle is $A = \pi r^2$ where $r$ is the radius.
```

### **Advanced Code Blocks**
```markdown
```php
<?php
// Islamic calendar calculation
function calculateHijriDate($gregorianDate) {
    $julianDay = gregorianToJulian($gregorianDate);
    $hijriYear = (int)((30 * $julianDay + 10646) / 10631);
    return $hijriYear;
}
```

```javascript
// Prayer time calculation
function calculatePrayerTimes(latitude, longitude, date) {
    // Astronomical calculations for prayer times
    const sunrise = calculateSunrise(latitude, longitude, date);
    const sunset = calculateSunset(latitude, longitude, date);
    return { sunrise, sunset };
}
```
```

## 🔧 **API Reference**

### **Markdown Processing API**
```php
use IslamWiki\Extensions\EnhancedMarkdown\Services\MarkdownProcessor;

$processor = new MarkdownProcessor();
$html = $processor->process($markdown, $options);
$metadata = $processor->extractMetadata($markdown);
```

### **Islamic Syntax API**
```php
use IslamWiki\Extensions\EnhancedMarkdown\Services\IslamicSyntaxProcessor;

$islamicProcessor = new IslamicSyntaxProcessor();
$processed = $islamicProcessor->processQuranReferences($content);
$processed = $islamicProcessor->processHadithCitations($content);
$processed = $islamicProcessor->processIslamicDates($content);
```

### **Table Enhancement API**
```php
use IslamWiki\Extensions\EnhancedMarkdown\Services\TableEnhancer;

$tableEnhancer = new TableEnhancer();
$enhancedTable = $tableEnhancer->enhance($tableMarkdown, $options);
$sortableTable = $tableEnhancer->makeSortable($table);
$filterableTable = $tableEnhancer->makeFilterable($table);
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
- **Content Parsing**: Parse markdown content with enhanced processors
- **Islamic Syntax Processing**: Apply custom Islamic syntax and patterns
- **Table Enhancement**: Process and enhance table functionality
- **Mathematical Formula Rendering**: Process LaTeX and MathML content
- **Code Block Processing**: Apply syntax highlighting and features

#### **3. Islamic Syntax Engine**
Advanced processing for Islamic content:
- **Pattern Recognition**: Identify Islamic syntax patterns in content
- **Content Enrichment**: Enhance content with additional information
- **Validation**: Ensure syntax correctness and completeness
- **Rendering**: Convert syntax to appropriate HTML output

#### **4. Table Enhancement System**
Comprehensive table functionality enhancement:
- **Sorting Algorithms**: Multiple sorting algorithms for different data types
- **Filtering System**: Advanced filtering with search and criteria
- **Responsive Design**: Mobile-friendly table layouts
- **Export Functionality**: Multiple export formats for table data

## 🎨 **Customization**

### **CSS Customization**
```css
/* Custom Islamic syntax styling */
.islamic-syntax {
    background: var(--islamic-cream);
    border: 2px solid var(--islamic-green);
    border-radius: var(--radius-lg);
}

/* Custom table styling */
.enhanced-table {
    background: var(--islamic-white);
    border: 1px solid var(--islamic-green);
    border-radius: var(--radius-md);
}

/* Custom mathematical formula styling */
.math-formula {
    background: rgba(45, 80, 22, 0.05);
    border: 1px solid rgba(45, 80, 22, 0.2);
    border-radius: var(--radius-sm);
    padding: var(--spacing-sm);
}
```

### **Template Customization**
Copy templates from `templates/` to your theme directory and modify as needed.

### **Islamic Syntax Customization**
```php
// Custom Islamic syntax pattern
class CustomIslamicSyntax
{
    public function processCustomPattern($content)
    {
        // Custom processing logic for Islamic content
        $pattern = '/\{\{custom:([^}]+)\}\}/';
        $replacement = '<span class="custom-islamic">$1</span>';
        
        return preg_replace($pattern, $replacement, $content);
    }
}
```

## 🧪 **Testing**

### **Test Checklist**
- [ ] Islamic syntax processing accuracy
- [ ] Table enhancement functionality
- [ ] Mathematical formula rendering
- [ ] Code block syntax highlighting
- [ ] Real-time preview system
- [ ] Template system functionality
- [ ] Performance with large documents
- [ ] Caching system effectiveness
- [ ] API endpoint reliability

### **Testing Tools**
- **Islamic syntax validator** for pattern checking
- **Table functionality tester** for enhancement validation
- **Mathematical formula renderer** for LaTeX validation
- **Code highlighting tester** for language support

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Islamic Syntax Not Processing**
- Check if Islamic syntax is enabled
- Verify syntax pattern configuration
- Review template paths and includes
- Check for syntax validation errors

#### **Tables Not Enhanced**
- Verify table enhancement is enabled
- Check table syntax and formatting
- Review enhancement options and configuration
- Test with simple table examples

#### **Mathematical Formulas Not Rendering**
- Check LaTeX/MathML support configuration
- Verify formula syntax and formatting
- Review rendering engine status
- Test with simple mathematical expressions

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
- **Advanced Islamic syntax** with more patterns and features
- **Integration with external processors** for enhanced functionality
- **Advanced table visualization** with charts and graphs
- **Mathematical formula editor** with visual interface

### **Long-term Goals**
- **AI-powered content enhancement** with machine learning
- **Advanced Islamic content analysis** and recommendations
- **Global Islamic content database** with community contributions
- **Educational platform** for Islamic content creation

### **Technical Roadmap**
- **Microservices architecture** for better scalability
- **Event-driven architecture** for real-time updates
- **Advanced caching strategies** for large documents
- **Machine learning integration** for intelligent features

## 🤝 **Contributing**

We welcome contributions to improve the EnhancedMarkdown extension:

1. **Report issues** with detailed descriptions and steps to reproduce
2. **Suggest improvements** for Islamic syntax and markdown features
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
- **GitHub**: [EnhancedMarkdown Extension Repository](https://github.com/islamwiki/EnhancedMarkdown)
- **Documentation**: [Extension Documentation](https://islamwiki.org/extensions/EnhancedMarkdown)
- **Community**: [IslamWiki Community Forum](https://community.islamwiki.org)

## 📄 **License**

This extension is part of IslamWiki and follows the same licensing terms.

## 🙏 **Acknowledgments**

- **Markdown community** for markdown standards and tools
- **Islamic scholars** for content patterns and methodologies
- **Open source contributors** for various markdown libraries
- **IslamWiki community** for testing and feedback

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building advanced markdown tools for Islamic content creation.* 
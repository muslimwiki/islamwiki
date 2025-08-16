# HadithExtension

A comprehensive hadith system for IslamWiki that provides advanced search, display, management, and analysis capabilities for Islamic hadith literature.

## 🌟 **Features**

### **Advanced Hadith Search**
- **Multiple search algorithms**: Exact text, fuzzy matching, semantic search, narrator-based
- **Intelligent relevance scoring**: Multi-factor ranking system
- **Advanced filtering**: By authenticity, collection, narrator, date, and category
- **Multi-language support**: Arabic, English, Urdu, Turkish, Indonesian
- **Search suggestions**: Intelligent query completion and suggestions

### **Authenticity Assessment System**
- **Comprehensive grading**: Sahih, Hasan, Daif, Mawdu with numerical scores
- **Multi-factor analysis**: Narrator reliability, chain continuity, historical consistency
- **Scholarly consensus**: Integration with multiple schools of thought
- **Expert verification**: Community-driven authenticity assessment
- **Historical context**: Cultural and linguistic analysis

### **Narrator Chain Analysis**
- **Chain visualization**: Interactive diagrams showing transmission routes
- **Reliability scoring**: Individual narrator assessment and grading
- **Historical mapping**: Timeline and geographic visualization
- **Chain strength analysis**: Mathematical scoring of transmission reliability
- **Biographical information**: Comprehensive narrator profiles

### **Commentary System**
- **Multiple interpretations**: Scholarly commentaries from various traditions
- **Contextual analysis**: Historical, cultural, and linguistic explanations
- **Comparative studies**: Cross-referencing different scholarly views
- **User annotations**: Personal notes and insights system
- **Citation management**: Proper scholarly attribution

### **Daily Hadith Feature**
- **Intelligent rotation**: Balanced content distribution algorithm
- **Seasonal relevance**: Time-appropriate hadith selection
- **Personalization**: User preference learning and recommendations
- **Social sharing**: Proper attribution and context sharing
- **Mobile notifications**: Daily reminders and updates

### **Widget System**
- **Multiple widget types**: Search, display, daily hadith, chain visualization
- **Customizable display**: Various layout and styling options
- **Responsive design**: Works on all device sizes
- **Easy integration**: Simple template includes for any page

## 🚀 **Installation**

### **Automatic Installation**
The HadithExtension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that hadith search is working correctly
2. Verify that hadith display is functioning
3. Test authenticity filtering system
4. Confirm that widgets are displaying properly
5. Test admin interface functionality

## ⚙️ **Configuration**

### **Basic Configuration**
```json
{
    "config": {
        "enableHadithSearch": true,
        "enableHadithDisplay": true,
        "enableHadithManagement": true,
        "enableHadithWidgets": true,
        "enableHadithTemplates": true,
        "defaultLanguage": "en",
        "supportedLanguages": ["en", "ar", "ur", "tr", "id"],
        "maxSearchResults": 100,
        "enableAuthenticityFiltering": true,
        "enableNarratorChains": true,
        "enableCommentaries": true,
        "enableDailyHadith": true,
        "enableHadithCategories": true
    }
}
```

### **Search Configuration**
- **Search algorithms**: Configure which search methods to use
- **Result ranking**: Customize relevance scoring factors
- **Cache settings**: Configure caching behavior and duration
- **Performance tuning**: Adjust search result limits and timeouts

## 📱 **Usage**

### **Basic Hadith Search**
```twig
{% include 'extensions/HadithExtension/templates/hadith-search.twig' %}
```

### **Hadith Display**
```twig
{% include 'extensions/HadithExtension/templates/hadith-display.twig' %}
```

### **Daily Hadith Widget**
```twig
{% include 'extensions/HadithExtension/templates/daily-hadith.twig' %}
```

### **Chain Visualization**
```twig
{% include 'extensions/HadithExtension/templates/chain-visualization.twig' %}
```

### **Authenticity Details**
```twig
{% include 'extensions/HadithExtension/templates/authenticity-details.twig' %}
```

## 🔧 **API Reference**

### **Hadith Search API**
```php
use IslamWiki\Extensions\HadithExtension\Services\HadithSearchService;

$searchService = new HadithSearchService();
$results = $searchService->searchHadiths($query, $filters, $page, $limit);
```

### **Authenticity Assessment API**
```php
use IslamWiki\Extensions\HadithExtension\Services\AuthenticityService;

$authenticityService = new AuthenticityService();
$grade = $authenticityService->assessAuthenticity($hadithId);
```

### **Chain Analysis API**
```php
use IslamWiki\Extensions\HadithExtension\Services\ChainAnalysisService;

$chainService = new ChainAnalysisService();
$analysis = $chainService->analyzeChain($narratorChain);
```

### **Commentary API**
```php
use IslamWiki\Extensions\HadithExtension\Services\CommentaryService;

$commentaryService = new CommentaryService();
$commentaries = $commentaryService->getCommentaries($hadithId);
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

#### **2. Hook System Integration**
The extension integrates with IslamWiki's hook system for seamless operation:
- **ContentParse**: Detect and process hadith references in content
- **PageDisplay**: Add hadith widgets and enhancements to pages
- **SearchIndex**: Index hadith content for search functionality
- **WidgetRender**: Handle hadith widget rendering
- **TemplateLoad**: Load hadith-specific templates
- **AdminMenu**: Add hadith management to admin interface
- **UserProfile**: Integrate with user profile systems

#### **3. Multi-Layer Caching System**
The extension implements a sophisticated caching strategy:
- **Memory Cache**: Fastest access for frequently used data
- **Redis Cache**: Distributed caching for multiple instances
- **File Cache**: Persistent storage for large datasets
- **Database**: Primary data source with optimization

#### **4. Search Algorithm Implementation**
Multiple search algorithms work together for optimal results:
- **Exact Text Matching**: Highest priority for precise queries
- **Fuzzy Matching**: Handle spelling variations and transliterations
- **Semantic Search**: Keyword expansion and related term matching
- **Narrator Search**: Find hadiths by narrator information

#### **5. Authenticity Assessment Engine**
Comprehensive authenticity scoring based on multiple factors:
- **Narrator Reliability**: Individual narrator assessment scores
- **Chain Continuity**: Transmission chain strength analysis
- **Historical Consistency**: Timeline and geographic validation
- **Scholarly Consensus**: Expert opinion integration
- **Text Quality**: Linguistic and content analysis

### **Database Architecture**
The extension uses an optimized database schema designed for:
- **Fast searches** with proper indexing
- **Efficient storage** with normalized structure
- **Scalability** for large hadith collections
- **Multi-language support** with proper encoding
- **Relationship management** between hadiths, narrators, and collections

### **Performance Optimization**
- **Query optimization** with execution plan analysis
- **Batch processing** for multiple operations
- **Intelligent caching** with automatic invalidation
- **Resource compression** for faster loading
- **Load balancing** support for multiple instances

## 🎨 **Customization**

### **CSS Customization**
```css
/* Custom hadith search styling */
.hadith-search {
    background: var(--islamic-cream);
    border: 2px solid var(--islamic-green);
    border-radius: var(--radius-lg);
}

/* Custom authenticity display styling */
.authenticity-grade.sahih {
    background: var(--islamic-green);
    color: var(--islamic-white);
}

.authenticity-grade.hasan {
    background: var(--islamic-gold);
    color: var(--islamic-dark-green);
}
```

### **Template Customization**
Copy templates from `templates/` to your theme directory and modify as needed.

### **Widget Customization**
```php
// Custom widget configuration
$widgetConfig = [
    'daily_hadith' => [
        'display_style' => 'card',
        'show_authenticity' => true,
        'show_commentary' => false,
        'max_length' => 200
    ]
];
```

## 🧪 **Testing**

### **Test Checklist**
- [ ] Hadith search accuracy with various query types
- [ ] Authenticity filtering system functionality
- [ ] Narrator chain analysis accuracy
- [ ] Commentary system functionality
- [ ] Daily hadith rotation algorithm
- [ ] Widget display on various page layouts
- [ ] Admin interface for all functions
- [ ] Performance with large hadith collections
- [ ] Caching system effectiveness
- [ ] API endpoint reliability

### **Testing Tools**
- **Hadith search tester** for query validation
- **Authenticity assessment tool** for grading verification
- **Chain analysis validator** for narrator chain accuracy
- **Performance testing** with large datasets

### **Performance Testing**
```php
// Performance benchmark test
$startTime = microtime(true);
$results = $searchService->searchHadiths($query, $filters);
$endTime = microtime(true);

$executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
$this->assertLessThan(200, $executionTime, 'Search should complete within 200ms');
```

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Search Not Working**
- Check if extension is properly loaded
- Verify database connection and tables
- Check search configuration settings
- Review error logs for specific issues

#### **Performance Issues**
- Verify caching system is working
- Check database query performance
- Review search algorithm configuration
- Monitor system resources

#### **Widget Display Problems**
- Check template paths and includes
- Verify CSS and JavaScript loading
- Check widget configuration
- Review browser console for errors

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

### **Performance Monitoring**
```php
// Enable performance monitoring
$monitor = new PerformanceMonitor();
$monitor->enableQueryLogging();
$monitor->enableCacheMonitoring();
$monitor->enableSearchMetrics();
```

## 📚 **Documentation**

### **Available Resources**
- **README.md**: This file with basic information
- **CHANGELOG.md**: Complete version history
- **docs/**: Comprehensive documentation folder
  - **RELEASE-0.0.2.md**: Detailed release notes
  - **TECHNICAL_ARCHITECTURE.md**: Complete technical documentation
  - **INSTALLATION.md**: Installation guide
  - **CONFIGURATION.md**: Configuration guide
  - **API_REFERENCE.md**: Complete API documentation
  - **TROUBLESHOOTING.md**: Troubleshooting guide
  - **EXAMPLES.md**: Usage examples

### **Code Documentation**
- **Inline comments**: Detailed code documentation
- **PHPDoc blocks**: Complete API documentation
- **Example code**: Working examples for common use cases
- **Architecture diagrams**: Visual system documentation

## 🔮 **Future Plans**

### **Upcoming Features**
- **Machine learning** for hadith classification and authenticity assessment
- **External database integration** for comprehensive hadith coverage
- **Advanced chain visualization** with 3D and interactive features
- **Community verification system** with user contributions
- **Mobile app integration** for offline hadith access

### **Long-term Goals**
- **AI-powered hadith analysis** with natural language processing
- **Global hadith database** with multi-language support
- **Scholarly collaboration tools** for hadith research
- **Educational platform** for hadith studies

### **Technical Roadmap**
- **Microservices architecture** for better scalability
- **Event-driven architecture** for real-time updates
- **Advanced caching strategies** for large datasets
- **Machine learning integration** for intelligent features

## 🤝 **Contributing**

We welcome contributions to improve the HadithExtension:

1. **Report issues** with detailed descriptions and steps to reproduce
2. **Suggest improvements** for hadith search and analysis
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
- **GitHub**: [HadithExtension Repository](https://github.com/islamwiki/HadithExtension)
- **Documentation**: [Extension Documentation](https://islamwiki.org/extensions/HadithExtension)
- **Community**: [IslamWiki Community Forum](https://community.islamwiki.org)

## 📄 **License**

This extension is part of IslamWiki and follows the same licensing terms.

## 🙏 **Acknowledgments**

- **Islamic scholars** for hadith authenticity methodologies
- **Hadith researchers** for transmission chain analysis
- **Open source contributors** for various libraries and tools
- **IslamWiki community** for testing and feedback

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building comprehensive Islamic knowledge tools for the digital age.*

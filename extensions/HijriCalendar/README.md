# HijriCalendar Extension

A comprehensive Hijri calendar system for IslamWiki that provides accurate Islamic date calculations, calendar management, and Islamic event tracking with advanced astronomical algorithms.

## 🌟 **Features**

### **Advanced Hijri Date Calculations**
- **Astronomical algorithms** for precise Hijri date calculations
- **Multiple calculation methods** with high accuracy
- **Edge case handling** for extreme latitudes and dates
- **Lunar visibility calculations** for new moon determination
- **Seasonal adjustments** for accurate Islamic dates

### **Date Conversion System**
- **Gregorian to Hijri** conversion with astronomical precision
- **Hijri to Gregorian** conversion with validation
- **Multiple calendar systems** support
- **Timezone handling** with automatic DST detection
- **Location-based calculations** for accurate dates

### **Islamic Event Management**
- **Automatic event calculation** for Islamic occasions
- **Custom event creation** and management
- **Event categorization** by type and importance
- **Seasonal event tracking** with automatic date updates
- **Community event sharing** and collaboration

### **Calendar Widget System**
- **Multiple display options** (month, year, week, day views)
- **Interactive calendar** with event highlighting
- **Responsive design** for all device sizes
- **Customizable themes** and styling options
- **Easy integration** into any page

### **Advanced Features**
- **Lunar phase tracking** with accurate calculations
- **Prayer time integration** with Hijri dates
- **Export functionality** for various calendar formats
- **Multi-language support** for international users
- **Admin interface** for comprehensive management

## 🚀 **Installation**

### **Automatic Installation**
The HijriCalendar extension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that Hijri date calculations are working correctly
2. Verify that calendar display is functioning
3. Test date conversion between calendars
4. Confirm that widgets are displaying properly
5. Test admin interface functionality

## ⚙️ **Configuration**

### **Basic Configuration**
```json
{
    "config": {
        "enableDateConversion": true,
        "enableHijriDisplay": true,
        "enableGregorianConversion": true,
        "enableIslamicEvents": true,
        "enableLunarPhases": true,
        "enableHijriWidgets": true,
        "enableHijriTemplates": true,
        "enableHijriAPI": true,
        "defaultCalendarView": "month",
        "supportedCalendarViews": ["month", "year", "week", "day"],
        "enableNotifications": true,
        "enableTimezoneSupport": true,
        "enableMultipleLocales": true,
        "defaultLocale": "en",
        "supportedLocales": ["en", "ar", "ur", "tr", "ms", "id"],
        "enableHijriHolidays": true,
        "enableCustomEvents": true
    }
}
```

### **Calculation Methods**
- **Astronomical algorithms** for highest accuracy
- **Traditional methods** for compatibility
- **Hybrid approaches** for optimal results
- **Custom algorithms** for specific regions

## 📱 **Usage**

### **Basic Date Conversion**
```twig
{% include 'extensions/HijriCalendar/templates/hijri-converter.twig' %}
```

### **Calendar Display**
```twig
{% include 'extensions/HijriCalendar/templates/hijri-calendar.twig' %}
```

### **Islamic Events Widget**
```twig
{% include 'extensions/HijriCalendar/templates/islamic-events.twig' %}
```

### **Lunar Phases**
```twig
{% include 'extensions/HijriCalendar/templates/lunar-phases.twig' %}
```

## 🔧 **API Reference**

### **Date Conversion API**
```php
use IslamWiki\Extensions\HijriCalendar\Services\HijriDateConverter;

$converter = new HijriDateConverter();
$hijriDate = $converter->gregorianToHijri(2024, 12, 19);
$gregorianDate = $converter->hijriToGregorian(1446, 6, 15);
```

### **Calendar Service API**
```php
use IslamWiki\Extensions\HijriCalendar\Services\CalendarService;

$calendarService = new CalendarService();
$events = $calendarService->getEventsForDate(1446, 6, 15);
$monthData = $calendarService->getMonthData(1446, 6);
```

### **Event Management API**
```php
use IslamWiki\Extensions\HijriCalendar\Services\EventService;

$eventService = new EventService();
$eventService->createEvent('Eid al-Fitr', 10, 1, 'Religious');
$eventService->getEventsByType('Religious');
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

#### **2. Date Calculation Engine**
The extension implements advanced astronomical algorithms for accurate Hijri date calculations:
- **Julian Day Number calculations** for precise date conversions
- **Lunar visibility algorithms** for new moon determination
- **Astronomical formulas** for Hijri year, month, and day calculations
- **Edge case handling** for extreme latitudes and dates

#### **3. Multi-Layer Caching System**
The extension implements a sophisticated caching strategy:
- **Memory Cache**: Fastest access for frequently used data
- **Redis Cache**: Distributed caching for multiple instances
- **Database Cache**: Persistent storage for large datasets
- **Intelligent invalidation** for optimal performance

#### **4. Event Management System**
Comprehensive Islamic event tracking and management:
- **Static events** with fixed Hijri dates
- **Dynamic events** with calculated dates
- **Custom events** for community use
- **Event categorization** and filtering

## 🎨 **Customization**

### **CSS Customization**
```css
/* Custom Hijri calendar styling */
.hijri-calendar {
    background: var(--islamic-cream);
    border: 2px solid var(--islamic-green);
    border-radius: var(--radius-lg);
}

/* Custom event styling */
.islamic-event {
    background: var(--islamic-green);
    color: var(--islamic-white);
    border-radius: var(--radius-md);
}
```

### **Template Customization**
Copy templates from `templates/` to your theme directory and modify as needed.

### **Event Customization**
```php
// Custom event configuration
$eventConfig = [
    'custom_events' => [
        'local_holiday' => [
            'name' => 'Local Islamic Holiday',
            'type' => 'Cultural',
            'is_public' => true
        ]
    ]
];
```

## 🧪 **Testing**

### **Test Checklist**
- [ ] Hijri date calculation accuracy
- [ ] Date conversion between calendars
- [ ] Islamic event calculation and display
- [ ] Calendar widget functionality
- [ ] Admin interface operations
- [ ] Performance with large date ranges
- [ ] Caching system effectiveness
- [ ] API endpoint reliability

### **Testing Tools**
- **Date conversion tester** for accuracy validation
- **Calendar display validator** for UI testing
- **Event calculation tool** for event verification
- **Performance testing** with large datasets

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Date Calculations Inaccurate**
- Check calculation method configuration
- Verify timezone settings
- Review astronomical algorithm settings
- Check for edge case handling

#### **Calendar Not Displaying**
- Verify extension is properly loaded
- Check template paths and includes
- Review CSS and JavaScript loading
- Check browser console for errors

#### **Events Not Showing**
- Verify event configuration
- Check date range settings
- Review event visibility settings
- Test event creation and retrieval

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
  - **TECHNICAL_ARCHITECTURE.md**: Complete technical documentation
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
- **External API integration** for enhanced accuracy
- **Advanced astronomical calculations** for extreme locations
- **Community event sharing** and collaboration
- **Mobile app integration** for offline access
- **Advanced calendar visualization** with 3D features

### **Long-term Goals**
- **AI-powered date optimization** with machine learning
- **Global Hijri database** with community contributions
- **Advanced notification system** with smart scheduling
- **Integration with Islamic apps** and services

### **Technical Roadmap**
- **Microservices architecture** for better scalability
- **Event-driven architecture** for real-time updates
- **Advanced caching strategies** for large datasets
- **Machine learning integration** for intelligent features

## 🤝 **Contributing**

We welcome contributions to improve the HijriCalendar extension:

1. **Report issues** with detailed descriptions and steps to reproduce
2. **Suggest improvements** for date calculations and calendar features
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
- **GitHub**: [HijriCalendar Extension Repository](https://github.com/islamwiki/HijriCalendar)
- **Documentation**: [Extension Documentation](https://islamwiki.org/extensions/HijriCalendar)
- **Community**: [IslamWiki Community Forum](https://community.islamwiki.org)

## 📄 **License**

This extension is part of IslamWiki and follows the same licensing terms.

## 🙏 **Acknowledgments**

- **Islamic scholars** for Hijri calendar methodologies
- **Astronomical community** for lunar calculation algorithms
- **Open source contributors** for various calculation libraries
- **IslamWiki community** for testing and feedback

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building accurate Islamic calendar tools for the digital age.* 
# SalahTime Extension

A comprehensive prayer time system for IslamWiki that provides accurate prayer time calculations, qibla direction, lunar phase tracking, and Hijri calendar integration.

## 🌟 **Features**

### **Prayer Time Calculations**
- **Multiple calculation methods**: MWL, ISNA, EGYPT, MAKKAH, KARACHI, TEHRAN, JAFARI
- **High accuracy**: Astronomical algorithms for precise calculations
- **Location-based**: Automatic timezone and DST detection
- **Seasonal adjustments**: Special handling for extreme latitudes

### **Location Services**
- **GPS integration**: Mobile device location detection
- **Multiple locations**: Support for users with multiple addresses
- **Timezone handling**: Automatic DST and timezone detection
- **Location validation**: Comprehensive error checking

### **Qibla Direction**
- **3D visualization**: Interactive qibla direction display
- **Compass integration**: Mobile device compass support
- **Distance calculation**: Accurate distance to Kaaba
- **Multiple methods**: Different calculation methods for various regions

### **Lunar & Islamic Calendar**
- **Lunar phase tracking**: Accurate moon phase calculations
- **Hijri calendar**: Islamic date conversion and display
- **Islamic events**: Tracking of important Islamic dates
- **Visual display**: Beautiful lunar phase visualization

### **Widget System**
- **Prayer time widgets**: Easy integration into pages
- **Customizable display**: Multiple display options
- **Responsive design**: Works on all device sizes
- **Multiple types**: Various widget configurations

## 🚀 **Installation**

### **Automatic Installation**
The SalahTime extension is automatically loaded by the IslamWiki extension system.

### **Manual Verification**
1. Check that prayer time calculations are working
2. Verify location services functionality
3. Test qibla direction calculations
4. Confirm widget display on pages

## ⚙️ **Configuration**

### **Basic Configuration**
```json
{
    "config": {
        "enableSalahCalculation": true,
        "enableSalahDisplay": true,
        "enableQiblaDirection": true,
        "enableLunarPhase": true,
        "defaultCalculationMethod": "MWL",
        "enableLocationServices": true
    }
}
```

### **Calculation Methods**
- **MWL**: Muslim World League
- **ISNA**: Islamic Society of North America
- **EGYPT**: Egyptian General Authority of Survey
- **MAKKAH**: Umm Al-Qura University, Makkah
- **KARACHI**: University of Islamic Sciences, Karachi
- **TEHRAN**: Institute of Geophysics, Tehran
- **JAFARI**: Shia Ithna Ashari

## 📱 **Usage**

### **Basic Prayer Times**
```twig
{% include 'extensions/SalahTime/templates/salah-times.twig' %}
```

### **Qibla Direction**
```twig
{% include 'extensions/SalahTime/templates/qibla-direction.twig' %}
```

### **Lunar Phase**
```twig
{% include 'extensions/SalahTime/templates/lunar-phase.twig' %}
```

### **Prayer Time Widget**
```twig
{% include 'extensions/SalahTime/templates/salah-widget.twig' %}
```

## 🔧 **API Reference**

### **Prayer Time Calculation**
```php
use IslamWiki\Extensions\SalahTime\Services\SalahTimeService;

$salahService = new SalahTimeService();
$prayerTimes = $salahService->calculatePrayerTimes($latitude, $longitude, $date, $method);
```

### **Qibla Direction**
```php
use IslamWiki\Extensions\SalahTime\Services\QiblaService;

$qiblaService = new QiblaService();
$qiblaDirection = $qiblaService->calculateQiblaDirection($latitude, $longitude);
```

### **Lunar Phase**
```php
use IslamWiki\Extensions\SalahTime\Services\LunarPhaseService;

$lunarService = new LunarPhaseService();
$lunarPhase = $lunarService->getLunarPhase($date);
```

## 🎨 **Customization**

### **CSS Customization**
```css
/* Custom prayer time styling */
.salah-times {
    background: var(--islamic-cream);
    border: 2px solid var(--islamic-green);
    border-radius: var(--radius-lg);
}

/* Custom qibla direction styling */
.qibla-direction {
    background: linear-gradient(135deg, var(--islamic-green), var(--islamic-dark-green));
    color: var(--islamic-white);
}
```

### **Template Customization**
Copy templates from `templates/` to your theme directory and modify as needed.

## 🧪 **Testing**

### **Test Checklist**
- [ ] Prayer time calculations accuracy
- [ ] Timezone handling for different regions
- [ ] Qibla direction calculations
- [ ] Lunar phase accuracy
- [ ] Widget display on various pages
- [ ] Admin interface functionality
- [ ] Location services reliability

### **Testing Tools**
- **Prayer time calculator**: Manual verification tool
- **Location testing**: Different coordinate testing
- **Widget testing**: Various page layout testing
- **Admin testing**: All administrative functions

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Prayer Times Not Displaying**
- Check if extension is properly loaded
- Verify template paths are correct
- Check browser console for JavaScript errors

#### **Location Services Not Working**
- Ensure HTTPS is enabled (required for GPS)
- Check browser permissions for location access
- Verify timezone data is available

#### **Qibla Direction Inaccurate**
- Check coordinate accuracy
- Verify calculation method is appropriate for region
- Test with known coordinates

### **Debug Mode**
Enable debug logging in the extension configuration:
```json
{
    "config": {
        "enableDebugLogging": true
    }
}
```

## 📚 **Documentation**

### **Available Resources**
- **README.md**: This file with basic information
- **CHANGELOG.md**: Complete version history
- **docs/**: Comprehensive documentation folder
  - **RELEASE-0.0.2.md**: Detailed release notes
  - **INSTALLATION.md**: Installation guide
  - **CONFIGURATION.md**: Configuration guide
  - **API_REFERENCE.md**: Complete API documentation
  - **TROUBLESHOOTING.md**: Troubleshooting guide

### **Code Documentation**
- **Inline comments**: Detailed code documentation
- **PHPDoc blocks**: Complete API documentation
- **Example code**: Working examples for common use cases

## 🔮 **Future Plans**

### **Upcoming Features**
- **External API integration** for enhanced accuracy
- **Prayer time notifications** and reminders
- **Advanced astronomical calculations** for extreme locations
- **Community prayer time sharing** features

### **Long-term Goals**
- **Machine learning** for prayer time optimization
- **Community-driven** prayer time data
- **Advanced notification system** with smart scheduling
- **Integration with Islamic apps** and services

## 🤝 **Contributing**

We welcome contributions to improve the SalahTime extension:

1. **Report issues** with detailed descriptions
2. **Suggest improvements** for calculations and UI
3. **Contribute code** for bug fixes and features
4. **Submit pull requests** for enhancements

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📞 **Support**

### **Getting Help**
- **Documentation**: Check the docs folder first
- **Issue reporting**: Use GitHub issues for bugs
- **Community support**: Contact IslamWiki community
- **Development team**: Contact the development team

### **Contact Information**
- **GitHub**: [SalahTime Extension Repository](https://github.com/islamwiki/SalahTime)
- **Documentation**: [Extension Documentation](https://islamwiki.org/extensions/SalahTime)
- **Community**: [IslamWiki Community Forum](https://community.islamwiki.org)

## 📄 **License**

This extension is part of IslamWiki and follows the same licensing terms.

## 🙏 **Acknowledgments**

- **Islamic scholars** for prayer time calculation methods
- **Astronomical community** for lunar phase algorithms
- **Open source contributors** for various calculation libraries
- **IslamWiki community** for testing and feedback

---

**Bismillah** - In the name of Allah, the Most Gracious, the Most Merciful

*Building accurate Islamic tools for the digital age.* 
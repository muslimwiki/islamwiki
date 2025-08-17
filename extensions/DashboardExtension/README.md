# DashboardExtension

A comprehensive, role-based dashboard system for IslamWiki that provides personalized experiences for different user roles including Admin, Scholar, Contributor, and basic Users.

## 🚀 Features

### **Role-Based Dashboards**
- **Admin Dashboard**: System administration, user management, content moderation
- **Scholar Dashboard**: Academic tools, research resources, scholarly content
- **Contributor Dashboard**: Content creation tools, contribution tracking
- **User Dashboard**: Learning progress, personalized recommendations, community updates

### **Core Components**
- **Dashboard Controller**: Handles role detection and template rendering
- **Dashboard Service**: Provides role-specific data and business logic
- **Service Provider**: Registers dashboard services with the application
- **Frontend Assets**: CSS and JavaScript for interactive dashboard experience

### **Widget System**
- **User Overview**: Personal statistics and progress tracking
- **Content Statistics**: Platform-wide content metrics
- **Recent Activity**: User and system activity timeline
- **System Status**: Platform health and performance indicators
- **Islamic Calendar**: Hijri date display and Islamic events
- **Prayer Times**: Current prayer time information
- **Quran Verse**: Daily Quran verse display
- **Hadith Quote**: Inspirational hadith quotes
- **Quick Actions**: Common task shortcuts
- **Notifications**: User notification center

## 📁 Structure

```
DashboardExtension/
├── DashboardExtension.php          # Main extension class
├── DashboardServiceProvider.php    # Service registration
├── extension.json                  # Extension metadata and configuration
├── config/
│   └── dashboard_config.php       # Role-based dashboard configurations
├── Controllers/
│   └── DashboardController.php    # Dashboard logic and template rendering
├── Services/
│   └── DashboardService.php       # Business logic and data services
├── assets/
│   ├── css/
│   │   └── dashboard.css         # Dashboard styling
│   └── js/
│       └── dashboard.js          # Dashboard interactivity
├── templates/                     # Role-specific dashboard templates
│   ├── admin_dashboard.twig
│   ├── scholar_dashboard.twig
│   ├── contributor_dashboard.twig
│   └── user_dashboard.twig
├── docs/                         # Extension documentation
├── CHANGELOG.md                  # Version history
└── README.md                     # This file
```

## 🔧 Installation

1. **Copy Extension**: Place the `DashboardExtension` folder in your `extensions/` directory
2. **Register Extension**: Add to your application's extension registry
3. **Activate**: Enable the extension through your admin panel
4. **Configure**: Set up role-based permissions and dashboard layouts

## ⚙️ Configuration

### **Extension Configuration** (`extension.json`)
```json
{
    "name": "DashboardExtension",
    "version": "0.0.1",
    "description": "Role-based dashboard system for IslamWiki",
    "author": "IslamWiki Team",
    "hooks": [
        "DashboardInit",
        "DashboardRender",
        "DashboardWidget",
        "UserStats",
        "ContentStats",
        "SystemStatus"
    ],
    "permissions": {
        "admin": ["full_access"],
        "scholar": ["dashboard_access", "content_management"],
        "contributor": ["dashboard_access", "content_creation"],
        "user": ["dashboard_access", "basic_features"]
    }
}
```

### **Role-Based Dashboard Config** (`config/dashboard_config.php`)
```php
return [
    'admin' => [
        'template' => 'admin_dashboard',
        'widgets' => ['system_overview', 'user_management', 'content_moderation'],
        'permissions' => ['full_access']
    ],
    'scholar' => [
        'template' => 'scholar_dashboard',
        'widgets' => ['academic_tools', 'research_resources'],
        'permissions' => ['content_management', 'academic_features']
    ],
    // ... more role configurations
];
```

## 🎨 Dashboard Templates

### **Admin Dashboard** (`admin_dashboard.twig`)
- System overview with metrics
- User management tools
- Content moderation queue
- System status monitoring
- Quick action buttons for common tasks

### **Scholar Dashboard** (`scholar_dashboard.twig`)
- Academic research tools
- Islamic sciences overview
- Content contribution tracking
- Scholarly community updates

### **Contributor Dashboard** (`contributor_dashboard.twig`)
- Content creation tools
- Contribution statistics
- Recent edits and changes
- Community guidelines

### **User Dashboard** (`user_dashboard.twig`)
- Learning progress tracking
- Personalized recommendations
- Recent activity timeline
- Quick access to Islamic content

## 🎯 Usage

### **Accessing Dashboards**
- **Admin**: `/dashboard` (redirects to admin dashboard)
- **Scholar**: `/dashboard` (redirects to scholar dashboard)
- **Contributor**: `/dashboard` (redirects to contributor dashboard)
- **User**: `/dashboard` (redirects to user dashboard)

### **Role Detection**
The extension automatically detects user roles and renders appropriate dashboards:
```php
// In DashboardController
$userRole = $this->determineUserRole($user);
$template = $this->getRoleBasedTemplate($userRole);
```

### **Customization**
- **Widgets**: Add/remove widgets per role in configuration
- **Layouts**: Modify dashboard layouts in template files
- **Styling**: Customize appearance through CSS files
- **Functionality**: Extend dashboard features through service classes

## 🔌 Hooks and Integration

### **Available Hooks**
- `DashboardInit`: Initialize dashboard data
- `DashboardRender`: Customize dashboard rendering
- `DashboardWidget`: Add custom widgets
- `UserStats`: Provide user statistics
- `ContentStats`: Provide content metrics
- `SystemStatus`: Provide system health data

### **Integration Points**
- **Authentication System**: User role detection
- **Content Management**: Article and page statistics
- **User Management**: User activity and contributions
- **System Monitoring**: Platform health and performance

## 🎨 Styling and Themes

### **CSS Framework**
- **Islamic Color Scheme**: Green, gold, and cream color palette
- **Responsive Design**: Mobile-first approach with grid layouts
- **Modern UI**: Card-based design with shadows and hover effects
- **Accessibility**: High contrast and readable typography

### **JavaScript Features**
- **Auto-refresh**: Dashboard data updates automatically
- **Interactive Elements**: Hover effects and smooth transitions
- **Responsive Behavior**: Adapts to different screen sizes
- **User Experience**: Smooth scrolling and intuitive navigation

## 🚀 Development

### **Adding New Widgets**
1. Create widget template in `templates/` directory
2. Add widget data in `DashboardService`
3. Include widget in role configuration
4. Style widget with CSS

### **Extending Dashboards**
1. Modify existing templates or create new ones
2. Add new services for additional functionality
3. Update configuration files for new features
4. Test with different user roles

### **Custom Roles**
1. Define new role in configuration
2. Create role-specific dashboard template
3. Set appropriate permissions
4. Add role-specific widgets and features

## 📊 Performance

### **Optimization Features**
- **Lazy Loading**: Widgets load data on demand
- **Caching**: Dashboard data is cached for performance
- **Minimal Queries**: Efficient database queries for dashboard data
- **Responsive Images**: Optimized image loading and display

### **Monitoring**
- **Dashboard Load Times**: Performance metrics tracking
- **User Engagement**: Dashboard usage analytics
- **System Resources**: Memory and CPU usage monitoring

## 🔒 Security

### **Permission System**
- **Role-Based Access**: Different dashboards for different user levels
- **Data Isolation**: Users only see data they're authorized to access
- **Input Validation**: All user inputs are properly validated
- **XSS Protection**: Output is properly escaped and sanitized

### **Authentication**
- **Session Management**: Secure user session handling
- **Role Verification**: User roles are verified on each request
- **Access Control**: Dashboard access is controlled by user permissions

## 🐛 Troubleshooting

### **Common Issues**
1. **Dashboard Not Loading**: Check user permissions and role configuration
2. **Widgets Missing**: Verify widget configuration in dashboard config
3. **Styling Issues**: Check CSS file paths and browser console for errors
4. **Performance Problems**: Monitor database queries and enable caching

### **Debug Mode**
Enable debug mode to see detailed error information:
```php
// In DashboardController
error_log("User role: " . $userRole);
error_log("Template: " . $template);
```

## 📈 Roadmap

### **Future Features**
- **Advanced Analytics**: Detailed user behavior tracking
- **Custom Widgets**: User-configurable dashboard layouts
- **Mobile App**: Native mobile dashboard application
- **AI Recommendations**: Machine learning-based content suggestions
- **Multi-language Support**: Dashboard localization for different languages

### **Performance Improvements**
- **Progressive Web App**: Offline dashboard functionality
- **Real-time Updates**: WebSocket-based live dashboard updates
- **Advanced Caching**: Redis-based dashboard data caching
- **CDN Integration**: Global content delivery for dashboard assets

## 🤝 Contributing

### **Development Setup**
1. Fork the repository
2. Create feature branch
3. Make changes and test thoroughly
4. Submit pull request with detailed description

### **Code Standards**
- **PHP**: PSR-12 coding standards
- **Twig**: Consistent template structure
- **CSS**: BEM methodology for class naming
- **JavaScript**: ES6+ with proper error handling

### **Testing**
- **Unit Tests**: Test individual components
- **Integration Tests**: Test dashboard workflows
- **Browser Tests**: Test across different browsers
- **Performance Tests**: Monitor dashboard performance

## 📄 License

This extension is part of the IslamWiki project and follows the same licensing terms.

## 🆘 Support

### **Documentation**
- **User Guide**: Complete dashboard usage instructions
- **Developer Guide**: Technical implementation details
- **API Reference**: Dashboard service and controller documentation

### **Community**
- **Forum**: Community support and discussions
- **Issues**: Bug reports and feature requests
- **Discussions**: General questions and help

---

**DashboardExtension** - Empowering users with personalized Islamic knowledge dashboards 🚀 
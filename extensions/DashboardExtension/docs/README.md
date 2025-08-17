# DashboardExtension Documentation

## Overview

The DashboardExtension is a comprehensive, role-based dashboard system for IslamWiki that provides personalized experiences for different user roles. It automatically detects user roles and renders appropriate dashboards with role-specific widgets and functionality.

## 🎯 Core Features

### Role-Based Dashboards
- **Admin Dashboard**: Full system administration capabilities
- **Scholar Dashboard**: Academic and research tools
- **Contributor Dashboard**: Content creation and management
- **User Dashboard**: Learning progress and personal features

### Smart Role Detection
The extension automatically identifies user roles and serves appropriate dashboards:
```php
// Automatic role detection
$userRole = $this->determineUserRole($user);
$template = $this->getRoleBasedTemplate($userRole);
```

### Responsive Design
- Mobile-first approach
- Grid-based layouts
- Islamic-themed color scheme
- Smooth animations and transitions

## 🏗️ Architecture

### File Structure
```
DashboardExtension/
├── DashboardExtension.php          # Main extension class
├── DashboardServiceProvider.php    # Service registration
├── extension.json                  # Extension configuration
├── config/
│   └── dashboard_config.php       # Role configurations
├── Controllers/
│   └── DashboardController.php    # Dashboard logic
├── Services/
│   └── DashboardService.php       # Business logic
├── assets/
│   ├── css/dashboard.css         # Styling
│   └── js/dashboard.js          # Interactivity
└── templates/                     # Role-specific templates
    ├── admin_dashboard.twig
    ├── scholar_dashboard.twig
    ├── contributor_dashboard.twig
    └── user_dashboard.twig
```

### Core Classes

#### DashboardExtension.php
Main extension class that:
- Registers hooks with the framework
- Initializes dashboard components
- Manages extension lifecycle

#### DashboardController.php
Handles HTTP requests and:
- Determines user roles
- Fetches role-specific data
- Renders appropriate templates
- Manages dashboard state

#### DashboardService.php
Provides business logic for:
- User statistics and metrics
- Content analytics
- System status information
- Role-specific data aggregation

## ⚙️ Configuration

### Extension Configuration (extension.json)
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

### Role Configuration (config/dashboard_config.php)
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
    'contributor' => [
        'template' => 'contributor_dashboard',
        'widgets' => ['content_creation', 'contribution_stats'],
        'permissions' => ['content_creation', 'basic_features']
    ],
    'user' => [
        'template' => 'user_dashboard',
        'widgets' => ['learning_progress', 'recommendations'],
        'permissions' => ['basic_features']
    ]
];
```

## 🎨 Dashboard Templates

### Admin Dashboard (admin_dashboard.twig)
**Features:**
- System overview with key metrics
- User management tools
- Content moderation queue
- System status monitoring
- Quick action buttons

**Widgets:**
- System Overview (Large)
- User Management (Medium)
- Content Moderation (Medium)
- System Status (Small)
- Analytics Dashboard (Large)

### Scholar Dashboard (scholar_dashboard.twig)
**Features:**
- Academic research tools
- Islamic sciences overview
- Content contribution tracking
- Scholarly community updates

**Widgets:**
- Academic Tools
- Research Resources
- Content Management
- Community Updates

### Contributor Dashboard (contributor_dashboard.twig)
**Features:**
- Content creation tools
- Contribution statistics
- Recent edits and changes
- Community guidelines

**Widgets:**
- Content Creation
- Contribution Stats
- Recent Activity
- Quick Actions

### User Dashboard (user_dashboard.twig)
**Features:**
- Learning progress tracking
- Personalized recommendations
- Recent activity timeline
- Quick access to Islamic content

**Widgets:**
- Learning Progress
- Recent Activity
- Quick Actions
- Recommendations
- Community Updates
- Daily Inspiration
- Upcoming Events

## 🔌 Hooks and Integration

### Available Hooks
```php
// Dashboard initialization
$this->framework->hook('DashboardInit', [$this, 'initializeDashboard']);

// Dashboard rendering
$this->framework->hook('DashboardRender', [$this, 'renderDashboard']);

// Widget management
$this->framework->hook('DashboardWidget', [$this, 'manageWidgets']);

// User statistics
$this->framework->hook('UserStats', [$this, 'getUserStats']);

// Content statistics
$this->framework->hook('ContentStats', [$this, 'getContentStats']);

// System status
$this->framework->hook('SystemStatus', [$this, 'getSystemStatus']);
```

### Integration Points
- **Authentication System**: User role detection
- **Content Management**: Article and page statistics
- **User Management**: User activity and contributions
- **System Monitoring**: Platform health and performance

## 🎯 Widget System

### Widget Types
1. **System Overview**: Platform-wide metrics
2. **User Management**: User administration tools
3. **Content Moderation**: Content review and approval
4. **System Status**: Platform health indicators
5. **Learning Progress**: User learning journey
6. **Recent Activity**: User and system activity
7. **Quick Actions**: Common task shortcuts
8. **Recommendations**: Personalized content suggestions
9. **Community Updates**: Community news and events
10. **Daily Inspiration**: Motivational Islamic content

### Widget Configuration
```php
// Widget configuration example
'widgets' => [
    'system_overview' => [
        'name' => 'System Overview',
        'description' => 'Platform-wide system metrics',
        'size' => 'large',
        'refreshable' => true,
        'refresh_interval' => 300
    ]
]
```

## 🎨 Styling and Themes

### CSS Framework
- **Islamic Color Scheme**: Green (#2d5016), Gold (#d4af37), Cream (#f8f6f0)
- **Responsive Grid**: CSS Grid and Flexbox layouts
- **Modern UI**: Card-based design with shadows
- **Hover Effects**: Smooth transitions and animations

### JavaScript Features
- **Auto-refresh**: Dashboard data updates automatically
- **Interactive Elements**: Hover effects and smooth transitions
- **Responsive Behavior**: Adapts to different screen sizes
- **User Experience**: Smooth scrolling and intuitive navigation

## 🚀 Development

### Adding New Widgets
1. **Create Widget Template**
```twig
<!-- templates/new_widget.twig -->
<div class="dashboard-widget new-widget">
    <div class="widget-header">
        <h3>New Widget</h3>
    </div>
    <div class="widget-content">
        <!-- Widget content here -->
    </div>
</div>
```

2. **Add Widget Data in Service**
```php
// In DashboardService.php
public function getNewWidgetData($userId) {
    // Fetch and return widget data
    return [
        'title' => 'New Widget',
        'data' => $this->fetchData($userId)
    ];
}
```

3. **Include in Role Configuration**
```php
// In dashboard_config.php
'admin' => [
    'widgets' => ['system_overview', 'new_widget'],
    // ... other config
]
```

4. **Style Widget**
```css
/* In dashboard.css */
.new-widget {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
```

### Extending Dashboards
1. **Modify Templates**: Edit existing templates or create new ones
2. **Add Services**: Create new services for additional functionality
3. **Update Configuration**: Modify role configurations for new features
4. **Test Roles**: Verify functionality with different user roles

### Custom Roles
1. **Define Role**: Add new role in configuration
2. **Create Template**: Build role-specific dashboard template
3. **Set Permissions**: Define appropriate access levels
4. **Add Widgets**: Include role-specific widgets and features

## 📊 Performance

### Optimization Features
- **Lazy Loading**: Widgets load data on demand
- **Caching**: Dashboard data is cached for performance
- **Minimal Queries**: Efficient database queries for dashboard data
- **Responsive Images**: Optimized image loading and display

### Monitoring
- **Dashboard Load Times**: Performance metrics tracking
- **User Engagement**: Dashboard usage analytics
- **System Resources**: Memory and CPU usage monitoring

## 🔒 Security

### Permission System
- **Role-Based Access**: Different dashboards for different user levels
- **Data Isolation**: Users only see data they're authorized to access
- **Input Validation**: All user inputs are properly validated
- **XSS Protection**: Output is properly escaped and sanitized

### Authentication
- **Session Management**: Secure user session handling
- **Role Verification**: User roles are verified on each request
- **Access Control**: Dashboard access is controlled by user permissions

## 🐛 Troubleshooting

### Common Issues
1. **Dashboard Not Loading**
   - Check user permissions and role configuration
   - Verify template files exist and are accessible
   - Check browser console for JavaScript errors

2. **Widgets Missing**
   - Verify widget configuration in dashboard config
   - Check if widget templates exist
   - Ensure user has permission to view widgets

3. **Styling Issues**
   - Check CSS file paths and browser console for errors
   - Verify CSS classes are properly applied
   - Check for CSS conflicts with other extensions

4. **Performance Problems**
   - Monitor database queries and enable caching
   - Check widget refresh intervals
   - Optimize data fetching in services

### Debug Mode
Enable debug mode to see detailed error information:
```php
// In DashboardController
error_log("User role: " . $userRole);
error_log("Template: " . $template);
error_log("User permissions: " . json_encode($userPermissions));
```

## 📈 Roadmap

### Future Features
- **Advanced Analytics**: Detailed user behavior tracking
- **Custom Widgets**: User-configurable dashboard layouts
- **Mobile App**: Native mobile dashboard application
- **AI Recommendations**: Machine learning-based content suggestions
- **Multi-language Support**: Dashboard localization for different languages

### Performance Improvements
- **Progressive Web App**: Offline dashboard functionality
- **Real-time Updates**: WebSocket-based live dashboard updates
- **Advanced Caching**: Redis-based dashboard data caching
- **CDN Integration**: Global content delivery for dashboard assets

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Submit pull request with detailed description

### Code Standards
- **PHP**: PSR-12 coding standards
- **Twig**: Consistent template structure
- **CSS**: BEM methodology for class naming
- **JavaScript**: ES6+ with proper error handling

### Testing
- **Unit Tests**: Test individual components
- **Integration Tests**: Test dashboard workflows
- **Browser Tests**: Test across different browsers
- **Performance Tests**: Monitor dashboard performance

## 📄 License

This extension is part of the IslamWiki project and follows the same licensing terms.

## 🆘 Support

### Documentation
- **User Guide**: Complete dashboard usage instructions
- **Developer Guide**: Technical implementation details
- **API Reference**: Dashboard service and controller documentation

### Community
- **Forum**: Community support and discussions
- **Issues**: Bug reports and feature requests
- **Discussions**: General questions and help

---

**DashboardExtension Documentation** - Complete guide to the role-based dashboard system 🚀 
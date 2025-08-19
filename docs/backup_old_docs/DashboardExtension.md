# DashboardExtension

## Overview

The **DashboardExtension** is a comprehensive, role-based dashboard system for IslamWiki that provides personalized experiences for different user roles. It automatically detects user roles and renders appropriate dashboards with role-specific widgets and functionality.

**Version**: 0.0.1  
**Status**: Active  
**Last Updated**: September 2024  
**Requires**: IslamWiki Core >= 0.0.18

## 🎯 Key Features

### **Role-Based Dashboard System**
- **Admin Dashboard**: Full system administration capabilities
- **Scholar Dashboard**: Academic and research tools  
- **Contributor Dashboard**: Content creation and management
- **User Dashboard**: Learning progress and personal features

### **Smart Role Detection**
- Automatic user role identification
- Dynamic template selection based on permissions
- Seamless user experience across different access levels

### **Comprehensive Widget System**
- 10+ pre-built widgets for different purposes
- Role-specific widget configurations
- Responsive and interactive design

## 🏗️ Architecture

### **Core Components**

#### **DashboardExtension.php**
- Main extension class
- Hook registration and management
- Extension lifecycle management

#### **DashboardController.php**
- HTTP request handling
- Role detection and template rendering
- Dashboard state management

#### **DashboardService.php**
- Business logic implementation
- Data aggregation and processing
- Role-specific data services

#### **DashboardServiceProvider.php**
- Service registration with the framework
- Dependency injection setup

### **File Structure**
```
DashboardExtension/
├── DashboardExtension.php          # Main extension class
├── DashboardServiceProvider.php    # Service registration
├── extension.json                  # Extension metadata
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

## 🎨 Dashboard Templates

### **Admin Dashboard**
**Purpose**: System administration and monitoring  
**Key Features**:
- System overview with key metrics
- User management tools
- Content moderation queue
- System status monitoring
- Quick action buttons

**Widgets**:
- System Overview (Large)
- User Management (Medium)
- Content Moderation (Medium)
- System Status (Small)
- Analytics Dashboard (Large)

### **Scholar Dashboard**
**Purpose**: Academic research and scholarly content  
**Key Features**:
- Academic research tools
- Islamic sciences overview
- Content contribution tracking
- Scholarly community updates

**Widgets**:
- Academic Tools
- Research Resources
- Content Management
- Community Updates

### **Contributor Dashboard**
**Purpose**: Content creation and contribution management  
**Key Features**:
- Content creation tools
- Contribution statistics
- Recent edits and changes
- Community guidelines

**Widgets**:
- Content Creation
- Contribution Stats
- Recent Activity
- Quick Actions

### **User Dashboard**
**Purpose**: Learning progress and personal features  
**Key Features**:
- Learning progress tracking
- Personalized recommendations
- Recent activity timeline
- Quick access to Islamic content

**Widgets**:
- Learning Progress
- Recent Activity
- Quick Actions
- Recommendations
- Community Updates
- Daily Inspiration
- Upcoming Events

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

### **Role Configuration** (`config/dashboard_config.php`)
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

## 🔌 Integration

### **Hooks and Events**
The extension integrates with IslamWiki through a comprehensive hook system:

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

### **Integration Points**
- **Authentication System**: User role detection and verification
- **Content Management**: Article and page statistics
- **User Management**: User activity and contribution tracking
- **System Monitoring**: Platform health and performance metrics

## 🎯 Widget System

### **Available Widgets**

#### **System Overview Widget**
- Platform-wide metrics and statistics
- User counts and activity levels
- Content growth and engagement
- System health indicators

#### **User Management Widget**
- User administration tools
- Role management interface
- User activity monitoring
- Permission management

#### **Content Moderation Widget**
- Content review queue
- Flagged content management
- Moderation statistics
- Content approval workflow

#### **Learning Progress Widget**
- User learning journey tracking
- Progress visualization
- Goal setting and achievement
- Learning recommendations

#### **Recent Activity Widget**
- User activity timeline
- Content changes and updates
- Community interactions
- System notifications

### **Widget Configuration**
```php
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

### **Design Philosophy**
- **Islamic Theme**: Green, gold, and cream color palette
- **Modern UI**: Card-based design with shadows and hover effects
- **Responsive Design**: Mobile-first approach with grid layouts
- **Accessibility**: High contrast and readable typography

### **CSS Framework**
- **Color Scheme**: 
  - Primary: #2d5016 (Islamic Green)
  - Secondary: #d4af37 (Islamic Gold)
  - Background: #f8f6f0 (Islamic Cream)
- **Layout System**: CSS Grid and Flexbox
- **Components**: Card-based widgets with consistent styling
- **Animations**: Smooth transitions and hover effects

### **JavaScript Features**
- **Auto-refresh**: Dashboard data updates automatically
- **Interactive Elements**: Hover effects and smooth transitions
- **Responsive Behavior**: Adapts to different screen sizes
- **User Experience**: Smooth scrolling and intuitive navigation

## 🚀 Development

### **Adding New Widgets**

#### **1. Create Widget Template**
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

#### **2. Add Widget Data in Service**
```php
// In DashboardService.php
public function getNewWidgetData($userId) {
    return [
        'title' => 'New Widget',
        'data' => $this->fetchData($userId)
    ];
}
```

#### **3. Include in Role Configuration**
```php
// In dashboard_config.php
'admin' => [
    'widgets' => ['system_overview', 'new_widget'],
    // ... other config
]
```

#### **4. Style Widget**
```css
/* In dashboard.css */
.new-widget {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
```

### **Extending Dashboards**
1. **Modify Templates**: Edit existing templates or create new ones
2. **Add Services**: Create new services for additional functionality
3. **Update Configuration**: Modify role configurations for new features
4. **Test Roles**: Verify functionality with different user roles

### **Custom Roles**
1. **Define Role**: Add new role in configuration
2. **Create Template**: Build role-specific dashboard template
3. **Set Permissions**: Define appropriate access levels
4. **Add Widgets**: Include role-specific widgets and features

## 📊 Performance

### **Optimization Features**
- **Lazy Loading**: Widgets load data on demand
- **Caching**: Dashboard data is cached for performance
- **Minimal Queries**: Efficient database queries for dashboard data
- **Responsive Images**: Optimized image loading and display

### **Monitoring and Metrics**
- **Dashboard Load Times**: Performance metrics tracking
- **User Engagement**: Dashboard usage analytics
- **System Resources**: Memory and CPU usage monitoring
- **Database Performance**: Query optimization and monitoring

## 🔒 Security

### **Permission System**
- **Role-Based Access**: Different dashboards for different user levels
- **Data Isolation**: Users only see data they're authorized to access
- **Input Validation**: All user inputs are properly validated
- **XSS Protection**: Output is properly escaped and sanitized

### **Authentication and Authorization**
- **Session Management**: Secure user session handling
- **Role Verification**: User roles are verified on each request
- **Access Control**: Dashboard access is controlled by user permissions
- **Data Privacy**: Sensitive information is properly protected

## 🐛 Troubleshooting

### **Common Issues and Solutions**

#### **Dashboard Not Loading**
- **Check**: User permissions and role configuration
- **Verify**: Template files exist and are accessible
- **Debug**: Browser console for JavaScript errors
- **Solution**: Ensure user has proper role and permissions

#### **Widgets Missing**
- **Check**: Widget configuration in dashboard config
- **Verify**: Widget templates exist
- **Ensure**: User has permission to view widgets
- **Solution**: Update role configuration and permissions

#### **Styling Issues**
- **Check**: CSS file paths and browser console for errors
- **Verify**: CSS classes are properly applied
- **Check**: CSS conflicts with other extensions
- **Solution**: Fix CSS paths and resolve conflicts

#### **Performance Problems**
- **Monitor**: Database queries and enable caching
- **Check**: Widget refresh intervals
- **Optimize**: Data fetching in services
- **Solution**: Implement caching and query optimization

### **Debug Mode**
Enable debug mode for detailed error information:
```php
// In DashboardController
error_log("User role: " . $userRole);
error_log("Template: " . $template);
error_log("User permissions: " . json_encode($userPermissions));
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

### **Security Enhancements**
- **Advanced Role Management**: Granular permission system
- **Audit Logging**: Comprehensive activity tracking
- **Two-Factor Authentication**: Enhanced security for admin access
- **API Security**: Secure dashboard API endpoints

## 🤝 Contributing

### **Development Setup**
1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Submit pull request with detailed description

### **Code Standards**
- **PHP**: PSR-12 coding standards
- **Twig**: Consistent template structure
- **CSS**: BEM methodology for class naming
- **JavaScript**: ES6+ with proper error handling

### **Testing Requirements**
- **Unit Tests**: Test individual components
- **Integration Tests**: Test dashboard workflows
- **Browser Tests**: Test across different browsers
- **Performance Tests**: Monitor dashboard performance

## 📄 License and Support

### **License**
This extension is part of the IslamWiki project and follows the same licensing terms.

### **Support Channels**
- **Documentation**: Complete setup and usage instructions
- **Community Forum**: Community support and discussions
- **Issue Tracker**: Bug reports and feature requests
- **Developer Guide**: Technical implementation details

### **Version History**
- **0.0.1**: Initial release with role-based dashboards
- **Future**: Advanced analytics, custom widgets, mobile app

---

**DashboardExtension** - Empowering users with personalized Islamic knowledge dashboards 🚀

*For detailed technical documentation, see the extension's internal docs folder.* 
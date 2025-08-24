# Role-Based Dashboard CSS System

This directory contains a comprehensive CSS system for creating role-based dashboards in the Bismillah skin. The system automatically applies different styles based on user roles.

## 📁 File Structure

```
skins/Bismillah/css/pages/
├── dashboard.css                    # Base dashboard styles (common to all roles)
├── admin-dashboard.css             # Admin-specific dashboard styles
├── user-dashboard.css              # User-specific dashboard styles  
├── scholar-dashboard.css           # Scholar-specific dashboard styles
├── contributor-dashboard.css       # Contributor-specific dashboard styles
├── dashboard-imports.css           # Role-based imports and class management
└── README.md                       # This documentation file
```

## 🎨 Role-Based Themes

### Admin Dashboard (`admin-dashboard.css`)
- **Color Scheme**: Blue and Gold theme
- **Features**: 
  - Administrative controls and panels
  - System statistics and monitoring
  - User management interfaces
  - Data tables and analytics
  - Control panels and widgets

### User Dashboard (`user-dashboard.css`)
- **Color Scheme**: Green and Teal theme
- **Features**:
  - Learning progress tracking
  - Personal statistics and achievements
  - Recent activity feeds
  - Learning goals and milestones
  - Quick access to learning resources

### Scholar Dashboard (`scholar-dashboard.css`)
- **Color Scheme**: Gold and Dark Blue theme
- **Features**:
  - Knowledge statistics and research areas
  - Research topic management
  - Publication tracking
  - Academic collaboration tools
  - Research progress monitoring

### Contributor Dashboard (`contributor-dashboard.css`)
- **Color Scheme**: Purple and Blue theme
- **Features**:
  - Contribution statistics and tracking
  - Content creation tools
  - Recent contributions history
  - Collaboration management
  - Content idea suggestions

## 🚀 Usage

### 1. Automatic Role Detection

The system automatically detects user roles and applies appropriate styling:

```html
<!-- The dashboard container will automatically get role-based classes -->
<div class="dashboard-container" data-user-role="admin">
    <!-- Dashboard content -->
</div>
```

### 2. Manual Role Application

You can manually apply role-based classes:

```html
<div class="dashboard-container dashboard-admin">
    <!-- Admin dashboard content -->
</div>

<div class="dashboard-container dashboard-user">
    <!-- User dashboard content -->
</div>

<div class="dashboard-container dashboard-scholar">
    <!-- Scholar dashboard content -->
</div>

<div class="dashboard-container dashboard-contributor">
    <!-- Contributor dashboard content -->
</div>
```

### 3. JavaScript Role Switching

Use the included JavaScript to dynamically change roles:

```javascript
// Change user role
dashboardRoleSwitcher.changeRole('admin');

// Check current role
const currentRole = dashboardRoleSwitcher.getCurrentRole();

// Check if user has specific role
if (dashboardRoleSwitcher.isAdmin()) {
    // Show admin features
}
```

## 🎯 CSS Classes

### Role-Based Container Classes
- `.dashboard-admin` - Admin dashboard styling
- `.dashboard-user` - User dashboard styling
- `.dashboard-scholar` - Scholar dashboard styling
- `.dashboard-contributor` - Contributor dashboard styling

### Role-Based Body Classes
- `.role-admin` - Admin role applied to body
- `.role-user` - User role applied to body
- `.role-scholar` - Scholar role applied to body
- `.role-contributor` - Contributor role applied to body

## 🔧 Customization

### Adding New Roles

1. Create a new CSS file: `new-role-dashboard.css`
2. Add it to `dashboard-imports.css`
3. Update the JavaScript role switcher
4. Add role-specific classes and styling

### Modifying Existing Themes

Each role CSS file is self-contained and can be modified independently:

```css
/* In admin-dashboard.css */
.admin-dashboard-header {
    background: linear-gradient(135deg, #your-color 0%, #your-color-2 100%);
}
```

### Custom Variables

Each role has its own CSS custom properties:

```css
.dashboard-admin {
    --dashboard-primary-color: var(--islamic-blue);
    --dashboard-secondary-color: var(--islamic-gold);
    --dashboard-accent-color: var(--islamic-dark-blue);
}
```

## 📱 Responsive Design

All role-based dashboards include responsive design:

- **Desktop**: Full feature set with side-by-side layouts
- **Tablet**: Adjusted grid layouts and spacing
- **Mobile**: Single-column layouts with optimized touch targets

## 🎭 Animations

Each role includes smooth animations:

- **Slide In Up**: Statistics cards and main content
- **Slide In Right**: Widgets and side panels
- **Slide In Left**: Activity feeds and lists

## 🔌 Integration

### With Twig Templates

```twig
{# In your dashboard template #}
<div class="dashboard-container dashboard-{{ user.role|default('user') }}">
    {% if user.role == 'admin' %}
        {% include 'dashboard/admin_dashboard.twig' %}
    {% elseif user.role == 'scholar' %}
        {% include 'dashboard/scholar_dashboard.twig' %}
    {% elseif user.role == 'contributor' %}
        {% include 'dashboard/contributor_dashboard.twig' %}
    {% else %}
        {% include 'dashboard/user_dashboard.twig' %}
    {% endif %}
</div>
```

### With PHP Controllers

```php
// In your DashboardController
public function index()
{
    $userRole = $this->determineUserRole($user);
    
    return $this->render('dashboard/index.twig', [
        'user' => $user,
        'userRole' => $userRole,
        'dashboardData' => $this->getDashboardData($userRole)
    ]);
}
```

## 🎨 Design Principles

1. **Consistency**: All roles follow the same design patterns
2. **Accessibility**: High contrast and readable typography
3. **Performance**: Optimized CSS with minimal redundancy
4. **Maintainability**: Modular structure for easy updates
5. **Scalability**: Easy to add new roles and features

## 🐛 Troubleshooting

### Styles Not Applying
- Check that `dashboard-imports.css` is included in `bismillah.css`
- Verify role classes are applied to dashboard container
- Check browser console for CSS import errors

### Role Detection Issues
- Ensure `data-user-role` attribute is set on dashboard container
- Check JavaScript console for role switcher errors
- Verify user role data is being passed correctly

### Responsive Issues
- Test on different screen sizes
- Check media query breakpoints
- Verify viewport meta tag is set

## 📚 Examples

### Admin Dashboard Example
```html
<div class="dashboard-container dashboard-admin">
    <div class="admin-dashboard-header">
        <h1 class="admin-dashboard-title">Admin Dashboard</h1>
        <div class="admin-status-badge">
            <i class="fas fa-shield-alt"></i>
            Administrator
        </div>
    </div>
    
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <span class="admin-stat-title">Total Users</span>
                <div class="admin-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="admin-stat-value">1,234</div>
        </div>
    </div>
</div>
```

### User Dashboard Example
```html
<div class="dashboard-container dashboard-user">
    <div class="user-dashboard-header">
        <h1 class="user-dashboard-title">Welcome Back!</h1>
        <div class="user-learning-streak">
            <i class="fas fa-fire"></i>
            7 Day Learning Streak
        </div>
    </div>
    
    <div class="user-learning-stats">
        <div class="user-learning-stat achievement">
            <div class="user-learning-stat-header">
                <span class="user-learning-stat-title">Achievements</span>
                <div class="user-learning-stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <div class="user-learning-stat-value">12</div>
        </div>
    </div>
</div>
```

## 🤝 Contributing

When adding new features or roles:

1. Follow the existing naming conventions
2. Include responsive design considerations
3. Add appropriate animations and transitions
4. Update this documentation
5. Test across different screen sizes

## 📄 License

This CSS system is part of the Bismillah skin and follows the same licensing terms. 
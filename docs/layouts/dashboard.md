# Dashboard Layout (`dashboard.twig`)

## Overview

The dashboard layout provides a specialized interface for administrative and content management tasks, featuring a fixed sidebar navigation and integration with the global site header and footer.

## Features

### 🎯 **Core Purpose**
- **Administrative Interface**: Specialized layout for dashboard and management pages
- **Sidebar Navigation**: Fixed sidebar with dashboard-specific navigation
- **Global Integration**: Maintains site-wide header, footer, and navigation
- **User Context**: Clear display of user roles and permissions

### 🎨 **Design & Theme**
- **Islamic Green Theme**: Consistent with Bismillah skin styling
- **Professional Appearance**: Clean, modern interface for administrative tasks
- **Responsive Design**: Mobile-friendly with collapsible sidebar
- **Visual Hierarchy**: Clear distinction between different navigation sections

### 🔧 **Technical Features**
- **Fixed Sidebar**: 300px width, positioned below global header
- **Sticky Header**: Dashboard header stays visible during scrolling
- **CSS Variables**: Uses Islamic theme color variables for consistency
- **JavaScript Integration**: Includes ZamZam.js and Bismillah skin scripts

## Structure

### 📱 **Layout Components**

```
┌─────────────────────────────────────────────────────────────┐
│                    Global Header                            │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │   Top Bar       │   Main Bar      │   Main Nav      │  │
│  │ Prayer/Date     │ Logo/Search     │ Site Navigation │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│  Sidebar  │              Main Content                      │
│  ┌──────┐ │  ┌─────────────────────────────────────────┐  │
│  │User  │ │  │            Dashboard Header              │  │
│  │Info  │ │  │        Title + Date Display              │  │
│  ├──────┤ │  └─────────────────────────────────────────┘  │
│  │Nav   │ │  ┌─────────────────────────────────────────┐  │
│  │Menu  │ │  │              Content Area                │  │
│  │      │ │  │                                         │  │
│  └──────┘ │  └─────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
│                    Global Footer                            │
└─────────────────────────────────────────────────────────────┘
```

### 🧭 **Sidebar Navigation**

#### **Overview Section**
- **Dashboard**: Main dashboard page
- **Analytics**: User analytics and statistics

#### **Content Management Section**
- **Articles**: Manage existing articles
- **Categories**: Organize content categories
- **Create Article**: Quick access to content creation

#### **My Workspace Section**
- **Profile**: User profile management
- **Settings**: Account and preference settings
- **Logout**: Secure session termination

### 👤 **User Information Display**

#### **User Info Structure**
```
 User Info
├── Username: [Actual Username]
├── Role: [Primary Role] (e.g., "Admin", "Moderator")
└── Islamic Role: [Islamic Role] (e.g., "Scholar", "Verified Scholar")
```

#### **Role Display Logic**
1. **Admin Users**: Show "Admin" + Islamic role (if any)
2. **Special Roles**: Show role name + Islamic role (if any)
3. **Islamic Only**: Show just the Islamic role
4. **Basic Users**: Show "Contributor"

#### **Available Roles**
- **Primary Roles**: Admin, Moderator, User
- **Islamic Roles**: Scholar, Verified Scholar, User
- **Default Role**: Contributor

## Implementation Details

### 🎨 **CSS Styling**

#### **Color Variables**
```css
:root {
    --islamic-green: #2d5016;
    --islamic-gold: #d4af37;
    --islamic-dark-green: #1a3009;
    --islamic-light-green: #4a8029;
    --islamic-cream: #f8f6f0;
    --islamic-white: #ffffff;
}
```

#### **Sidebar Styling**
- **Background**: Islamic green with dark green borders
- **Text**: White text with gold accents
- **Hover Effects**: Gold-tinted backgrounds and borders
- **Active States**: Enhanced styling for current page

### 📱 **Responsive Design**

#### **Mobile Adaptations**
- **Collapsible Sidebar**: Hidden by default on mobile
- **Touch-Friendly**: Optimized for mobile interactions
- **Flexible Layout**: Adapts to different screen sizes

#### **Breakpoints**
- **Desktop**: Full sidebar + main content
- **Tablet**: Collapsible sidebar with overlay
- **Mobile**: Hidden sidebar with hamburger menu

### 🔌 **JavaScript Integration**

#### **Required Scripts**
- **ZamZam.js**: Frontend interactivity framework
- **Bismillah Skin JS**: Skin-specific functionality
- **Custom Scripts**: Page-specific functionality via `{% block scripts %}`

## Usage Examples

### 📄 **Basic Dashboard Page**

```twig
{% extends "layouts/dashboard.twig" %}

{% block title %}My Dashboard - IslamWiki{% endblock %}

{% block content %}
<div class="dashboard-content">
    <h1>Welcome to Your Dashboard</h1>
    <p>Manage your content and settings here.</p>
</div>
{% endblock %}
```

### 📊 **Dashboard with Custom Scripts**

```twig
{% extends "layouts/dashboard.twig" %}

{% block title %}Analytics Dashboard{% endblock %}

{% block content %}
<!-- Dashboard content here -->
{% endblock %}

{% block scripts %}
<script>
    // Custom dashboard functionality
    console.log('Dashboard loaded');
</script>
{% endblock %}
```

## Recent Updates

### ✅ **v0.0.60+ Updates**

#### **Global Header & Footer Integration**
- Added complete global header with navigation
- Integrated global footer for consistency
- Maintained site-wide branding and navigation

#### **Sidebar Optimization**
- Removed redundant navigation items
- Streamlined to essential dashboard functions
- Eliminated duplicate logo and prayer information

#### **Role Display Improvements**
- Fixed admin role display logic
- Added dual role support (primary + Islamic)
- Improved role visibility and clarity

#### **Styling Enhancements**
- Applied Islamic green theme consistently
- Enhanced visual hierarchy and spacing
- Improved responsive design and mobile experience

## Best Practices

### 🎯 **When to Use Dashboard Layout**

#### **Use Dashboard Layout For**:
- Administrative interfaces
- Content management pages
- User workspace pages
- Analytics and reporting pages
- Settings and configuration pages

#### **Use App Layout For**:
- Public content pages
- User-facing information
- Community pages
- General site navigation

### 🔧 **Customization Guidelines**

#### **CSS Customization**
- Use Islamic theme variables for consistency
- Maintain sidebar width (300px) for layout integrity
- Follow established color scheme and typography

#### **Content Organization**
- Keep sidebar navigation focused and relevant
- Use clear section titles and icons
- Maintain logical grouping of related functions

#### **Responsive Considerations**
- Test sidebar behavior on mobile devices
- Ensure touch targets are appropriately sized
- Maintain usability across all screen sizes

## Troubleshooting

### ❓ **Common Issues**

#### **Role Not Displaying Correctly**
- Check user object structure
- Verify `is_admin` flag and `role` fields
- Ensure proper authentication state

#### **Sidebar Styling Issues**
- Verify Bismillah skin CSS is loaded
- Check CSS variable definitions
- Ensure proper CSS specificity

#### **Mobile Responsiveness**
- Test sidebar collapse functionality
- Verify touch interactions work properly
- Check overlay behavior on small screens

### 🔍 **Debug Information**

#### **User Object Structure**
```php
$user = [
    'username' => 'admin',
    'is_admin' => true,
    'role' => 'admin',
    'islamic_role' => 'scholar'
];
```

#### **Template Variables**
- `user`: Current authenticated user object
- `current_language`: User's language preference
- `app.request`: Current request information

## Future Enhancements

### 🚀 **Planned Features**
- **Advanced Sidebar**: Collapsible sections and search
- **Quick Actions**: Context-sensitive action buttons
- **Notifications**: Real-time notification system
- **Themes**: Additional dashboard theme options
- **Accessibility**: Enhanced screen reader support

### 🔧 **Technical Improvements**
- **Performance**: Lazy loading for sidebar content
- **Caching**: Sidebar state persistence
- **Internationalization**: Multi-language sidebar support
- **Customization**: User-configurable sidebar layout 
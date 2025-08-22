# Wiki Troubleshooting Guide - IslamWiki

**Version:** 0.0.2.1  
**Last Updated:** 2025-01-20  
**Status:** Complete Troubleshooting Guide ✅  

## 🎯 **Overview**

This troubleshooting guide provides solutions for common issues that may arise when using or administering the IslamWiki WikiExtension system. It covers content issues, user problems, system errors, and performance problems.

## 🚨 **Quick Issue Resolution**

### **Emergency Contact Information**
- **Critical Issues**: Contact system administrator immediately
- **Support Email**: support@islamwiki.org
- **Emergency Phone**: +1-555-0123 (24/7 for critical issues)
- **Status Page**: status.islamwiki.org

### **Priority Levels**
- **P1 (Critical)**: System down, data loss, security breach
- **P2 (High)**: Major functionality broken, performance severely degraded
- **P3 (Medium)**: Minor functionality issues, performance problems
- **P4 (Low)**: Cosmetic issues, minor bugs

## 📚 **Content Issues**

### **Page Not Displaying**

#### **Symptoms**
- Page shows blank content
- Page shows error message
- Page redirects to error page
- Page shows "Page Not Found"

#### **Diagnosis Steps**
1. **Check URL**: Verify the page URL is correct
2. **Check Database**: Verify page exists in database
3. **Check Permissions**: Verify user has access to page
4. **Check Template**: Verify template file exists and is valid

#### **Solutions**

**Page Not Found (404)**
```bash
# Check if page exists in database
mysql -u username -p database_name
SELECT * FROM wiki_pages WHERE slug = 'page-slug';

# Check if template exists
ls extensions/WikiExtension/templates/show.twig
```

**Blank Page Content**
```php
// Check if content is being loaded
// In WikiController.php, add debugging:
error_log("Page content: " . print_r($page, true));
```

**Template Errors**
```bash
# Check Twig template syntax
php bin/console twig:lint extensions/WikiExtension/templates/

# Check template file permissions
ls -la extensions/WikiExtension/templates/
```

#### **Prevention**
- Regular template validation
- Database integrity checks
- Permission system testing
- Content backup procedures

### **Content Formatting Problems**

#### **Symptoms**
- Markdown not rendering properly
- HTML tags displayed as text
- Images not displaying
- Tables not formatted correctly

#### **Solutions**

**Markdown Rendering Issues**
```php
// Check if Markdown parser is working
$parser = new MarkdownParser();
$html = $parser->parse($markdown);
error_log("Markdown to HTML: " . $html);
```

**HTML Escaping Issues**
```twig
{# In Twig templates, use raw filter for HTML #}
{{ page.content|raw }}
```

**Image Display Issues**
```bash
# Check image file permissions
ls -la public/uploads/wiki/

# Check image file existence
find public/uploads/wiki/ -name "*.jpg" -o -name "*.png"
```

#### **Prevention**
- Content validation before saving
- Image optimization and validation
- Markdown syntax checking
- Regular content review

### **Search Not Working**

#### **Symptoms**
- Search returns no results
- Search returns irrelevant results
- Search is slow
- Search crashes the system

#### **Diagnosis Steps**
1. **Check Search Index**: Verify search index is up to date
2. **Check Search Query**: Verify search query is valid
3. **Check Database**: Verify search tables exist
4. **Check Permissions**: Verify user has search access

#### **Solutions**

**Search Index Issues**
```bash
# Rebuild search index
php bin/console wiki:rebuild-index

# Check search table
mysql -u username -p database_name
SELECT COUNT(*) FROM wiki_search_logs;
```

**Search Performance Issues**
```sql
-- Add search indexes
ALTER TABLE wiki_pages ADD FULLTEXT(title, content, meta_description);
ALTER TABLE wiki_tags ADD INDEX(name);
```

**Search Configuration**
```php
// Check search configuration
$config = [
    'search' => [
        'min_query_length' => 3,
        'max_results' => 100,
        'enable_fuzzy_search' => true
    ]
];
```

#### **Prevention**
- Regular index maintenance
- Search performance monitoring
- Query optimization
- Regular search testing

## 👥 **User Issues**

### **Authentication Problems**

#### **Symptoms**
- User cannot log in
- User gets logged out frequently
- User cannot access certain pages
- User gets permission denied errors

#### **Diagnosis Steps**
1. **Check User Account**: Verify user account exists and is active
2. **Check Password**: Verify password is correct
3. **Check Permissions**: Verify user has required permissions
4. **Check Session**: Verify session is working properly

#### **Solutions**

**Login Issues**
```php
// Check user authentication
$user = $userModel->findByEmail($email);
if (!$user || !password_verify($password, $user->password)) {
    error_log("Login failed for email: " . $email);
}
```

**Session Issues**
```php
// Check session configuration
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
```

**Permission Issues**
```php
// Check user permissions
if (!$user->hasPermission('wiki.edit')) {
    throw new ForbiddenException('Insufficient permissions');
}
```

#### **Prevention**
- Regular password policy enforcement
- Session timeout configuration
- Permission system testing
- User activity monitoring

### **User Account Problems**

#### **Symptoms**
- User cannot create account
- User cannot update profile
- User cannot change password
- User account is locked

#### **Solutions**

**Account Creation Issues**
```php
// Check email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new ValidationException('Invalid email format');
}

// Check username uniqueness
if ($userModel->findByUsername($username)) {
    throw new ValidationException('Username already exists');
}
```

**Profile Update Issues**
```php
// Check file upload permissions
if (!is_writable(public_path('uploads/avatars/'))) {
    error_log("Avatar directory not writable");
}
```

**Account Locking**
```sql
-- Check account status
SELECT username, is_active, locked_until FROM users WHERE email = ?;

-- Unlock account if needed
UPDATE users SET is_active = 1, locked_until = NULL WHERE email = ?;
```

#### **Prevention**
- Input validation
- File permission management
- Account security policies
- Regular security audits

## 🔧 **System Issues**

### **Database Connection Problems**

#### **Symptoms**
- "Database connection failed" errors
- Slow page loading
- Database timeout errors
- Connection pool exhaustion

#### **Diagnosis Steps**
1. **Check Database Server**: Verify database server is running
2. **Check Connection Settings**: Verify connection parameters
3. **Check Network**: Verify network connectivity
4. **Check Resources**: Verify database server resources

#### **Solutions**

**Connection Issues**
```bash
# Test database connection
mysql -h hostname -u username -p database_name

# Check database status
systemctl status mysql
```

**Connection Configuration**
```php
// Check database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

**Performance Issues**
```sql
-- Check slow queries
SHOW PROCESSLIST;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'islamwiki';
```

#### **Prevention**
- Database monitoring
- Connection pooling
- Query optimization
- Regular maintenance

### **File Upload Issues**

#### **Symptoms**
- Files cannot be uploaded
- Uploaded files are corrupted
- File size limits too restrictive
- File type restrictions too strict

#### **Solutions**

**Upload Configuration**
```php
// Check upload settings
$config = [
    'max_file_size' => 2 * 1024 * 1024, // 2MB
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    'upload_path' => public_path('uploads/wiki/'),
    'create_directories' => true
];
```

**File Permission Issues**
```bash
# Check upload directory permissions
ls -la public/uploads/wiki/

# Fix permissions if needed
chmod 755 public/uploads/wiki/
chown www-data:www-data public/uploads/wiki/
```

**File Validation**
```php
// Validate uploaded files
if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {
    throw new ValidationException('File type not allowed');
}

if ($file->getSize() > $maxFileSize) {
    throw new ValidationException('File too large');
}
```

#### **Prevention**
- File type validation
- Size limit enforcement
- Virus scanning
- Regular backup

### **Email Notification Issues**

#### **Symptoms**
- Users don't receive emails
- Email notifications are delayed
- Email formatting is broken
- Email delivery fails

#### **Solutions**

**Email Configuration**
```php
// Check email settings
$config = [
    'driver' => 'smtp',
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
    'port' => $_ENV['MAIL_PORT'] ?? 2525,
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@islamwiki.org',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'IslamWiki'
    ]
];
```

**Email Testing**
```php
// Test email functionality
try {
    Mail::raw('Test email', function($message) {
        $message->to('test@example.com')
                ->subject('Test Email');
    });
    echo "Email sent successfully";
} catch (Exception $e) {
    error_log("Email error: " . $e->getMessage());
}
```

**Email Queue Issues**
```bash
# Check email queue
php artisan queue:work

# Check failed jobs
php artisan queue:failed
```

#### **Prevention**
- Email service monitoring
- Queue monitoring
- Delivery confirmation
- Regular testing

## 📱 **Performance Issues**

### **Slow Page Loading**

#### **Symptoms**
- Pages take long time to load
- Database queries are slow
- Asset loading is slow
- Search is slow

#### **Diagnosis Steps**
1. **Check Response Times**: Monitor page load times
2. **Check Database Queries**: Monitor query performance
3. **Check Asset Loading**: Monitor CSS/JS loading
4. **Check Server Resources**: Monitor server performance

#### **Solutions**

**Database Optimization**
```sql
-- Add missing indexes
ALTER TABLE wiki_pages ADD INDEX idx_status_created (status, created_at);
ALTER TABLE wiki_categories ADD INDEX idx_parent_sort (parent_id, sort_order);

-- Optimize queries
EXPLAIN SELECT * FROM wiki_pages WHERE status = 'published' ORDER BY created_at DESC;
```

**Caching Implementation**
```php
// Implement page caching
$cacheKey = "wiki_page_{$slug}";
$page = Cache::remember($cacheKey, 3600, function() use ($slug) {
    return $this->wikiPageModel->getBySlug($slug);
});
```

**Asset Optimization**
```bash
# Minify CSS and JS
npm run build

# Optimize images
find public/uploads/wiki/ -name "*.jpg" -exec jpegoptim {} \;
find public/uploads/wiki/ -name "*.png" -exec optipng {} \;
```

#### **Prevention**
- Performance monitoring
- Regular optimization
- Caching strategies
- Resource monitoring

### **Memory Issues**

#### **Symptoms**
- High memory usage
- Out of memory errors
- Slow performance
- System crashes

#### **Solutions**

**Memory Configuration**
```php
// Increase memory limits
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Optimize database connections
$pdo->setAttribute(PDO::ATTR_PERSISTENT, false);
```

**Memory Monitoring**
```bash
# Check memory usage
free -h

# Check PHP memory usage
php -i | grep memory_limit

# Monitor memory in real-time
watch -n 1 'free -h'
```

**Memory Optimization**
```php
// Use generators for large datasets
function getLargeDataset() {
    $query = "SELECT * FROM wiki_pages";
    $stmt = $pdo->query($query);
    
    while ($row = $stmt->fetch()) {
        yield $row;
    }
}

// Process in chunks
foreach (getLargeDataset() as $row) {
    // Process row
}
```

#### **Prevention**
- Memory monitoring
- Code optimization
- Resource limits
- Regular cleanup

## 🛡️ **Security Issues**

### **Authentication Vulnerabilities**

#### **Symptoms**
- Unauthorized access
- Brute force attacks
- Session hijacking
- Password breaches

#### **Solutions**

**Brute Force Protection**
```php
// Implement rate limiting
$attempts = Cache::get("login_attempts_{$ip}", 0);
if ($attempts >= 5) {
    throw new SecurityException('Too many login attempts');
}

// Increment attempts
Cache::put("login_attempts_{$ip}", $attempts + 1, 300);
```

**Session Security**
```php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
```

**Password Security**
```php
// Strong password requirements
if (strlen($password) < 8) {
    throw new ValidationException('Password too short');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
    throw new ValidationException('Password too weak');
}
```

#### **Prevention**
- Security monitoring
- Regular audits
- User education
- Security policies

### **Content Security**

#### **Symptoms**
- Malicious content posted
- XSS attacks
- SQL injection attempts
- File upload attacks

#### **Solutions**

**Content Validation**
```php
// Sanitize user input
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

// Validate file uploads
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
$extension = strtolower($file->getClientOriginalExtension());
if (!in_array($extension, $allowedTypes)) {
    throw new SecurityException('File type not allowed');
}
```

**SQL Injection Prevention**
```php
// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM wiki_pages WHERE slug = ?");
$stmt->execute([$slug]);

// Validate input
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    throw new ValidationException('Invalid slug format');
}
```

**XSS Prevention**
```twig
{# In Twig templates, escape output #}
{{ page.content|escape }}

{# For trusted HTML content #}
{{ page.content|raw }}
```

#### **Prevention**
- Input validation
- Output escaping
- Security headers
- Regular security updates

## 🔍 **Debugging Tools**

### **Logging and Monitoring**

#### **Error Logging**
```php
// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/wiki_errors.log');

// Log custom messages
error_log("User {$userId} accessed page {$pageId}");
```

#### **Debug Mode**
```php
// Enable debug mode
$config = [
    'debug' => true,
    'log_level' => 'debug',
    'display_errors' => true
];
```

#### **Performance Profiling**
```php
// Profile database queries
$start = microtime(true);
$result = $pdo->query($sql);
$time = microtime(true) - $start;
error_log("Query took {$time} seconds: {$sql}");
```

### **Database Debugging**

#### **Query Logging**
```sql
-- Enable query logging
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';

-- View logged queries
SELECT * FROM mysql.general_log ORDER BY event_time DESC LIMIT 100;
```

#### **Performance Analysis**
```sql
-- Check slow queries
SHOW VARIABLES LIKE 'slow_query_log';
SHOW VARIABLES LIKE 'long_query_time';

-- Analyze table performance
ANALYZE TABLE wiki_pages;
CHECK TABLE wiki_pages;
```

### **System Monitoring**

#### **Resource Monitoring**
```bash
# Monitor CPU and memory
htop

# Monitor disk usage
df -h

# Monitor network
iftop

# Monitor processes
ps aux | grep php
```

#### **Application Monitoring**
```php
// Monitor application performance
$metrics = [
    'memory_usage' => memory_get_usage(true),
    'peak_memory' => memory_get_peak_usage(true),
    'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
];

// Send to monitoring service
$this->monitoringService->record($metrics);
```

## 📋 **Maintenance Procedures**

### **Regular Maintenance Tasks**

#### **Daily Tasks**
- Check error logs
- Monitor system performance
- Verify backups completed
- Check user reports

#### **Weekly Tasks**
- Review security logs
- Analyze performance metrics
- Clean temporary files
- Update system statistics

#### **Monthly Tasks**
- Database optimization
- Log file rotation
- Security updates
- Performance review

### **Emergency Procedures**

#### **System Recovery**
1. **Assess Damage**: Determine extent of problem
2. **Stop Bleeding**: Prevent further damage
3. **Restore Services**: Restore critical services
4. **Investigate Root Cause**: Find underlying cause
5. **Implement Fixes**: Apply permanent solutions
6. **Test Recovery**: Verify system is working
7. **Document Incident**: Record what happened

#### **Data Recovery**
```bash
# Restore from backup
mysql -u username -p database_name < backup.sql

# Verify restoration
mysql -u username -p database_name -e "SELECT COUNT(*) FROM wiki_pages;"
```

## 📞 **Support Resources**

### **Documentation**
- **User Manual**: Complete user documentation
- **Admin Guide**: Administrative procedures
- **API Reference**: Technical API documentation
- **Developer Guide**: Development guidelines

### **Community Support**
- **User Forum**: Community support forum
- **GitHub Issues**: Bug reports and feature requests
- **Discord Server**: Real-time community support
- **Email Support**: Direct support contact

### **Professional Support**
- **Premium Support**: Priority support for premium users
- **Consulting Services**: Professional consulting
- **Training Programs**: User and admin training
- **Custom Development**: Custom feature development

## 🎯 **Prevention Strategies**

### **Proactive Monitoring**
- **System Health Checks**: Regular system monitoring
- **Performance Baselines**: Establish performance standards
- **Security Scanning**: Regular security assessments
- **Backup Verification**: Verify backup integrity

### **User Education**
- **Best Practices**: Teach users best practices
- **Security Awareness**: Security training programs
- **Troubleshooting Guides**: Self-help resources
- **Regular Updates**: Keep users informed

### **System Hardening**
- **Security Updates**: Regular security patches
- **Configuration Review**: Regular configuration audits
- **Access Control**: Strict access management
- **Monitoring**: Comprehensive system monitoring

---

**You're now equipped to troubleshoot any wiki system issues!** 🚀

This troubleshooting guide covers:
- ✅ **Content Issues**: Page display, formatting, and search problems
- ✅ **User Issues**: Authentication and account problems
- ✅ **System Issues**: Database, file upload, and email problems
- ✅ **Performance Issues**: Slow loading and memory problems
- ✅ **Security Issues**: Authentication and content security
- ✅ **Debugging Tools**: Logging, monitoring, and profiling
- ✅ **Maintenance Procedures**: Regular and emergency procedures
- ✅ **Support Resources**: Documentation and community support

**Happy troubleshooting!** 🔧✨

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.2.1  
**Status:** Complete Troubleshooting Guide ✅  
**Next:** Migration Guide and Best Practices 📋 
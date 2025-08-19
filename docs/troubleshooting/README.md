# IslamWiki Troubleshooting Guide

## 🎯 **Overview**

This directory contains comprehensive troubleshooting documentation for IslamWiki, covering common issues, problem diagnosis, and resolution procedures. All troubleshooting follows Islamic naming conventions and provides step-by-step solutions.

---

## 🏗️ **Troubleshooting Categories**

### **Issue Types**
```
Troubleshooting Categories:
├── 📁 Application Issues - Core application problems
├── 📁 Database Issues - Database connection and query problems
├── 📁 Performance Issues - Performance and optimization problems
├── 📁 Security Issues - Security and authentication problems
├── 📁 Extension Issues - Extension and plugin problems
├── 📁 Skin Issues - Theme and appearance problems
└── 📁 Deployment Issues - Server and deployment problems
```

### **Troubleshooting Principles**
- **Systematic Approach**: Follow structured problem-solving steps
- **Documentation First**: Check documentation before troubleshooting
- **Log Analysis**: Analyze logs for error patterns
- **Root Cause Analysis**: Identify underlying causes
- **Prevention Focus**: Implement solutions to prevent recurrence

---

## 🔍 **Problem Diagnosis Process**

### **1. Information Gathering**
```bash
# Check application status
php artisan about

# Check system information
php -v
mysql --version
nginx -v

# Check application logs
tail -f storage/logs/islamwiki.log
tail -f storage/logs/error.log

# Check system logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.1-fpm -f
sudo journalctl -u mysql -f
```

### **2. Error Analysis**
```bash
# Check PHP error log
tail -f /var/log/php8.1-fpm.log

# Check web server error log
tail -f /var/log/nginx/error.log

# Check database error log
tail -f /var/log/mysql/error.log

# Check application exceptions
grep -r "Exception" storage/logs/
grep -r "Error" storage/logs/
```

### **3. Performance Analysis**
```bash
# Check system resources
htop
free -h
df -h

# Check PHP-FPM status
php-fpm8.1 -t
sudo systemctl status php8.1-fpm

# Check MySQL status
sudo systemctl status mysql
mysqladmin -u root -p status
```

---

## 🚨 **Common Issues & Solutions**

### **1. Application Won't Start**

#### **Symptoms**
- White screen or error page
- Application not responding
- HTTP 500 errors

#### **Diagnosis**
```bash
# Check application configuration
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check file permissions
ls -la storage/
ls -la cache/
ls -la bootstrap/cache/

# Check environment configuration
cat .env
php artisan env
```

#### **Solutions**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/islamwiki
sudo chmod -R 755 /var/www/islamwiki
sudo chmod -R 775 /var/www/islamwiki/storage
sudo chmod -R 775 /var/www/islamwiki/cache

# Regenerate configuration cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check for syntax errors
php -l app/Http/Controllers/
php -l app/Models/
```

### **2. Database Connection Issues**

#### **Symptoms**
- Database connection errors
- Query failures
- Migration errors

#### **Diagnosis**
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check database configuration
php artisan config:show database

# Test MySQL connection
mysql -u islamwiki_user -p -h localhost islamwiki_db

# Check MySQL service status
sudo systemctl status mysql
```

#### **Solutions**
```bash
# Restart MySQL service
sudo systemctl restart mysql

# Check MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Reset database password
sudo mysql
ALTER USER 'islamwiki_user'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log
```

### **3. Performance Issues**

#### **Symptoms**
- Slow page loads
- High response times
- Server resource exhaustion

#### **Diagnosis**
```bash
# Check PHP-FPM configuration
php-fpm8.1 -t
sudo nano /etc/php/8.1/fpm/php-fpm.conf

# Check OPcache status
php -r "var_dump(opcache_get_status());"

# Check MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW STATUS LIKE 'Slow_queries';"

# Check system resources
top
iostat -x 1
```

#### **Solutions**
```bash
# Optimize PHP-FPM
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
# Adjust pm.max_children, pm.start_servers, pm.min_spare_servers

# Optimize OPcache
sudo nano /etc/php/8.1/fpm/php.ini
# Adjust opcache.memory_consumption, opcache.max_accelerated_files

# Optimize MySQL
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Adjust innodb_buffer_pool_size, query_cache_size

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
```

---

## 🔒 **Security Issues**

### **1. Authentication Problems**

#### **Symptoms**
- Users can't log in
- Session timeouts
- Permission errors

#### **Diagnosis**
```bash
# Check authentication configuration
php artisan config:show auth

# Check session configuration
php artisan config:show session

# Check user permissions
php artisan tinker
User::find(1)->permissions;
```

#### **Solutions**
```bash
# Clear user sessions
php artisan session:table
php artisan migrate

# Reset user password
php artisan tinker
$user = User::find(1);
$user->password = Hash::make('new_password');
$user->save();

# Check authentication logs
tail -f storage/logs/auth.log
```

### **2. Permission Issues**

#### **Symptoms**
- Access denied errors
- Role-based access failures
- Admin panel inaccessible

#### **Diagnosis**
```bash
# Check user roles
php artisan tinker
User::with('roles')->get();

# Check role permissions
php artisan tinker
Role::with('permissions')->get();

# Check middleware configuration
php artisan route:list
```

#### **Solutions**
```bash
# Assign admin role
php artisan tinker
$user = User::find(1);
$adminRole = Role::where('name', 'admin')->first();
$user->roles()->attach($adminRole->id);

# Check permission middleware
php artisan make:middleware CheckPermission
```

---

## 🔧 **Extension & Skin Issues**

### **1. Extension Problems**

#### **Symptoms**
- Extension not loading
- Extension errors
- Feature not working

#### **Diagnosis**
```bash
# Check extension status
php artisan extension:list

# Check extension configuration
php artisan config:show extensions

# Check extension logs
tail -f storage/logs/extension.log
```

#### **Solutions**
```bash
# Disable problematic extension
php artisan extension:disable ExtensionName

# Clear extension cache
php artisan extension:clear

# Reinstall extension
php artisan extension:uninstall ExtensionName
php artisan extension:install ExtensionName
```

### **2. Skin Problems**

#### **Symptoms**
- Theme not applying
- CSS/JS not loading
- Layout broken

#### **Diagnosis**
```bash
# Check active skin
php artisan config:show skins

# Check skin assets
ls -la public/skins/
ls -la resources/views/skins/

# Check browser console for errors
# Check network tab for failed requests
```

#### **Solutions**
```bash
# Switch to default skin
php artisan config:set skins.default default

# Clear skin cache
php artisan view:clear

# Check skin configuration
php artisan config:show skins
```

---

## 📊 **Performance Troubleshooting**

### **1. Slow Page Loads**

#### **Diagnosis Steps**
```bash
# Check response times
curl -w "@curl-format.txt" -o /dev/null -s "http://localhost/"

# Check database queries
php artisan tinker
DB::enableQueryLog();
// Perform action
DB::getQueryLog();

# Check cache hit rates
php artisan cache:stats
```

#### **Solutions**
```bash
# Enable query caching
php artisan config:set database.query_cache true

# Optimize database indexes
php artisan migrate:status
php artisan db:optimize

# Enable page caching
php artisan config:set cache.page_cache true
```

### **2. High Memory Usage**

#### **Diagnosis**
```bash
# Check PHP memory usage
php -r "echo memory_get_usage(true);"

# Check MySQL memory usage
mysql -u root -p -e "SHOW VARIABLES LIKE 'max_connections';"
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
```

#### **Solutions**
```bash
# Optimize PHP memory
sudo nano /etc/php/8.1/fpm/php.ini
# Adjust memory_limit, max_execution_time

# Optimize MySQL connections
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Adjust max_connections, innodb_buffer_pool_size
```

---

## 📚 **Troubleshooting Documentation**

### **Available Troubleshooting Guides**
- **[Application Issues](application.md)** - Core application problems
- **[Database Issues](database.md)** - Database problems
- **[Performance Issues](performance.md)** - Performance problems
- **[Security Issues](security.md)** - Security problems
- **[Extension Issues](extensions.md)** - Extension problems
- **[Skin Issues](skins.md)** - Theme problems
- **[Deployment Issues](deployment.md)** - Server problems

### **Troubleshooting Development**
- **[Troubleshooting Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🚀 **Prevention & Maintenance**

### **Regular Maintenance Tasks**
```bash
# Daily tasks
php artisan queue:work --once
php artisan cache:clear

# Weekly tasks
php artisan backup:run
php artisan optimize

# Monthly tasks
php artisan migrate:status
php artisan extension:update
php artisan skin:update
```

### **Monitoring & Alerting**
```bash
# Set up monitoring
php artisan monitoring:setup

# Configure alerts
php artisan monitoring:alerts

# Check system health
php artisan health:check
```

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Security Documentation](../security/README.md)** - Security guidelines
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

### **Troubleshooting Resources**
- **[PHP Error Reference](https://www.php.net/manual/en/errorfunc.constants.php)** - PHP error codes
- **[MySQL Error Reference](https://dev.mysql.com/doc/refman/8.0/en/error-reference.html)** - MySQL error codes
- **[Nginx Error Reference](https://nginx.org/en/docs/http/ngx_http_core_module.html)** - Nginx configuration

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Troubleshooting Documentation Complete ✅ 
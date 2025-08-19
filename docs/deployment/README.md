# IslamWiki Deployment Guide

## 🎯 **Overview**

This directory contains comprehensive deployment documentation for IslamWiki, covering production deployment, server configuration, performance optimization, and maintenance procedures. All deployment follows Islamic naming conventions and enterprise-grade standards.

---

## 🏗️ **Deployment Architecture**

### **Deployment Environment**
```
Deployment Architecture:
├── 📁 Production - Live production environment
├── 📁 Staging - Pre-production testing environment
├── 📁 Development - Development and testing environment
└── 📁 Local - Local development environment
```

### **Deployment Principles**
- **Zero Downtime**: Seamless deployment without service interruption
- **Rollback Ready**: Quick rollback to previous versions
- **Performance Optimized**: Production-ready performance configuration
- **Security Hardened**: Enterprise-grade security configuration
- **Monitoring Enabled**: Comprehensive monitoring and alerting

---

## 🔧 **Server Requirements**

### **System Requirements**
- **Operating System**: Ubuntu 20.04 LTS or later
- **PHP Version**: PHP 8.1 or later
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: Minimum 4GB RAM, recommended 8GB+
- **Storage**: Minimum 50GB, recommended 100GB+

### **Software Dependencies**
```bash
# System packages
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring \
    php8.1-curl php8.1-gd php8.1-zip php8.1-opcache mysql-server redis-server \
    git composer nodejs npm

# PHP extensions
sudo apt install -y php8.1-intl php8.1-bcmath php8.1-soap php8.1-redis
```

---

## 📝 **Deployment Process**

### **1. Environment Setup**
```bash
# Clone repository
git clone https://github.com/islamwiki/islamwiki.git /var/www/islamwiki
cd /var/www/islamwiki

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Set permissions
sudo chown -R www-data:www-data /var/www/islamwiki
sudo chmod -R 755 /var/www/islamwiki
sudo chmod -R 775 /var/www/islamwiki/storage
sudo chmod -R 775 /var/www/islamwiki/cache
```

### **2. Configuration Setup**
```bash
# Copy configuration files
cp config/production.php config/app.php
cp .env.example .env

# Edit environment configuration
nano .env

# Generate application key
php artisan key:generate

# Configure database
php artisan migrate --force
php artisan db:seed --force
```

### **3. Web Server Configuration**

#### **Nginx Configuration**
```nginx
# /etc/nginx/sites-available/islamwiki
server {
    listen 80;
    server_name islamwiki.com www.islamwiki.com;
    root /var/www/islamwiki/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security: Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(config|database|docs|src|tests|vendor) {
        deny all;
    }
}
```

#### **Apache Configuration**
```apache
# /etc/apache2/sites-available/islamwiki.conf
<VirtualHost *:80>
    ServerName islamwiki.com
    ServerAlias www.islamwiki.com
    DocumentRoot /var/www/islamwiki/public

    <Directory /var/www/islamwiki/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer-when-downgrade"

    # Gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
    </IfModule>

    # Caching
    <IfModule mod_expires.c>
        ExpiresActive on
        ExpiresByType text/css "access plus 1 year"
        ExpiresByType application/javascript "access plus 1 year"
        ExpiresByType image/png "access plus 1 year"
        ExpiresByType image/jpg "access plus 1 year"
        ExpiresByType image/jpeg "access plus 1 year"
        ExpiresByType image/gif "access plus 1 year"
    </IfModule>
</VirtualHost>
```

---

## 🚀 **Performance Optimization**

### **PHP Optimization**
```ini
# /etc/php/8.1/fpm/php.ini
[PHP]
; Performance settings
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000
post_max_size = 100M
upload_max_filesize = 100M

; OPcache settings
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

; Session settings
session.gc_maxlifetime = 3600
session.cookie_lifetime = 3600
session.cookie_httponly = 1
session.cookie_secure = 1
```

### **MySQL Optimization**
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
; Performance settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

; Connection settings
max_connections = 200
max_connect_errors = 1000000

; Query cache
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M
```

---

## 🔒 **Security Configuration**

### **SSL/TLS Configuration**
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d islamwiki.com -d www.islamwiki.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### **Firewall Configuration**
```bash
# Configure UFW firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### **Security Headers**
```nginx
# Additional security headers
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## 📊 **Monitoring & Logging**

### **Application Monitoring**
```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Configure log rotation
sudo nano /etc/logrotate.d/islamwiki

/var/www/islamwiki/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### **Performance Monitoring**
```bash
# Install New Relic or similar APM
# Configure application performance monitoring
# Set up alerting for performance issues
```

---

## 🔄 **Deployment Automation**

### **Deployment Script**
```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Starting IslamWiki deployment..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize application
php artisan optimize

# Set permissions
sudo chown -R www-data:www-data /var/www/islamwiki
sudo chmod -R 755 /var/www/islamwiki
sudo chmod -R 775 /var/www/islamwiki/storage

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl reload nginx

echo "✅ Deployment completed successfully!"
```

### **CI/CD Pipeline**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Deploy to server
        uses: appleboy/ssh-action@v0.1.4
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.KEY }}
          script: |
            cd /var/www/islamwiki
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force
            php artisan optimize
            sudo systemctl restart php8.1-fpm
            sudo systemctl reload nginx
```

---

## 📚 **Deployment Documentation**

### **Available Deployment Guides**
- **[Production Deployment](production.md)** - Production environment setup
- **[Staging Deployment](staging.md)** - Staging environment setup
- **[Performance Tuning](performance.md)** - Performance optimization
- **[Security Hardening](security.md)** - Security configuration
- **[Monitoring Setup](monitoring.md)** - Monitoring and alerting

### **Deployment Development**
- **[Deployment Standards](../standards.md)** - Deployment standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🧪 **Deployment Testing**

### **Pre-deployment Checklist**
- [ ] All tests passing
- [ ] Security scan completed
- [ ] Performance benchmarks met
- [ ] Database backup completed
- [ ] Rollback plan prepared
- [ ] Monitoring configured
- [ ] Team notified

### **Post-deployment Verification**
- [ ] Application accessible
- [ ] All features working
- [ ] Performance acceptable
- [ ] Error logs clean
- [ ] Monitoring active
- [ ] Team confirmed

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Security Documentation](../security/README.md)** - Security guidelines
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

### **Deployment Resources**
- **[Nginx Documentation](https://nginx.org/en/docs/)** - Nginx configuration
- **[Apache Documentation](https://httpd.apache.org/docs/)** - Apache configuration
- **[PHP-FPM Documentation](https://www.php.net/manual/en/install.fpm.php)** - PHP-FPM setup
- **[MySQL Documentation](https://dev.mysql.com/doc/)** - MySQL optimization

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Deployment Documentation Complete ✅ 
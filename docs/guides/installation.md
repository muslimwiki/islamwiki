# IslamWiki Installation Guide

## 🎯 **Overview**

This guide provides step-by-step instructions for installing IslamWiki on your server. IslamWiki is a hybrid platform that combines the best features of MediaWiki, WordPress, and modern PHP.

---

## 📋 **System Requirements**

### **Server Requirements**
- **Operating System**: Linux (Ubuntu 20.04+, CentOS 8+), Windows Server 2019+, macOS 10.15+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.0 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: Minimum 512MB RAM, Recommended 2GB+
- **Disk Space**: Minimum 1GB, Recommended 5GB+

### **PHP Extensions**
```bash
# Required PHP extensions
php-bcmath
php-curl
php-gd
php-intl
php-mbstring
php-mysql
php-opcache
php-xml
php-zip
php-json
php-tokenizer
php-fileinfo
php-session
php-pdo
php-pdo-mysql
```

### **Server Software**
- **Apache**: mod_rewrite enabled
- **Nginx**: FastCGI configuration
- **SSL Certificate**: HTTPS support recommended

---

## 🚀 **Quick Installation**

### **1. Download IslamWiki**
```bash
# Clone from Git repository
git clone https://github.com/your-org/islamwiki.git
cd islamwiki

# Or download from releases
wget https://github.com/your-org/islamwiki/releases/latest/download/islamwiki-latest.zip
unzip islamwiki-latest.zip
cd islamwiki
```

### **2. Install Dependencies**
```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies (if using frontend build tools)
npm install
npm run build
```

### **3. Configure Web Server**
```bash
# Set document root to public/ directory
# Apache: DocumentRoot /path/to/islamwiki/public
# Nginx: root /path/to/islamwiki/public;
```

### **4. Create Configuration**
```bash
# Copy configuration files
cp LocalSettings.php.example LocalSettings.php
cp .env.example .env

# Edit configuration files
nano LocalSettings.php
nano .env
```

### **5. Run Installation Script**
```bash
# Run installation script
php scripts/install.php

# Or run manually
php scripts/database/migrate.php
php scripts/create-admin-user.php
```

---

## 🔧 **Detailed Installation**

### **Step 1: Server Preparation**

#### **Update System**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common
```

#### **Install PHP**
```bash
# Ubuntu/Debian
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-gd php8.1-mbstring php8.1-curl php8.1-intl php8.1-zip php8.1-bcmath

# CentOS/RHEL
sudo yum install -y epel-release
sudo yum install -y php php-cli php-fpm php-mysql php-xml php-gd php-mbstring php-curl php-intl php-zip php-bcmath
```

#### **Install MySQL/MariaDB**
```bash
# Ubuntu/Debian
sudo apt install -y mysql-server

# CentOS/RHEL
sudo yum install -y mariadb-server mariadb

# Start and enable MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation
sudo mysql_secure_installation
```

#### **Install Composer**
```bash
# Download Composer
curl -sS https://getcomposer.org/installer | php

# Move to global location
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

### **Step 2: Web Server Configuration**

#### **Apache Configuration**
```apache
# /etc/apache2/sites-available/islamwiki.conf
<VirtualHost *:80>
    ServerName islam.wiki
    ServerAlias www.islam.wiki
    DocumentRoot /var/www/html/local.islam.wiki/public
    
    <Directory /var/www/html/local.islam.wiki/public>
        AllowOverride All
        Require all granted
        
        # Enable mod_rewrite
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/islamwiki_error.log
    CustomLog ${APACHE_LOG_DIR}/islamwiki_access.log combined
</VirtualHost>

# Enable site and required modules
sudo a2ensite islamwiki
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### **Nginx Configuration**
```nginx
# /etc/nginx/sites-available/islamwiki
server {
    listen 80;
    server_name islam.wiki www.islam.wiki;
    root /var/www/html/local.islam.wiki/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    
    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Handle static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Handle all other requests
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Logs
    access_log /var/log/nginx/islamwiki_access.log;
    error_log /var/log/nginx/islamwiki_error.log;
}

# Enable site
sudo ln -s /etc/nginx/sites-available/islamwiki /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### **Step 3: Database Setup**

#### **Create Database**
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE islamwiki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'islamwiki_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON islamwiki.* TO 'islamwiki_user'@'localhost';
GRANT ALL PRIVILEGES ON islamwiki.* TO 'islamwiki_user'@'127.0.0.1';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

#### **Create Islamic Databases**
```sql
-- Quran database
CREATE DATABASE quran_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON quran_db.* TO 'islamwiki_user'@'localhost';

-- Hadith database
CREATE DATABASE hadith_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON hadith_db.* TO 'islamwiki_user'@'localhost';

-- Islamic database
CREATE DATABASE islamic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON islamic_db.* TO 'islamwiki_user'@'localhost';
```

### **Step 4: Application Setup**

#### **Set File Permissions**
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/local.islam.wiki

# Set directory permissions
sudo find /var/www/html/local.islam.wiki -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/html/local.islam.wiki -type f -exec chmod 644 {} \;

# Set special permissions for writable directories
sudo chmod -R 775 /var/www/html/local.islam.wiki/storage
sudo chmod -R 775 /var/www/html/local.islam.wiki/var
sudo chmod -R 775 /var/www/html/local.islam.wiki/logs
```

#### **Create Configuration Files**
```bash
# Copy configuration files
cp LocalSettings.php.example LocalSettings.php
cp .env.example .env

# Edit LocalSettings.php
nano LocalSettings.php
```

**LocalSettings.php Configuration:**
```php
<?php
// Database configuration
$wgDBserver = 'localhost';
$wgDBname = 'islamwiki';
$wgDBuser = 'islamwiki_user';
$wgDBpassword = 'secure_password';

// Islamic databases
$wgQuranDatabase = 'quran_db';
$wgHadithDatabase = 'hadith_db';
$wgIslamicDatabase = 'islamic_db';

// Site configuration
$wgSitename = 'IslamWiki';
$wgMetaNamespace = 'IslamWiki';
$wgLanguageCode = 'en';

// Security settings
$wgSecretKey = 'your-secret-key-here';
$wgUpgradeKey = 'your-upgrade-key-here';

// Performance settings
$wgCacheDirectory = '/var/www/html/local.islam.wiki/var/cache';
$wgFileCacheDirectory = '/var/www/html/local.islam.wiki/var/cache';
$wgUploadDirectory = '/var/www/html/local.islam.wiki/storage/uploads';

// Debug settings (disable in production)
$wgDebug = false;
$wgShowDebug = false;
$wgShowExceptionDetails = false;

// Extension settings
$wgEnableExtensions = [
    'DashboardExtension',
    'QuranExtension',
    'HadithExtension',
    'HijriCalendar',
    'SalahTime'
];

// Skin settings
$wgDefaultSkin = 'Bismillah';
$wgAvailableSkins = ['Bismillah', 'Muslim'];

// Load local settings if exists
if (file_exists(__DIR__ . '/IslamSettings.php')) {
    require_once __DIR__ . '/IslamSettings.php';
}
```

**Environment Configuration (.env):**
```bash
# Application
APP_NAME=IslamWiki
APP_ENV=production
APP_DEBUG=false
APP_URL=https://islam.wiki

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=islamwiki_user
DB_PASSWORD=secure_password

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### **Step 5: Run Installation Scripts**

#### **Database Migration**
```bash
# Run database migrations
php scripts/database/migrate.php

# Check migration status
php scripts/database/check-migrations.php
```

#### **Create Admin User**
```bash
# Create admin user
php scripts/create-admin-user.php

# Or manually
php scripts/database/seed-admin-user.php
```

#### **Install Extensions**
```bash
# Check extension status
php scripts/extensions/check-status.php

# Install extensions
php scripts/extensions/install-all.php
```

### **Step 6: Final Configuration**

#### **SSL Certificate (Recommended)**
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d islam.wiki -d www.islam.wiki

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### **Performance Optimization**
```bash
# Enable OPcache
sudo nano /etc/php/8.1/fpm/php.ini

# Add/modify:
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

#### **Security Hardening**
```bash
# Set secure file permissions
sudo chmod 600 /var/www/html/local.islam.wiki/LocalSettings.php
sudo chmod 600 /var/www/html/local.islam.wiki/.env

# Configure firewall
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

---

## 🧪 **Testing Installation**

### **Basic Functionality Test**
```bash
# Test web access
curl -I http://islam.wiki

# Test database connection
php scripts/database/test-connection.php

# Test extension system
php scripts/extensions/test-system.php
```

### **Performance Test**
```bash
# Test page load time
curl -w "@curl-format.txt" -o /dev/null -s "http://islam.wiki"

# Test database performance
php scripts/database/performance-test.php
```

---

## 🔍 **Troubleshooting**

### **Common Issues**

#### **Database Connection Error**
```bash
# Check MySQL status
sudo systemctl status mysql

# Check connection
mysql -u islamwiki_user -p -h localhost

# Check configuration
php scripts/database/check-connection.php
```

#### **Permission Errors**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/html/local.islam.wiki

# Fix permissions
sudo chmod -R 755 /var/www/html/local.islam.wiki
sudo chmod -R 775 /var/www/html/local.islam.wiki/storage
sudo chmod -R 775 /var/www/html/local.islam.wiki/var
```

#### **Extension Issues**
```bash
# Check extension status
php scripts/extensions/check-status.php

# Reinstall extensions
php scripts/extensions/reinstall-all.php

# Check logs
tail -f /var/www/html/local.islam.wiki/logs/error.log
```

### **Debug Mode**
```bash
# Enable debug mode
sed -i 's/$wgDebug = false;/$wgDebug = true;/' LocalSettings.php
sed -i 's/$wgShowDebug = false;/$wgShowDebug = true;/' LocalSettings.php

# Check error logs
tail -f logs/error.log
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

---

## 📚 **Post-Installation**

### **Initial Setup**
1. **Access Admin Panel**: Visit `/admin` and login
2. **Configure Site**: Set site title, description, and settings
3. **Install Extensions**: Activate required extensions
4. **Configure Skins**: Set default skin and customize
5. **Create Content**: Add initial content and pages

### **Backup Strategy**
```bash
# Create backup script
nano /root/backup-islamwiki.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/islamwiki"

# Database backup
mysqldump -u islamwiki_user -p'secure_password' islamwiki > $BACKUP_DIR/db_$DATE.sql
mysqldump -u islamwiki_user -p'secure_password' quran_db > $BACKUP_DIR/quran_db_$DATE.sql
mysqldump -u islamwiki_user -p'secure_password' hadith_db > $BACKUP_DIR/hadith_db_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/local.islam.wiki

# Clean old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

# Make executable and add to cron
chmod +x /root/backup-islamwiki.sh
crontab -e
# Add: 0 2 * * * /root/backup-islamwiki.sh
```

### **Monitoring Setup**
```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Set up log rotation
sudo nano /etc/logrotate.d/islamwiki

/var/www/html/local.islam.wiki/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

---

## 📞 **Support**

### **Documentation**
- **User Guide**: Basic usage instructions
- **Admin Guide**: Administration instructions
- **Developer Guide**: Development information
- **API Reference**: API documentation

### **Community**
- **Forum**: Community support forum
- **GitHub Issues**: Bug reports and feature requests
- **Discord**: Real-time community chat
- **Email Support**: Direct support contact

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Installation:** MediaWiki + WordPress + Modern PHP Hybrid 
# IslamWiki Installation Guide

## Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher / MariaDB 10.5 or higher
- Composer
- Web server (Apache/Nginx)

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-org/islamwiki.git
   cd islamwiki
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Database**
   - Create a MySQL database
   - Copy `config/database.php.example` to `config/database.php`
   - Update database credentials in `config/database.php`

4. **Run Migrations**
   ```bash
   php scripts/migrate.php
   ```

5. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 logs/
   ```

6. **Configure Web Server**
   - Point document root to `public/`
   - Ensure URL rewriting is enabled

7. **Access IslamWiki**
   - Navigate to your domain
   - Complete the setup wizard

## Detailed Installation

For detailed installation instructions, see `docs/user-guides/installation-guide.md`.

## Troubleshooting

- Check `logs/` directory for error logs
- Verify database connection settings
- Ensure all required PHP extensions are installed
- Check file permissions on storage and logs directories

## Support

For support, please refer to:
- `FAQ` - Frequently asked questions
- `docs/user-guides/` - User documentation
- `docs/developer/` - Developer documentation

---

**Version**: 0.0.10  
**Last Updated**: 2025-07-30 
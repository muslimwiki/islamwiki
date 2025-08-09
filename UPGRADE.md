# IslamWiki Upgrade Guide

## Before Upgrading

1. **Backup Your Data**
   ```bash
   # Backup database
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   
   # Backup files
   tar -czf islamwiki_backup_$(date +%Y%m%d_%H%M%S).tar.gz . --exclude=vendor --exclude=.git
   ```

2. **Check Requirements**
   - Verify PHP version compatibility
   - Check database version requirements
   - Review changelog for breaking changes

## Upgrade Process

### Standard Upgrade

1. **Download New Version**
   ```bash
   git pull origin main
   # or download and extract new version
   ```

2. **Update Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Run Database Migrations**
   ```bash
   php scripts/migrate.php
   ```

4. **Clear Caches**
   ```bash
   rm -rf storage/framework/cache/*
   rm -rf var/cache/*
   ```

5. **Update Configuration**
   - Review `config/` directory for new configuration files
   - Update any custom settings in `LocalSettings.php`
   - Check `IslamSettings.php` for Islamic-specific updates

### Major Version Upgrades

For major version upgrades (e.g., 0.0.x to 0.1.x):

1. **Review Breaking Changes**
   - Check `CHANGELOG.md` for breaking changes
   - Review `docs/developer/upgrade-guide.md`

2. **Test in Staging**
   - Set up staging environment
   - Test all functionality before production

3. **Update Extensions**
   - Check compatibility of installed extensions
   - Update or replace incompatible extensions

## Post-Upgrade Tasks

1. **Verify Installation**
   - Check all pages load correctly
   - Test user authentication
   - Verify Islamic features work properly

2. **Performance Check**
   - Monitor page load times
   - Check database performance
   - Review error logs

3. **Update Documentation**
   - Review and update local documentation
   - Update any custom guides

## Rollback Plan

If issues occur:

1. **Restore Database**
   ```bash
   mysql -u username -p database_name < backup_file.sql
   ```

2. **Restore Files**
   ```bash
   tar -xzf backup_file.tar.gz
   ```

3. **Clear Caches**
   ```bash
   rm -rf storage/framework/cache/*
   rm -rf var/cache/*
   ```

## Support

For upgrade issues:
- Check `logs/` for error messages
- Review `CHANGELOG.md` for known issues
- Consult `docs/developer/` for technical details

---

**Version**: 0.0.10  
**Last Updated**: 2025-07-30 
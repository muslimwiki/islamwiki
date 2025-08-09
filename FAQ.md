# IslamWiki Frequently Asked Questions

## General Questions

### What is IslamWiki?
IslamWiki is a MediaWiki-inspired platform specifically designed for Islamic content management. It provides tools for creating, editing, and managing Islamic knowledge with features tailored to Islamic scholarship and community needs.

### Is IslamWiki free to use?
Yes, IslamWiki is open-source software licensed under the GNU Affero General Public License v3.0. You can download, use, modify, and distribute it freely.

### What makes IslamWiki different from regular MediaWiki?
IslamWiki includes Islamic-specific features such as:
- Quran and Hadith integration
- Islamic calendar support
- Scholar verification system
- Islamic content guidelines
- Prayer time calculations
- Arabic text support

## Installation & Setup

### What are the system requirements?
- PHP 8.1 or higher
- MySQL 8.0+ or MariaDB 10.5+
- Web server (Apache/Nginx)
- Composer for dependency management

### How do I install IslamWiki?
See the `INSTALL` file for detailed instructions. The basic steps are:
1. Clone the repository
2. Run `composer install`
3. Configure database settings
4. Run migrations
5. Set file permissions
6. Configure web server

### Can I install IslamWiki on shared hosting?
Yes, as long as your hosting provider supports the system requirements. Check with your provider about PHP version and database support.

## Islamic Content Features

### How does the Quran integration work?
IslamWiki includes a Quran service that allows you to:
- Reference Quranic verses in articles
- Link to specific translations
- Include Arabic text with proper formatting
- Cross-reference with scholarly commentary

### What is the scholar verification system?
The scholar verification system helps ensure content accuracy by:
- Verifying scholar credentials
- Checking source authenticity
- Maintaining scholarly standards
- Documenting verification process

### How do I add Islamic content?
Islamic content can be added through:
- Standard wiki editing interface
- Islamic content templates
- Scholar-verified content forms
- Community contribution system

## Technical Questions

### Can I customize the appearance?
Yes, IslamWiki supports custom themes (skins) and you can modify the appearance through:
- Custom CSS
- Template modifications
- Theme development
- Islamic design elements

### How do I create extensions?
See `docs/developer/extension-development.md` for detailed instructions. Extensions can add:
- New Islamic features
- Custom content types
- Additional functionality
- Integration with external services

### Is there an API available?
Yes, IslamWiki provides APIs for:
- Content management
- Islamic data access
- User authentication
- External integrations

## Content Management

### How do I moderate content?
Content moderation includes:
- Community reporting system
- Islamic content guidelines
- Scholar review process
- Automated content checks

### Can I import content from other wikis?
Yes, IslamWiki supports importing content from:
- MediaWiki installations
- Other wiki platforms
- Islamic databases
- Structured data sources

### How do I backup my data?
Regular backups should include:
- Database exports
- File system backups
- Configuration files
- User uploads

## Security & Privacy

### How secure is IslamWiki?
IslamWiki includes multiple security features:
- CSRF protection
- SQL injection prevention
- XSS protection
- Secure authentication
- Islamic content security

### How do I report security issues?
Please email security@islamwiki.org with:
- Detailed description
- Steps to reproduce
- Impact assessment
- Suggested fixes

### Is user data protected?
Yes, IslamWiki follows privacy best practices:
- Secure data storage
- User consent management
- Data encryption
- Privacy policy compliance

## Community & Support

### How can I contribute?
You can contribute through:
- Code contributions
- Documentation improvements
- Islamic content creation
- Community support
- Bug reporting

### Where can I get help?
Support is available through:
- Documentation in `docs/`
- Community forums
- Issue tracking
- Developer guides

### How do I report bugs?
Report bugs through:
- GitHub issues
- Community forums
- Email support
- Developer channels

## Islamic Features

### How does the Islamic calendar work?
The Islamic calendar integration provides:
- Hijri date conversion
- Islamic event tracking
- Prayer time calculations
- Religious observance reminders

### What Islamic content standards are followed?
IslamWiki follows:
- Scholarly verification
- Source authenticity
- Islamic guidelines
- Community standards

### How do I add prayer times?
Prayer times can be:
- Calculated automatically
- Imported from APIs
- Manually configured
- Location-based

## Performance & Scalability

### How well does IslamWiki perform?
Performance depends on:
- Server configuration
- Database optimization
- Caching settings
- Content volume

### Can IslamWiki handle large sites?
Yes, with proper:
- Database optimization
- Caching configuration
- Server resources
- Content management

### How do I optimize performance?
Optimization includes:
- Database indexing
- Caching strategies
- CDN integration
- Resource optimization

---

**Version**: 0.0.10  
**Last Updated**: 2025-07-30 
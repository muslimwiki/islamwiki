# Security Policy

IslamWiki follows a responsible disclosure policy. If you discover a security vulnerability, please report it to our security team.

## Reporting a Vulnerability

Please **do not** create a public GitHub issue for security vulnerabilities.

Email `security@islamwiki.org` with:
- Detailed description of the vulnerability
- Steps to reproduce
- Potential impact assessment
- Suggested fix (if available)

## Supported Versions

We currently support security fixes for the latest minor release (0.0.x). Older versions may receive fixes at our discretion.

## Security Best Practices

### For Administrators

1. **Keep Software Updated**
   - Regularly update IslamWiki to the latest version
   - Update PHP and database software
   - Monitor security advisories

2. **Secure Configuration**
   - Use HTTPS in production
   - Configure proper file permissions
   - Use strong database passwords
   - Enable CSRF protection

3. **Access Control**
   - Use strong admin passwords
   - Implement two-factor authentication
   - Regularly review user permissions
   - Monitor login attempts

4. **Content Security**
   - Review user-generated content
   - Implement content moderation
   - Monitor for inappropriate content
   - Use Islamic content guidelines

### For Developers

1. **Input Validation**
   - Always validate and sanitize user input
   - Use prepared statements for database queries
   - Implement proper CSRF protection
   - Validate file uploads

2. **Authentication & Authorization**
   - Implement secure session management
   - Use password hashing (bcrypt/Argon2)
   - Implement proper role-based access control
   - Secure password reset functionality

3. **Data Protection**
   - Encrypt sensitive data at rest
   - Use HTTPS for all communications
   - Implement proper logging
   - Regular security audits

## Islamic Content Security (Project‑Specific)

### Content Moderation
- Review Islamic content for accuracy
- Verify scholarly sources
- Monitor for extremist content
- Implement community reporting

### Scholar Verification
- Verify scholar credentials
- Check source authenticity
- Maintain scholarly standards
- Document verification process

## Security Features

### Built-in Security
- CSRF protection on all forms
- SQL injection prevention
- XSS protection
- File upload security
- Session security

### Islamic Security
- Content authenticity verification
- Scholar credential verification
- Islamic content guidelines
- Community moderation tools

## Security Updates

### Version 0.0.10
- Enhanced CSRF protection
- Improved input validation
- Better session security
- Islamic content guidelines

### Previous Versions
- See `CHANGELOG.md` for security updates

## Emergency Contacts

- **Security Team**: security@islamwiki.org
- **Emergency**: emergency@islamwiki.org
- **Community**: community@islamwiki.org

## Resources

- `docs/security/` - Detailed security documentation
- `docs/developer/security-guide.md` - Developer security guide
- `docs/islamic/moderation-policy.md` - Content moderation policy

---

**Version**: 0.0.11  
**Last Updated**: 2025-08-08
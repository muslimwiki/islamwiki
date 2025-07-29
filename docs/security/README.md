<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
# Security Documentation

This document outlines the security measures, best practices, and guidelines for the IslamWiki application.

## Table of Contents
1. [Authentication](#authentication)
2. [Authorization](#authorization)
3. [Input Validation](#input-validation)
4. [Output Encoding](#output-encoding)
5. [CSRF Protection](#csrf-protection)
6. [XSS Prevention](#xss-prevention)
7. [SQL Injection Prevention](#sql-injection-prevention)
8. [Session Security](#session-security)
9. [File Upload Security](#file-upload-security)
10. [API Security](#api-security)
11. [Security Headers](#security-headers)
12. [Logging and Monitoring](#logging-and-monitoring)
13. [Dependency Security](#dependency-security)
14. [Compliance](#compliance)
15. [Incident Response](#incident-response)

## Authentication

### Password Security
- All passwords are hashed using Argon2id
- Minimum password length: 12 characters
- Password complexity requirements:
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character
- Password history: Last 5 passwords are remembered
- Account lockout after 5 failed login attempts
- Password reset tokens expire after 1 hour

### Multi-Factor Authentication (MFA)
- TOTP (Time-based One-Time Password) support
- Recovery codes for MFA backup
- MFA required for administrative actions

### Session Management
- Secure, HTTP-only cookies
- Session timeout: 2 hours of inactivity
- Session regeneration on login/logout
- Concurrent session control

## Authorization

### Role-Based Access Control (RBAC)

#### Roles
1. **Guest** (unauthenticated users)
   - View public pages
   - Search content

2. **User** (authenticated users)
   - All guest permissions
   - Create and edit pages
   - Upload files
   - Comment on pages

3. **Editor**
   - All user permissions
   - Moderate comments
   - Manage categories
   - Lock/unlock pages

4. **Administrator**
   - All editor permissions
   - Manage users
   - Configure system settings
   - Access system logs

### Permission System
- Fine-grained permissions for all actions
- Permission inheritance through roles
- Override capabilities for specific users

## Input Validation

### Client-Side Validation
- HTML5 form validation
- Custom JavaScript validation
- Real-time feedback

### Server-Side Validation
- All input validated on the server
- Whitelist approach for all inputs
- Type checking and sanitization
- Custom validation rules for complex scenarios

### Common Validation Rules

#### Usernames
- 3-30 characters
- Alphanumeric, underscores, and hyphens only
- Case-insensitive uniqueness

#### Emails
- Standard email format validation
- MX record verification
- Disposable email detection
- Confirmation required for new addresses

#### File Uploads
- Whitelisted file extensions
- MIME type verification
- Maximum file size: 10MB
- Virus scanning for all uploads

## Output Encoding

### Context-Aware Escaping
- HTML entity encoding
- JavaScript string escaping
- URL encoding
- CSS escaping

### Template Engine
- Automatic context-aware escaping
- Safe string handling
- Raw output only when explicitly marked safe

## CSRF Protection
- Synchronizer token pattern
- Required for all state-changing requests
- SameSite cookie attribute
- Double-submit cookie pattern for APIs

## XSS Prevention
- Content Security Policy (CSP) headers
- `X-XSS-Protection` header
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- Input sanitization
- Output encoding

## SQL Injection Prevention
- Prepared statements for all queries
- Query builder with parameter binding
- Stored procedures with input validation
- Principle of least privilege for database users

## Session Security
- Secure, HTTP-only session cookies
- Session ID regeneration on privilege changes
- Session fixation protection
- Secure flag for HTTPS-only cookies
- Session timeout with user notification

## File Upload Security
- Uploads stored outside web root
- Random, unguessable filenames
- MIME type verification
- File content validation
- Image manipulation to prevent steganography
- Quarantine for suspicious files

## API Security

### Authentication
- OAuth 2.0 with JWT
- API key authentication
- Rate limiting
- Request signing

### Data Protection
- Field-level encryption for sensitive data
- Masking of sensitive information in responses
- Pagination for large result sets
- Input validation for all parameters

### Rate Limiting
- Tiered rate limiting based on authentication
- IP-based rate limiting for public endpoints
- `Retry-After` headers
- Rate limit information in response headers

## Security Headers

### HTTP Headers
```
Content-Security-Policy: default-src 'self';
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### CORS Headers
```
Access-Control-Allow-Origin: https://trusted-origin.com
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
```

## Logging and Monitoring

### Security Events
- Failed login attempts
- Password changes
- Permission changes
- Sensitive operations
- File uploads
- Security exceptions

### Monitoring
- Real-time alerting for suspicious activities
- Anomaly detection
- Regular security scans
- Log analysis for patterns

## Dependency Security

### Management
- Regular dependency updates
- Automated vulnerability scanning
- Software Bill of Materials (SBOM)
- Pinned dependency versions

### Third-Party Services
- Vetted third-party services
- Limited API scopes
- Regular access reviews
- Secure credential management

## Compliance

### Data Protection
- GDPR compliance
- Data retention policies
- Right to be forgotten
- Data portability

### Auditing
- Regular security audits
- Penetration testing
- Code reviews
- Compliance certifications

## Incident Response

### Reporting
- Security contact: security@islam.wiki
- Responsible disclosure policy
- Bug bounty program

### Response Plan
1. **Identification**
   - Detect and confirm incident
   - Classify severity

2. **Containment**
   - Isolate affected systems
   - Preserve evidence

3. **Eradication**
   - Remove threat
   - Patch vulnerabilities

4. **Recovery**
   - Restore systems
   - Verify security

5. **Post-Mortem**
   - Root cause analysis
   - Document lessons learned
   - Update security controls

### Communication
- Internal notification procedures
- External disclosure timeline
- Customer notification process
- Regulatory reporting requirements

---
*Last Updated: 2025-07-25*

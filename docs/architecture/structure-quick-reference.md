# IslamWiki Structure Quick Reference

## 🚨 **CRITICAL RULES - ALWAYS CHECK BEFORE MAKING CHANGES**

### ❌ **NEVER PUT IN `public/`**
- CSS files
- JavaScript files  
- Template files
- PHP source code
- Configuration files
- Cache files

### ✅ **CORRECT LOCATIONS**

| File Type | Location | Web Access |
|-----------|----------|------------|
| **Framework CSS** | `resources/assets/css/` | `/assets/css/` |
| **Framework JS** | `resources/assets/js/` | `/assets/js/` |
| **Skin CSS** | `skins/{SkinName}/css/` | `/skins/{SkinName}/css/` |
| **Skin JS** | `skins/{SkinName}/js/` | `/skins/{SkinName}/js/` |
| **Extension Assets** | `extensions/{Name}/assets/` | `/extensions/{Name}/assets/` |
| **Templates** | `resources/views/` | Rendered by app |
| **PHP Code** | `src/` | N/A |
| **Entry Points** | `public/` | Direct web access |
| **Language Files** | `languages/locale/` | Via TranslationService |

## 📁 **Directory Purposes**

- `resources/` = Frontend assets (CSS, JS, templates)
- `src/` = PHP source code only
- `public/` = Web entry points only
- `skins/` = Skin-specific assets (WordPress-style themes)
- `extensions/` = Extension system (WordPress-style plugins)
- `config/` = Configuration files
- `database/` = Database migrations
- `scripts/` = Utility scripts
- `maintenance/` = Debug/test files
- `storage/` = Application storage
- `backup/` = Backup files
- `logs/` = Application logs
- `var/` = Variable data (cache, temporary files)
- `languages/` = Language files (JSON-based)
- `docs/` = Documentation

## 🔍 **Validation Commands**

```bash
# Check for misplaced assets
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins | grep -v extensions

# Check public directory (should only have entry points)
ls -la public/

# Verify framework assets
ls -la resources/assets/css/ resources/assets/js/

# Verify skin assets  
ls -la skins/*/css/ skins/*/js/

# Verify extension assets
ls -la extensions/*/assets/

# Verify all directories exist
ls -la backup/ logs/ var/ storage/ languages/
```

## 📋 **Before Any File Operation**

1. **Check this reference**
2. **Verify current structure**
3. **Follow naming conventions**
4. **Test web access paths**
5. **Update documentation if needed**

---

## 🏗️ **Architecture Summary**

**IslamWiki = MediaWiki + WordPress + Modern PHP**

- **MediaWiki**: Content management, versioning, collaborative editing
- **WordPress**: Plugin system, theme system, user experience  
- **Modern PHP**: Performance, security, developer experience

**Key Features:**
- ✅ **SabilRouting**: Inline route definition (no external route files)
- ✅ **Extension System**: WordPress-style plugin architecture
- ✅ **Skin System**: WordPress-style theme architecture
- ✅ **Multi-Database**: Separate databases for different content types
- ✅ **Performance**: Built-in caching and optimization
- ✅ **Security**: Enterprise-grade with Islamic content validation

---

**Reference:** `docs/structure.md` for full documentation  
**Architecture:** `docs/architecture/hybrid-architecture.md` for hybrid philosophy  
**Standards:** `docs/STANDARDS.md` for development standards 
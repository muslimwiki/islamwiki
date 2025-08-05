# IslamWiki Structure Quick Reference

## 🚨 CRITICAL RULES - ALWAYS CHECK BEFORE MAKING CHANGES

### ❌ NEVER PUT IN `public/`
- CSS files
- JavaScript files  
- Template files
- PHP source code
- Configuration files

### ✅ CORRECT LOCATIONS

| File Type | Location | Web Access |
|-----------|----------|------------|
| **Framework CSS** | `resources/assets/css/` | `/assets/css/` |
| **Framework JS** | `resources/assets/js/` | `/assets/js/` |
| **Skin CSS** | `skins/{SkinName}/css/` | `/skins/{SkinName}/css/` |
| **Skin JS** | `skins/{SkinName}/js/` | `/skins/{SkinName}/js/` |
| **Templates** | `resources/views/` | Rendered by app |
| **PHP Code** | `src/` | N/A |
| **Entry Points** | `public/` | Direct web access |

## 📁 Directory Purposes

- `resources/` = Frontend assets (CSS, JS, templates)
- `src/` = PHP source code only
- `public/` = Web entry points only
- `skins/` = Skin-specific assets
- `config/` = Configuration files
- `database/` = Database migrations
- `extensions/` = Extension files
- `scripts/` = Utility scripts
- `maintenance/` = Debug/test files
- `storage/` = Application storage
- `backup/` = Backup files
- `logs/` = Application logs
- `var/` = Variable data
- `routes/` = Route definitions
- `docs/` = Documentation

## 🔍 Validation Commands

```bash
# Check for misplaced assets
find . -name "*.css" -o -name "*.js" | grep -v vendor | grep -v skins

# Check public directory (should only have entry points)
ls -la public/

# Verify framework assets
ls -la resources/assets/css/ resources/assets/js/

# Verify skin assets  
ls -la skins/*/css/ skins/*/js/

# Verify all directories exist
ls -la backup/ logs/ var/ storage/
```

## 📋 Before Any File Operation

1. **Check this reference**
2. **Verify current structure**
3. **Follow naming conventions**
4. **Test web access paths**
5. **Update documentation if needed**

---

**Reference:** `docs/STRUCTURE.md` for full documentation
**Standards:** `docs/STANDARDS.md` for development standards 
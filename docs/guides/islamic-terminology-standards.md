# Islamic Terminology Standards Guide

## 🎯 **Overview**

This guide establishes the standard Islamic terminology to be used throughout the IslamWiki platform. Using authentic Islamic terms instead of English translations maintains cultural authenticity, improves user experience for Muslim users, and creates a more professional and respectful platform.

---

## 🕌 **Core Terminology Standards**

### **Salah (Prayer)**
- **✅ Correct**: "salah", "salah times", "salah system"
- **❌ Incorrect**: "prayer", "prayer times", "prayer system"
- **Usage**: All references to Islamic prayer should use "salah"
- **Examples**: 
  - "Salah times integration" (not "Prayer times integration")
  - "Salah notification system" (not "Prayer notification system")
  - "Salah API" (not "Prayer API")

### **Quran (Holy Book)**
- **✅ Correct**: "Quran", "Quranic", "Quran system"
- **❌ Incorrect**: "Koran", "Koranic", "Holy Book system"
- **Usage**: Always use "Quran" in all contexts
- **Examples**:
  - "Quran search engine" (not "Koran search engine")
  - "Quranic content" (not "Koranic content")
  - "Quran database" (not "Holy Book database")

### **Hadith (Prophetic Traditions)**
- **✅ Correct**: "Hadith", "Hadith system", "Hadith collection"
- **❌ Incorrect**: "Ahadith" (in English contexts), "Prophetic traditions"
- **Usage**: Use "Hadith" for singular and plural in English contexts
- **Examples**:
  - "Hadith search" (not "Ahadith search")
  - "Hadith database" (not "Prophetic traditions database")
  - "Hadith authentication" (not "Ahadith authentication")

### **Hijri (Islamic Calendar)**
- **✅ Correct**: "Hijri calendar", "Hijri date", "Hijri system"
- **❌ Incorrect**: "Islamic calendar", "Muslim calendar", "Islamic date"
- **Usage**: Use "Hijri" when referring to the calendar system specifically
- **Examples**:
  - "Hijri calendar integration" (not "Islamic calendar integration")
  - "Hijri date converter" (not "Islamic date converter")
  - "Hijri event system" (not "Islamic event system")

### **Adhan (Call to Prayer)**
- **✅ Correct**: "Adhan", "Adhan system", "Adhan notification"
- **❌ Incorrect**: "Call to prayer", "Prayer call", "Prayer announcement"
- **Usage**: Use "Adhan" for the Islamic call to prayer
- **Examples**:
  - "Adhan integration" (not "Call to prayer integration")
  - "Adhan audio system" (not "Prayer call audio system")
  - "Adhan timing" (not "Prayer call timing")

### **Qibla (Direction of Prayer)**
- **✅ Correct**: "Qibla", "Qibla direction", "Qibla system"
- **❌ Incorrect**: "Direction of prayer", "Prayer direction", "Mecca direction"
- **Usage**: Use "Qibla" for the direction Muslims face during salah
- **Examples**:
  - "Qibla finder" (not "Prayer direction finder")
  - "Qibla compass" (not "Direction of prayer compass")
  - "Qibla API" (not "Prayer direction API")

---

## 🔧 **Implementation Guidelines**

### **Code Naming**
```php
// ✅ Correct - Using Islamic terminology
class SalahTimeCalculator {}      // Not PrayerTimeCalculator
class QuranSearchEngine {}        // Not KoranSearchEngine
class HadithDatabase {}           // Not AhadithDatabase
class HijriCalendarSystem {}      // Not IslamicCalendarSystem
class AdhanNotificationService {} // Not PrayerCallService
class QiblaDirectionFinder {}     // Not PrayerDirectionFinder

// ❌ Incorrect - Using English translations
class PrayerTimeCalculator {}
class KoranSearchEngine {}
class AhadithDatabase {}
class IslamicCalendarSystem {}
class PrayerCallService {}
class PrayerDirectionFinder {}
```

### **File Naming**
```
// ✅ Correct - Using Islamic terminology
salah-times.php           // Not prayer-times.php
quran-search.php          // Not koran-search.php
hadith-management.php     // Not ahadith-management.php
hijri-calendar.php        // Not islamic-calendar.php
adhan-system.php          // Not prayer-call-system.php
qibla-finder.php          // Not prayer-direction-finder.php

// ❌ Incorrect - Using English translations
prayer-times.php
koran-search.php
ahadith-management.php
islamic-calendar.php
prayer-call-system.php
prayer-direction-finder.php
```

### **Database Naming**
```sql
-- ✅ Correct - Using Islamic terminology
CREATE TABLE salah_times (
    id INT PRIMARY KEY,
    salah_name VARCHAR(50),
    salah_time TIME
);

CREATE TABLE quran_verses (
    id INT PRIMARY KEY,
    surah_name VARCHAR(100),
    ayah_number INT
);

CREATE TABLE hadith_collections (
    id INT PRIMARY KEY,
    hadith_text TEXT,
    narrator VARCHAR(100)
);

-- ❌ Incorrect - Using English translations
CREATE TABLE prayer_times (
    id INT PRIMARY KEY,
    prayer_name VARCHAR(50),
    prayer_time TIME
);
```

### **API Endpoints**
```
// ✅ Correct - Using Islamic terminology
GET /api/salah-times          // Not /api/prayer-times
GET /api/quran/search         // Not /api/koran/search
GET /api/hadith/authenticate  // Not /api/ahadith/authenticate
GET /api/hijri/convert        // Not /api/islamic/convert
GET /api/adhan/notify         // Not /api/prayer-call/notify
GET /api/qibla/direction      // Not /api/prayer-direction/direction

// ❌ Incorrect - Using English translations
GET /api/prayer-times
GET /api/koran/search
GET /api/ahadith/authenticate
GET /api/islamic/convert
GET /api/prayer-call/notify
GET /api/prayer-direction/direction
```

---

## 📚 **Documentation Standards**

### **User-Facing Text**
```markdown
# ✅ Correct - Using Islamic terminology

## Salah Times
Get accurate salah times for your location with our advanced calculation system.

## Quran Search
Search through the complete Quran with our powerful search engine.

## Hadith Database
Access authentic Hadith collections with detailed authentication information.

# ❌ Incorrect - Using English translations

## Prayer Times
Get accurate prayer times for your location with our advanced calculation system.

## Koran Search
Search through the complete Koran with our powerful search engine.

## Ahadith Database
Access authentic Ahadith collections with detailed authentication information.
```

### **Technical Documentation**
```markdown
# ✅ Correct - Using Islamic terminology

## Salah Time Calculation
The salah time calculation system uses astronomical algorithms to determine accurate prayer times.

## Quran Content Management
The Quran content management system handles verse storage and retrieval.

## Hadith Authentication System
The Hadith authentication system verifies the authenticity of prophetic traditions.

# ❌ Incorrect - Using English translations

## Prayer Time Calculation
The prayer time calculation system uses astronomical algorithms to determine accurate prayer times.

## Koran Content Management
The Koran content management system handles verse storage and retrieval.

## Ahadith Authentication System
The Ahadith authentication system verifies the authenticity of prophetic traditions.
```

---

## 🌐 **Internationalization Considerations**

### **Language-Specific Usage**
- **English**: Use Islamic terms (salah, Quran, Hadith, etc.)
- **Arabic**: Use Arabic terms (صلاة، قرآن، حديث، etc.)
- **Other Languages**: Use appropriate Islamic terms for that language

### **Translation Guidelines**
```php
// ✅ Correct - Language-aware terminology
$translations = [
    'en' => [
        'salah_times' => 'Salah Times',
        'quran_search' => 'Quran Search',
        'hadith_database' => 'Hadith Database'
    ],
    'ar' => [
        'salah_times' => 'أوقات الصلاة',
        'quran_search' => 'البحث في القرآن',
        'hadith_database' => 'قاعدة بيانات الحديث'
    ]
];

// ❌ Incorrect - English translations in all languages
$translations = [
    'en' => [
        'salah_times' => 'Prayer Times',
        'quran_search' => 'Koran Search',
        'hadith_database' => 'Ahadith Database'
    ]
];
```

---

## 📋 **Compliance Checklist**

### **For Developers**
- [ ] All code uses Islamic terminology (salah, Quran, Hadith, etc.)
- [ ] No English translations in class names, methods, or variables
- [ ] Database tables use Islamic terminology
- [ ] API endpoints use Islamic terminology
- [ ] Configuration files use Islamic terminology

### **For Documentation Writers**
- [ ] All user-facing text uses Islamic terminology
- [ ] Technical documentation uses Islamic terminology
- [ ] Examples and code snippets use Islamic terminology
- [ ] No English translations in documentation
- [ ] Consistent terminology across all documents

### **For Translators**
- [ ] Maintain Islamic terminology in target languages
- [ ] Use appropriate Islamic terms for each language
- [ ] Avoid literal translations that lose Islamic meaning
- [ ] Consult with Islamic scholars for proper terminology
- [ ] Maintain consistency across all translations

---

## 🚨 **Common Mistakes to Avoid**

### **Terminology Errors**
1. **"Prayer" instead of "Salah"**
   - ❌ "Prayer time system"
   - ✅ "Salah time system"

2. **"Koran" instead of "Quran"**
   - ❌ "Koran search engine"
   - ✅ "Quran search engine"

3. **"Ahadith" in English contexts**
   - ❌ "Ahadith database"
   - ✅ "Hadith database"

4. **"Islamic calendar" instead of "Hijri"**
   - ❌ "Islamic calendar system"
   - ✅ "Hijri calendar system"

5. **"Call to prayer" instead of "Adhan"**
   - ❌ "Call to prayer system"
   - ✅ "Adhan system"

### **Context Errors**
1. **Using English terms in technical contexts**
   - ❌ "Prayer API documentation"
   - ✅ "Salah API documentation"

2. **Mixing terminology within the same document**
   - ❌ "Salah times and prayer notifications"
   - ✅ "Salah times and salah notifications"

3. **Inconsistent terminology across related features**
   - ❌ "Salah times" and "Prayer reminders"
   - ✅ "Salah times" and "Salah reminders"

---

## 📖 **Additional Resources**

### **Islamic Terminology References**
- [Islamic Studies Glossary](https://www.islamicstudies.info/glossary)
- [Quranic Terminology](https://www.quranicterminology.com)
- [Hadith Studies Dictionary](https://www.hadithstudies.org/dictionary)

### **Language-Specific Resources**
- [Arabic Islamic Terms](https://www.arabicislamicterms.org)
- [Urdu Islamic Terminology](https://www.urduislamicterms.org)
- [Indonesian Islamic Terms](https://www.indonesianislamicterms.org)

---

## 📄 **Copyright Notice**

```
Copyright (c) 2025 IslamWiki Development Team

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
```

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Islamic Terminology Standards Complete ✅ 
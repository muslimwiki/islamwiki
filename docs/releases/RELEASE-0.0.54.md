# Release 0.0.54 - Quran Data Model, Salah Times, and Extensions

**Release Date:** 2025-08-12  
**Status:** Feature + Infrastructure Release  
**Type:** Quran data model refactor, Salah Times system, new extensions

## 🌙 Salah Times System

- New `SalahTime` domain with controller `SalahTimeController` and model `src/Models/SalahTime.php`
- Added Salah calculation utilities: `src/Core/Islamic/SalahTimeCalculator.php` (and minimal variant)
- Frontend calculator helper: `resources/assets/js/salah-calculator.js`
- Views for prayer times integrated into site navigation and pages
- New migration for Salah tables: `database/migrations/0010_salah_times_schema.php`

## 📖 Quran Data & Rendering

- Quran schema refactor and new models:
  - Added: `QuranSurah`, `QuranPage`, `QuranJuz`, `QuranTranslation`
  - Removed: legacy `QuranVerse` model
- New views for Quran browsing: `resources/views/quran/*.twig` (surah, ayah, page, juz, search, widgets)
- Data import and maintenance scripts for Quran content (under `scripts/database/` and `scripts/`)
- Routing updates for Quran pages and search

## 🧩 New Extensions

- `extensions/QuranExtension/` – Quran features modularized as an extension
- `extensions/HadithExtension/` – scaffolding for Hadith features
- `extensions/HijriCalendar/` – Hijri calendar features
- `extensions/SalahTime/` – Salah time features

## 🔎 Search & Iqra

- Search indexes migration updates: `database/migrations/0011_search_indexes.php`
- `src/Core/Search/IqraSearch.php` improvements and web tests added
- Updated search views: `resources/views/search/index.twig`, `resources/views/iqra-search/index.twig`

## 🧱 Infrastructure & Core

- Routing and controllers expanded: Quran, Hadith, HijriCalendar, SalahTime, Settings
- `public/index.php` and `.htaccess` updates; route tables in `routes/web.php` and `config/routes.php`
- Database utilities and setup scripts enhanced (`scripts/database/*.php`)
- Caching, formatting, and knowledge graph improvements in `src/Core/*`

## 🧰 Developer Experience

- Added debug pages under `maintenance/debug/` and tests under `maintenance/tests/` and `scripts/tests/`
- PHPStan and PHPUnit configs introduced: `phpstan.neon`, `phpunit.xml`
- Code style tooling: `.php-cs-fixer.php`, `phpcs.xml` adjustments
- Docs helper script: `scripts/docs/normalize_code_fences.php`

## 🖼️ UI/UX

- New error pages: `resources/views/errors/{401,404,500}.twig`
- Bismillah skin polish and Quran page designs updated

## 📚 Documentation

- New docs:
  - `docs/features/SALAH_TIMES.md`
  - `docs/features/QURAN_IMPLEMENTATION_SUMMARY.md`
  - `docs/features/QURAN_STATUS_UPDATE.md`
  - `docs/extensions/QuranImportSystem.md` (+ summary)
- Updated docs navigation and examples

## 🔐 Security & Migrations

- Consolidated/renamed security migrations (`0017_advanced_security_schema.php`) and removed legacy `0013_advanced_security_schema.php`
- Safe defaults and guards added in controllers and core utils

## ⚠️ Breaking Changes

- Legacy `src/Models/QuranVerse.php` removed; use `QuranAyah` representation via new models/views/routes
- Migration paths updated; run the new Quran migration and data population scripts

## ✅ Upgrade Notes

1. Back up your database
2. Run migrations and data import helpers as needed:
   ```bash
   php scripts/database/setup_database.php
   php scripts/database/rename_verse_to_ayah_tables.php # if applicable
   php scripts/database/populate_surah_details.php
   php scripts/database/seed_quran_translations.php
   ```
3. Clear caches and warm routes

---

**Next:** 0.0.55 – Docs polish (admonitions), search improvements, and Hadith UI

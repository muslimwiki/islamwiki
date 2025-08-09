# Release 0.0.53 - Docs Viewer and Enhanced Markdown

**Release Date:** 2025-08-09  
**Status:** Feature Release  
**Type:** Documentation UX & Markdown Enhancements

## 📚 Markdown Docs Viewer

- New extension: MarkdownDocsViewer
  - Adds a "Docs" link to the main navigation
  - Renders `/docs` as a two-pane layout:
    - Left: Folder-aware sidebar with collapsible directories and search filter
    - Right: Markdown content (wider pane)
  - Supports nested directories; current file's ancestors auto-expand

## ✍️ Enhanced Markdown

- Pre/post render hooks integrated into the docs pipeline
- Added support for:
  - Images: `![alt](src)`
  - Strikethrough: `~~text~~`
  - GFM tables with alignment
  - Blockquotes and horizontal rules
  - Auto-generated in-page TOC (Contents)
- Progress bars (inspired by PyMdown Extensions ProgressBar):
  - Syntax examples:
    - `[=65% "Roadmap"]`
    - `[== 3/5 'Phase 3']`
    - With classes: `[=85% "Milestone"]{: .thin .candystripe}`
  - Produces `.progress`, `.progress-bar`, `.progress-label`, and `progress-XXplus` classes

## 🗂️ Documentation Organization

- Moved architecture docs under `docs/architecture/`:
  - `AMAN_SECURITY_UPDATE.md`, `CORE_ARCHITECTURE_UPDATE.md`, `bayan-system.md`
- Moved initial Cursor prompt to `docs/plans/`
- Moved naming conventions to `docs/guides/`

## 🧭 Routes & Templates

- Routes added: `/docs`, plus nested path handling
- Layout templates updated to accept extension-provided nav links

## ✅ User Experience

- **Discoverable Docs**: One-click access via Docs in main nav
- **Better Readability**: Auto TOC, tables, and progress visuals
- **Organized Content**: Folder-aware navigation in sidebar

## 🛠️ Developer Experience

- Extension hooks for markdown pre/post processing
- Simple syntax for progress bars and rich markdown

## 🔧 Technical Notes

- Extension system enabled and wired via service provider
- Docs renderer operates without external runtime deps; can be swapped later if needed

---

**Next Release:** 0.0.54 - Docs polish (global code-fence normalization, admonitions), search improvements  
**Target Date:** 2025-08-13



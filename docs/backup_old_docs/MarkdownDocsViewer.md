# MarkdownDocsViewer Extension

Presents Markdown files from `docs/` in a two-pane viewer with a collapsible sidebar tree, search, and enhanced rendering.

## Features

- Sidebar tree with nested folders and collapse/expand
- Search filter on filenames
- 1/3 (sidebar) + 2/3 (content) layout
- Code block enhancements (copy button; per-block ASCII toggle for `tree`)
- Auto TOC, color swatches, styled comments via EnhancedMarkdown + viewer
- Secondary list for root-level `.md` files under “Project Files”

## Routes

- `/docs` → default to `docs/README.md`
- `/docs/{path...}` → open a file or directory under `docs/`
- `/docs/_root/{file}.md` → open permitted root-level Markdown (e.g., `CHANGELOG.md`)

## Notes

- The sidebar lists only `docs/`. Root `.md` files appear in a separate section.
- To enable ASCII/Unicode toggle for file trees, use a fenced block with `tree`:

```tree
repo/
├── docs
└── src
```

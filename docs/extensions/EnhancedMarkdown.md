# EnhancedMarkdown Extension

The EnhancedMarkdown extension augments the built-in Markdown renderer with additional features and conveniences tailored for IslamWiki.

## Features

- Progress bar shorthand (converted to styled HTML in the Docs viewer)
- Auto-generated table of contents
- Nicer defaults for tables, images, blockquotes, and code blocks
- Islamic content helpers (Quran, Hadith, Hijri date, Salah times, Scholar references)
- Arabic text wrapping with proper RTL handling

## Progress Bars

Syntax inspired by PyMdown ProgressBar. Use bracket shorthand and optional classes:

```markdown
[=65% "Indexing"]{: .success}
[=20% "WIP"]{: .warning}
[=100% "Done"]{: .primary}
[=3/5 "Tasks"]{: .info}
```

- Percentage can be `NN%` or a fraction `N/D`.
- Title in quotes is optional; if omitted, the label defaults to the calculated percent.
- Classes are added with `{:.class1 .class2}` and map to CSS like `progress progress-XXplus`.

These are transformed at parse time into placeholders the Docs viewer renders as a progress bar.

### Live Example

[=45% "Docs Overhaul"]{: .info}

```markdown
[=45% "Docs Overhaul"]{: .info}
```

[=80% "EnhancedMarkdown"]{: .success}

```markdown
[=80% "EnhancedMarkdown"]{: .success}
```

[=30% "Bayan Graph"]{: .warning}

```markdown
[=30% "Bayan Graph"]{: .warning}
```

The above examples render each progress bar followed by its exact source.

## Islamic Content Helpers

Inline helpers that render structured placeholders:

- Quran verse: `{{quran:1:1}}`
- Hadith citation: `{{hadith:bukhari:1:1}}`
- Hijri date: `{{hijri:1445-01-01}}`
- Salah times: `{{prayer-times:location:mecca}}`
- Scholar reference: `{{scholar:ibn-taymiyyah}}`

## Arabic Text Handling

Detected Arabic sequences are wrapped with `dir="rtl"` and `lang="ar"` for better layout and accessibility.

## Styling and TOC

- Tables receive `.md-table` and are styled by the Bismillah skin.
- Code blocks receive `.md-code` and include copy buttons in the Docs viewer.
- Blockquotes get `.md-quote` styling.
- A simple table of contents is injected when headings are present.

## Editor Shortcuts

The extension provides shortcuts the editor can use:

- `quran` → `{{quran:1:1}}`
- `hadith` → `{{hadith:bukhari:1:1}}`
- `hijri` → `{{hijri:1445-01-01}}`
- `prayer` → `{{prayer-times:location:mecca}}`
- `scholar` → `{{scholar:ibn-taymiyyah}}`

## Notes

- Progress bars only render in the Docs viewer context where placeholders are converted to HTML.
- For file-tree blocks, use fenced code with `tree` language to enable the ASCII/Unicode toggle:

```tree
project/
├── src
│   └── index.php
└── README.md
```



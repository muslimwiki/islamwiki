# Enhanced Markdown Syntax Guide

## Overview
IslamWiki uses **Enhanced Markdown with Wiki Extensions** - a powerful combination of standard Markdown syntax with custom wiki features for internal linking, templates, categories, references, and Islamic content management.

## Basic Markdown

### Text Formatting
- **Bold**: `**text**` → **text**
- **Italic**: `*text*` → *text*
- **Bold Italic**: `***text***` → ***text***
- **Strikethrough**: `~~text~~` → ~~text~~

### Headings
- `# Heading 1`
- `## Heading 2`
- `### Heading 3`
- `#### Heading 4`

### Lists
- **Unordered**: `- Item` or `* Item`
- **Ordered**: `1. Item`
- **Nested**: Indent with spaces

### Links and Images
- **External Link**: `[Text](https://example.com)`
- **Image**: `![Alt text](image.jpg)`
- **Image with title**: `![Alt text](image.jpg "Title")`

### Code
- **Inline**: `` `code` ``
- **Block**: ``` ```code block``` ```

### Blockquotes
- `> Quote text`

### Tables
```markdown
| Header 1 | Header 2 |
|----------|----------|
| Cell 1   | Cell 2   |
```

### Horizontal Rules
- `---` or `***`

## Wiki Extensions

### Internal Links
- **Simple**: `[[Page Name]]` → Links to a wiki page
- **Display Text**: `[[Page Name|Display Text]]` → Links with custom display text
- **Automatic Page Creation**: Clicking non-existent links offers to create the page

### Categories
- **Format**: `[Category:Name]`
- **Example**: `[Category:Islam]` → Adds page to Islam category
- **Multiple**: `[Category:Islam][Category:Religions]`

### Templates
Templates provide consistent formatting and reusable content blocks.

#### Basic Templates
- **Infobox**: `{{Infobox|title=Title|content=Content}}`
- **Warning**: `{{Warning|message=Important notice}}`
- **Note**: `{{Note|message=Additional information}}`
- **Success**: `{{Success|message=Operation completed}}`
- **Error**: `{{Error|message=Something went wrong}}`

#### Page Information Templates
- **About**: `{{About|topic||disambiguation}}` → Disambiguation notice
- **Page Protection**: `{{pp-semi-indef}}` → Shows page protection status
- **Article Quality**: `{{good article}}` → Indicates article quality
- **Date Formatting**: `{{Use dmy dates|date=March 2022}}` → Sets date format preference
- **Spelling**: `{{Use Oxford spelling|date=May 2022}}` → Sets spelling preference

#### Navigation Templates
- **Sidebar**: `{{Sidebar Islam}}` → Adds navigation sidebar
- **Main Article**: `{{Main|Article Name}}` → Links to main article
- **Further Information**: `{{Further information|[[Link1]]|[[Link2]]}}` → Additional reading
- **See Also**: `{{See also|[[Link1]]|[[Link2]]}}` → Related articles

#### Quote Templates
- **Cquote**: `{{Cquote|text|source}}` → Formatted quote with source

#### Reference Templates
- **Reflist**: `{{reflist|30em}}` → Generates reference list with column width
- **Portal**: `{{Islam portal}}` → Links to portal page

### References
- **Simple**: `<ref>Reference content</ref>` → Adds numbered reference
- **Named**: `<ref name="name">Reference content</ref>` → Named reference for reuse
- **Quran**: `{{qref|surah|ayah|b=yl|c=y|y=si}}` → Quran verse reference
- **Template**: `{{Reference|source=Source}}` → Reference from template

### File Handling
- **Images**: `[[File:filename.jpg|alt=Alt text|thumb|caption=Caption]]`
- **Parameters**:
  - `alt=text` → Alternative text for accessibility
  - `thumb` → Thumbnail display
  - `caption=text` → Image caption

### Special Formatting
- **Abbreviations**: `<abbr>text</abbr>` → Abbreviation with tooltip
- **Nowiki**: `<nowiki>text</nowiki>` → Prevents wiki processing
- **Blockquotes**: `<blockquote>text</blockquote>` → Formatted quote block
- **Arabic Text**: Automatically detected and formatted with RTL support

## Islamic Content Extensions

### Quran Templates
- **Verse**: `{{Quran|surah=1|ayah=1-7}}` → Displays Quran verses
- **Chapter**: `{{Quran|surah=1}}` → Displays entire chapter
- **Translation**: `{{Quran|surah=1|ayah=1|translation=en}}`

### Hadith Templates
- **Single Hadith**: `{{Hadith|book=Bukhari|number=1}}` → Displays Hadith
- **Hadith Chain**: `{{HadithChain|narrators=...}}` → Shows transmission chain
- **Hadith Grade**: `{{HadithGrade|grade=Sahih}}` → Indicates authenticity

### Scholar Templates
- **Biography**: `{{Scholar|name=Ibn Sina|period=Medieval|field=Medicine}}` → Scholar information
- **Works**: `{{ScholarWorks|scholar=Ibn Sina}}` → Lists major works

### Fatwa Templates
- **Islamic Ruling**: `{{Fatwa|scholar=Name|topic=Topic|date=Date}}` → Fatwa information
- **Type**: `{{FatwaType|type=Obligatory}}` → Categorizes fatwa

### Islamic Utilities
- **Prayer Times**: `{{PrayerTimes}}` → Current prayer times
- **Hijri Calendar**: `{{HijriCalendar}}` → Islamic date
- **Qibla Direction**: `{{QiblaDirection}}` → Prayer direction

## Advanced Features

### Template Parameters
Templates support various parameter formats:
- **Key-Value**: `param=value`
- **Positional**: First, second, third parameters
- **Mixed**: Combine both formats

### Conditional Rendering
Some templates support conditional logic:
- **Date-based**: Show different content based on dates
- **User-based**: Adapt content for different user types
- **Context-based**: Change based on page context

### Custom Templates
Create your own templates:
- **Format**: `{{TemplateName|param1=value1|param2=value2}}`
- **Fallback**: Unknown templates display with parameters
- **Extensibility**: Easy to add new template types

## Best Practices

### Content Organization
1. **Start with clear headings** using Markdown heading syntax
2. **Use templates consistently** for similar content types
3. **Organize with categories** for easy navigation
4. **Include references** for all factual claims

### Template Usage
1. **Choose appropriate templates** for content type
2. **Provide all required parameters** for proper rendering
3. **Use consistent formatting** across similar content
4. **Test template rendering** before publishing

### Reference Management
1. **Cite sources** for all factual information
2. **Use appropriate reference types** (simple, named, Quran)
3. **Maintain reference consistency** throughout articles
4. **Include complete source information**

### File Management
1. **Use descriptive filenames** for uploads
2. **Provide alt text** for accessibility
3. **Choose appropriate display options** (thumb, caption)
4. **Optimize file sizes** for web delivery

## Examples

### Complete Article Structure
```markdown
# Article Title

{{About|main topic||disambiguation}}

{{Infobox|title=Quick Facts|content=Key information}}

## Introduction
Main content with [[internal links]] and <ref>references</ref>.

## Main Sections
### Section 1
Content with {{templates}} and **formatting**.

### Section 2
More content with [Category:MainCategory].

## References
{{reflist}}

[[Category:MainCategory]]
[[Category:SubCategory]]
```

### Template Combinations
```markdown
{{Sidebar Islam}}
{{Main|Five Pillars of Islam}}

{{Cquote|Indeed, the religion in the sight of Allah is Islam.|Quran 3:19}}

{{Further information|[[Shahada]]|[[Salah]]|[[Zakat]]}}
```

## Troubleshooting

### Common Issues
1. **Template not rendering**: Check parameter format and template name
2. **Links not working**: Verify page names and internal link syntax
3. **References missing**: Ensure proper `<ref>` tag usage
4. **Categories not showing**: Check category syntax and permissions

### Getting Help
- **Documentation**: Check this guide for syntax examples
- **Community**: Ask other editors for assistance
- **Templates**: Look at existing pages for examples
- **Testing**: Use preview mode to test formatting

---

*This guide covers the comprehensive Enhanced Markdown system used by IslamWiki. For additional help, consult the community or refer to existing articles for examples.* 
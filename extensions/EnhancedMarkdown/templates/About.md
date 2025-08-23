# Template:About

This template creates a disambiguation notice at the top of articles, explaining what the article is about and linking to related disambiguation pages.

## Usage

```markdown
{{About|main topic||disambiguation page}}
```

## Parameters

1. **First parameter**: The main topic the article covers
2. **Second parameter**: (optional) Additional context
3. **Third parameter**: (optional) Link to disambiguation page

## Examples

### Basic usage
```markdown
{{About|the religion||Islam (disambiguation)}}
```

### With additional context
```markdown
{{About|the Islamic concept of submission||other meanings}}
```

### Simple topic
```markdown
{{About|the Prophet Muhammad}}
```

## Output

<div class="template about-template">
    <p>This article is about <strong>the religion</strong>. For other uses, see <a href="/wiki/Islam_(disambiguation)">Islam (disambiguation)</a>.</p>
</div>

## When to use

Use this template when:

- An article title could refer to multiple topics
- You want to clarify the scope of the article
- There are related articles with similar names
- You want to help readers find the right information

## Template code

The template uses this HTML structure:

```html
<div class="template about-template">
    <p>This article is about <strong>{{{1}}}</strong>
    {{#if:{{{3}}}|. For other uses, see <a href="/wiki/{{{3}}}">{{{3}}}</a>|}}.</p>
</div>
```

## Parameters explained

- `{{{1}}}` - First parameter (main topic)
- `{{{2}}}` - Second parameter (additional context, rarely used)
- `{{{3}}}` - Third parameter (disambiguation page link)

## Related templates

- `{{Other uses}}` - For articles with multiple meanings
- `{{Distinguish}}` - For similar but different concepts
- `{{See also}}` - For related articles

## Customization

Users can edit this template by:

1. Going to `/wiki/Template:About`
2. Clicking "Edit" 
3. Modifying the template code
4. Saving changes

The template will automatically update across all pages that use it.

---

*This template is part of IslamWiki's Enhanced Markdown system. Templates are stored as namespace pages and can be modified by users.* 
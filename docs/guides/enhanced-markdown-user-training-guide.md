# Enhanced Markdown User Training Guide

**Version:** 0.0.3.0  
**Created:** 2025-08-23  
**Last Updated:** 2025-08-23  

## 🎯 **Welcome to Enhanced Markdown with Wiki Extensions!**

This guide will teach you how to use IslamWiki's new Enhanced Markdown system. Whether you're a beginner or an experienced editor, this guide will help you create rich, well-formatted content with powerful wiki features.

## 📚 **What is Enhanced Markdown?**

Enhanced Markdown combines the simplicity of standard Markdown with powerful wiki extensions that give you MediaWiki-like functionality:

- **Easy to Learn**: Simple syntax that's intuitive and quick to master
- **Rich Formatting**: Create beautiful, well-structured content
- **Wiki Features**: Internal links, templates, categories, and references
- **Islamic Content**: Specialized templates for Quran, Hadith, and Islamic topics
- **Consistent**: Single format across all pages eliminates confusion

## 🚀 **Getting Started**

### **Basic Principles**
1. **Markdown First**: Start with standard Markdown syntax
2. **Enhance Gradually**: Add wiki extensions as needed
3. **Preview Often**: Use the live preview to see your changes
4. **Save Regularly**: Save your work frequently

### **The Editor Interface**
- **Toolbar**: Quick access to common formatting options
- **Editor**: Where you write your content
- **Preview**: See how your content will look
- **Help**: Built-in examples and guidance

## ✏️ **Basic Markdown Syntax**

### **Text Formatting**
```markdown
**Bold text**          → Bold text
*Italic text*          → Italic text
***Bold italic***      → Bold italic text
`Inline code`          → Inline code
~~Strikethrough~~      → ~~Strikethrough~~
```

### **Headings**
```markdown
# Main Heading        → Main Heading
## Section Heading    → Section Heading
### Subsection        → Subsection
#### Sub-subsection   → Sub-subsection
```

### **Lists**
```markdown
- Unordered item 1
- Unordered item 2
  - Nested item
  - Another nested item

1. Ordered item 1
2. Ordered item 2
   1. Nested ordered item
   2. Another nested ordered item
```

### **Links and Images**
```markdown
[Link text](https://example.com)           → External link
![Alt text](image.jpg)                     → Image
![Alt text](image.jpg "Tooltip text")      → Image with tooltip
```

### **Code Blocks**
```markdown
`Inline code`

```python
# Code block
def hello_world():
    print("Hello, World!")
```
```

### **Blockquotes**
```markdown
> This is a blockquote
> It can span multiple lines
>> And can be nested
```

### **Tables**
```markdown
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |
```

### **Horizontal Rules**
```markdown
---
or
***
or
___
```

## 🔗 **Wiki Extensions**

### **Internal Links**
Internal links connect pages within IslamWiki:

```markdown
[[Page Name]]                    → Link to another page
[[Page Name|Display Text]]       → Link with custom display text
[[Category:Name]]                → Category link
```

**Examples:**
```markdown
Learn more about [[Quran]] and [[Hadith]].
Check out [[Islamic History|the history of Islam]].
Browse [[Category:Islamic Scholars]] for more information.
```

### **Templates**
Templates provide consistent formatting and dynamic content:

```markdown
{{Infobox|title=Title|content=Content}}
{{Warning|message=Important notice}}
{{Note|message=Helpful information}}
{{Success|message=Operation completed}}
{{Error|message=Something went wrong}}
```

**Examples:**
```markdown
{{Infobox|title=Quick Facts|content=Islam is a monotheistic religion}}
{{Warning|message=This information needs verification}}
{{Note|message=See also: [[Quran]], [[Hadith]]}}
```

### **References**
Add citations and references to your content:

```markdown
<ref>Reference content</ref>                    → Simple reference
<ref name="source1">Reference content</ref>     → Named reference
```

**Examples:**
```markdown
The Prophet Muhammad said: "Seek knowledge from the cradle to the grave."<ref>Sahih Bukhari, Book 1, Hadith 1</ref>

According to Islamic tradition<ref name="quran">Quran 2:255</ref>, Allah is the Most Merciful.
```

## 🕌 **Islamic Content Extensions**

### **Quran Templates**
Display Quran verses and chapters:

```markdown
{{Quran|surah=1|ayah=1-7}}                    → Specific verse range
{{Quran|surah=2|ayah=255}}                     → Single verse
{{Quran|surah=1|translation=en}}               → Entire chapter
{{Quran|surah=1|ayah=1-7|translation=ar}}     → Arabic with translation
```

**Examples:**
```markdown
The opening chapter of the Quran: {{Quran|surah=1|ayah=1-7}}

The Throne Verse: {{Quran|surah=2|ayah=255}}
```

### **Hadith Templates**
Reference Islamic traditions:

```markdown
{{Hadith|book=Bukhari|number=1}}               → Specific hadith
{{Hadith|book=Muslim|number=1|grade=Sahih}}    → With authenticity grade
{{Hadith|chain=Abu Hurairah → Prophet}}        → Hadith chain
{{Hadith|narrator=Abu Hurairah}}                → By narrator
```

**Examples:**
```markdown
A famous hadith: {{Hadith|book=Bukhari|number=1|grade=Sahih}}

The chain of transmission: {{Hadith|chain=Abu Hurairah → Prophet Muhammad}}
```

### **Scholar Templates**
Information about Islamic scholars:

```markdown
{{Scholar|name=Ibn Sina}}                      → Basic scholar info
{{Scholar|name=Al-Ghazali|period=1058-1111}}   → With time period
{{Scholar|name=Ibn Khaldun|field=History}}     → With field of study
```

**Examples:**
```markdown
{{Scholar|name=Ibn Sina|period=980-1037|field=Medicine}}

{{Scholar|name=Al-Ghazali|period=1058-1111|field=Theology}}
```

### **Fatwa Templates**
Islamic legal opinions:

```markdown
{{Fatwa|scholar=Al-Ghazali}}                   → Basic fatwa info
{{Fatwa|scholar=Name|topic=Prayer}}            → With topic
{{Fatwa|scholar=Name|date=1100|type=Personal}} → With date and type
```

**Examples:**
```markdown
{{Fatwa|scholar=Al-Ghazali|topic=Prayer|date=1100}}

{{Fatwa|scholar=Ibn Taymiyyah|topic=Jihad|type=General}}
```

### **Other Islamic Templates**
```markdown
{{PrayerTimes|city=Mecca|date=today}}          → Prayer times
{{HijriCalendar|date=today}}                   → Hijri date
{{QiblaDirection|from=Current|to=Mecca}}       → Qibla direction
```

## 🛠️ **Using the Toolbar**

### **Basic Formatting**
- **B**: Make selected text **bold**
- **I**: Make selected text *italic*
- **Heading**: Add a heading
- **Link**: Insert a link
- **Image**: Insert an image
- **List**: Create a list
- **Code**: Add inline code

### **Wiki Extensions**
- **Wiki Extensions**: Internal links, templates, references
- **Islamic Content**: Quran, Hadith, Scholar, Fatwa templates
- **Special Characters**: Accented letters and symbols

### **Advanced Features**
- **Insert**: Gallery, tables, redirects
- **Search**: Find and replace text
- **Preview**: Toggle live preview
- **Help**: Syntax examples and guidance

## 📝 **Content Creation Workflow**

### **Step 1: Plan Your Content**
1. Determine the topic and scope
2. Outline the main sections
3. Identify key concepts and terms
4. Plan internal links to other pages

### **Step 2: Write the Content**
1. Start with the main heading
2. Write section by section
3. Add formatting as you go
4. Include internal links naturally

### **Step 3: Add Wiki Features**
1. Apply appropriate templates
2. Add categories for organization
3. Include references and citations
4. Use Islamic content templates where relevant

### **Step 4: Review and Polish**
1. Use the preview to check formatting
2. Verify all links work correctly
3. Check for consistent formatting
4. Ensure proper categorization

## 🎨 **Best Practices**

### **Content Structure**
- Use clear, descriptive headings
- Organize content logically
- Keep paragraphs focused and concise
- Use lists for related information

### **Formatting Consistency**
- Use consistent heading levels
- Apply formatting consistently
- Maintain uniform list formatting
- Use templates for repeated elements

### **Internal Linking**
- Link to relevant existing pages
- Use descriptive link text
- Avoid over-linking
- Create new pages when needed

### **Template Usage**
- Choose appropriate templates
- Fill in all required parameters
- Use consistent parameter formatting
- Customize templates when needed

## 🔍 **Common Patterns and Examples**

### **Article Introduction**
```markdown
# Article Title

Brief introduction to the topic.

## Overview
Main points and key concepts.

## History
Historical background and development.

## Key Concepts
Important terms and definitions.

## References
<ref>Source 1</ref>
<ref>Source 2</ref>
```

### **Islamic Content Page**
```markdown
# Topic Name

{{Infobox|title=Quick Facts|content=Key information about the topic}}

## Introduction
Basic explanation and context.

## Islamic Perspective
{{Quran|surah=1|ayah=1-7}}

{{Hadith|book=Bukhari|number=1|grade=Sahih}}

## Scholar Opinions
{{Scholar|name=Ibn Sina|period=980-1037}}

## Related Topics
- [[Related Topic 1]]
- [[Related Topic 2]]
- [[Category:Related Category]]

## References
<ref>Primary source</ref>
<ref>Secondary source</ref>
```

### **Category Page**
```markdown
# Category: Category Name

Description of what this category contains.

## Pages in this Category
- [[Page 1]]
- [[Page 2]]
- [[Page 3]]

## Subcategories
- [[Category:Subcategory 1]]
- [[Category:Subcategory 2]]

## See Also
- [[Category:Related Category]]
- [[Main Topic]]
```

## ❓ **Troubleshooting**

### **Common Issues**

**Formatting not working?**
- Check syntax carefully
- Ensure proper spacing
- Use the preview to verify

**Links not working?**
- Verify page names are correct
- Check for typos
- Ensure proper bracket placement

**Templates not rendering?**
- Check parameter syntax
- Verify template names
- Ensure proper parameter values

**Preview not updating?**
- Save your changes
- Refresh the preview
- Check for syntax errors

### **Getting Help**
1. **Built-in Help**: Use the Help dropdown in the toolbar
2. **Syntax Examples**: Check the help section for examples
3. **Community Support**: Ask other editors for assistance
4. **Documentation**: Refer to this guide and other resources

## 🎓 **Practice Exercises**

### **Exercise 1: Basic Formatting**
Create a page with:
- Multiple heading levels
- Bold and italic text
- A numbered list
- A blockquote

### **Exercise 2: Internal Linking**
Create a page that links to:
- At least 3 other pages
- Uses custom display text
- Includes category links

### **Exercise 3: Templates**
Create a page using:
- An infobox template
- A warning or note template
- At least one Islamic content template

### **Exercise 4: Complete Article**
Write a complete article incorporating:
- All basic Markdown features
- Multiple wiki extensions
- Islamic content templates
- Proper categorization
- References and citations

## 📚 **Additional Resources**

### **Reference Materials**
- **Enhanced Markdown Syntax Guide**: Complete syntax reference
- **Template Library**: Available templates and usage
- **Migration Guide**: Converting from old format
- **Best Practices**: Content creation guidelines

### **Community Resources**
- **Editor Community**: Connect with other editors
- **Discussion Forums**: Ask questions and share tips
- **Example Pages**: Study well-formatted content
- **Feedback System**: Report issues and suggest improvements

## 🎉 **Congratulations!**

You've completed the Enhanced Markdown User Training Guide! You now have the knowledge and skills to:

- Create well-formatted content using Markdown
- Use wiki extensions for enhanced functionality
- Apply Islamic content templates appropriately
- Follow best practices for content creation
- Troubleshoot common issues

Remember: **Practice makes perfect!** Start with simple formatting and gradually add more advanced features as you become comfortable with the system.

## 🔄 **Next Steps**

1. **Practice**: Try the exercises in this guide
2. **Explore**: Experiment with different features
3. **Contribute**: Start editing and creating content
4. **Learn**: Continue improving your skills
5. **Share**: Help other users learn the system

---

**Happy Editing!** 🚀

*This guide is part of IslamWiki's Enhanced Markdown system. For questions or feedback, please contact the development team.* 
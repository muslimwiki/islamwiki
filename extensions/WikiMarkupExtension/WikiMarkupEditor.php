<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Database\Connection;

/**
 * Wiki Markup Editor
 *
 * Provides comprehensive editing capabilities for both WikiMarkup and Markdown
 * with edit functionality for existing pages, live preview, and auto-save.
 * 
 * @package IslamWiki\Extensions\WikiMarkupExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiMarkupEditor
{
    /**
     * @var WikiMarkupParser
     */
    private WikiMarkupParser $parser;

    /**
     * @var array Configuration options
     */
    private array $config;

    /**
     * @var Connection|null Database connection
     */
    private ?Connection $database;

    /**
     * Create a new editor instance
     */
    public function __construct(WikiMarkupParser $parser, array $config = [], ?Connection $database = null)
    {
        $this->parser = $parser;
        $this->config = array_merge([
            'enable_edit_functionality' => true,
            'auto_save_interval' => 30000,
            'show_edit_button' => true,
            'show_source_button' => true,
            'enable_live_preview' => true,
            'default_format' => 'wikimarkup',
            'supported_formats' => ['wikimarkup', 'markdown'],
            'max_content_length' => 1000000,
            'enable_syntax_help' => true
        ], $config);
        $this->database = $database;
    }

    /**
     * Generate edit form for existing page
     */
    public function generateEditForm(string $pageTitle, string $currentContent, string $format = 'wikimarkup'): string
    {
        if (!$this->config['enable_edit_functionality']) {
            return $this->generateReadOnlyView($currentContent, $format);
        }

        $isNewPage = empty($currentContent);
        $pageHeader = $isNewPage ? "Creating {$pageTitle}" : "Editing {$pageTitle}";
        $subtitle = $isNewPage ? "From IslamWiki" : "Current revision";
        
        $formatSelector = $this->generateFormatSelector($format);
        $toolbar = $this->generateProfessionalToolbar();
        $editor = $this->generateProfessionalEditor($currentContent, $format);
        $preview = $this->generatePreview($currentContent, $format);
        $summary = $this->generateSummarySection();
        $options = $this->generateOptionsSection();
        $actionButtons = $this->generateActionButtons($isNewPage);

        return sprintf('
            <div class="wiki-markup-editor muslimwiki-style" data-page-title="%s">
                <div class="editor-header">
                    <h1 class="page-title">%s</h1>
                    <div class="page-subtitle">%s</div>
                    %s
                </div>
                
                <div class="editor-instructions">
                    %s
                </div>
                
                <div class="editor-toolbar-container">
                    %s
                </div>
                
                <form method="POST" action="%s" class="edit-form" id="wikiEditForm">
                    <input type="hidden" name="_token" value="%s">
                    <input type="hidden" name="format" value="%s" id="contentFormat">
                    <input type="hidden" name="title" value="%s">
                    
                    <div class="editor-main">
                        %s
                    </div>
                    
                    <div class="editor-sidebar">
                        %s
                        %s
                        %s
                    </div>
                    
                    <div class="editor-actions">
                        %s
                    </div>
                </form>
            </div>
            
            <script>
                %s
            </script>
        ',
            htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($pageHeader, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'),
            $formatSelector,
            $this->generateInstructions($isNewPage),
            $toolbar,
            $isNewPage ? '/wiki/create' : '/wiki/' . $this->slugify($pageTitle) . '/edit',
            $this->generateCsrfToken(),
            htmlspecialchars($format, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'),
            $editor,
            $summary,
            $options,
            $preview,
            $actionButtons,
            $this->generateEditorJavaScript()
        );
    }

    /**
     * Generate read-only view for pages without edit permission
     */
    public function generateReadOnlyView(string $content, string $format = 'wikimarkup'): string
    {
        $parsedContent = $this->parser->parse($content, $format);
        
        return sprintf('
            <div class="wiki-content-readonly">
                <div class="content-header">
                    <div class="format-badge format-%s">%s</div>
                </div>
                <div class="content-body">
                    %s
                </div>
            </div>
        ',
            htmlspecialchars($format, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(ucfirst($format), ENT_QUOTES, 'UTF-8'),
            $parsedContent
        );
    }

    /**
     * Generate format selector
     */
    private function generateFormatSelector(string $currentFormat): string
    {
        $options = '';
        foreach ($this->config['supported_formats'] as $format) {
            $selected = $format === $currentFormat ? 'selected' : '';
            $options .= sprintf(
                '<option value="%s" %s>%s</option>',
                htmlspecialchars($format, ENT_QUOTES, 'UTF-8'),
                $selected,
                htmlspecialchars(ucfirst($format), ENT_QUOTES, 'UTF-8')
            );
        }

        return sprintf('
            <div class="format-selector">
                <label for="formatSelect">Content Format:</label>
                <select id="formatSelect" onchange="changeFormat(this.value)">
                    %s
                </select>
            </div>
        ', $options);
    }

    /**
     * Generate professional MediaWiki-style toolbar
     */
    private function generateProfessionalToolbar(): string
    {
        return '
            <div class="editor-toolbar muslimwiki-toolbar">
                <div class="toolbar-section">
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'bold\')" title="Bold (Ctrl+B)">
                            <strong>B</strong>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'italic\')" title="Italic (Ctrl+I)">
                            <em>I</em>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'underline\')" title="Underline">
                            <u>U</u>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'strikethrough\')" title="Strikethrough">
                            <del>S</del>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'link\')" title="Internal link">
                            <span class="icon">🔗</span>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'image\')" title="Image">
                            <span class="icon">🖼️</span>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'gallery\')" title="Gallery">
                            <span class="icon">📷</span>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'indent\')" title="Indent">
                            <span class="icon">→</span>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'header1\')" title="Level 1 heading">H1</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'header2\')" title="Level 2 heading">H2</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'header3\')" title="Level 3 heading">H3</button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'unordered_list\')" title="Bullet list">•</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'ordered_list\')" title="Numbered list">1.</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'definition_list\')" title="Definition list">;</button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'table\')" title="Insert table">⊞</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'code\')" title="Code">&lt;/&gt;</button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkup(\'math\')" title="Math formula">∑</button>
                    </div>
                </div>
                
                <div class="toolbar-section">
                    <div class="toolbar-dropdown">
                        <button type="button" class="dropdown-btn">Advanced</button>
                        <div class="dropdown-content">
                            <button type="button" onclick="insertMarkup(\'signature\')">Signature</button>
                            <button type="button" onclick="insertMarkup(\'timestamp\')">Timestamp</button>
                            <button type="button" onclick="insertMarkup(\'comment\')">Comment</button>
                            <button type="button" onclick="insertMarkup(\'nowiki\')">No Wiki</button>
                        </div>
                    </div>
                    
                    <div class="toolbar-dropdown">
                        <button type="button" class="dropdown-btn">Special characters</button>
                        <div class="dropdown-content">
                            <button type="button" onclick="insertCharacter(\'á\')">á</button>
                            <button type="button" onclick="insertCharacter(\'é\')">é</button>
                            <button type="button" onclick="insertCharacter(\'í\')">í</button>
                            <button type="button" onclick="insertCharacter(\'ó\')">ó</button>
                            <button type="button" onclick="insertCharacter(\'ú\')">ú</button>
                            <button type="button" onclick="insertCharacter(\'ñ\')">ñ</button>
                            <button type="button" onclick="insertCharacter(\'©\')">©</button>
                            <button type="button" onclick="insertCharacter(\'®\')">®</button>
                            <button type="button" onclick="insertCharacter(\'™\')">™</button>
                        </div>
                    </div>
                    
                    <div class="toolbar-dropdown">
                        <button type="button" class="dropdown-btn">Help</button>
                        <div class="dropdown-content">
                            <button type="button" onclick="showSyntaxHelp()">Syntax help</button>
                            <button type="button" onclick="showTemplateHelp()">Template help</button>
                            <button type="button" onclick="showCategoryHelp()">Category help</button>
                        </div>
                    </div>
                </div>
                
                <div class="toolbar-section">
                    <div class="toolbar-dropdown preview-dropdown">
                        <button type="button" class="dropdown-btn preview-btn">
                            <span class="icon">👁️</span> Preview
                        </button>
                        <div class="dropdown-content">
                            <button type="button" onclick="showPreview()">Show preview</button>
                            <button type="button" onclick="toggleLivePreview()">Toggle live preview</button>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    /**
     * Generate professional editor interface
     */
    private function generateProfessionalEditor(string $content, string $format): string
    {
        $placeholder = $this->getEditorPlaceholder($format);
        
        return sprintf('
            <div class="editor-textarea-container">
                <div class="editor-label">Page content:</div>
                <textarea 
                    id="contentEditor" 
                    name="content" 
                    class="wiki-content-editor muslimwiki-editor" 
                    placeholder="%s"
                    rows="25"
                    maxlength="%d"
                    oninput="autoSave(); updateCharCount();"
                    onkeydown="handleTabKey(event)"
                >%s</textarea>
                
                <div class="editor-status">
                    <span class="char-count" id="charCount">%d characters</span>
                    <span class="auto-save-status" id="autoSaveStatus"></span>
                </div>
            </div>
        ',
            htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'),
            $this->config['max_content_length'],
            htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            strlen($content)
        );
    }

    /**
     * Generate summary section
     */
    private function generateSummarySection(): string
    {
        return '
            <div class="summary-section">
                <div class="summary-label">Summary:</div>
                <input type="text" name="summary" class="summary-input" placeholder="Brief description of your changes" maxlength="200">
                <div class="summary-help">Brief description of your changes (optional)</div>
            </div>
        ';
    }

    /**
     * Generate options section
     */
    private function generateOptionsSection(): string
    {
        return '
            <div class="options-section">
                <div class="option-item">
                    <label class="checkbox-label">
                        <input type="checkbox" name="watch_page" value="1">
                        <span class="checkmark"></span>
                        Watch this page
                    </label>
                </div>
                
                <div class="option-item">
                    <label class="checkbox-label">
                        <input type="checkbox" name="minor_edit" value="1">
                        <span class="checkmark"></span>
                        This is a minor edit
                    </label>
                </div>
                
                <div class="license-notice">
                    <strong>Licensing:</strong> By saving changes, you agree to release your contribution under the 
                    <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 License</a>.
                    <br><br>
                    <strong>Copyright:</strong> Do not submit copyrighted work without permission. 
                    Your contributions are released under the CC BY-SA license.
                </div>
            </div>
        ';
    }

    /**
     * Generate action buttons
     */
    private function generateActionButtons(bool $isNewPage): string
    {
        $saveText = $isNewPage ? 'Create page' : 'Save page';
        
        return sprintf('
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary btn-save">%s</button>
                <button type="button" class="btn btn-secondary btn-preview" onclick="showPreview()">Show preview</button>
                <button type="button" class="btn btn-secondary btn-changes" onclick="showChanges()">Show changes</button>
                <button type="button" class="btn btn-secondary btn-draft" onclick="saveDraft()">Save draft</button>
                <button type="button" class="btn btn-danger btn-cancel" onclick="cancelEdit()">Cancel</button>
            </div>
        ', $saveText);
    }

    /**
     * Generate instructions for new pages
     */
    private function generateInstructions(bool $isNewPage): string
    {
        if ($isNewPage) {
            return '
                <div class="editor-instructions new-page">
                    <p>You have followed a link to a page that does not exist yet. To create the page, start typing in the box below.</p>
                    <p>See the <a href="/wiki/Help:Creating_pages" target="_blank">help page</a> for more info, or click your browser\'s "back" button if you are there by mistake.</p>
                </div>
            ';
        }
        
        return '
            <div class="editor-instructions edit-page">
                <p>You are editing an existing page. Make your changes below and use the summary field to describe what you changed.</p>
            </div>
        ';
    }

    /**
     * Generate live preview
     */
    private function generatePreview(string $content, string $format): string
    {
        if (!$this->config['enable_live_preview']) {
            return '<div class="preview-disabled">Live preview is disabled</div>';
        }

        $parsedContent = $this->parser->parse($content, $format);
        
        return sprintf('
            <div class="preview-header">
                <h3>Live Preview</h3>
                <div class="preview-controls">
                    <button type="button" class="btn btn-sm" onclick="refreshPreview()">Refresh</button>
                    <button type="button" class="btn btn-sm" onclick="togglePreview()">Hide</button>
                </div>
            </div>
            <div class="preview-content" id="previewContent">
                %s
            </div>
        ', $parsedContent);
    }

    /**
     * Generate syntax help
     */
    private function generateSyntaxHelp(string $format): string
    {
        if (!$this->config['enable_syntax_help']) {
            return '';
        }

        $helpContent = $this->getSyntaxHelp($format);
        
        return sprintf('
            <div class="syntax-help">
                <h3>%s Syntax Help</h3>
                <div class="help-content">
                    %s
                </div>
                <button type="button" class="btn btn-sm" onclick="toggleHelp()">Toggle Help</button>
            </div>
        ',
            htmlspecialchars(ucfirst($format), ENT_QUOTES, 'UTF-8'),
            $helpContent
        );
    }

    /**
     * Get editor placeholder text
     */
    private function getEditorPlaceholder(string $format): string
    {
        if ($format === 'markdown') {
            return '# Start writing your content here...

## Use headers for organization
### Subheadings work too

**Bold text** and *italic text* are supported.

- Unordered lists
- With multiple items
- Easy to create

1. Ordered lists
2. Numbered automatically
3. Perfect for steps

[Links](https://example.com) and `inline code` work too.

```php
// Code blocks with syntax highlighting
function hello() {
    echo "Hello, World!";
}
```';
        } else {
            return '= Start writing your content here =

== Use headers for organization ==
=== Subheadings work too ===

\'\'\'Bold text\'\' and \'\'italic text\'\' are supported.

* Unordered lists
* With multiple items
* Easy to create

# Ordered lists
# Numbered automatically
# Perfect for steps

[[Internal Links]] and [[External Links|with display text]] work too.

<code>
// Code blocks with syntax highlighting
function hello() {
    echo "Hello, World!";
}
</code>';
        }
    }

    /**
     * Get syntax help content
     */
    private function getSyntaxHelp(string $format): string
    {
        if ($format === 'markdown') {
            return '
                <div class="help-section">
                    <h4>Headers</h4>
                    <code># H1</code>, <code>## H2</code>, <code>### H3</code>
                </div>
                
                <div class="help-section">
                    <h4>Emphasis</h4>
                    <code>**bold**</code>, <code>*italic*</code>, <code>~~strikethrough~~</code>
                </div>
                
                <div class="help-section">
                    <h4>Lists</h4>
                    <code>* item</code> (unordered), <code># item</code> (ordered)
                </div>
                
                <div class="help-section">
                    <h4>Links</h4>
                    <code>[text](url)</code>
                </div>
                
                <div class="help-section">
                    <h4>Code</h4>
                    <code>`inline`</code>, <code>```block```</code>
                </div>
            ';
        } else {
            return '
                <div class="help-section">
                    <h4>Headers</h4>
                    <code>= H1 =</code>, <code>== H2 ==</code>, <code>=== H3 ===</code>
                </div>
                
                <div class="help-section">
                    <h4>Emphasis</h4>
                    <code>\'\'\'bold\'\'</code>, <code>\'\'italic\'\'</code>, <code><del>strikethrough</del></code>
                </div>
                
                <div class="help-section">
                    <h4>Lists</h4>
                    <code>* item</code> (unordered), <code># item</code> (ordered)
                </div>
                
                <div class="help-section">
                    <h4>Links</h4>
                    <code>[[Page]]</code>, <code>[[Page|Display Text]]</code>
                </div>
                
                <div class="help-section">
                    <h4>Tables</h4>
                    <code>{| |} |- | ||</code>
                </div>
                
                <div class="help-section">
                    <h4>Media</h4>
                    <code>[[Image:filename.jpg|Caption]]</code>
                </div>
            ';
        }
    }

    /**
     * Generate editor JavaScript
     */
    private function generateEditorJavaScript(): string
    {
        return "
            let autoSaveTimer = null;
            let lastContent = '';
            
            // Initialize editor
            document.addEventListener('DOMContentLoaded', function() {
                updateCharCount();
                setupAutoSave();
            });
            
            // Format change handler
            function changeFormat(format) {
                document.getElementById('contentFormat').value = format;
                refreshPreview();
                updateSyntaxHelp(format);
            }
            
            // Insert markup helper
            function insertMarkup(type) {
                const editor = document.getElementById('contentEditor');
                const start = editor.selectionStart;
                const end = editor.selectionEnd;
                const selectedText = editor.value.substring(start, end) || 'Selected Text';
                const format = document.getElementById('contentFormat').value;
                
                let replacement = '';
                switch(type) {
                    case 'bold':
                        replacement = format === 'markdown' ? `**${selectedText}**` : `'''${selectedText}'''`;
                        break;
                    case 'italic':
                        replacement = format === 'markdown' ? `*${selectedText}*` : `''${selectedText}''`;
                        break;
                    case 'header1':
                        replacement = format === 'markdown' ? `# ${selectedText}` : `= ${selectedText} =`;
                        break;
                    case 'header2':
                        replacement = format === 'markdown' ? `## ${selectedText}` : `== ${selectedText} ==`;
                        break;
                    case 'header3':
                        replacement = format === 'markdown' ? `### ${selectedText}` : `=== ${selectedText} ===`;
                        break;
                    case 'unordered_list':
                        replacement = `* ${selectedText}`;
                        break;
                    case 'ordered_list':
                        replacement = `# ${selectedText}`;
                        break;
                    case 'link':
                        replacement = `[[${selectedText}]]`;
                        break;
                    case 'image':
                        replacement = format === 'markdown' ? `![${selectedText}](image.jpg)` : `[[Image:${selectedText}.jpg|Caption]]`;
                        break;
                    case 'gallery':
                        replacement = `<gallery>\\n${selectedText}\\n</gallery>`;
                        break;
                    case 'indent':
                        replacement = `: ${selectedText}`;
                        break;
                    case 'table':
                        replacement = format === 'markdown' ? 
                            `| Header 1 | Header 2 |\\n|----------|----------|\\n| Cell 1   | Cell 2   |` :
                            `{| class=\"wikitable\"\\n|+ Caption\\n|-\\n| Header 1 || Header 2\\n|-\\n| Cell 1 || Cell 2\\n|}`;
                        break;
                    case 'code':
                        replacement = format === 'markdown' ? `\`${selectedText}\`` : `<code>${selectedText}</code>`;
                        break;
                    case 'math':
                        replacement = `<math>${selectedText}</math>`;
                        break;
                    case 'signature':
                        replacement = `-- [[User:${selectedText}]]`;
                        break;
                    case 'timestamp':
                        replacement = `-- ${new Date().toLocaleString()}`;
                        break;
                    case 'comment':
                        replacement = `<!-- ${selectedText} -->`;
                        break;
                    case 'nowiki':
                        replacement = `<nowiki>${selectedText}</nowiki>`;
                        break;
                }
                
                editor.value = editor.value.substring(0, start) + replacement + editor.value.substring(end);
                editor.focus();
                editor.setSelectionRange(start + replacement.length, start + replacement.length);
                
                updateCharCount();
                refreshPreview();
            }
            
            // Insert special character
            function insertCharacter(char) {
                const editor = document.getElementById('contentEditor');
                const start = editor.selectionStart;
                const end = editor.selectionEnd;
                
                editor.value = editor.value.substring(0, start) + char + editor.value.substring(end);
                editor.focus();
                editor.setSelectionRange(start + char.length, start + char.length);
                
                updateCharCount();
                refreshPreview();
            }
            
            // Handle tab key in textarea
            function handleTabKey(event) {
                if (event.key === 'Tab') {
                    event.preventDefault();
                    const editor = document.getElementById('contentEditor');
                    const start = editor.selectionStart;
                    const end = editor.selectionEnd;
                    
                    editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                    editor.selectionStart = editor.selectionEnd = start + 4;
                }
            }
            
            // Update character count
            function updateCharCount() {
                const editor = document.getElementById('contentEditor');
                const count = editor.value.length;
                const max = " . $this->config['max_content_length'] . ";
                document.getElementById('charCount').textContent = count + ' / ' + max + ' characters';
                
                if (count > max * 0.9) {
                    document.getElementById('charCount').classList.add('warning');
                } else {
                    document.getElementById('charCount').classList.remove('warning');
                }
            }
            
            // Auto-save functionality
            function setupAutoSave() {
                const editor = document.getElementById('contentEditor');
                editor.addEventListener('input', function() {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(autoSave, 1000);
                });
            }
            
            function autoSave() {
                const editor = document.getElementById('contentEditor');
                const content = editor.value;
                
                if (content === lastContent) return;
                
                lastContent = content;
                updateCharCount();
                
                // Show auto-save status
                const status = document.getElementById('autoSaveStatus');
                status.textContent = 'Auto-saving...';
                status.className = 'auto-save-status saving';
                
                // Simulate auto-save (replace with actual API call)
                setTimeout(() => {
                    status.textContent = 'Auto-saved';
                    status.className = 'auto-save-status saved';
                    setTimeout(() => {
                        status.textContent = '';
                        status.className = 'auto-save-status';
                    }, 2000);
                }, 500);
            }
            
            // Preview functionality
            function refreshPreview() {
                const editor = document.getElementById('contentEditor');
                const content = editor.value;
                const format = document.getElementById('contentFormat').value;
                
                // Send content to server for parsing
                fetch('/api/parse-wiki-markup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=\"_token\"]').value
                    },
                    body: JSON.stringify({ content, format })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('previewContent').innerHTML = data.html;
                    }
                })
                .catch(error => {
                    console.error('Preview error:', error);
                });
            }
            
            function togglePreview() {
                const preview = document.querySelector('.editor-preview');
                preview.classList.toggle('hidden');
            }
            
            // Help functionality
            function toggleHelp() {
                const help = document.querySelector('.syntax-help');
                help.classList.toggle('collapsed');
            }
            
            function updateSyntaxHelp(format) {
                // Update help content based on format
                // This would typically involve AJAX to get format-specific help
            }
            
            // Show syntax help
            function showSyntaxHelp() {
                const format = document.getElementById('contentFormat').value;
                const helpContent = format === 'markdown' ? 
                    'Markdown syntax: **bold**, *italic*, # headers, - lists, [links](url)' :
                    'WikiMarkup syntax: \'\'\'bold\'\', \'\'italic\'\', = headers =, * lists, [[links]]';
                
                alert('Syntax Help:\\n\\n' + helpContent);
            }
            
            // Show template help
            function showTemplateHelp() {
                const helpContent = 'Template syntax: {{TemplateName|param1|param2}}\\n\\n' +
                    'Examples:\\n' +
                    '{{quran|2|255|English}}\\n' +
                    '{{hadith|bukhari|1|1}}\\n' +
                    '{{image|photo.jpg|Caption}}';
                
                alert('Template Help:\\n\\n' + helpContent);
            }
            
            // Show category help
            function showCategoryHelp() {
                const helpContent = 'Category syntax: [[Category:CategoryName]]\\n\\n' +
                    'Examples:\\n' +
                    '[[Category:Islamic Sciences]]\\n' +
                    '[[Category:Beginner Level]]\\n' +
                    '[[Category:Fiqh]]';
                
                alert('Category Help:\\n\\n' + helpContent);
            }
            
            // Show preview
            function showPreview() {
                refreshPreview();
                const preview = document.querySelector('.editor-preview');
                if (preview) {
                    preview.classList.remove('hidden');
                    preview.scrollIntoView({ behavior: 'smooth' });
                }
            }
            
            // Toggle live preview
            function toggleLivePreview() {
                const preview = document.querySelector('.editor-preview');
                if (preview) {
                    preview.classList.toggle('hidden');
                }
            }
            
            // Show changes
            function showChanges() {
                alert('Changes preview will be implemented in future versions.');
            }
            
            // Save draft
            function saveDraft() {
                const editor = document.getElementById('contentEditor');
                const content = editor.value;
                
                // Save to localStorage as draft
                localStorage.setItem('wiki_draft_' + Date.now(), content);
                
                // Show status
                const status = document.getElementById('autoSaveStatus');
                status.textContent = 'Draft saved locally';
                status.className = 'auto-save-status saved';
                
                setTimeout(() => {
                    status.textContent = '';
                    status.className = 'auto-save-status';
                }, 2000);
            }
            
            // Form submission
            document.getElementById('wikiEditForm').addEventListener('submit', function(e) {
                const editor = document.getElementById('contentEditor');
                if (editor.value.trim() === '') {
                    e.preventDefault();
                    alert('Content cannot be empty');
                    return;
                }
                
                // Show saving status
                const submitBtn = this.querySelector('button[type=\"submit\"]');
                submitBtn.textContent = 'Saving...';
                submitBtn.disabled = true;
            });
            
            // Cancel edit
            function cancelEdit() {
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    window.history.back();
                }
            }
        ";
    }

    /**
     * Generate CSRF token
     */
    private function generateCsrfToken(): string
    {
        // In a real implementation, this would generate a proper CSRF token
        return bin2hex(random_bytes(32));
    }

    /**
     * Convert title to URL slug
     */
    private function slugify(string $title): string
    {
        $slug = strtolower(str_replace(' ', '-', $title));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get editor configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update editor configuration
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
} 
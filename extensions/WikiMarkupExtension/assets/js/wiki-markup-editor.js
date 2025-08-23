/**
 * WikiMarkup Editor JavaScript
 * Provides enhanced functionality for the MediaWiki-style editor
 */

(function() {
    'use strict';

    // Initialize editor when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeEditor();
    });

    function initializeEditor() {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;

        // Set up auto-save
        setupAutoSave();
        
        // Set up keyboard shortcuts
        setupKeyboardShortcuts();
        
        // Initialize character count
        updateCharCount();
        
        // Set up preview functionality
        setupPreview();
    }

    // Auto-save functionality
    let autoSaveTimer;
    let lastContent = '';

    function setupAutoSave() {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;

        editor.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 30000); // 30 seconds
            
            // Update character count
            updateCharCount();
            
            // Update preview if live preview is enabled
            if (document.querySelector('.editor-preview:not(.hidden)')) {
                refreshPreview();
            }
        });
    }

    function autoSave() {
        const editor = document.getElementById('contentEditor');
        const content = editor.value;
        
        if (content === lastContent) return;
        
        // Show saving status
        const status = document.getElementById('autoSaveStatus');
        if (status) {
            status.textContent = 'Saving...';
            status.className = 'auto-save-status saving';
        }
        
        // Save to localStorage
        const pageTitle = document.querySelector('.wiki-markup-editor').dataset.pageTitle;
        localStorage.setItem(`wiki_autosave_${pageTitle}`, content);
        lastContent = content;
        
        // Update status
        if (status) {
            status.textContent = 'Auto-saved';
            status.className = 'auto-save-status saved';
            
            setTimeout(() => {
                status.textContent = '';
                status.className = 'auto-save-status';
            }, 2000);
        }
    }

    // Keyboard shortcuts
    function setupKeyboardShortcuts() {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;

        editor.addEventListener('keydown', function(event) {
            // Ctrl+B for bold
            if (event.ctrlKey && event.key === 'b') {
                event.preventDefault();
                insertMarkup('bold');
            }
            
            // Ctrl+I for italic
            if (event.ctrlKey && event.key === 'i') {
                event.preventDefault();
                insertMarkup('italic');
            }
            
            // Tab key handling
            if (event.key === 'Tab') {
                event.preventDefault();
                handleTabKey(event);
            }
        });
    }

    // Character count
    function updateCharCount() {
        const editor = document.getElementById('contentEditor');
        const countElement = document.getElementById('charCount');
        if (!editor || !countElement) return;

        const count = editor.value.length;
        const maxLength = parseInt(editor.getAttribute('maxlength')) || 1000000;
        
        countElement.textContent = `${count} / ${maxLength.toLocaleString()} characters`;
        
        // Add warning class if approaching limit
        if (count > maxLength * 0.9) {
            countElement.classList.add('warning');
        } else {
            countElement.classList.remove('warning');
        }
    }

    // Tab key handling
    function handleTabKey(event) {
        const editor = event.target;
        const start = editor.selectionStart;
        const end = editor.selectionEnd;
        
        // Insert 4 spaces
        const spaces = '    ';
        editor.value = editor.value.substring(0, start) + spaces + editor.value.substring(end);
        
        // Set cursor position after spaces
        editor.selectionStart = editor.selectionEnd = start + spaces.length;
    }

    // Preview functionality
    function setupPreview() {
        const previewBtn = document.querySelector('.btn-preview');
        if (previewBtn) {
            previewBtn.addEventListener('click', showPreview);
        }
    }

    function showPreview() {
        const preview = document.querySelector('.editor-preview');
        if (!preview) return;

        // Show preview
        preview.classList.remove('hidden');
        
        // Refresh content
        refreshPreview();
        
        // Scroll to preview
        preview.scrollIntoView({ behavior: 'smooth' });
    }

    function refreshPreview() {
        const editor = document.getElementById('contentEditor');
        const preview = document.querySelector('.editor-preview');
        if (!editor || !preview) return;

        const content = editor.value;
        const format = document.getElementById('contentFormat').value;
        
        // For now, just show the raw content
        // In a real implementation, this would parse the markup and show HTML
        preview.innerHTML = `
            <div class="preview-header">
                <h3>Preview</h3>
                <div class="preview-controls">
                    <button class="btn btn-secondary" onclick="hidePreview()">Hide Preview</button>
                </div>
            </div>
            <div class="preview-content">
                <pre>${escapeHtml(content)}</pre>
            </div>
        `;
    }

    function hidePreview() {
        const preview = document.querySelector('.editor-preview');
        if (preview) {
            preview.classList.add('hidden');
        }
    }

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Global functions for toolbar buttons
    window.insertMarkup = function(type) {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;

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
                replacement = `<gallery>\n${selectedText}\n</gallery>`;
                break;
            case 'indent':
                replacement = `: ${selectedText}`;
                break;
            case 'table':
                replacement = format === 'markdown' ? 
                    `| Header 1 | Header 2 |\n|----------|----------|\n| Cell 1   | Cell 2   |` :
                    `{| class="wikitable"\n|+ Caption\n|-\n| Header 1 || Header 2\n|-\n| Cell 1 || Cell 2\n|}`;
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
        
        // Insert replacement
        editor.value = editor.value.substring(0, start) + replacement + editor.value.substring(end);
        
        // Set focus and selection
        editor.focus();
        editor.setSelectionRange(start + replacement.length, start + replacement.length);
        
        // Update character count and preview
        updateCharCount();
        if (document.querySelector('.editor-preview:not(.hidden)')) {
            refreshPreview();
        }
    };

    window.insertCharacter = function(char) {
        const editor = document.getElementById('contentEditor');
        if (!editor) return;

        const start = editor.selectionStart;
        const end = editor.selectionEnd;
        
        editor.value = editor.value.substring(0, start) + char + editor.value.substring(end);
        editor.focus();
        editor.setSelectionRange(start + char.length, start + char.length);
        
        updateCharCount();
    };

    window.showSyntaxHelp = function() {
        const format = document.getElementById('contentFormat').value;
        const helpContent = format === 'markdown' ? 
            'Markdown syntax: **bold**, *italic*, # headers, - lists, [links](url)' :
            'WikiMarkup syntax: \'\'\'bold\'\', \'\'italic\'\', = headers =, * lists, [[links]]';
        
        alert('Syntax Help:\n\n' + helpContent);
    };

    window.showTemplateHelp = function() {
        const helpContent = 'Template syntax: {{TemplateName|param1|param2}}\n\n' +
            'Examples:\n' +
            '{{quran|2|255|English}}\n' +
            '{{hadith|bukhari|1|1}}\n' +
            '{{image|photo.jpg|Caption}}';
        
        alert('Template Help:\n\n' + helpContent);
    };

    window.showCategoryHelp = function() {
        const helpContent = 'Category syntax: [[Category:CategoryName]]\n\n' +
            'Examples:\n' +
            '[[Category:Islamic Sciences]]\n' +
            '[[Category:Beginner Level]]\n' +
            '[[Category:Fiqh]]';
        
        alert('Category Help:\n\n' + helpContent);
    };

    window.showPreview = function() {
        showPreview();
    };

    window.toggleLivePreview = function() {
        const preview = document.querySelector('.editor-preview');
        if (preview) {
            preview.classList.toggle('hidden');
        }
    };

    window.showChanges = function() {
        alert('Changes preview will be implemented in future versions.');
    };

    window.saveDraft = function() {
        const editor = document.getElementById('contentEditor');
        const content = editor.value;
        
        // Save to localStorage as draft
        localStorage.setItem('wiki_draft_' + Date.now(), content);
        
        // Show status
        const status = document.getElementById('autoSaveStatus');
        if (status) {
            status.textContent = 'Draft saved locally';
            status.className = 'auto-save-status saved';
            
            setTimeout(() => {
                status.textContent = '';
                status.className = 'auto-save-status';
            }, 2000);
        }
    };

    window.cancelEdit = function() {
        if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
            window.history.back();
        }
    };

})(); 
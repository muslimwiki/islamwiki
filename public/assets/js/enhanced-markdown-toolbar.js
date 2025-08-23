document.addEventListener('DOMContentLoaded', function() {
    // Enhanced Markdown with Wiki Extensions System
    
    // Preview functionality
    const previewBtn = document.querySelector('.preview-btn');
    const showPreviewBtn = document.getElementById('show-preview-btn');
    const previewSection = document.getElementById('preview-section');
    const editorSection = document.querySelector('.editor-section');
    const previewContent = document.getElementById('preview-content');
    const contentTextarea = document.getElementById('content');
    
    function togglePreview() {
        if (previewSection.style.display === 'none') {
            // Show preview
            previewSection.style.display = 'block';
            editorSection.style.flex = '1';
            previewSection.style.flex = '1';
            
            // Update preview content
            updatePreview();
        } else {
            // Hide preview
            previewSection.style.display = 'none';
            editorSection.style.flex = '1';
        }
    }
    
    function updatePreview() {
        const content = contentTextarea.value;
        
        // Convert Enhanced Markdown to HTML for preview
        let html = content
            // Basic Markdown
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/^# (.+)/gm, '<h1>$1</h1>')
            .replace(/^## (.+)/gm, '<h2>$1</h2>')
            .replace(/^### (.+)/gm, '<h3>$1</h3>')
            .replace(/^- (.+)/gm, '<li>$1</li>')
            .replace(/^\d+\. (.+)/gm, '<li>$1</li>')
            .replace(/`(.+?)`/g, '<code>$1</code>')
            .replace(/^> (.+)/gm, '<blockquote>$1</blockquote>')
            .replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2">$1</a>')
            .replace(/!\[(.+?)\]\((.+?)\)/g, '<img src="$2" alt="$1">')
            
            // Wiki Extensions
            .replace(/\[\[(.+?)\]\]/g, '<a href="/wiki/$1" class="wiki-link">$1</a>')
            .replace(/\[\[(.+?)\|(.+?)\]\]/g, '<a href="/wiki/$1" class="wiki-link">$2</a>')
            .replace(/\[Category:(.+?)\]/g, '<span class="category">Category: $1</span>')
            .replace(/\{\{Template\|(.+?)\}\}/g, '<div class="template">Template: $1</div>')
            .replace(/\{\{Infobox\|(.+?)\}\}/g, '<div class="infobox">Infobox: $1</div>')
            .replace(/<ref>(.+?)<\/ref>/g, '<sup class="reference">[1]</sup>')
            
            // Islamic Content Extensions
            .replace(/\{\{Quran\|surah=(\d+)\|ayah=(\d+)\}\}/g, '<div class="quran-verse">Quran: Surah $1, Ayah $2</div>')
            .replace(/\{\{Hadith\|book=(.+?)\|number=(\d+)\}\}/g, '<div class="hadith">Hadith: $1, Number $2</div>')
            .replace(/\{\{Scholar\|name=(.+?)\}\}/g, '<div class="scholar">Scholar: $1</div>')
            .replace(/\{\{Fatwa\|scholar=(.+?)\}\}/g, '<div class="fatwa">Fatwa by: $1</div>');
        
        // Wrap lists
        html = html.replace(/(<li>.+<\/li>)/g, '<ul>$1</ul>');
        
        previewContent.innerHTML = html;
    }
    
    // Event listeners for preview
    if (previewBtn) {
        previewBtn.addEventListener('click', togglePreview);
    }
    if (showPreviewBtn) {
        showPreviewBtn.addEventListener('click', togglePreview);
    }
    
    // Update preview when content changes
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            if (previewSection && previewSection.style.display !== 'none') {
                updatePreview();
            }
        });
    }
    
    // Close preview button
    const closePreviewBtn = document.querySelector('.close-preview');
    if (closePreviewBtn) {
        closePreviewBtn.addEventListener('click', function() {
            previewSection.style.display = 'none';
            editorSection.style.flex = '1';
        });
    }
    
    // Enhanced Markdown Toolbar functionality
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const textarea = document.getElementById('content');
            if (!textarea) return;
            
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            let insertText = '';
            
            // Enhanced Markdown with Wiki Extensions
            switch(action) {
                // Basic Markdown
                case 'bold':
                    insertText = '**' + selectedText + '**';
                    break;
                case 'italic':
                    insertText = '*' + selectedText + '*';
                    break;
                case 'heading':
                    insertText = '# ' + selectedText;
                    break;
                case 'link':
                    insertText = '[' + selectedText + '](URL)';
                    break;
                case 'image':
                    insertText = '![' + selectedText + '](image-url)';
                    break;
                case 'list':
                    insertText = '- ' + selectedText;
                    break;
                case 'code':
                    insertText = '`' + selectedText + '`';
                    break;
                
                // Wiki Extensions
                case 'internal-link':
                    insertText = '[[' + selectedText + ']]';
                    break;
                case 'internal-link-display':
                    insertText = '[[' + selectedText + '|Display Text]]';
                    break;
                case 'category':
                    insertText = '[Category:' + selectedText + ']';
                    break;
                case 'template':
                    insertText = '{{Template|param=value}}';
                    break;
                case 'infobox':
                    insertText = '{{Infobox|title=' + selectedText + '}}';
                    break;
                case 'reference':
                    insertText = '{{Reference|source=' + selectedText + '}}';
                    break;
                case 'ref':
                    insertText = '<ref>' + selectedText + '</ref>';
                    break;
                case 'ref-named':
                    insertText = '<ref name="source1">' + selectedText + '</ref>';
                    break;
                
                // Islamic Content Extensions
                case 'quran-template':
                    insertText = '{{Quran|surah=1|ayah=1-7}}';
                    break;
                case 'quran-verse':
                    insertText = '{{Quran|surah=1|ayah=1|translation=en}}';
                    break;
                case 'quran-chapter':
                    insertText = '{{Quran|surah=1|translation=en}}';
                    break;
                case 'hadith-template':
                    insertText = '{{Hadith|book=Bukhari|number=1|grade=Sahih}}';
                    break;
                case 'hadith-chain':
                    insertText = '{{Hadith|chain=Abu Hurairah → Prophet Muhammad}}';
                    break;
                case 'hadith-grade':
                    insertText = '{{Hadith|grade=Sahih|narrator=Abu Hurairah}}';
                    break;
                case 'scholar-template':
                    insertText = '{{Scholar|name=' + selectedText + '|period=980-1037|field=Medicine}}';
                    break;
                case 'fatwa-template':
                    insertText = '{{Fatwa|scholar=' + selectedText + '|topic=Prayer|date=1300}}';
                    break;
                case 'prayer-times':
                    insertText = '{{PrayerTimes|city=Mecca|date=today}}';
                    break;
            }
            
            if (insertText) {
                textarea.value = textarea.value.substring(0, start) + insertText + textarea.value.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + insertText.length, start + insertText.length);
                console.log('Inserted Enhanced Markdown:', insertText);
            }
        });
    });
    
    // Special character insertion
    document.querySelectorAll('[data-char]').forEach(btn => {
        btn.addEventListener('click', function() {
            const char = this.dataset.char;
            const textarea = document.getElementById('content');
            if (!textarea) return;
            
            const start = textarea.selectionStart;
            
            textarea.value = textarea.value.substring(0, start) + char + textarea.value.substring(start);
            textarea.focus();
            textarea.setSelectionRange(start + char.length, start + char.length);
        });
    });
    
    // Initialize the system
    console.log('Enhanced Markdown with Wiki Extensions system initialized');
}); 
/**
 * Translator Extension JavaScript
 * 
 * Handles translation functionality between different language versions
 */

class TranslatorExtension {
    constructor() {
        this.initialize();
    }

    initialize() {
        this.bindEvents();
        this.setupTranslationTools();
    }

    bindEvents() {
        // Translate page button
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('translate-page-btn')) {
                this.handleTranslatePage(e.target);
            }
            
            if (e.target.classList.contains('translate-btn')) {
                this.handleTranslateSection(e.target);
            }
            
            if (e.target.classList.contains('suggest-translation-btn')) {
                this.handleSuggestTranslation();
            }
            
            if (e.target.classList.contains('review-translations-btn')) {
                this.handleReviewTranslations();
            }
        });

        // Translatable terms
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('translatable-term')) {
                this.handleTranslatableTerm(e.target);
            }
        });
    }

    setupTranslationTools() {
        // Add translation context menu
        this.createContextMenu();
        
        // Setup translation memory
        this.setupTranslationMemory();
    }

    handleTranslatePage(button) {
        const pagePath = button.dataset.pagePath;
        const currentLanguage = button.dataset.currentLanguage;
        
        console.log('Translating page:', pagePath, 'from', currentLanguage);
        
        // Show translation modal
        this.showTranslationModal({
            type: 'page',
            sourceLanguage: currentLanguage,
            pagePath: pagePath
        });
    }

    handleTranslateSection(button) {
        const sourceLanguage = button.dataset.source;
        const targetLanguage = button.dataset.target;
        const pagePath = button.dataset.pagePath;
        
        console.log('Translating section:', pagePath, 'from', sourceLanguage, 'to', targetLanguage);
        
        // Show translation modal
        this.showTranslationModal({
            type: 'section',
            sourceLanguage: sourceLanguage,
            targetLanguage: targetLanguage,
            pagePath: pagePath
        });
    }

    handleTranslatableTerm(term) {
        const termText = term.textContent;
        const termData = term.dataset.term;
        
        console.log('Translatable term clicked:', termText, termData);
        
        // Show term translation tooltip
        this.showTermTranslationTooltip(term, termText);
    }

    handleSuggestTranslation() {
        console.log('Suggest translation clicked');
        
        // Show suggestion form
        this.showSuggestionForm();
    }

    handleReviewTranslations() {
        console.log('Review translations clicked');
        
        // Show review interface
        this.showReviewInterface();
    }

    showTranslationModal(data) {
        // Create modal HTML
        const modal = document.createElement('div');
        modal.className = 'translation-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3>🌍 Translate Content</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="translation-form">
                        <div class="form-group">
                            <label>Source Language:</label>
                            <span class="language-display">${this.getLanguageDisplay(data.sourceLanguage)}</span>
                        </div>
                        ${data.targetLanguage ? `
                            <div class="form-group">
                                <label>Target Language:</label>
                                <span class="language-display">${this.getLanguageDisplay(data.targetLanguage)}</span>
                            </div>
                        ` : `
                            <div class="form-group">
                                <label>Target Language:</label>
                                <select id="targetLanguageSelect">
                                    ${this.getLanguageOptions(data.sourceLanguage)}
                                </select>
                            </div>
                        `}
                        <div class="form-group">
                            <label>Content to Translate:</label>
                            <textarea id="sourceContent" rows="10" placeholder="Enter content to translate..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Translation:</label>
                            <textarea id="targetContent" rows="10" placeholder="Translation will appear here..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.translation-modal').remove()">Cancel</button>
                    <button class="btn btn-primary" onclick="translatorExtension.translateContent()">Translate</button>
                    <button class="btn btn-success" onclick="translatorExtension.saveTranslation()">Save Translation</button>
                </div>
            </div>
        `;

        // Add modal to page
        document.body.appendChild(modal);
        
        // Bind close event
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
        
        // Load content if translating a page
        if (data.type === 'page') {
            this.loadPageContent(data.pagePath, data.sourceLanguage);
        }
    }

    showTermTranslationTooltip(term, termText) {
        // Remove existing tooltips
        document.querySelectorAll('.term-tooltip').forEach(t => t.remove());
        
        // Create tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'term-tooltip';
        tooltip.innerHTML = `
            <div class="tooltip-content">
                <h4>Translate: ${termText}</h4>
                <div class="translation-inputs">
                    <input type="text" placeholder="Arabic translation" class="ar-translation">
                    <input type="text" placeholder="Turkish translation" class="tr-translation">
                    <input type="text" placeholder="Urdu translation" class="ur-translation">
                </div>
                <div class="tooltip-actions">
                    <button class="btn btn-sm btn-primary save-translation">Save</button>
                    <button class="btn btn-sm btn-secondary cancel-translation">Cancel</button>
                </div>
            </div>
        `;
        
        // Position tooltip
        const rect = term.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.top = (rect.bottom + 5) + 'px';
        tooltip.style.left = rect.left + 'px';
        
        // Add to page
        document.body.appendChild(tooltip);
        
        // Bind events
        tooltip.querySelector('.save-translation').addEventListener('click', () => {
            this.saveTermTranslation(term, tooltip);
        });
        
        tooltip.querySelector('.cancel-translation').addEventListener('click', () => {
            tooltip.remove();
        });
        
        // Auto-remove on outside click
        setTimeout(() => {
            document.addEventListener('click', (e) => {
                if (!tooltip.contains(e.target) && !term.contains(e.target)) {
                    tooltip.remove();
                }
            }, { once: true });
        }, 100);
    }

    showSuggestionForm() {
        // Create suggestion form modal
        const modal = document.createElement('div');
        modal.className = 'suggestion-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3>💡 Suggest Translation</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="suggestion-form">
                        <div class="form-group">
                            <label>Original Text:</label>
                            <textarea id="originalText" rows="3" placeholder="Enter the original text..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Suggested Translation:</label>
                            <textarea id="suggestedTranslation" rows="3" placeholder="Enter your suggested translation..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Target Language:</label>
                            <select id="suggestionLanguage">
                                <option value="ar">Arabic</option>
                                <option value="tr">Turkish</option>
                                <option value="ur">Urdu</option>
                                <option value="id">Indonesian</option>
                                <option value="ms">Malay</option>
                                <option value="fa">Persian</option>
                                <option value="he">Hebrew</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes (optional):</label>
                            <textarea id="suggestionNotes" rows="2" placeholder="Any additional notes or context..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.suggestion-modal').remove()">Cancel</button>
                    <button class="btn btn-primary" onclick="translatorExtension.submitSuggestion()">Submit Suggestion</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Bind close event
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
    }

    showReviewInterface() {
        // Create review interface modal
        const modal = document.createElement('div');
        modal.className = 'review-modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3>🔍 Review Translations</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="review-filters">
                        <select id="reviewLanguage">
                            <option value="">All Languages</option>
                            <option value="ar">Arabic</option>
                            <option value="tr">Turkish</option>
                            <option value="ur">Urdu</option>
                            <option value="id">Indonesian</option>
                            <option value="ms">Malay</option>
                            <option value="fa">Persian</option>
                            <option value="he">Hebrew</option>
                        </select>
                        <select id="reviewStatus">
                            <option value="">All Status</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="review-list">
                        <p>Loading translations for review...</p>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Bind close event
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
        
        // Load review data
        this.loadReviewData();
    }

    getLanguageDisplay(languageCode) {
        const languages = {
            'en': '🇺🇸 English',
            'ar': '🇸🇦 العربية',
            'tr': '🇹🇷 Türkçe',
            'ur': '🇵🇰 اردو',
            'id': '🇮🇩 Bahasa Indonesia',
            'ms': '🇲🇾 Bahasa Melayu',
            'fa': '🇮🇷 فارسی',
            'he': '🇮🇱 עברית'
        };
        
        return languages[languageCode] || languageCode;
    }

    getLanguageOptions(excludeLanguage) {
        const languages = [
            { code: 'ar', name: '🇸🇦 العربية' },
            { code: 'tr', name: '🇹🇷 Türkçe' },
            { code: 'ur', name: '🇵🇰 اردو' },
            { code: 'id', name: '🇮🇩 Bahasa Indonesia' },
            { code: 'ms', name: '🇲🇾 Bahasa Melayu' },
            { code: 'fa', name: '🇮🇷 فارسی' },
            { code: 'he', name: '🇮🇱 עברית' }
        ];
        
        return languages
            .filter(lang => lang.code !== excludeLanguage)
            .map(lang => `<option value="${lang.code}">${lang.name}</option>`)
            .join('');
    }

    loadPageContent(pagePath, sourceLanguage) {
        // This would load the actual page content
        // For now, show a placeholder
        const sourceContent = document.getElementById('sourceContent');
        if (sourceContent) {
            sourceContent.value = `Loading content from ${pagePath}...`;
        }
    }

    translateContent() {
        const sourceContent = document.getElementById('sourceContent').value;
        const targetLanguage = document.getElementById('targetLanguageSelect')?.value || 'ar';
        
        if (!sourceContent.trim()) {
            alert('Please enter content to translate');
            return;
        }
        
        // This would call a translation API
        // For now, show a placeholder
        const targetContent = document.getElementById('targetContent');
        if (targetContent) {
            targetContent.value = `[Translation to ${targetLanguage} would appear here]`;
        }
    }

    saveTranslation() {
        const sourceContent = document.getElementById('sourceContent').value;
        const targetContent = document.getElementById('targetContent').value;
        const targetLanguage = document.getElementById('targetLanguageSelect')?.value || 'ar';
        
        if (!targetContent.trim()) {
            alert('Please provide a translation');
            return;
        }
        
        // This would save the translation to the database
        console.log('Saving translation:', {
            source: sourceContent,
            target: targetContent,
            language: targetLanguage
        });
        
        alert('Translation saved successfully!');
        
        // Close modal
        document.querySelector('.translation-modal').remove();
    }

    saveTermTranslation(term, tooltip) {
        const arTranslation = tooltip.querySelector('.ar-translation').value;
        const trTranslation = tooltip.querySelector('.tr-translation').value;
        const urTranslation = tooltip.querySelector('.ur-translation').value;
        
        // This would save the term translations
        console.log('Saving term translations:', {
            term: term.textContent,
            ar: arTranslation,
            tr: trTranslation,
            ur: urTranslation
        });
        
        // Update term with translation data
        term.dataset.translations = JSON.stringify({
            ar: arTranslation,
            tr: trTranslation,
            ur: urTranslation
        });
        
        tooltip.remove();
        alert('Term translations saved!');
    }

    submitSuggestion() {
        const originalText = document.getElementById('originalText').value;
        const suggestedTranslation = document.getElementById('suggestedTranslation').value;
        const language = document.getElementById('suggestionLanguage').value;
        const notes = document.getElementById('suggestionNotes').value;
        
        if (!originalText.trim() || !suggestedTranslation.trim()) {
            alert('Please fill in all required fields');
            return;
        }
        
        // This would submit the suggestion
        console.log('Submitting suggestion:', {
            original: originalText,
            translation: suggestedTranslation,
            language: language,
            notes: notes
        });
        
        alert('Suggestion submitted successfully!');
        
        // Close modal
        document.querySelector('.suggestion-modal').remove();
    }

    loadReviewData() {
        // This would load actual review data
        // For now, show placeholder
        const reviewList = document.querySelector('.review-list');
        if (reviewList) {
            reviewList.innerHTML = `
                <div class="review-item">
                    <div class="review-content">
                        <strong>Original:</strong> "Allah is the Most Merciful"
                        <br><strong>Translation:</strong> "الله هو الأكثر رحمة"
                        <br><strong>Language:</strong> Arabic
                        <br><strong>Status:</strong> Pending Review
                    </div>
                    <div class="review-actions">
                        <button class="btn btn-sm btn-success">Approve</button>
                        <button class="btn btn-sm btn-danger">Reject</button>
                        <button class="btn btn-sm btn-secondary">Edit</button>
                    </div>
                </div>
            `;
        }
    }

    createContextMenu() {
        // Create context menu for translation features
        const contextMenu = document.createElement('div');
        contextMenu.className = 'translation-context-menu';
        contextMenu.style.display = 'none';
        contextMenu.innerHTML = `
            <div class="context-menu-item" data-action="translate">🌍 Translate</div>
            <div class="context-menu-item" data-action="suggest">💡 Suggest Translation</div>
            <div class="context-menu-item" data-action="review">🔍 Review</div>
        `;
        
        document.body.appendChild(contextMenu);
        
        // Bind context menu events
        document.addEventListener('contextmenu', (e) => {
            if (e.target.classList.contains('translatable-term')) {
                e.preventDefault();
                this.showContextMenu(e, contextMenu);
            }
        });
        
        // Hide context menu on click
        document.addEventListener('click', () => {
            contextMenu.style.display = 'none';
        });
    }

    showContextMenu(event, contextMenu) {
        const rect = event.target.getBoundingClientRect();
        contextMenu.style.left = rect.right + 'px';
        contextMenu.style.top = rect.top + 'px';
        contextMenu.style.display = 'block';
        
        // Bind context menu actions
        contextMenu.querySelectorAll('.context-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const action = item.dataset.action;
                this.handleContextMenuAction(action, event.target);
                contextMenu.style.display = 'none';
            });
        });
    }

    handleContextMenuAction(action, target) {
        switch (action) {
            case 'translate':
                this.handleTranslatableTerm(target);
                break;
            case 'suggest':
                this.handleSuggestTranslation();
                break;
            case 'review':
                this.handleReviewTranslations();
                break;
        }
    }

    setupTranslationMemory() {
        // Initialize translation memory system
        if (!localStorage.getItem('translationMemory')) {
            localStorage.setItem('translationMemory', JSON.stringify({}));
        }
    }
}

// Initialize the translator extension when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.translatorExtension = new TranslatorExtension();
});

// Add CSS for modals and tooltips
const style = document.createElement('style');
style.textContent = `
    .translation-modal,
    .suggestion-modal,
    .review-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        color: #2c3e50;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c757d;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .language-display {
        font-weight: 600;
        color: #1976d2;
        padding: 8px 12px;
        background: #e3f2fd;
        border-radius: 4px;
        display: inline-block;
    }
    
    .term-tooltip {
        position: absolute;
        z-index: 10001;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        padding: 15px;
        min-width: 300px;
    }
    
    .tooltip-content h4 {
        margin: 0 0 15px 0;
        color: #2c3e50;
    }
    
    .translation-inputs {
        display: grid;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .translation-inputs input {
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    
    .tooltip-actions {
        display: flex;
        gap: 10px;
    }
    
    .translation-context-menu {
        position: fixed;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 10002;
    }
    
    .context-menu-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .context-menu-item:hover {
        background: #f8f9fa;
    }
    
    .context-menu-item:last-child {
        border-bottom: none;
    }
    
    .review-filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .review-filters select {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    
    .review-item {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
    }
    
    .review-content {
        margin-bottom: 15px;
        line-height: 1.6;
    }
    
    .review-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-primary {
        background: #007bff;
        color: white;
    }
    
    .btn-primary:hover {
        background: #0056b3;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #545b62;
    }
    
    .btn-success {
        background: #28a745;
        color: white;
    }
    
    .btn-success:hover {
        background: #218838;
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c82333;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
`;

document.head.appendChild(style); 
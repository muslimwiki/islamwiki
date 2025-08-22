/**
 * Iqra Search Extension - Advanced Search Functionality
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class IqraSearch {
    constructor() {
        this.searchInput = document.getElementById('search-input');
        this.searchSuggestions = document.getElementById('search-suggestions');
        this.suggestionsList = document.getElementById('suggestions-list');
        this.searchTimeout = null;
        this.currentQuery = '';
        
        this.init();
    }

    init() {
        if (this.searchInput) {
            this.setupEventListeners();
            this.setupFilterButtons();
        }
    }

    setupEventListeners() {
        // Search input events
        this.searchInput.addEventListener('input', (e) => this.handleInput(e));
        this.searchInput.addEventListener('focus', () => this.handleFocus());
        this.searchInput.addEventListener('blur', () => this.handleBlur());
        
        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Click outside to hide suggestions
        document.addEventListener('click', (e) => this.handleClickOutside(e));
        
        // Form submission
        const searchForm = document.querySelector('.search-form__form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    setupFilterButtons() {
        const filterButtons = document.querySelectorAll('.search-results__filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleFilterClick(e));
        });
    }

    handleInput(e) {
        const query = e.target.value.trim();
        this.currentQuery = query;
        
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Show suggestions after 300ms delay
        if (query.length >= 2) {
            this.searchTimeout = setTimeout(() => {
                this.getSearchSuggestions(query);
            }, 300);
        } else {
            this.hideSuggestions();
        }
    }

    handleFocus() {
        if (this.currentQuery.length >= 2) {
            this.getSearchSuggestions(this.currentQuery);
        }
    }

    handleBlur() {
        // Delay hiding to allow for suggestion clicks
        setTimeout(() => {
            if (!this.searchSuggestions.contains(document.activeElement)) {
                this.hideSuggestions();
            }
        }, 200);
    }

    handleKeydown(e) {
        const suggestions = this.suggestionsList.querySelectorAll('.search-suggestion-item');
        const currentIndex = Array.from(suggestions).findIndex(item => item.classList.contains('active'));
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.navigateSuggestions(currentIndex, 1, suggestions);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.navigateSuggestions(currentIndex, -1, suggestions);
                break;
            case 'Enter':
                if (currentIndex >= 0) {
                    e.preventDefault();
                    this.selectSuggestion(suggestions[currentIndex]);
                }
                break;
            case 'Escape':
                this.hideSuggestions();
                this.searchInput.blur();
                break;
        }
    }

    handleClickOutside(e) {
        if (!this.searchInput.contains(e.target) && !this.searchSuggestions.contains(e.target)) {
            this.hideSuggestions();
        }
    }

    handleSubmit(e) {
        const query = this.searchInput.value.trim();
        if (query.length < 2) {
            e.preventDefault();
            this.showError('Please enter at least 2 characters to search');
            return;
        }
        
        // Hide suggestions before submitting
        this.hideSuggestions();
    }

    handleFilterClick(e) {
        e.preventDefault();
        const button = e.target;
        const filter = button.dataset.filter;
        
        // Update active state
        document.querySelectorAll('.search-results__filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
        
        // Filter results
        this.filterResults(filter);
    }

    async getSearchSuggestions(query) {
        try {
            const response = await fetch(`/search/api/suggestions?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success !== false && data.suggestions && data.suggestions.length > 0) {
                this.showSuggestions(data.suggestions);
            } else {
                this.hideSuggestions();
            }
        } catch (error) {
            console.error('Failed to get search suggestions:', error);
            this.hideSuggestions();
        }
    }

    showSuggestions(suggestions) {
        if (!suggestions || suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        // Clear previous suggestions
        this.suggestionsList.innerHTML = '';
        
        // Add new suggestions
        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'search-suggestion-item';
            suggestionItem.innerHTML = `
                <span class="suggestion-text">${this.escapeHtml(suggestion)}</span>
                <span class="suggestion-icon">🔍</span>
            `;
            
            // Click handler for suggestion
            suggestionItem.addEventListener('click', () => {
                this.selectSuggestion(suggestionItem);
            });
            
            // Mouse events for highlighting
            suggestionItem.addEventListener('mouseenter', () => {
                this.highlightSuggestion(suggestionItem);
            });
            
            this.suggestionsList.appendChild(suggestionItem);
        });
        
        // Show suggestions container
        this.searchSuggestions.style.display = 'block';
    }

    hideSuggestions() {
        this.searchSuggestions.style.display = 'none';
        this.suggestionsList.innerHTML = '';
        
        // Remove active state from all suggestions
        this.suggestionsList.querySelectorAll('.search-suggestion-item').forEach(item => {
            item.classList.remove('active');
        });
    }

    selectSuggestion(suggestionItem) {
        const suggestionText = suggestionItem.querySelector('.suggestion-text').textContent;
        this.searchInput.value = suggestionText;
        this.hideSuggestions();
        
        // Trigger search
        this.searchInput.form.submit();
    }

    highlightSuggestion(suggestionItem) {
        // Remove active state from all suggestions
        this.suggestionsList.querySelectorAll('.search-suggestion-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active state to current suggestion
        suggestionItem.classList.add('active');
    }

    navigateSuggestions(currentIndex, direction, suggestions) {
        if (suggestions.length === 0) return;
        
        let newIndex = currentIndex + direction;
        
        // Wrap around
        if (newIndex < 0) {
            newIndex = suggestions.length - 1;
        } else if (newIndex >= suggestions.length) {
            newIndex = 0;
        }
        
        // Remove active state from current suggestion
        if (currentIndex >= 0 && suggestions[currentIndex]) {
            suggestions[currentIndex].classList.remove('active');
        }
        
        // Add active state to new suggestion
        suggestions[newIndex].classList.add('active');
        
        // Scroll into view if needed
        suggestions[newIndex].scrollIntoView({ block: 'nearest' });
    }

    filterResults(filter) {
        const results = document.querySelectorAll('.search-result');
        
        results.forEach(result => {
            if (filter === 'all' || result.dataset.type === filter) {
                result.style.display = 'block';
                result.style.opacity = '1';
            } else {
                result.style.display = 'none';
                result.style.opacity = '0';
            }
        });
        
        // Update result count
        this.updateResultCount(filter);
    }

    updateResultCount(filter) {
        const visibleResults = document.querySelectorAll('.search-result[style*="block"], .search-result:not([style*="none"])');
        const totalResults = document.querySelectorAll('.search-result');
        
        let count = visibleResults.length;
        if (filter === 'all') {
            count = totalResults.length;
        }
        
        // Update the result count display
        const resultInfo = document.querySelector('.search-results__info p');
        if (resultInfo) {
            const originalText = resultInfo.textContent;
            const newText = originalText.replace(/\d+ results/, `${count} results`);
            resultInfo.textContent = newText;
        }
    }

    showError(message) {
        // Create error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'search-error';
        errorDiv.innerHTML = `
            <div class="search-error__content">
                <span class="search-error__icon">⚠️</span>
                <span class="search-error__message">${this.escapeHtml(message)}</span>
                <button class="search-error__close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        // Add styles
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
        `;
        
        // Add to page
        document.body.appendChild(errorDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentElement) {
                errorDiv.remove();
            }
        }, 5000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize Iqra Search when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new IqraSearch();
});

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IqraSearch;
} 
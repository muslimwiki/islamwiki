/**
 * Global Header Search Functionality
 * Provides search capabilities across all pages
 */

class HeaderSearch {
    constructor() {
        this.searchInput = document.getElementById('header-search-input');
        this.searchForm = document.querySelector('.header-search__form');
        this.suggestionsContainer = document.getElementById('header-search-suggestions');
        this.suggestionsList = document.getElementById('header-suggestions-list');
        this.searchTimeout = null;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        if (!this.searchInput) return;
        
        this.bindEvents();
        this.setupKeyboardNavigation();
    }

    bindEvents() {
        // Search input events
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });

        this.searchInput.addEventListener('focus', () => {
            this.showSuggestions();
        });

        this.searchInput.addEventListener('blur', () => {
            // Delay hiding to allow clicking on suggestions
            setTimeout(() => {
                this.hideSuggestions();
            }, 200);
        });

        // Form submission
        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Click outside to hide suggestions
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.suggestionsContainer.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }

    setupKeyboardNavigation() {
        this.searchInput.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.navigateSuggestions('down');
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.navigateSuggestions('up');
                    break;
                case 'Enter':
                    e.preventDefault();
                    this.performSearch();
                    break;
                case 'Escape':
                    this.hideSuggestions();
                    this.searchInput.blur();
                    break;
            }
        });
    }

    handleSearchInput(query) {
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Hide suggestions if query is too short
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }

        // Set loading state
        this.setLoadingState(true);

        // Debounce search suggestions
        this.searchTimeout = setTimeout(() => {
            this.fetchSearchSuggestions(query);
        }, 300);
    }

    async fetchSearchSuggestions(query) {
        try {
            const response = await fetch(`/search/api/suggestions?q=${encodeURIComponent(query)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.displaySuggestions(data.suggestions, query);
            
        } catch (error) {
            console.error('Error fetching search suggestions:', error);
            this.displaySuggestions(this.getFallbackSuggestions(query), query);
        } finally {
            this.setLoadingState(false);
        }
    }

    displaySuggestions(suggestions, query) {
        if (!suggestions || suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }

        this.suggestionsList.innerHTML = '';
        
        suggestions.forEach((suggestion, index) => {
            const item = this.createSuggestionItem(suggestion, query, index);
            this.suggestionsList.appendChild(item);
        });

        this.showSuggestions();
    }

    createSuggestionItem(suggestion, query, index) {
        const li = document.createElement('li');
        li.className = 'header-search__suggestion-item';
        li.setAttribute('data-index', index);
        li.setAttribute('tabindex', '0');
        
        // Determine content type based on suggestion
        const contentType = this.determineContentType(suggestion);
        const icon = this.getContentTypeIcon(contentType);
        
        li.innerHTML = `
            <span class="header-search__suggestion-icon">${icon}</span>
            <span class="header-search__suggestion-text">${this.highlightQuery(suggestion, query)}</span>
            <span class="header-search__suggestion-type">${contentType}</span>
        `;

        // Add click event
        li.addEventListener('click', () => {
            this.selectSuggestion(suggestion);
        });

        // Add keyboard events
        li.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.selectSuggestion(suggestion);
            }
        });

        return li;
    }

    determineContentType(suggestion) {
        const suggestionLower = suggestion.toLowerCase();
        
        if (suggestionLower.includes('quran') || suggestionLower.includes('surah')) {
            return 'Quran';
        } else if (suggestionLower.includes('hadith')) {
            return 'Hadith';
        } else if (suggestionLower.includes('article')) {
            return 'Article';
        } else if (suggestionLower.includes('scholar')) {
            return 'Scholar';
        } else {
            return 'Wiki';
        }
    }

    getContentTypeIcon(contentType) {
        const icons = {
            'Quran': '📖',
            'Hadith': '📜',
            'Article': '📝',
            'Scholar': '👨‍🏫',
            'Wiki': '📚'
        };
        
        return icons[contentType] || '📚';
    }

    highlightQuery(suggestion, query) {
        if (!query) return suggestion;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return suggestion.replace(regex, '<strong>$1</strong>');
    }

    selectSuggestion(suggestion) {
        this.searchInput.value = suggestion;
        this.hideSuggestions();
        this.performSearch();
    }

    performSearch() {
        const query = this.searchInput.value.trim();
        
        if (query) {
            // Redirect to search page with query
            window.location.href = `/search?q=${encodeURIComponent(query)}`;
        }
    }

    navigateSuggestions(direction) {
        const items = this.suggestionsList.querySelectorAll('.header-search__suggestion-item');
        if (items.length === 0) return;

        const currentIndex = Array.from(items).findIndex(item => 
            item === document.activeElement || item.classList.contains('active')
        );

        let newIndex;
        if (direction === 'down') {
            newIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
        } else {
            newIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
        }

        // Remove active class from all items
        items.forEach(item => item.classList.remove('active'));
        
        // Add active class to new item
        items[newIndex].classList.add('active');
        items[newIndex].focus();
    }

    showSuggestions() {
        if (this.suggestionsContainer && this.suggestionsList.children.length > 0) {
            this.suggestionsContainer.style.display = 'block';
        }
    }

    hideSuggestions() {
        if (this.suggestionsContainer) {
            this.suggestionsContainer.style.display = 'none';
        }
    }

    setLoadingState(loading) {
        this.isLoading = loading;
        const inputGroup = this.searchInput.closest('.header-search__input-group');
        
        if (loading) {
            inputGroup.classList.add('loading');
        } else {
            inputGroup.classList.remove('loading');
        }
    }

    getFallbackSuggestions(query) {
        // Fallback suggestions if API fails
        return [
            `${query} knowledge`,
            `${query} principles`,
            `${query} history`,
            `${query} teachings`
        ];
    }
}

// Initialize header search when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new HeaderSearch();
});

// Export for potential external use
window.HeaderSearch = HeaderSearch; 
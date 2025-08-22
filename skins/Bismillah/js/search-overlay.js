/**
 * Search Overlay System for Bismillah Skin
 * Version: 0.0.2.2
 * Provides beautiful bismillah search interface
 */

class SearchOverlay {
    constructor() {
        this.searchOverlay = document.querySelector('.search-overlay');
        this.searchInput = document.querySelector('.search-input');
        this.shortcutInput = document.querySelector('.shortcut-input');
        this.init();
    }

    init() {
        this.setupSearchInput();
        this.setupKeyboardShortcuts();
        this.setupClickOutside();
    }

    setupSearchInput() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });

            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch();
                }
            });
        }

        if (this.shortcutInput) {
            this.shortcutInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    this.performNamespaceSearch();
                }
            });
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes search overlay
            if (e.key === 'Escape' && this.isVisible()) {
                this.hideSearchOverlay();
            }
            
            // Colon key opens search overlay
            if (e.key === ':' && !this.isVisible()) {
                e.preventDefault();
                this.showSearchOverlay();
            }
            
            // Ctrl/Cmd + K opens search overlay
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.showSearchOverlay();
            }
        });
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (this.isVisible() && !this.searchOverlay.contains(e.target)) {
                this.hideSearchOverlay();
            }
        });
    }

    showSearchOverlay() {
        this.searchOverlay.classList.add('active');
        this.searchOverlay.style.display = 'flex';
        
        // Focus on search input
        setTimeout(() => {
            if (this.searchInput) {
                this.searchInput.focus();
            }
        }, 100);
        
        // Animate bismillah elements
        this.animateBismillahElements();
        
        // Add body class to prevent scrolling
        document.body.classList.add('search-overlay-open');
    }

    hideSearchOverlay() {
        this.searchOverlay.classList.remove('active');
        setTimeout(() => {
            this.searchOverlay.style.display = 'none';
        }, 300);
        
        // Remove body class
        document.body.classList.remove('search-overlay-open');
        
        // Clear inputs
        if (this.searchInput) this.searchInput.value = '';
        if (this.shortcutInput) this.shortcutInput.value = '';
    }

    animateBismillahElements() {
        const bismillahText = this.searchOverlay.querySelector('.bismillah-text');
        const searchIcon = this.searchOverlay.querySelector('.search-icon-large');
        const title = this.searchOverlay.querySelector('h2');
        const subtitle = this.searchOverlay.querySelector('p');
        const shortcuts = this.searchOverlay.querySelector('.search-shortcuts');
        const actions = this.searchOverlay.querySelector('.search-actions');

        // Reset animations
        [bismillahText, searchIcon, title, subtitle, shortcuts, actions].forEach(el => {
            if (el) {
                el.style.animation = 'none';
                el.offsetHeight; // Trigger reflow
            }
        });

        // Apply animations with delays
        if (bismillahText) {
            bismillahText.style.animation = 'fadeInUp 0.8s ease forwards';
        }
        
        if (searchIcon) {
            searchIcon.style.animation = 'fadeInUp 0.8s ease 0.2s forwards';
        }
        
        if (title) {
            title.style.animation = 'fadeInUp 0.8s ease 0.4s forwards';
        }
        
        if (subtitle) {
            subtitle.style.animation = 'fadeInUp 0.8s ease 0.6s forwards';
        }
        
        if (shortcuts) {
            shortcuts.style.animation = 'fadeInUp 0.8s ease 0.8s forwards';
        }
        
        if (actions) {
            actions.style.animation = 'fadeInUp 0.8s ease 1s forwards';
        }
    }

    handleSearchInput(query) {
        // Real-time search suggestions could be implemented here
        if (query.length >= 2) {
            this.showSearchSuggestions(query);
        } else {
            this.hideSearchSuggestions();
        }
    }

    showSearchSuggestions(query) {
        // Create or update suggestions dropdown
        let suggestionsContainer = this.searchOverlay.querySelector('.search-suggestions');
        
        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions';
            this.searchOverlay.querySelector('.search-content').appendChild(suggestionsContainer);
        }

        // Mock suggestions for now - replace with real API call
        const suggestions = this.generateMockSuggestions(query);
        this.renderSuggestions(suggestionsContainer, suggestions);
    }

    hideSearchSuggestions() {
        const suggestionsContainer = this.searchOverlay.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }

    generateMockSuggestions(query) {
        // Mock suggestions - replace with real search API
        const baseSuggestions = [
            'Islamic knowledge',
            'Quran studies',
            'Hadith collections',
            'Islamic history',
            'Fiqh principles',
            'Islamic practices',
            'Islamic scholars',
            'Islamic books'
        ];

        return baseSuggestions
            .filter(suggestion => suggestion.toLowerCase().includes(query.toLowerCase()))
            .slice(0, 5);
    }

    renderSuggestions(container, suggestions) {
        container.innerHTML = '';
        
        if (suggestions.length === 0) {
            container.innerHTML = '<div class="no-suggestions">No suggestions found</div>';
            return;
        }

        const suggestionsList = document.createElement('ul');
        suggestionsList.className = 'suggestions-list';
        
        suggestions.forEach(suggestion => {
            const li = document.createElement('li');
            li.className = 'suggestion-item';
            li.textContent = suggestion;
            li.addEventListener('click', () => {
                this.selectSuggestion(suggestion);
            });
            suggestionsList.appendChild(li);
        });
        
        container.appendChild(suggestionsList);
    }

    selectSuggestion(suggestion) {
        if (this.searchInput) {
            this.searchInput.value = suggestion;
        }
        this.performSearch();
    }

    performSearch() {
        const query = this.searchInput ? this.searchInput.value.trim() : '';
        
        if (query.length === 0) {
            return;
        }

        // Perform the search
        this.executeSearch(query);
    }

    performNamespaceSearch() {
        const namespace = this.shortcutInput ? this.shortcutInput.value.trim() : '';
        
        if (namespace.length === 0) {
            return;
        }

        // Search within specific namespace
        this.executeSearch(`namespace:${namespace}`);
    }

    executeSearch(query) {
        // Close search overlay
        this.hideSearchOverlay();
        
        // Redirect to search results page
        const searchUrl = `/search?q=${encodeURIComponent(query)}`;
        window.location.href = searchUrl;
    }

    isVisible() {
        return this.searchOverlay.classList.contains('active');
    }

    // Public methods for external control
    show() {
        this.showSearchOverlay();
    }

    hide() {
        this.hideSearchOverlay();
    }

    toggle() {
        if (this.isVisible()) {
            this.hideSearchOverlay();
        } else {
            this.showSearchOverlay();
        }
    }

    // Method to update search input value
    setSearchQuery(query) {
        if (this.searchInput) {
            this.searchInput.value = query;
        }
    }

    // Method to clear all inputs
    clear() {
        if (this.searchInput) this.searchInput.value = '';
        if (this.shortcutInput) this.shortcutInput.value = '';
    }
}

// Export for global use
window.SearchOverlay = SearchOverlay; 
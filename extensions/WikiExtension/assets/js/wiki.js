/**
 * WikiExtension - Main JavaScript
 * 
 * @package IslamWiki\Extensions\WikiExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class WikiExtension {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeComponents();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.querySelector('.wiki-search input');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearch.bind(this));
        }
        
        // Category filtering
        const categoryLinks = document.querySelectorAll('.wiki-category');
        categoryLinks.forEach(link => {
            link.addEventListener('click', this.handleCategoryClick.bind(this));
        });
        
        // Edit buttons
        const editButtons = document.querySelectorAll('.wiki-btn[onclick*="edit"]');
        editButtons.forEach(button => {
            button.addEventListener('click', this.handleEdit.bind(this));
        });
        
        // History buttons
        const historyButtons = document.querySelectorAll('.wiki-btn[onclick*="history"]');
        historyButtons.forEach(button => {
            button.addEventListener('click', this.handleHistory.bind(this));
        });
    }
    
    initializeComponents() {
        // Initialize any components that need setup
        this.initializeSearch();
        this.initializeCategories();
        this.initializeActions();
    }
    
    initializeSearch() {
        const searchContainer = document.querySelector('.wiki-search');
        if (searchContainer) {
            // Add search suggestions if needed
            this.setupSearchSuggestions();
        }
    }
    
    initializeCategories() {
        const categoryContainer = document.querySelector('.wiki-categories');
        if (categoryContainer) {
            // Add category management if needed
            this.setupCategoryManagement();
        }
    }
    
    initializeActions() {
        const actionsContainer = document.querySelector('.wiki-actions');
        if (actionsContainer) {
            // Add action tooltips or enhancements
            this.setupActionEnhancements();
        }
    }
    
    handleSearch(event) {
        const query = event.target.value.trim();
        if (query.length >= 2) {
            this.performSearch(query);
        } else if (query.length === 0) {
            this.clearSearchResults();
        }
    }
    
    performSearch(query) {
        // Implement search functionality
        console.log('Searching for:', query);
        
        // Show loading state
        this.showSearchLoading();
        
        // Simulate search (replace with actual API call)
        setTimeout(() => {
            this.displaySearchResults(query);
        }, 500);
    }
    
    showSearchLoading() {
        const resultsContainer = document.querySelector('.wiki-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="search-loading">Searching...</div>';
        }
    }
    
    displaySearchResults(query) {
        const resultsContainer = document.querySelector('.wiki-results');
        if (resultsContainer) {
            // Mock results - replace with actual results
            const mockResults = [
                {
                    title: 'Sample Wiki Page 1',
                    excerpt: 'This is a sample search result for the query: ' + query,
                    url: '/wiki/sample-page-1',
                    author: 'Admin',
                    date: '2025-01-20'
                },
                {
                    title: 'Sample Wiki Page 2',
                    excerpt: 'Another sample result matching the search query: ' + query,
                    url: '/wiki/sample-page-2',
                    author: 'User',
                    date: '2025-01-19'
                }
            ];
            
            const resultsHTML = mockResults.map(result => `
                <div class="wiki-result-item">
                    <a href="${result.url}" class="wiki-result-title">${result.title}</a>
                    <div class="wiki-result-excerpt">${result.excerpt}</div>
                    <div class="wiki-result-meta">
                        By ${result.author} on ${result.date}
                    </div>
                </div>
            `).join('');
            
            resultsContainer.innerHTML = resultsHTML;
        }
    }
    
    clearSearchResults() {
        const resultsContainer = document.querySelector('.wiki-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
    }
    
    handleCategoryClick(event) {
        event.preventDefault();
        const category = event.target.textContent;
        this.filterByCategory(category);
    }
    
    filterByCategory(category) {
        console.log('Filtering by category:', category);
        
        // Update active category
        document.querySelectorAll('.wiki-category').forEach(cat => {
            cat.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Implement category filtering
        this.loadCategoryContent(category);
    }
    
    loadCategoryContent(category) {
        // Load content for the selected category
        console.log('Loading content for category:', category);
        
        // Show loading state
        this.showCategoryLoading();
        
        // Simulate loading (replace with actual API call)
        setTimeout(() => {
            this.displayCategoryContent(category);
        }, 300);
    }
    
    showCategoryLoading() {
        const contentContainer = document.querySelector('.wiki-content');
        if (contentContainer) {
            contentContainer.innerHTML = '<div class="category-loading">Loading category content...</div>';
        }
    }
    
    displayCategoryContent(category) {
        const contentContainer = document.querySelector('.wiki-content');
        if (contentContainer) {
            // Mock content - replace with actual content
            contentContainer.innerHTML = `
                <div class="wiki-page">
                    <h1>Category: ${category}</h1>
                    <p>This page shows all content in the ${category} category.</p>
                    <div class="wiki-meta">
                        <div class="wiki-meta-item">
                            <span class="wiki-meta-label">Category:</span>
                            <span>${category}</span>
                        </div>
                        <div class="wiki-meta-item">
                            <span class="wiki-meta-label">Pages:</span>
                            <span>5</span>
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    handleEdit(event) {
        event.preventDefault();
        const pageId = event.target.dataset.pageId || 'unknown';
        console.log('Editing page:', pageId);
        
        // Redirect to edit page
        window.location.href = `/wiki/${pageId}/edit`;
    }
    
    handleHistory(event) {
        event.preventDefault();
        const pageId = event.target.dataset.pageId || 'unknown';
        console.log('Viewing history for page:', pageId);
        
        // Redirect to history page
        window.location.href = `/wiki/${pageId}/history`;
    }
    
    setupSearchSuggestions() {
        // Add search suggestions functionality
        console.log('Search suggestions initialized');
    }
    
    setupCategoryManagement() {
        // Add category management functionality
        console.log('Category management initialized');
    }
    
    setupActionEnhancements() {
        // Add action enhancements
        console.log('Action enhancements initialized');
    }
}

// Initialize WikiExtension when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new WikiExtension();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WikiExtension;
} 
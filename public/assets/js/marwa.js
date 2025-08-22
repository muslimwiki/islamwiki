/**
 * Marwa JavaScript Framework - Excellence in User Interactions
 * 
 * @package IslamWiki\Core\JavaScript
 * @version 1.0.0
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class MarwaFramework {
    constructor() {
        this.components = new Map();
        this.eventBus = new EventTarget();
        this.config = {
            debug: false,
            animationDuration: 300,
            zIndexBase: 1000
        };
        
        this.init();
    }
    
    init() {
        this.log('Marwa Framework initialized');
        this.setupGlobalEventListeners();
    }
    
    log(message, ...args) {
        if (this.config.debug) {
            console.log(`[Marwa] ${message}`, ...args);
        }
    }
    
    setupGlobalEventListeners() {
        // Handle escape key for closing dropdowns
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
            }
        });
        
        // Handle clicks outside dropdowns
        document.addEventListener('click', (e) => {
            // Only close if clicking outside both the sidebar icon and the dropdown
            if (!e.target.closest('.sidebar-icon') && !e.target.closest('.settings-dropdown, .notifications-panel, .profile-dropdown, .search-dropdown, .hamburger-dropdown')) {
                this.closeAllDropdowns();
            }
        });
    }
    
    closeAllDropdowns() {
        document.querySelectorAll('.settings-dropdown, .notifications-panel, .profile-menu')
            .forEach(dropdown => {
                dropdown.style.display = 'none';
            });
    }
    
    registerComponent(name, component) {
        this.components.set(name, component);
        this.log(`Component registered: ${name}`);
    }
    
    getComponent(name) {
        return this.components.get(name);
    }
}

/**
 * Sidebar Dropdown Component
 */
class MarwaSidebarDropdown {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            trigger: options.trigger || '.sidebar-icon',
            dropdown: options.dropdown || '.settings-dropdown',
            position: options.position || 'right',
            offset: options.offset || 10,
            ...options
        };
        
        this.isOpen = false;
        this.init();
    }
    
    init() {
        this.trigger = this.container.querySelector(this.options.trigger);
        this.dropdown = this.container.querySelector(this.options.dropdown);
        
        if (!this.trigger || !this.dropdown) {
            console.warn('MarwaSidebarDropdown: Required elements not found');
            return;
        }
        
        this.setupEventListeners();
        this.positionDropdown();
    }
    
    setupEventListeners() {
        // Toggle dropdown on trigger click
        this.trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        if (this.dropdown) {
            this.dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }
    
    positionDropdown() {
        const triggerRect = this.trigger.getBoundingClientRect();
        const sidebarWidth = 60; // Sidebar width
        
        // Position dropdown to the right of the sidebar
        this.dropdown.style.position = 'fixed';
        this.dropdown.style.left = `${sidebarWidth + this.options.offset}px`;
        this.dropdown.style.top = `${triggerRect.top}px`;
        this.dropdown.style.zIndex = '9999';
        
        // Ensure dropdown doesn't go off-screen
        const dropdownRect = this.dropdown.getBoundingClientRect();
        if (dropdownRect.right > window.innerWidth) {
            this.dropdown.style.left = `${window.innerWidth - dropdownRect.width - this.options.offset}px`;
        }
    }
    
    open() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
        this.container.classList.add('dropdown-open');
        this.log('Dropdown opened');
    }
    
    close() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
        this.container.classList.remove('dropdown-open');
        this.log('Dropdown closed');
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.closeAllOtherDropdowns();
            this.open();
        }
    }
    
    closeAllOtherDropdowns() {
        document.querySelectorAll('.sidebar-icon').forEach(icon => {
            if (icon !== this.trigger) {
                const dropdown = icon.querySelector('.settings-dropdown, .notifications-panel');
                if (dropdown) {
                    dropdown.style.display = 'none';
                    icon.classList.remove('dropdown-open');
                }
            }
        });
    }
    
    log(message, ...args) {
        if (window.marwa && window.marwa.config.debug) {
            console.log(`[MarwaSidebarDropdown] ${message}`, ...args);
        }
    }
}

/**
 * Settings Menu Component
 */
class MarwaSettingsMenu extends MarwaSidebarDropdown {
    constructor(container) {
        super(container, {
            trigger: '.settings-icon',
            dropdown: '.settings-dropdown'
        });
        
        this.setupSettingsHandlers();
    }
    
    setupSettingsHandlers() {
        if (this.dropdown) {
            this.dropdown.addEventListener('click', (e) => {
                if (e.target.classList.contains('dropdown-item')) {
                    e.preventDefault();
                    const href = e.target.getAttribute('href');
                    if (href) {
                        window.location.href = href;
                    }
                }
            });
        }
    }
}

/**
 * Notifications Component
 */
class MarwaNotifications extends MarwaSidebarDropdown {
    constructor(container) {
        super(container, {
            trigger: '.notifications-icon',
            dropdown: '.notifications-panel'
        });
        
        this.setupNotificationHandlers();
    }
    
    setupNotificationHandlers() {
        if (this.dropdown) {
            // Close button handler
            const closeBtn = this.dropdown.querySelector('.close-notifications');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.close();
                });
            }
            
            // Mark all as read
            const markAllRead = this.dropdown.querySelector('.mark-all-read');
            if (markAllRead) {
                markAllRead.addEventListener('click', () => {
                    this.markAllAsRead();
                });
            }
        }
    }
    
    markAllAsRead() {
        const notificationItems = this.dropdown.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.classList.add('read');
        });
        
        // Update badge
        const badge = this.container.querySelector('.notification-badge');
        if (badge) {
            badge.style.display = 'none';
        }
        
        this.log('All notifications marked as read');
    }
}

/**
 * Profile Menu Component
 */
class MarwaProfileMenu extends MarwaSidebarDropdown {
    constructor(container) {
        super(container, {
            trigger: '.profile-icon',
            dropdown: '.profile-menu'
        });
        
        this.setupProfileHandlers();
    }
    
    setupProfileHandlers() {
        if (this.dropdown) {
            // Logout button handler
            const logoutBtn = this.dropdown.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', () => {
                    this.handleLogout();
                });
            }
        }
    }
    
    handleLogout() {
        if (confirm('Are you sure you want to log out?')) {
            // Create and submit logout form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/auth/logout';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrfToken.content;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

/**
 * Search Overlay Component
 */
class MarwaSearchOverlay {
    constructor() {
        this.overlay = null;
        this.searchInput = null;
        this.isOpen = false;
        this.init();
    }
    
    init() {
        this.createOverlay();
        this.setupEventListeners();
    }
    
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'marwa-search-overlay';
        this.overlay.innerHTML = `
            <div class="search-overlay-content">
                <div class="search-header">
                    <h2>Search Islamic Knowledge</h2>
                    <button class="close-search" title="Close Search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="search-input-container">
                    <input type="text" class="search-input" placeholder="Search for articles, Quran, Hadith...">
                    <button class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="search-suggestions"></div>
            </div>
        `;
        
        document.body.appendChild(this.overlay);
        this.searchInput = this.overlay.querySelector('.search-input');
    }
    
    setupEventListeners() {
        // Search trigger
        const searchTrigger = document.getElementById('search-trigger');
        if (searchTrigger) {
            searchTrigger.addEventListener('click', () => {
                this.open();
            });
        }
        
        // Close button
        const closeBtn = this.overlay.querySelector('.close-search');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.close();
            });
        }
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Search input
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });
            
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(e.target.value);
                }
            });
        }
    }
    
    open() {
        this.overlay.style.display = 'flex';
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
        
        // Focus search input
        setTimeout(() => {
            if (this.searchInput) {
                this.searchInput.focus();
            }
        }, 100);
        
        this.log('Search overlay opened');
    }
    
    close() {
        this.overlay.style.display = 'none';
        this.isOpen = false;
        document.body.style.overflow = '';
        this.log('Search overlay closed');
    }
    
    handleSearchInput(query) {
        if (query.length < 2) {
            this.clearSuggestions();
            return;
        }
        
        // Show loading state
        this.showSuggestions(['Loading...']);
        
        // Simulate search suggestions (replace with actual API call)
        setTimeout(() => {
            const suggestions = [
                `${query} in Islam`,
                `${query} Quran`,
                `${query} Hadith`,
                `${query} Islamic history`
            ];
            this.showSuggestions(suggestions);
        }, 300);
    }
    
    showSuggestions(suggestions) {
        const container = this.overlay.querySelector('.search-suggestions');
        if (container) {
            container.innerHTML = suggestions.map(suggestion => 
                `<div class="search-suggestion">${suggestion}</div>`
            ).join('');
        }
    }
    
    clearSuggestions() {
        const container = this.overlay.querySelector('.search-suggestions');
        if (container) {
            container.innerHTML = '';
        }
    }
    
    performSearch(query) {
        if (query.trim()) {
            window.location.href = `/search?q=${encodeURIComponent(query)}`;
        }
    }
    
    log(message, ...args) {
        if (window.marwa && window.marwa.config.debug) {
            console.log(`[MarwaSearchOverlay] ${message}`, ...args);
        }
    }
}

// Initialize Marwa Framework
window.marwa = new MarwaFramework();

// Export components for global use
window.MarwaSidebarDropdown = MarwaSidebarDropdown;
window.MarwaSettingsMenu = MarwaSettingsMenu;
window.MarwaNotifications = MarwaNotifications;
window.MarwaProfileMenu = MarwaProfileMenu;
window.MarwaSearchOverlay = MarwaSearchOverlay; 
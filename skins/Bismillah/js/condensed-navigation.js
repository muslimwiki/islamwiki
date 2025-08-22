/**
 * Condensed Navigation System for Bismillah Skin
 * Version: 0.0.2.2
 * Handles all interactive menu functionality
 */

class CondensedNavigation {
    constructor() {
        this.sidebar = document.querySelector('.condensed-sidebar');
        this.hamburgerMenu = document.querySelector('.hamburger-menu-icon');
        this.menuOverlay = document.querySelector('.menu-overlay');
        this.init();
    }

    init() {
        this.setupHamburgerMenu();
        this.setupIconInteractions();
        this.setupKeyboardShortcuts();
    }

    setupHamburgerMenu() {
        if (this.hamburgerMenu) {
            this.hamburgerMenu.addEventListener('click', () => {
                this.toggleMenuOverlay();
            });
        }
    }

    setupIconInteractions() {
        // Add click event listeners to all sidebar icons
        const sidebarIcons = this.sidebar.querySelectorAll('.sidebar-icon');
        sidebarIcons.forEach(icon => {
            icon.addEventListener('click', (e) => {
                this.handleIconClick(e, icon);
            });
        });
    }

    handleIconClick(e, icon) {
        const iconType = this.getIconType(icon);
        
        switch (iconType) {
            case 'hamburger-menu':
                this.toggleMenuOverlay();
                break;
            case 'search':
                this.triggerSearch();
                break;
            case 'settings':
                this.triggerSettings();
                break;
            case 'notifications':
                this.triggerNotifications();
                break;
            case 'navigation':
                this.triggerNavigation();
                break;
            case 'profile':
                this.triggerProfile();
                break;
        }
    }

    getIconType(icon) {
        if (icon.classList.contains('hamburger-menu-icon')) return 'hamburger-menu';
        if (icon.classList.contains('search-icon')) return 'search';
        if (icon.classList.contains('settings-icon')) return 'settings';
        if (icon.classList.contains('notifications-icon')) return 'notifications';
        if (icon.classList.contains('navigation-icon')) return 'navigation';
        if (icon.classList.contains('profile-icon')) return 'profile';
        return 'unknown';
    }

    toggleMenuOverlay() {
        if (this.menuOverlay.classList.contains('active')) {
            this.hideMenuOverlay();
        } else {
            this.showMenuOverlay();
        }
    }

    showMenuOverlay() {
        this.menuOverlay.classList.add('active');
        this.menuOverlay.style.display = 'block';
        
        // Animate menu items
        const menuItems = this.menuOverlay.querySelectorAll('.menu-item');
        menuItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
            item.classList.add('animate-in');
        });

        // Add active state to hamburger icon
        this.hamburgerMenu.classList.add('active');
    }

    hideMenuOverlay() {
        this.menuOverlay.classList.remove('active');
        setTimeout(() => {
            this.menuOverlay.style.display = 'none';
        }, 300);

        // Remove active state from hamburger icon
        this.hamburgerMenu.classList.remove('active');
    }

    triggerSearch() {
        // Trigger search overlay through global search system
        if (window.searchOverlay) {
            window.searchOverlay.showSearchOverlay();
        }
    }

    triggerSettings() {
        // Trigger settings menu through global settings system
        if (window.settingsMenu) {
            window.settingsMenu.toggleSettingsMenu();
        }
    }

    triggerNotifications() {
        // Trigger notifications panel through global notifications system
        if (window.notifications) {
            window.notifications.toggleNotificationsPanel();
        }
    }

    triggerNavigation() {
        // Trigger navigation toggle through global navigation system
        if (window.navigationToggle) {
            window.navigationToggle.toggleNavigationPanel();
        }
    }

    triggerProfile() {
        // Trigger profile menu through global profile system
        if (window.profile) {
            window.profile.toggleProfileMenu();
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes all overlays
            if (e.key === 'Escape') {
                this.closeAllOverlays();
            }
            
            // Ctrl/Cmd + K opens search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.triggerSearch();
            }
            
            // Ctrl/Cmd + M opens menu
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                this.toggleMenuOverlay();
            }
        });
    }

    closeAllOverlays() {
        this.hideMenuOverlay();
        
        // Close other overlays through their respective systems
        if (window.searchOverlay) window.searchOverlay.hideSearchOverlay();
        if (window.settingsMenu) window.settingsMenu.hideSettingsMenu();
        if (window.notifications) window.notifications.hideNotificationsPanel();
        if (window.profile) window.profile.hideProfileMenu();
        if (window.navigationToggle) window.navigationToggle.hideNavigationPanel();
    }

    // Public methods for external control
    showMenu() {
        this.showMenuOverlay();
    }

    hideMenu() {
        this.hideMenuOverlay();
    }

    isMenuVisible() {
        return this.menuOverlay.classList.contains('active');
    }
}

// Export for global use
window.CondensedNavigation = CondensedNavigation; 
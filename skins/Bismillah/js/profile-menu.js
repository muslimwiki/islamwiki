/**
 * Profile Menu System for Bismillah Skin
 * Version: 0.0.2.2
 * Handles user profile information and actions
 */

class ProfileMenu {
    constructor() {
        this.profileIcon = document.querySelector('.profile-icon');
        this.profileMenu = document.querySelector('.profile-menu');
        this.userInfo = this.profileMenu?.querySelector('.user-info');
        this.profileActions = this.profileMenu?.querySelector('.profile-actions');
        this.logoutBtn = this.profileMenu?.querySelector('.logout-btn');
        this.init();
    }

    init() {
        this.setupProfileIcon();
        this.setupLogoutButton();
        this.setupClickOutside();
        this.loadUserProfile();
        this.setupKeyboardShortcuts();
    }

    setupProfileIcon() {
        if (this.profileIcon) {
            this.profileIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleProfileMenu();
            });
        }
    }

    setupLogoutButton() {
        if (this.logoutBtn) {
            this.logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (this.isVisible() && !this.profileMenu.contains(e.target) && !this.profileIcon.contains(e.target)) {
                this.hideProfileMenu();
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes profile menu
            if (e.key === 'Escape' && this.isVisible()) {
                this.hideProfileMenu();
            }
            
            // Alt + P opens profile menu
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                this.toggleProfileMenu();
            }
        });
    }

    toggleProfileMenu() {
        if (this.isVisible()) {
            this.hideProfileMenu();
        } else {
            this.showProfileMenu();
        }
    }

    showProfileMenu() {
        this.profileMenu.classList.add('active');
        this.profileMenu.style.display = 'block';
        
        // Add active state to profile icon
        this.profileIcon.classList.add('active');
        
        // Position the menu relative to the icon
        this.positionMenu();
        
        // Load fresh user profile
        this.loadUserProfile();
    }

    hideProfileMenu() {
        this.profileMenu.classList.remove('active');
        setTimeout(() => {
            this.profileMenu.style.display = 'none';
        }, 200);
        
        // Remove active state from profile icon
        this.profileIcon.classList.remove('active');
    }

    positionMenu() {
        if (!this.profileIcon || !this.profileMenu) return;
        
        const iconRect = this.profileIcon.getBoundingClientRect();
        const menuRect = this.profileMenu.getBoundingClientRect();
        
        // Position menu above and to the right of the icon
        const top = iconRect.top - menuRect.height - 10;
        const left = iconRect.left - (menuRect.width - iconRect.width);
        
        this.profileMenu.style.top = `${top}px`;
        this.profileMenu.style.left = `${left}px`;
    }

    async loadUserProfile() {
        try {
            // Try to load from API first
            const response = await fetch('/api/user/profile');
            if (response.ok) {
                const profile = await response.json();
                this.renderProfileMenu(profile);
                return;
            }
        } catch (error) {
            console.log('API not available, using mock profile');
        }

        // Fallback to mock profile
        const mockProfile = this.generateMockProfile();
        this.renderProfileMenu(mockProfile);
    }

    generateMockProfile() {
        return {
            name: 'Khalid ibn Mika\'il Abdullah',
            role: 'Abdullah',
            groups: [
                '<group-autoreview-member>',
                'Bureaucrat Interface administrator',
                '<group-reviewer-member>',
                'Administrator'
            ],
            editCount: 6653,
            joinDate: '2 November 2024',
            language: 'English',
            preferences: {
                theme: 'light',
                textSize: 'standard',
                width: 'standard'
            }
        };
    }

    renderProfileMenu(profile) {
        if (!this.userInfo) return;
        
        // Render user information
        this.userInfo.innerHTML = `
            <div class="user-name">${profile.name}</div>
            <div class="user-role">${profile.role}</div>
            <div class="user-groups">
                ${profile.groups.map(group => `<span class="user-group">${group}</span>`).join('')}
            </div>
            <div class="user-stats">
                <span class="stat">Edits: ${profile.editCount}</span>
                <span class="stat">Joined: ${profile.joinDate}</span>
            </div>
        `;

        // Render profile actions
        if (this.profileActions) {
            this.profileActions.innerHTML = `
                <a href="/user/language" class="profile-action">
                    <i class="icon-language"></i>
                    <span>${profile.language}</span>
                </a>
                <a href="/user/talk" class="profile-action">
                    <i class="icon-chat"></i>
                    <span>Talk</span>
                    <span class="shortcut">Alt ↑ N</span>
                </a>
                <a href="/user/preferences" class="profile-action">
                    <i class="icon-settings"></i>
                    <span>Preferences</span>
                </a>
                <a href="/user/beta" class="profile-action">
                    <i class="icon-flask"></i>
                    <span>Beta</span>
                </a>
                <a href="/user/watchlist" class="profile-action">
                    <i class="icon-star"></i>
                    <span>Watchlist</span>
                    <span class="shortcut">Alt ↑ L</span>
                </a>
                <a href="/user/contributions" class="profile-action">
                    <i class="icon-document"></i>
                    <span>Contributions</span>
                    <span class="shortcut">Alt ↑ Y</span>
                </a>
            `;
        }
    }

    async handleLogout() {
        try {
            // Show confirmation dialog
            if (!confirm('Are you sure you want to log out?')) {
                return;
            }

            // Try to logout via API
            const response = await fetch('/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                // Redirect to home page
                window.location.href = '/';
            } else {
                throw new Error('Logout failed');
            }
        } catch (error) {
            console.error('Logout error:', error);
            
            // Fallback: redirect to logout page
            window.location.href = '/auth/logout';
        }
    }

    isVisible() {
        return this.profileMenu?.classList.contains('active') || false;
    }

    // Public methods for external control
    show() {
        this.showProfileMenu();
    }

    hide() {
        this.hideProfileMenu();
    }

    toggle() {
        this.toggleProfileMenu();
    }

    // Method to update user profile
    updateProfile(profileData) {
        if (profileData) {
            this.renderProfileMenu(profileData);
        }
    }

    // Method to refresh user profile
    refreshProfile() {
        this.loadUserProfile();
    }

    // Method to get current user info
    getCurrentUser() {
        try {
            // Try to get from localStorage or session
            const userData = localStorage.getItem('user-profile') || sessionStorage.getItem('user-profile');
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            return null;
        }
    }

    // Method to check if user is logged in
    isLoggedIn() {
        const user = this.getCurrentUser();
        return user && user.id;
    }

    // Method to check user permissions
    hasPermission(permission) {
        const user = this.getCurrentUser();
        if (!user || !user.groups) return false;
        
        const permissionMap = {
            'edit': ['user', 'editor', 'moderator', 'administrator'],
            'delete': ['moderator', 'administrator'],
            'admin': ['administrator'],
            'bureaucrat': ['bureaucrat', 'administrator']
        };
        
        const requiredGroups = permissionMap[permission] || [];
        return user.groups.some(group => requiredGroups.includes(group.toLowerCase()));
    }

    // Method to show user status
    showUserStatus() {
        const user = this.getCurrentUser();
        if (!user) return;
        
        const statusDiv = document.createElement('div');
        statusDiv.className = 'user-status';
        statusDiv.innerHTML = `
            <div class="status-indicator online"></div>
            <span class="status-text">Online</span>
        `;
        
        this.profileMenu.appendChild(statusDiv);
    }

    // Method to handle profile action clicks
    handleProfileAction(action) {
        switch (action) {
            case 'language':
                this.openLanguageSettings();
                break;
            case 'talk':
                this.openTalkPage();
                break;
            case 'preferences':
                this.openPreferences();
                break;
            case 'beta':
                this.toggleBetaFeatures();
                break;
            case 'watchlist':
                this.openWatchlist();
                break;
            case 'contributions':
                this.openContributions();
                break;
            default:
                console.log('Unknown profile action:', action);
        }
    }

    openLanguageSettings() {
        window.location.href = '/user/language';
    }

    openTalkPage() {
        const user = this.getCurrentUser();
        if (user) {
            window.location.href = `/user/${user.username}/talk`;
        }
    }

    openPreferences() {
        window.location.href = '/user/preferences';
    }

    toggleBetaFeatures() {
        // Toggle beta features
        const isBeta = localStorage.getItem('beta-features') === 'true';
        localStorage.setItem('beta-features', !isBeta);
        
        // Show feedback
        this.showFeedback(`Beta features ${!isBeta ? 'enabled' : 'disabled'}`);
    }

    openWatchlist() {
        window.location.href = '/user/watchlist';
    }

    openContributions() {
        const user = this.getCurrentUser();
        if (user) {
            window.location.href = `/user/${user.username}/contributions`;
        }
    }

    showFeedback(message) {
        const feedbackDiv = document.createElement('div');
        feedbackDiv.className = 'feedback-message';
        feedbackDiv.textContent = message;
        
        document.body.appendChild(feedbackDiv);
        
        setTimeout(() => {
            feedbackDiv.remove();
        }, 3000);
    }

    // Method to handle keyboard shortcuts for profile actions
    handleKeyboardShortcut(key) {
        switch (key) {
            case 'n':
                this.openTalkPage();
                break;
            case 'l':
                this.openWatchlist();
                break;
            case 'y':
                this.openContributions();
                break;
            case 'p':
                this.openPreferences();
                break;
            default:
                return false;
        }
        return true;
    }
}

// Export for global use
window.ProfileMenu = ProfileMenu; 
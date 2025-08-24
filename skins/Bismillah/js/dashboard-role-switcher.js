/**
 * Dashboard Role Switcher
 * Dynamically applies role-based CSS classes to the dashboard
 */

class DashboardRoleSwitcher {
    constructor() {
        this.currentRole = null;
        this.dashboardContainer = null;
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.dashboardContainer = document.querySelector('.dashboard-container, .main-content');
        if (!this.dashboardContainer) {
            console.warn('Dashboard container not found');
            return;
        }

        // Determine user role from page data or session
        this.determineUserRole();
        
        // Apply role-based styling
        this.applyRoleStyling();
        
        // Set up role switching if needed
        this.setupRoleSwitching();
    }

    determineUserRole() {
        // Try to get role from data attributes first
        const roleFromData = this.dashboardContainer.dataset.userRole;
        if (roleFromData) {
            this.currentRole = roleFromData;
            return;
        }

        // Try to get role from meta tags
        const roleMeta = document.querySelector('meta[name="user-role"]');
        if (roleMeta) {
            this.currentRole = roleMeta.content;
            return;
        }

        // Try to get role from page content analysis
        this.currentRole = this.analyzePageContent();
        
        // Default to user role if none found
        if (!this.currentRole) {
            this.currentRole = 'user';
        }
    }

    analyzePageContent() {
        // Analyze page content to determine role
        const pageContent = document.body.textContent.toLowerCase();
        
        if (pageContent.includes('admin') || pageContent.includes('administrator')) {
            return 'admin';
        }
        
        if (pageContent.includes('scholar') || pageContent.includes('research') || pageContent.includes('publication')) {
            return 'scholar';
        }
        
        if (pageContent.includes('contributor') || pageContent.includes('contribution') || pageContent.includes('content creation')) {
            return 'contributor';
        }
        
        return 'user';
    }

    applyRoleStyling() {
        if (!this.currentRole) return;

        // Remove any existing role classes
        this.dashboardContainer.classList.remove(
            'dashboard-admin',
            'dashboard-user', 
            'dashboard-scholar',
            'dashboard-contributor'
        );

        // Add current role class
        this.dashboardContainer.classList.add(`dashboard-${this.currentRole}`);
        
        // Add role-specific body class for global styling
        document.body.classList.remove(
            'role-admin',
            'role-user',
            'role-scholar', 
            'role-contributor'
        );
        document.body.classList.add(`role-${this.currentRole}`);

        console.log(`Dashboard role applied: ${this.currentRole}`);
    }

    setupRoleSwitching() {
        // Listen for role change events
        document.addEventListener('roleChanged', (event) => {
            if (event.detail && event.detail.role) {
                this.currentRole = event.detail.role;
                this.applyRoleStyling();
            }
        });

        // Listen for user login/logout events
        document.addEventListener('userLoggedIn', (event) => {
            if (event.detail && event.detail.role) {
                this.currentRole = event.detail.role;
                this.applyRoleStyling();
            }
        });

        document.addEventListener('userLoggedOut', () => {
            this.currentRole = 'user';
            this.applyRoleStyling();
        });
    }

    // Public method to change role programmatically
    changeRole(newRole) {
        if (!['admin', 'user', 'scholar', 'contributor'].includes(newRole)) {
            console.warn(`Invalid role: ${newRole}`);
            return;
        }

        this.currentRole = newRole;
        this.applyRoleStyling();

        // Dispatch custom event
        const event = new CustomEvent('roleChanged', {
            detail: { role: newRole }
        });
        document.dispatchEvent(event);
    }

    // Get current role
    getCurrentRole() {
        return this.currentRole;
    }

    // Check if user has specific role
    hasRole(role) {
        return this.currentRole === role;
    }

    // Check if user has admin privileges
    isAdmin() {
        return this.currentRole === 'admin';
    }

    // Check if user is a scholar
    isScholar() {
        return this.currentRole === 'scholar';
    }

    // Check if user is a contributor
    isContributor() {
        return this.currentRole === 'contributor';
    }
}

// Initialize the role switcher
const dashboardRoleSwitcher = new DashboardRoleSwitcher();

// Make it available globally
window.DashboardRoleSwitcher = DashboardRoleSwitcher;
window.dashboardRoleSwitcher = dashboardRoleSwitcher;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardRoleSwitcher;
} 
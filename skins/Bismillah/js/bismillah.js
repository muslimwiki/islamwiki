// Bismillah Skin JavaScript
// Default JavaScript for Bismillah skin

console.log('Bismillah skin loaded');

// Bismillah Skin Object
window.BismillahSkin = {
    // Initialize the skin
    init: function() {
        console.log('Initializing Bismillah skin...');
        this.setupEventListeners();
        this.setupAnimations();
    },

    // Setup event listeners
    setupEventListeners: function() {
        // Search functionality
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', this.handleSearch.bind(this));
        }

        // Navigation highlighting
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', this.handleNavClick.bind(this));
        });

        // Smooth scrolling for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', this.handleSmoothScroll.bind(this));
        });

        // User dropdown functionality
        this.setupUserDropdown();
    },

    // Setup animations
    setupAnimations: function() {
        // Add fade-in animation to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Add hover effects to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', this.handleButtonHover.bind(this));
            button.addEventListener('mouseleave', this.handleButtonLeave.bind(this));
        });
    },

    // Handle search form submission
    handleSearch: function(event) {
        const searchInput = event.target.querySelector('.search-input');
        if (searchInput && searchInput.value.trim() === '') {
            event.preventDefault();
            this.showNotification('Please enter a search term', 'warning');
        }
    },

    // Handle navigation clicks
    handleNavClick: function(event) {
        // Remove active class from all nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to clicked link
        event.target.classList.add('active');
    },

    // Handle smooth scrolling
    handleSmoothScroll: function(event) {
        event.preventDefault();
        const targetId = event.target.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    },

    // Handle button hover effects
    handleButtonHover: function(event) {
        event.target.style.transform = 'translateY(-2px)';
        event.target.style.boxShadow = '0 10px 20px rgba(79, 70, 229, 0.3)';
    },

    // Handle button leave effects
    handleButtonLeave: function(event) {
        event.target.style.transform = 'translateY(0)';
        event.target.style.boxShadow = 'none';
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;

        // Set background color based on type
        if (type === 'warning') {
            notification.style.background = '#F59E0B';
        } else if (type === 'error') {
            notification.style.background = '#EF4444';
        } else {
            notification.style.background = '#4F46E5';
        }

        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    },

    // Theme switching functionality
    switchTheme: function(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('bismillah-theme', theme);
        this.showNotification(`Theme switched to ${theme}`, 'info');
    },

    // Load saved theme
    loadSavedTheme: function() {
        const savedTheme = localStorage.getItem('bismillah-theme');
        if (savedTheme) {
            this.switchTheme(savedTheme);
        }
    },

    // Setup user dropdown functionality
    setupUserDropdown: function() {
        const userDropdowns = document.querySelectorAll('.user-dropdown');
        
        userDropdowns.forEach(dropdown => {
            const userMenu = dropdown.querySelector('.user-menu');
            const dropdownMenu = dropdown.querySelector('.user-dropdown-menu');
            
            if (userMenu && dropdownMenu) {
                // Toggle dropdown on click
                userMenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
                
                // Close dropdown on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.BismillahSkin.init();
    window.BismillahSkin.loadSavedTheme();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style); 
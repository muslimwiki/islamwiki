// Muslim Skin JavaScript
// Modern Islamic design with comprehensive functionality

document.addEventListener('DOMContentLoaded', function() {
    console.log('Muslim skin loaded');
    
    // Initialize all functionality
    initializeSearch();
    initializeNavigation();
    initializeResponsiveMenu();
    initializeAnimations();
    initializePrayerTimes();
    initializeDarkMode();
    initializeNotifications();
});

/**
 * Initialize search functionality with enhanced features
 */
function initializeSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (!query) {
                e.preventDefault();
                searchInput.focus();
                showNotification('Please enter a search term', 'warning');
            }
        });
        
        // Add search suggestions
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchSearchSuggestions(query);
            }
        }, 300));
        
        // Add keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                this.form.submit();
            }
        });
    }
}

/**
 * Initialize navigation with smooth scrolling
 */
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Smooth scroll to anchor links
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
}

/**
 * Initialize responsive menu functionality
 */
function initializeResponsiveMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
            
            // Animate menu items
            const menuItems = navMenu.querySelectorAll('.nav-link');
            menuItems.forEach((item, index) => {
                if (navMenu.classList.contains('active')) {
                    item.style.animation = `slideInLeft 0.3s ease ${index * 0.1}s both`;
                } else {
                    item.style.animation = '';
                }
            });
        });
    }
}

/**
 * Initialize animations and effects
 */
function initializeAnimations() {
    // Add scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease both';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.container > *');
    animatedElements.forEach(el => observer.observe(el));
}

/**
 * Initialize prayer times functionality
 */
function initializePrayerTimes() {
    const prayerTimeElements = document.querySelectorAll('.prayer-time');
    
    prayerTimeElements.forEach(element => {
        // Add countdown functionality
        const time = element.getAttribute('data-time');
        if (time) {
            updatePrayerCountdown(element, time);
        }
    });
}

/**
 * Update prayer countdown
 */
function updatePrayerCountdown(element, prayerTime) {
    const now = new Date();
    const prayer = new Date(prayerTime);
    
    if (prayer > now) {
        const diff = prayer - now;
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        element.textContent = `${hours}h ${minutes}m`;
    } else {
        element.textContent = 'Now';
        element.classList.add('prayer-time-now');
    }
}

/**
 * Initialize dark mode functionality
 */
function initializeDarkMode() {
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            
            showNotification(
                isDark ? 'Dark mode enabled' : 'Light mode enabled',
                'success'
            );
        });
        
        // Check for saved preference
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode === 'true') {
            document.body.classList.add('dark-mode');
        }
    }
}

/**
 * Initialize notification system
 */
function initializeNotifications() {
    // Create notification container if it doesn't exist
    if (!document.querySelector('.notification-container')) {
        const container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
}

/**
 * Show notification message with enhanced styling
 */
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.querySelector('.notification-container');
    const notification = document.createElement('div');
    
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Enhanced styles
    notification.style.cssText = `
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        pointer-events: auto;
        cursor: pointer;
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    container.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove on click
    notification.addEventListener('click', () => {
        removeNotification(notification);
    });
    
    // Auto remove
    setTimeout(() => {
        removeNotification(notification);
    }, duration);
}

/**
 * Remove notification with animation
 */
function removeNotification(notification) {
    notification.style.transform = 'translateX(100%)';
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

/**
 * Get notification color based on type
 */
function getNotificationColor(type) {
    switch (type) {
        case 'success': return '#10b981';
        case 'error': return '#ef4444';
        case 'warning': return '#f59e0b';
        default: return '#3b82f6';
    }
}

/**
 * Fetch search suggestions
 */
function fetchSearchSuggestions(query) {
    // TODO: Implement actual search suggestions API
    console.log('Fetching suggestions for:', query);
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Add CSS animations
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .prayer-time-now {
        color: #ef4444;
        font-weight: bold;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .dark-mode {
        --background-color: #1f2937;
        --card-background: #374151;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --border-color: #4b5563;
    }
`;
document.head.appendChild(style);

// Export functions for global use
window.MuslimSkin = {
    showNotification,
    initializeSearch,
    initializeNavigation,
    initializeResponsiveMenu,
    initializeAnimations,
    initializePrayerTimes,
    initializeDarkMode
}; 
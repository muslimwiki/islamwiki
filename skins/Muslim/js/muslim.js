/**
 * Muslim Skin JavaScript
 * Citizen-inspired functionality for IslamWiki
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeMuslimSkin();
    });

    /**
     * Initialize Muslim Skin functionality
     */
    function initializeMuslimSkin() {
        initializeMobileMenu();
        initializeSearchEnhancements();
        initializeDropdowns();
        initializeAnimations();
        initializeAccessibility();
        initializeThemeSupport();
    }

    /**
     * Mobile menu functionality
     */
    function initializeMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const header = document.querySelector('.citizen-header');
        
        if (mobileToggle && header) {
            mobileToggle.addEventListener('click', function() {
                header.classList.toggle('mobile-menu-open');
                mobileToggle.classList.toggle('active');
                
                // Animate hamburger to X
                const spans = mobileToggle.querySelectorAll('span');
                spans.forEach((span, index) => {
                    if (index === 0) {
                        span.style.transform = header.classList.contains('mobile-menu-open') 
                            ? 'rotate(45deg) translate(5px, 5px)' : '';
                    } else if (index === 1) {
                        span.style.opacity = header.classList.contains('mobile-menu-open') ? '0' : '1';
                    } else if (index === 2) {
                        span.style.transform = header.classList.contains('mobile-menu-open') 
                            ? 'rotate(-45deg) translate(7px, -6px)' : '';
                    }
                });
            });
        }
    }

    /**
     * Search functionality enhancements
     */
    function initializeSearchEnhancements() {
        const searchInput = document.querySelector('.search-input');
        const searchForm = document.querySelector('.search-form');
        
        if (searchInput && searchForm) {
            // Auto-focus search on keyboard shortcut
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });

            // Search suggestions (placeholder for future implementation)
            searchInput.addEventListener('input', function() {
                // TODO: Implement search suggestions
                console.log('Search input:', this.value);
            });

            // Search form submission enhancement
            searchForm.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                if (!query) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }
    }

    /**
     * Dropdown functionality
     */
    function initializeDropdowns() {
        const dropdowns = document.querySelectorAll('.user-dropdown');
        
        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('.user-button');
            const menu = dropdown.querySelector('.user-dropdown-menu');
            
            if (button && menu) {
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                    }
                });

                // Toggle dropdown on button click
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isVisible = menu.style.opacity === '1';
                    
                    if (isVisible) {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                    } else {
                        menu.style.opacity = '1';
                        menu.style.visibility = 'visible';
                        menu.style.transform = 'translateY(0)';
                    }
                });
            }
        });
    }

    /**
     * Animation enhancements
     */
    function initializeAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with animation classes
        const animatedElements = document.querySelectorAll('.card, .alert, .btn');
        animatedElements.forEach(el => {
            observer.observe(el);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Accessibility enhancements
     */
    function initializeAccessibility() {
        // Skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.textContent = 'Skip to main content';
        skipLink.className = 'skip-link';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
        `;
        
        skipLink.addEventListener('focus', function() {
            this.style.top = '6px';
        });
        
        skipLink.addEventListener('blur', function() {
            this.style.top = '-40px';
        });
        
        document.body.insertBefore(skipLink, document.body.firstChild);

        // Add main content ID
        const mainContent = document.querySelector('.citizen-main');
        if (mainContent) {
            mainContent.id = 'main-content';
        }

        // Enhanced focus management
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
    }

    /**
     * Theme support
     */
    function initializeThemeSupport() {
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('muslim-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else if (prefersDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        // Theme toggle functionality (if theme toggle button exists)
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('muslim-theme', newTheme);
            });
        }
    }

    /**
     * Utility functions
     */
    const MuslimSkin = {
        // Show notification
        showNotification: function(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 300px;
                animation: slideInRight 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, duration);
        },

        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Throttle function
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };

    // Expose to global scope for debugging
    window.MuslimSkin = MuslimSkin;

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .keyboard-navigation *:focus {
            outline: 2px solid var(--primary-color) !important;
            outline-offset: 2px !important;
        }
        
        .skip-link:focus {
            top: 6px !important;
        }
    `;
    document.head.appendChild(style);

})(); 
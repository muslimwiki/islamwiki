// Bismillah Skin JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Prism.js for syntax highlighting
    if (typeof Prism !== 'undefined') {
        Prism.highlightAll();
    }
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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
    
    // Add loading states to buttons
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                this.disabled = true;
                
                // Remove loading state after a delay (for demo purposes)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.disabled = false;
                }, 2000);
            }
        });
    });
    
    // Add hover effects to cards
    document.querySelectorAll('.card, .stat-card, .action-card, .resource-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Initialize tooltips if needed
    if (typeof tippy !== 'undefined') {
        tippy('[data-tippy-content]', {
            placement: 'top',
            animation: 'scale',
            duration: [200, 150]
        });
    }
    
    // Add intersection observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.stat-card, .action-card, .resource-card, .dashboard-card').forEach(el => {
        observer.observe(el);
    });
    
    // Add keyboard navigation for dropdowns
    document.querySelectorAll('.user-dropdown').forEach(dropdown => {
        const button = dropdown.querySelector('.user-avatar-btn');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        
        if (button && menu) {
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const isOpen = menu.style.display === 'block';
                    menu.style.display = isOpen ? 'none' : 'block';
                }
            });
        }
    });
    
    // Add search functionality if search input exists
    const searchInput = document.querySelector('input[type="search"], .search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Implement search logic here
                console.log('Searching for:', this.value);
            }, 300);
        });
    }
    
    // Add theme toggle functionality
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-theme');
            const isDark = document.body.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    }
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
    
    // Add copy to clipboard functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy');
            if (textToCopy) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Show success message
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    this.classList.add('copied');
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('copied');
                    }, 2000);
                });
            }
        });
    });
    
    // Add lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Add form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-error';
                errorMsg.textContent = 'Please fill in all required fields.';
                form.insertBefore(errorMsg, form.firstChild);
            }
        });
    });
    
    // Add mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('mobile-open');
            this.classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('mobile-open');
                mobileMenuToggle.classList.remove('active');
            }
        });
    }
});

// Add CSS for loading states and animations
const style = document.createElement('style');
style.textContent = `
    .btn.loading {
        position: relative;
        color: transparent;
    }
    
    .btn.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
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
    
    .copied {
        background-color: var(--success-color) !important;
        color: white !important;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
    
    .error {
        border-color: var(--error-color) !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
    }
    
    .mobile-menu-toggle {
        display: none;
        flex-direction: column;
        cursor: pointer;
        padding: 0.5rem;
    }
    
    .mobile-menu-toggle span {
        width: 25px;
        height: 3px;
        background-color: var(--text-primary);
        margin: 3px 0;
        transition: 0.3s;
    }
    
    .mobile-menu-toggle.active span:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }
    
    .mobile-menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-toggle.active span:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }
    
    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: flex;
        }
        
        .nav-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            flex-direction: column;
            padding: 1rem;
        }
        
        .nav-menu.mobile-open {
            display: flex;
        }
    }
    
    /* Dark theme styles */
    .dark-theme {
        --background-color: #1f2937;
        --card-background: #374151;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --border-color: #4b5563;
    }
    
    .dark-theme .header {
        background-color: rgba(31, 41, 55, 0.95);
        border-bottom-color: #4b5563;
    }
    
    .dark-theme .user-dropdown-menu {
        background: #374151;
        border-color: #4b5563;
    }
    
    .dark-theme .dropdown-item:hover {
        background-color: rgba(79, 70, 229, 0.2);
    }
`;
document.head.appendChild(style); 
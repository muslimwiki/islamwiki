/**
 * GreenSkin JavaScript
 * Green-themed functionality for IslamWiki
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🌿 GreenSkin loaded successfully!');
    
    // Initialize green theme
    initGreenTheme();
    
    // Add green-themed animations
    addGreenAnimations();
    
    // Initialize interactive elements
    initInteractiveElements();
});

/**
 * Initialize green theme functionality
 */
function initGreenTheme() {
    // Add green theme class to body
    document.body.classList.add('green-theme');
    
    // Add green accent to page title
    const pageTitle = document.querySelector('h1');
    if (pageTitle) {
        pageTitle.classList.add('green-accent');
    }
    
    // Add green borders to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.add('green-border');
    });
    
    // Add green background to sections
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.classList.add('green-bg');
    });
}

/**
 * Add green-themed animations
 */
function addGreenAnimations() {
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 15px rgba(46, 125, 50, 0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 10px rgba(46, 125, 50, 0.1)';
        });
    });
    
    // Add card hover animations
    const cards = document.querySelectorAll('.card, .action-card, .resource-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(46, 125, 50, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(46, 125, 50, 0.1)';
        });
    });
    
    // Add stat card animations
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(46, 125, 50, 0.3)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 15px rgba(46, 125, 50, 0.1)';
        });
    });
}

/**
 * Initialize interactive elements
 */
function initInteractiveElements() {
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
                submitBtn.disabled = true;
            }
        });
    });
    
    // Add green theme to navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.color = '#81C784 !important';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.color = 'white !important';
        });
    });
    
    // Add green theme to links
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.color = '#1B5E20';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.color = '#2E7D32';
        });
    });
}

/**
 * Show green-themed notification
 */
function showGreenNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} green-notification`;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="green-icon">🌿</span>
            <span class="ms-2">${message}</span>
        </div>
    `;
    
    // Add green styling
    notification.style.backgroundColor = '#E8F5E8';
    notification.style.borderColor = '#4CAF50';
    notification.style.color = '#1B5E20';
    notification.style.borderRadius = '10px';
    notification.style.padding = '1rem';
    notification.style.margin = '1rem 0';
    notification.style.boxShadow = '0 4px 15px rgba(46, 125, 50, 0.1)';
    
    // Insert at top of page
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(notification, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

/**
 * Add green theme to form inputs
 */
function initGreenForms() {
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#2E7D32';
            this.style.boxShadow = '0 0 0 0.2rem rgba(46, 125, 50, 0.25)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#4CAF50';
            this.style.boxShadow = 'none';
        });
    });
}

/**
 * Add green theme to tables
 */
function initGreenTables() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        table.classList.add('table-green');
        
        // Add green styling to table headers
        const headers = table.querySelectorAll('th');
        headers.forEach(header => {
            header.style.backgroundColor = '#2E7D32';
            header.style.color = 'white';
            header.style.borderColor = '#4CAF50';
        });
        
        // Add green styling to table rows
        const rows = table.querySelectorAll('tr');
        rows.forEach((row, index) => {
            if (index % 2 === 0) {
                row.style.backgroundColor = '#F1F8E9';
            } else {
                row.style.backgroundColor = '#E8F5E8';
            }
        });
    });
}

/**
 * Initialize green theme for modals
 */
function initGreenModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const header = modal.querySelector('.modal-header');
        if (header) {
            header.style.backgroundColor = '#2E7D32';
            header.style.color = 'white';
            header.style.borderBottom = '2px solid #4CAF50';
        }
        
        const footer = modal.querySelector('.modal-footer');
        if (footer) {
            footer.style.borderTop = '2px solid #4CAF50';
        }
    });
}

/**
 * Add green theme to pagination
 */
function initGreenPagination() {
    const pagination = document.querySelectorAll('.pagination');
    pagination.forEach(pag => {
        const links = pag.querySelectorAll('.page-link');
        links.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#4CAF50';
                this.style.borderColor = '#4CAF50';
                this.style.color = 'white';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
                this.style.borderColor = '#4CAF50';
                this.style.color = '#2E7D32';
            });
        });
    });
}

// Initialize all green theme components
document.addEventListener('DOMContentLoaded', function() {
    initGreenForms();
    initGreenTables();
    initGreenModals();
    initGreenPagination();
});

// Export functions for global use
window.GreenSkin = {
    showNotification: showGreenNotification,
    initTheme: initGreenTheme,
    addAnimations: addGreenAnimations
}; 
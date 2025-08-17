/**
 * Dashboard Extension - Main JavaScript
 * Provides interactivity and dynamic functionality for the dashboard
 */

class DashboardExtension {
    constructor() {
        this.config = {};
        this.widgets = {};
        this.currentLayout = 'islamic';
        this.refreshInterval = 300000; // 5 minutes
        this.init();
    }

    /**
     * Initialize the dashboard
     */
    init() {
        this.loadConfig();
        this.setupEventListeners();
        this.initializeWidgets();
        this.startAutoRefresh();
        this.setupNotifications();
    }

    /**
     * Load dashboard configuration
     */
    loadConfig() {
        // Load configuration from data attributes or API
        const configElement = document.querySelector('[data-dashboard-config]');
        if (configElement) {
            try {
                this.config = JSON.parse(configElement.dataset.dashboardConfig);
            } catch (e) {
                console.warn('Failed to parse dashboard config:', e);
            }
        }

        // Set default configuration
        this.config = {
            ...this.config,
            refreshInterval: this.config.refreshInterval || 300000,
            enableAnimations: this.config.enableAnimations !== false,
            enableNotifications: this.config.enableNotifications !== false
        };
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Layout switcher
        const layoutSwitcher = document.querySelector('.layout-switcher');
        if (layoutSwitcher) {
            layoutSwitcher.addEventListener('change', (e) => {
                this.changeLayout(e.target.value);
            });
        }

        // Widget refresh buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.widget-refresh')) {
                const widgetId = e.target.closest('.dashboard-widget').dataset.widgetId;
                this.refreshWidget(widgetId);
            }
        });

        // Widget settings buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.widget-settings')) {
                const widgetId = e.target.closest('.dashboard-widget').dataset.widgetId;
                this.showWidgetSettings(widgetId);
            }
        });

        // Quick action buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.action-btn')) {
                this.handleQuickAction(e.target.closest('.action-btn'));
            }
        });

        // Notification interactions
        document.addEventListener('click', (e) => {
            if (e.target.matches('.notification-mark-read')) {
                const notificationId = e.target.dataset.notificationId;
                this.markNotificationRead(notificationId);
            }
        });

        // Responsive behavior
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Theme switcher
        const themeSwitcher = document.querySelector('.theme-switcher');
        if (themeSwitcher) {
            themeSwitcher.addEventListener('change', (e) => {
                this.changeTheme(e.target.value);
            });
        }
    }

    /**
     * Initialize dashboard widgets
     */
    initializeWidgets() {
        const widgets = document.querySelectorAll('.dashboard-widget');
        
        widgets.forEach(widget => {
            const widgetId = widget.dataset.widgetId;
            const widgetType = widget.dataset.widgetType;
            
            if (widgetId && widgetType) {
                this.widgets[widgetId] = {
                    element: widget,
                    type: widgetType,
                    lastRefresh: Date.now(),
                    refreshable: widget.dataset.refreshable === 'true'
                };

                // Initialize widget-specific functionality
                this.initializeWidget(widgetId, widgetType);
            }
        });
    }

    /**
     * Initialize specific widget functionality
     */
    initializeWidget(widgetId, widgetType) {
        switch (widgetType) {
            case 'user-overview':
                this.initializeUserOverview(widgetId);
                break;
            case 'content-stats':
                this.initializeContentStats(widgetId);
                break;
            case 'recent-activity':
                this.initializeRecentActivity(widgetId);
                break;
            case 'islamic-calendar':
                this.initializeIslamicCalendar(widgetId);
                break;
            case 'prayer-times':
                this.initializePrayerTimes(widgetId);
                break;
            case 'quran-verse':
                this.initializeQuranVerse(widgetId);
                break;
            case 'hadith-quote':
                this.initializeHadithQuote(widgetId);
                break;
            case 'quick-actions':
                this.initializeQuickActions(widgetId);
                break;
            case 'notifications':
                this.initializeNotifications(widgetId);
                break;
            case 'system-status':
                this.initializeSystemStatus(widgetId);
                break;
        }
    }

    /**
     * Initialize user overview widget
     */
    initializeUserOverview(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add user avatar if not present
        const avatarElement = widget.element.querySelector('.user-avatar');
        if (avatarElement && !avatarElement.textContent.trim()) {
            const username = widget.element.querySelector('.user-name')?.textContent || 'U';
            avatarElement.textContent = username.charAt(0).toUpperCase();
        }

        // Add click handler for profile link
        const userNameElement = widget.element.querySelector('.user-name');
        if (userNameElement) {
            userNameElement.style.cursor = 'pointer';
            userNameElement.addEventListener('click', () => {
                window.location.href = '/profile';
            });
        }
    }

    /**
     * Initialize content stats widget
     */
    initializeContentStats(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add progress bars for stats if needed
        const statElements = widget.element.querySelectorAll('.content-stat');
        statElements.forEach(stat => {
            const value = parseInt(stat.querySelector('.stat-value').textContent) || 0;
            const max = Math.max(value * 1.2, 100); // Calculate max value
            const percentage = (value / max) * 100;
            
            // Add progress bar
            const progressBar = document.createElement('div');
            progressBar.className = 'stat-progress';
            progressBar.style.cssText = `
                width: 100%;
                height: 4px;
                background: #e9ecef;
                border-radius: 2px;
                margin-top: 0.5rem;
                overflow: hidden;
            `;
            
            const progressFill = document.createElement('div');
            progressFill.style.cssText = `
                width: ${percentage}%;
                height: 100%;
                background: linear-gradient(90deg, #d4af37, #4a8029);
                border-radius: 2px;
                transition: width 0.3s ease;
            `;
            
            progressBar.appendChild(progressFill);
            stat.appendChild(progressBar);
        });
    }

    /**
     * Initialize recent activity widget
     */
    initializeRecentActivity(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add click handlers for activity items
        const activityItems = widget.element.querySelectorAll('.activity-item');
        activityItems.forEach(item => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', () => {
                const pageTitle = item.querySelector('.activity-text')?.textContent;
                if (pageTitle) {
                    // Navigate to the page mentioned in the activity
                    window.location.href = `/wiki/${encodeURIComponent(pageTitle)}`;
                }
            });
        });
    }

    /**
     * Initialize Islamic calendar widget
     */
    initializeIslamicCalendar(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Update Hijri date if needed
        this.updateHijriDate(widgetId);
    }

    /**
     * Initialize prayer times widget
     */
    initializePrayerTimes(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Highlight next prayer
        this.highlightNextPrayer(widgetId);
        
        // Update prayer times periodically
        setInterval(() => {
            this.updatePrayerTimes(widgetId);
        }, 60000); // Update every minute
    }

    /**
     * Initialize Quran verse widget
     */
    initializeQuranVerse(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add click handler to view full verse
        const verseElement = widget.element.querySelector('.verse-text');
        if (verseElement) {
            verseElement.style.cursor = 'pointer';
            verseElement.addEventListener('click', () => {
                const reference = widget.element.querySelector('.verse-reference')?.textContent;
                if (reference) {
                    window.location.href = `/quran/${reference}`;
                }
            });
        }
    }

    /**
     * Initialize hadith quote widget
     */
    initializeHadithQuote(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add click handler to view full hadith
        const hadithElement = widget.element.querySelector('.hadith-text');
        if (hadithElement) {
            hadithElement.style.cursor = 'pointer';
            hadithElement.addEventListener('click', () => {
                const reference = widget.element.querySelector('.hadith-reference')?.textContent;
                if (reference) {
                    window.location.href = `/hadith/${reference}`;
                }
            });
        }
    }

    /**
     * Initialize quick actions widget
     */
    initializeQuickActions(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add hover effects
        const actionButtons = widget.element.querySelectorAll('.action-btn');
        actionButtons.forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    /**
     * Initialize notifications widget
     */
    initializeNotifications(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Add notification count badge
        const unreadCount = widget.element.querySelectorAll('.notification-unread').length;
        if (unreadCount > 0) {
            this.addNotificationBadge(widgetId, unreadCount);
        }
    }

    /**
     * Initialize system status widget
     */
    initializeSystemStatus(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Update status indicators
        this.updateSystemStatus(widgetId);
        
        // Update status periodically
        setInterval(() => {
            this.updateSystemStatus(widgetId);
        }, 300000); // Update every 5 minutes
    }

    /**
     * Start auto-refresh for refreshable widgets
     */
    startAutoRefresh() {
        setInterval(() => {
            Object.keys(this.widgets).forEach(widgetId => {
                const widget = this.widgets[widgetId];
                if (widget.refreshable && this.shouldRefreshWidget(widgetId)) {
                    this.refreshWidget(widgetId);
                }
            });
        }, this.config.refreshInterval);
    }

    /**
     * Check if widget should be refreshed
     */
    shouldRefreshWidget(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return false;

        const now = Date.now();
        const timeSinceLastRefresh = now - widget.lastRefresh;
        return timeSinceLastRefresh >= this.config.refreshInterval;
    }

    /**
     * Refresh a specific widget
     */
    refreshWidget(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        // Show loading state
        this.showWidgetLoading(widgetId);

        // Simulate refresh (in real implementation, this would make an API call)
        setTimeout(() => {
            this.hideWidgetLoading(widgetId);
            widget.lastRefresh = Date.now();
            
            // Trigger widget-specific refresh
            this.refreshWidgetContent(widgetId);
        }, 1000);
    }

    /**
     * Show widget loading state
     */
    showWidgetLoading(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'widget-loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="loading-spinner"></div>
            <div class="loading-text">Refreshing...</div>
        `;
        loadingOverlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        `;

        widget.element.style.position = 'relative';
        widget.element.appendChild(loadingOverlay);
    }

    /**
     * Hide widget loading state
     */
    hideWidgetLoading(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        const loadingOverlay = widget.element.querySelector('.widget-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    /**
     * Refresh widget content
     */
    refreshWidgetContent(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        switch (widget.type) {
            case 'content-stats':
                this.refreshContentStats(widgetId);
                break;
            case 'recent-activity':
                this.refreshRecentActivity(widgetId);
                break;
            case 'prayer-times':
                this.refreshPrayerTimes(widgetId);
                break;
            case 'quran-verse':
                this.refreshQuranVerse(widgetId);
                break;
            case 'hadith-quote':
                this.refreshHadithQuote(widgetId);
                break;
            case 'notifications':
                this.refreshNotifications(widgetId);
                break;
            case 'system-status':
                this.refreshSystemStatus(widgetId);
                break;
        }
    }

    /**
     * Change dashboard layout
     */
    changeLayout(layoutName) {
        this.currentLayout = layoutName;
        
        // Update grid layout
        const dashboardGrid = document.querySelector('.dashboard-grid');
        if (dashboardGrid) {
            const layouts = {
                'islamic': 'repeat(auto-fit, minmax(300px, 1fr))',
                'modern': 'repeat(auto-fit, minmax(250px, 1fr))',
                'compact': 'repeat(auto-fit, minmax(200px, 1fr))'
            };
            
            dashboardGrid.style.gridTemplateColumns = layouts[layoutName] || layouts.islamic;
        }

        // Save layout preference
        this.saveLayoutPreference(layoutName);
        
        // Trigger layout change event
        this.triggerEvent('layoutChanged', { layout: layoutName });
    }

    /**
     * Change dashboard theme
     */
    changeTheme(themeName) {
        document.documentElement.setAttribute('data-theme', themeName);
        
        // Save theme preference
        this.saveThemePreference(themeName);
        
        // Trigger theme change event
        this.triggerEvent('themeChanged', { theme: themeName });
    }

    /**
     * Handle quick action clicks
     */
    handleQuickAction(actionButton) {
        const action = actionButton.dataset.action;
        const url = actionButton.href;
        
        // Track action
        this.trackAction('quick_action', { action, url });
        
        // Navigate to URL
        if (url) {
            window.location.href = url;
        }
    }

    /**
     * Mark notification as read
     */
    markNotificationRead(notificationId) {
        // Update UI
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.remove('notification-unread');
            notificationElement.classList.add('notification-read');
        }

        // Update notification count
        this.updateNotificationCount();
        
        // Send API request to mark as read
        this.sendNotificationReadRequest(notificationId);
    }

    /**
     * Update notification count
     */
    updateNotificationCount() {
        const unreadCount = document.querySelectorAll('.notification-unread').length;
        const badge = document.querySelector('.notification-badge');
        
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Setup notifications
     */
    setupNotifications() {
        if (!this.config.enableNotifications) return;

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Setup real-time notifications if available
        this.setupRealTimeNotifications();
    }

    /**
     * Setup real-time notifications
     */
    setupRealTimeNotifications() {
        // This would integrate with WebSockets or Server-Sent Events
        // For now, we'll use polling
        setInterval(() => {
            this.checkForNewNotifications();
        }, 30000); // Check every 30 seconds
    }

    /**
     * Check for new notifications
     */
    checkForNewNotifications() {
        // This would make an API call to check for new notifications
        // For now, it's a placeholder
    }

    /**
     * Handle window resize
     */
    handleResize() {
        // Adjust grid layout for mobile
        const dashboardGrid = document.querySelector('.dashboard-grid');
        if (dashboardGrid) {
            if (window.innerWidth <= 768) {
                dashboardGrid.style.gridTemplateColumns = '1fr';
            } else {
                // Restore original layout
                this.changeLayout(this.currentLayout);
            }
        }
    }

    /**
     * Save layout preference
     */
    saveLayoutPreference(layout) {
        localStorage.setItem('dashboard_layout', layout);
    }

    /**
     * Save theme preference
     */
    saveThemePreference(theme) {
        localStorage.setItem('dashboard_theme', theme);
    }

    /**
     * Load saved preferences
     */
    loadSavedPreferences() {
        const savedLayout = localStorage.getItem('dashboard_layout');
        const savedTheme = localStorage.getItem('dashboard_theme');
        
        if (savedLayout) {
            this.changeLayout(savedLayout);
        }
        
        if (savedTheme) {
            this.changeTheme(savedTheme);
        }
    }

    /**
     * Track user actions
     */
    trackAction(action, data) {
        // This would integrate with analytics
        console.log('Dashboard Action:', action, data);
    }

    /**
     * Trigger custom events
     */
    triggerEvent(eventName, data) {
        const event = new CustomEvent(`dashboard:${eventName}`, {
            detail: data,
            bubbles: true
        });
        document.dispatchEvent(event);
    }

    /**
     * Send notification read request
     */
    sendNotificationReadRequest(notificationId) {
        // This would make an API call to mark notification as read
        fetch(`/api/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        }).catch(error => {
            console.warn('Failed to mark notification as read:', error);
        });
    }

    /**
     * Add notification badge
     */
    addNotificationBadge(widgetId, count) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        const header = widget.element.querySelector('.widget-header h3');
        if (header) {
            const badge = document.createElement('span');
            badge.className = 'notification-count-badge';
            badge.textContent = count;
            badge.style.cssText = `
                background: #e74c3c;
                color: white;
                border-radius: 50%;
                padding: 0.2rem 0.5rem;
                font-size: 0.7rem;
                font-weight: 600;
                margin-left: 0.5rem;
            `;
            header.appendChild(badge);
        }
    }

    /**
     * Update Hijri date
     */
    updateHijriDate(widgetId) {
        // This would calculate the current Hijri date
        // For now, it's a placeholder
    }

    /**
     * Highlight next prayer
     */
    highlightNextPrayer(widgetId) {
        const widget = this.widgets[widgetId];
        if (!widget) return;

        const prayerTimes = widget.element.querySelectorAll('.prayer-time');
        prayerTimes.forEach(prayer => {
            prayer.classList.remove('next-prayer-highlight');
        });

        // Find and highlight next prayer
        const now = new Date();
        const currentTime = now.getHours() * 60 + now.getMinutes();
        
        prayerTimes.forEach(prayer => {
            const timeText = prayer.querySelector('.prayer-time-value')?.textContent;
            if (timeText) {
                const [hours, minutes] = timeText.split(':');
                const prayerTime = parseInt(hours) * 60 + parseInt(minutes);
                
                if (prayerTime > currentTime) {
                    prayer.classList.add('next-prayer-highlight');
                    return;
                }
            }
        });
    }

    /**
     * Update prayer times
     */
    updatePrayerTimes(widgetId) {
        // This would fetch updated prayer times
        // For now, it's a placeholder
    }

    /**
     * Update system status
     */
    updateSystemStatus(widgetId) {
        // This would check system status
        // For now, it's a placeholder
    }

    /**
     * Refresh content stats
     */
    refreshContentStats(widgetId) {
        // This would fetch updated content statistics
        // For now, it's a placeholder
    }

    /**
     * Refresh recent activity
     */
    refreshRecentActivity(widgetId) {
        // This would fetch updated recent activity
        // For now, it's a placeholder
    }

    /**
     * Refresh prayer times
     */
    refreshPrayerTimes(widgetId) {
        // This would fetch updated prayer times
        // For now, it's a placeholder
    }

    /**
     * Refresh Quran verse
     */
    refreshQuranVerse(widgetId) {
        // This would fetch a new Quran verse
        // For now, it's a placeholder
    }

    /**
     * Refresh hadith quote
     */
    refreshHadithQuote(widgetId) {
        // This would fetch a new hadith quote
        // For now, it's a placeholder
    }

    /**
     * Refresh notifications
     */
    refreshNotifications(widgetId) {
        // This would fetch updated notifications
        // For now, it's a placeholder
    }

    /**
     * Refresh system status
     */
    refreshSystemStatus(widgetId) {
        // This would check system status
        // For now, it's a placeholder
    }

    /**
     * Show widget settings
     */
    showWidgetSettings(widgetId) {
        // This would show a settings modal for the widget
        // For now, it's a placeholder
        console.log('Show settings for widget:', widgetId);
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardExtension = new DashboardExtension();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DashboardExtension;
} 
/**
 * Notifications System for Bismillah Skin
 * Version: 0.0.2.2
 * Handles alerts, notices, and user notifications
 */

class NotificationsSystem {
    constructor() {
        this.notificationsIcon = document.querySelector('.notifications-icon');
        this.notificationsPanel = document.querySelector('.notifications-panel');
        this.notificationsList = this.notificationsPanel?.querySelector('.notifications-list');
        this.markAllReadBtn = this.notificationsPanel?.querySelector('.mark-all-read');
        this.init();
    }

    init() {
        this.setupNotificationsIcon();
        this.setupMarkAllRead();
        this.setupClickOutside();
        this.loadNotifications();
        this.setupNotificationBadge();
    }

    setupNotificationsIcon() {
        if (this.notificationsIcon) {
            this.notificationsIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationsPanel();
            });
        }
    }

    setupMarkAllRead() {
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (this.isVisible() && !this.notificationsPanel.contains(e.target) && !this.notificationsIcon.contains(e.target)) {
                this.hideNotificationsPanel();
            }
        });
    }

    toggleNotificationsPanel() {
        if (this.isVisible()) {
            this.hideNotificationsPanel();
        } else {
            this.showNotificationsPanel();
        }
    }

    showNotificationsPanel() {
        this.notificationsPanel.classList.add('active');
        this.notificationsPanel.style.display = 'block';
        
        // Add active state to notifications icon
        this.notificationsIcon.classList.add('active');
        
        // Position the panel relative to the icon
        this.positionPanel();
        
        // Load fresh notifications
        this.loadNotifications();
    }

    hideNotificationsPanel() {
        this.notificationsPanel.classList.remove('active');
        setTimeout(() => {
            this.notificationsPanel.style.display = 'none';
        }, 200);
        
        // Remove active state from notifications icon
        this.notificationsIcon.classList.remove('active');
    }

    positionPanel() {
        if (!this.notificationsIcon || !this.notificationsPanel) return;
        
        const iconRect = this.notificationsIcon.getBoundingClientRect();
        const panelRect = this.notificationsPanel.getBoundingClientRect();
        
        // Position panel below and to the right of the icon
        const top = iconRect.bottom + 10;
        const left = iconRect.left - (panelRect.width - iconRect.width);
        
        this.notificationsPanel.style.top = `${top}px`;
        this.notificationsPanel.style.left = `${left}px`;
    }

    async loadNotifications() {
        try {
            // Try to load from API first
            const response = await fetch('/api/notifications');
            if (response.ok) {
                const notifications = await response.json();
                this.renderNotifications(notifications);
                return;
            }
        } catch (error) {
            console.log('API not available, using mock notifications');
        }

        // Fallback to mock notifications
        const mockNotifications = this.generateMockNotifications();
        this.renderNotifications(mockNotifications);
    }

    generateMockNotifications() {
        return [
            {
                id: 1,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Tawhid.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 2,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Surah Ya-Sin.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 3,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Surah An-Naba.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 4,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Surah Al-Mu\'minun.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 5,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Surah Al-Anbiya.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 6,
                type: 'link',
                message: 'A link was made from Signs of Allah in creation to Scientific miracles in the Qur\'an.',
                time: '2mo',
                read: false,
                expandable: false
            },
            {
                id: 7,
                type: 'link',
                message: 'Links were made from 2 pages to Tafsir.',
                time: '2mo',
                read: false,
                expandable: true,
                expanded: false
            }
        ];
    }

    renderNotifications(notifications) {
        if (!this.notificationsList) return;
        
        this.notificationsList.innerHTML = '';
        
        if (notifications.length === 0) {
            this.notificationsList.innerHTML = `
                <div class="no-notifications">
                    <div class="no-notifications-icon">🔔</div>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }

        notifications.forEach(notification => {
            const notificationElement = this.createNotificationElement(notification);
            this.notificationsList.appendChild(notificationElement);
        });

        // Update notification badge
        this.updateNotificationBadge(notifications);
    }

    createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `notification-item ${notification.read ? 'read' : ''}`;
        element.dataset.id = notification.id;
        
        const iconClass = this.getNotificationIconClass(notification.type);
        const expandButton = notification.expandable ? this.createExpandButton(notification) : '';
        
        element.innerHTML = `
            <div class="notification-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-text">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
                ${expandButton}
            </div>
            <button class="mark-read" onclick="notifications.markAsRead(${notification.id})" title="Mark as read">
                <i class="icon-check"></i>
            </button>
        `;

        // Add expand functionality if needed
        if (notification.expandable) {
            this.setupExpandFunctionality(element, notification);
        }

        return element;
    }

    getNotificationIconClass(type) {
        const iconMap = {
            'link': 'icon-link',
            'edit': 'icon-edit',
            'comment': 'icon-comment',
            'mention': 'icon-at',
            'warning': 'icon-warning',
            'info': 'icon-info'
        };
        
        return iconMap[type] || 'icon-info';
    }

    createExpandButton(notification) {
        return `
            <div class="expand-section">
                <button class="expand-toggle" data-expanded="${notification.expanded || false}">
                    <span class="expand-text">${notification.expanded ? 'Collapse' : 'Expand'}</span>
                    <i class="expand-icon">${notification.expanded ? '▼' : '▶'}</i>
                </button>
                <div class="expand-content" style="display: ${notification.expanded ? 'block' : 'none'}">
                    <div class="expand-details">
                        <a href="/wiki/Tafsir" class="expand-link">All links to this page</a>
                    </div>
                </div>
            </div>
        `;
    }

    setupExpandFunctionality(element, notification) {
        const expandToggle = element.querySelector('.expand-toggle');
        const expandContent = element.querySelector('.expand-content');
        
        if (expandToggle && expandContent) {
            expandToggle.addEventListener('click', () => {
                const isExpanded = expandToggle.dataset.expanded === 'true';
                
                if (isExpanded) {
                    this.collapseNotification(element, expandToggle, expandContent);
                } else {
                    this.expandNotification(element, expandToggle, expandContent);
                }
            });
        }
    }

    expandNotification(element, expandToggle, expandContent) {
        expandToggle.dataset.expanded = 'true';
        expandToggle.querySelector('.expand-text').textContent = 'Collapse';
        expandToggle.querySelector('.expand-icon').textContent = '▼';
        expandContent.style.display = 'block';
        
        element.classList.add('expanded');
    }

    collapseNotification(element, expandToggle, expandContent) {
        expandToggle.dataset.expanded = 'false';
        expandToggle.querySelector('.expand-text').textContent = 'Expand';
        expandToggle.querySelector('.expand-icon').textContent = '▶';
        expandContent.style.display = 'none';
        
        element.classList.remove('expanded');
    }

    async markAsRead(notificationId) {
        try {
            // Try to mark as read via API
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST'
            });
            
            if (!response.ok) {
                throw new Error('API call failed');
            }
        } catch (error) {
            console.log('API not available, marking as read locally');
        }

        // Update UI
        const notificationElement = this.notificationsPanel.querySelector(`[data-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.add('read');
            
            // Update notification badge
            this.updateNotificationBadge();
        }
    }

    async markAllAsRead() {
        try {
            // Try to mark all as read via API
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST'
            });
            
            if (!response.ok) {
                throw new Error('API call failed');
            }
        } catch (error) {
            console.log('API not available, marking all as read locally');
        }

        // Update UI
        const notificationItems = this.notificationsPanel.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.classList.add('read');
        });

        // Update notification badge
        this.updateNotificationBadge();
        
        // Show success message
        this.showSuccessMessage('All notifications marked as read');
    }

    showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        
        this.notificationsPanel.appendChild(successDiv);
        
        setTimeout(() => {
            successDiv.remove();
        }, 3000);
    }

    setupNotificationBadge() {
        // Add notification count badge to icon
        this.updateNotificationBadge();
    }

    updateNotificationBadge(notifications = null) {
        if (!this.notificationsIcon) return;
        
        let unreadCount = 0;
        
        if (notifications) {
            unreadCount = notifications.filter(n => !n.read).length;
        } else {
            // Count unread notifications from DOM
            const unreadItems = this.notificationsPanel?.querySelectorAll('.notification-item:not(.read)') || [];
            unreadCount = unreadItems.length;
        }

        // Update or create badge
        let badge = this.notificationsIcon.querySelector('.notification-badge');
        
        if (unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                this.notificationsIcon.appendChild(badge);
            }
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
        } else if (badge) {
            badge.remove();
        }
    }

    isVisible() {
        return this.notificationsPanel?.classList.contains('active') || false;
    }

    // Public methods for external control
    show() {
        this.showNotificationsPanel();
    }

    hide() {
        this.hideNotificationsPanel();
    }

    toggle() {
        this.toggleNotificationsPanel();
    }

    // Method to add a new notification
    addNotification(notification) {
        if (!this.notificationsList) return;
        
        const element = this.createNotificationElement(notification);
        this.notificationsList.insertBefore(element, this.notificationsList.firstChild);
        
        // Update notification badge
        this.updateNotificationBadge();
        
        // Show success message
        this.showSuccessMessage('New notification received');
    }

    // Method to remove a notification
    removeNotification(notificationId) {
        const notificationElement = this.notificationsPanel.querySelector(`[data-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.remove();
            this.updateNotificationBadge();
        }
    }

    // Method to clear all notifications
    clearAllNotifications() {
        if (this.notificationsList) {
            this.notificationsList.innerHTML = '';
            this.updateNotificationBadge();
            this.showSuccessMessage('All notifications cleared');
        }
    }

    // Method to get notification count
    getNotificationCount() {
        const items = this.notificationsPanel?.querySelectorAll('.notification-item') || [];
        return items.length;
    }

    // Method to get unread notification count
    getUnreadCount() {
        const unreadItems = this.notificationsPanel?.querySelectorAll('.notification-item:not(.read)') || [];
        return unreadItems.length;
    }
}

// Export for global use
window.NotificationsSystem = NotificationsSystem; 
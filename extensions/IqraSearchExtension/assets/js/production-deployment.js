/**
 * Production Deployment Dashboard JavaScript
 * Provides interactive functionality for the production deployment dashboard
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class ProductionDeploymentDashboard {
    constructor() {
        this.refreshInterval = null;
        this.currentDeployment = null;
        this.deploymentHistory = [];
        this.isInitialized = false;
        
        this.init();
    }

    /**
     * Initialize the dashboard
     */
    init() {
        if (this.isInitialized) return;
        
        this.bindEvents();
        this.loadInitialData();
        this.startAutoRefresh();
        
        this.isInitialized = true;
        console.log('Production Deployment Dashboard initialized');
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Refresh status button
        const refreshBtn = document.getElementById('refreshStatus');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshDeploymentStatus());
        }

        // Deployment form
        const deploymentForm = document.getElementById('deploymentForm');
        if (deploymentForm) {
            deploymentForm.addEventListener('submit', (e) => this.handleDeploymentStart(e));
        }

        // Rollback form
        const rollbackForm = document.getElementById('rollbackForm');
        if (rollbackForm) {
            rollbackForm.addEventListener('submit', (e) => this.handleRollback(e));
        }

        // Training and documentation buttons
        const viewTrainingBtn = document.getElementById('viewTraining');
        if (viewTrainingBtn) {
            viewTrainingBtn.addEventListener('click', () => this.viewTrainingModules());
        }

        const viewDocumentationBtn = document.getElementById('viewDocumentation');
        if (viewDocumentationBtn) {
            viewDocumentationBtn.addEventListener('click', () => this.viewDocumentation());
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
    }

    /**
     * Load initial data
     */
    async loadInitialData() {
        try {
            await Promise.all([
                this.loadDeploymentStatus(),
                this.loadDeploymentHistory(),
                this.loadProductionReadiness(),
                this.loadLaunchChecklist()
            ]);
            
            this.updateDashboard();
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showNotification('Error loading dashboard data', 'error');
        }
    }

    /**
     * Start auto-refresh
     */
    startAutoRefresh() {
        // Refresh every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.refreshDeploymentStatus();
        }, 30000);
    }

    /**
     * Stop auto-refresh
     */
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    /**
     * Handle deployment start
     */
    async handleDeploymentStart(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const environment = formData.get('environment');
        
        if (!environment) {
            this.showNotification('Please select a target environment', 'warning');
            return;
        }

        try {
            this.showNotification(`Starting deployment to ${environment}...`, 'info');
            
            const response = await fetch('/admin/production-deployment/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ environment: environment })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(`Deployment to ${environment} started successfully!`, 'success');
                this.currentDeployment = {
                    environment: environment,
                    startTime: new Date(),
                    status: 'in_progress'
                };
                
                // Refresh status immediately
                setTimeout(() => this.refreshDeploymentStatus(), 2000);
            } else {
                this.showNotification(`Failed to start deployment: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Error starting deployment:', error);
            this.showNotification('Error starting deployment', 'error');
        }
    }

    /**
     * Handle rollback
     */
    async handleRollback(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const environment = formData.get('environment');
        
        if (!environment) {
            this.showNotification('Please select an environment to rollback', 'warning');
            return;
        }

        const confirmed = confirm(`Are you sure you want to rollback the deployment for ${environment}? This action cannot be undone.`);
        
        if (!confirmed) return;

        try {
            this.showNotification(`Rolling back deployment for ${environment}...`, 'info');
            
            const response = await fetch('/admin/production-deployment/rollback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ environment: environment })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(`Deployment rollback completed for ${environment}!`, 'success');
                
                // Refresh status immediately
                setTimeout(() => this.refreshDeploymentStatus(), 2000);
            } else {
                this.showNotification(`Failed to rollback deployment: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Error rolling back deployment:', error);
            this.showNotification('Error rolling back deployment', 'error');
        }
    }

    /**
     * Refresh deployment status
     */
    async refreshDeploymentStatus() {
        try {
            const response = await fetch('/admin/production-deployment/status');
            const data = await response.json();
            
            if (data.success) {
                this.updateDeploymentStatus(data.deployment_status);
                this.updateDeploymentProgress(data.is_deployment_in_progress);
            }
        } catch (error) {
            console.error('Error refreshing deployment status:', error);
        }
    }

    /**
     * Load deployment status
     */
    async loadDeploymentStatus() {
        try {
            const response = await fetch('/admin/production-deployment/status');
            const data = await response.json();
            
            if (data.success) {
                this.currentDeployment = data.deployment_status;
                this.updateDeploymentStatus(data.deployment_status);
                this.updateDeploymentProgress(data.is_deployment_in_progress);
            }
        } catch (error) {
            console.error('Error loading deployment status:', error);
        }
    }

    /**
     * Load deployment history
     */
    async loadDeploymentHistory() {
        try {
            const response = await fetch('/admin/production-deployment/history');
            const data = await response.json();
            
            if (data.success) {
                this.deploymentHistory = data.deployment_history;
                this.updateDeploymentHistory();
            }
        } catch (error) {
            console.error('Error loading deployment history:', error);
        }
    }

    /**
     * Load production readiness
     */
    async loadProductionReadiness() {
        try {
            const response = await fetch('/admin/production-deployment/readiness');
            const data = await response.json();
            
            if (data.success) {
                this.updateProductionReadiness(data.production_readiness);
            }
        } catch (error) {
            console.error('Error loading production readiness:', error);
        }
    }

    /**
     * Load launch checklist
     */
    async loadLaunchChecklist() {
        try {
            const response = await fetch('/admin/production-deployment/checklist');
            const data = await response.json();
            
            if (data.success) {
                this.updateLaunchChecklist(data.launch_checklist);
            }
        } catch (error) {
            console.error('Error loading launch checklist:', error);
        }
    }

    /**
     * Update deployment status display
     */
    updateDeploymentStatus(status) {
        const statusElement = document.querySelector('.safa-status-badge--deployment');
        if (statusElement && status) {
            const statusClass = this.getStatusClass(status.status || 'ready');
            statusElement.className = `safa-status-badge safa-status-badge--${statusClass}`;
            statusElement.textContent = status.status ? status.status.toUpperCase() : 'READY';
        }
    }

    /**
     * Update deployment progress
     */
    updateDeploymentProgress(isInProgress) {
        const progressElement = document.querySelector('.safa-progress-fill');
        if (progressElement) {
            if (isInProgress) {
                progressElement.style.width = '100%';
                progressElement.style.background = 'linear-gradient(90deg, #fbc02d 0%, #ff9800 100%)';
            } else {
                progressElement.style.width = '0%';
                progressElement.style.background = 'linear-gradient(90deg, #4caf50 0%, #8bc34a 100%)';
            }
        }
    }

    /**
     * Update deployment history table
     */
    updateDeploymentHistory() {
        const tableBody = document.getElementById('deploymentHistoryTable');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        this.deploymentHistory.forEach(deployment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${deployment.environment}</td>
                <td><span class="safa-status-badge safa-status-badge--${this.getStatusClass(deployment.status)}">${deployment.status.toUpperCase()}</span></td>
                <td>${deployment.start_time}</td>
                <td>${deployment.end_time || '-'}</td>
                <td>${deployment.duration || '-'}</td>
                <td>${deployment.steps_completed}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    /**
     * Update production readiness display
     */
    updateProductionReadiness(readiness) {
        if (!readiness) return;

        // Update system health progress bar
        const healthElement = document.querySelector('.safa-progress-fill');
        if (healthElement) {
            healthElement.style.width = `${readiness.system_health}%`;
        }

        // Update readiness indicators
        this.updateReadinessIndicator('performance', readiness.performance_ready);
        this.updateReadinessIndicator('security', readiness.security_ready);
        this.updateReadinessIndicator('scalability', readiness.scalability_ready);
    }

    /**
     * Update readiness indicator
     */
    updateReadinessIndicator(type, isReady) {
        const indicator = document.querySelector(`.safa-status-badge--${type}`);
        if (indicator) {
            const statusClass = isReady ? 'success' : 'warning';
            const statusText = isReady ? 'READY' : 'NEEDS ATTENTION';
            
            indicator.className = `safa-status-badge safa-status-badge--${statusClass}`;
            indicator.textContent = statusText;
        }
    }

    /**
     * Update launch checklist
     */
    updateLaunchChecklist(checklist) {
        if (!checklist) return;

        // Update checklist items
        Object.keys(checklist).forEach(category => {
            Object.keys(checklist[category]).forEach(item => {
                const status = checklist[category][item];
                this.updateChecklistItem(category, item, status);
            });
        });
    }

    /**
     * Update checklist item
     */
    updateChecklistItem(category, item, status) {
        const itemElement = document.querySelector(`[data-checklist="${category}-${item}"]`);
        if (itemElement) {
            const statusClass = this.getChecklistStatusClass(status);
            const statusIcon = this.getChecklistStatusIcon(status);
            
            itemElement.querySelector('.safa-checklist-status').className = `safa-checklist-status safa-checklist-status--${statusClass}`;
            itemElement.querySelector('.safa-checklist-status').textContent = statusIcon;
        }
    }

    /**
     * View training modules
     */
    viewTrainingModules() {
        window.location.href = '/admin/training';
    }

    /**
     * View documentation
     */
    viewDocumentation() {
        window.location.href = '/admin/documentation';
    }

    /**
     * Handle keyboard shortcuts
     */
    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + R: Refresh status
        if ((event.ctrlKey || event.metaKey) && event.key === 'r') {
            event.preventDefault();
            this.refreshDeploymentStatus();
        }
        
        // Ctrl/Cmd + D: Start deployment
        if ((event.ctrlKey || event.metaKey) && event.key === 'd') {
            event.preventDefault();
            this.focusDeploymentForm();
        }
        
        // Ctrl/Cmd + B: Rollback deployment
        if ((event.ctrlKey || event.metaKey) && event.key === 'b') {
            event.preventDefault();
            this.focusRollbackForm();
        }
    }

    /**
     * Focus deployment form
     */
    focusDeploymentForm() {
        const environmentSelect = document.getElementById('targetEnvironment');
        if (environmentSelect) {
            environmentSelect.focus();
        }
    }

    /**
     * Focus rollback form
     */
    focusRollbackForm() {
        const environmentSelect = document.getElementById('rollbackEnvironment');
        if (environmentSelect) {
            environmentSelect.focus();
        }
    }

    /**
     * Update dashboard display
     */
    updateDashboard() {
        this.updateDeploymentStatus(this.currentDeployment);
        this.updateDeploymentHistory();
        this.updateProductionReadiness(this.productionReadiness);
        this.updateLaunchChecklist(this.launchChecklist);
    }

    /**
     * Get status class for CSS styling
     */
    getStatusClass(status) {
        const statusMap = {
            'completed': 'success',
            'in_progress': 'in-progress',
            'failed': 'danger',
            'ready': 'ready'
        };
        
        return statusMap[status] || 'ready';
    }

    /**
     * Get checklist status class
     */
    getChecklistStatusClass(status) {
        if (status === 'Ready' || status === 'Compliant') {
            return 'ready';
        }
        return 'pending';
    }

    /**
     * Get checklist status icon
     */
    getChecklistStatusIcon(status) {
        if (status === 'Ready' || status === 'Compliant') {
            return '✅';
        }
        return '⏳';
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `safa-notification safa-notification--${type}`;
        notification.innerHTML = `
            <div class="safa-notification-content">
                <span class="safa-notification-message">${message}</span>
                <button class="safa-notification-close">&times;</button>
            </div>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('safa-notification--show');
        }, 100);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hideNotification(notification);
        }, 5000);

        // Close button functionality
        const closeBtn = notification.querySelector('.safa-notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hideNotification(notification);
            });
        }
    }

    /**
     * Hide notification
     */
    hideNotification(notification) {
        notification.classList.remove('safa-notification--show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    /**
     * Destroy dashboard
     */
    destroy() {
        this.stopAutoRefresh();
        this.isInitialized = false;
        console.log('Production Deployment Dashboard destroyed');
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.productionDeploymentDashboard = new ProductionDeploymentDashboard();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductionDeploymentDashboard;
} 
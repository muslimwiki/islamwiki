/**
 * Settings Menu System for Bismillah Skin
 * Version: 0.0.2.2
 * Handles display preferences and settings
 */

class SettingsMenu {
    constructor() {
        this.settingsIcon = document.querySelector('.settings-icon');
        this.settingsMenu = document.querySelector('.settings-menu');
        this.init();
    }

    init() {
        this.setupSettingsIcon();
        this.setupPreferenceControls();
        this.setupClickOutside();
        this.loadUserPreferences();
    }

    setupSettingsIcon() {
        if (this.settingsIcon) {
            this.settingsIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSettingsMenu();
            });
        }
    }

    setupPreferenceControls() {
        // Text size controls
        const textSizeButtons = this.settingsMenu?.querySelectorAll('.text-size-btn');
        if (textSizeButtons) {
            textSizeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    this.setTextSize(btn.dataset.size);
                });
            });
        }

        // Color theme controls
        const colorButtons = this.settingsMenu?.querySelectorAll('.color-theme-btn');
        if (colorButtons) {
            colorButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    this.setColorTheme(btn.dataset.theme);
                });
            });
        }

        // Width controls
        const widthButtons = this.settingsMenu?.querySelectorAll('.width-btn');
        if (widthButtons) {
            widthButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    this.setWidth(btn.dataset.width);
                });
            });
        }
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (this.isVisible() && !this.settingsMenu.contains(e.target) && !this.settingsIcon.contains(e.target)) {
                this.hideSettingsMenu();
            }
        });
    }

    toggleSettingsMenu() {
        if (this.isVisible()) {
            this.hideSettingsMenu();
        } else {
            this.showSettingsMenu();
        }
    }

    showSettingsMenu() {
        this.settingsMenu.classList.add('active');
        this.settingsMenu.style.display = 'block';
        
        // Add active state to settings icon
        this.settingsIcon.classList.add('active');
        
        // Position the menu relative to the icon
        this.positionMenu();
    }

    hideSettingsMenu() {
        this.settingsMenu.classList.remove('active');
        setTimeout(() => {
            this.settingsMenu.style.display = 'none';
        }, 200);
        
        // Remove active state from settings icon
        this.settingsIcon.classList.remove('active');
    }

    positionMenu() {
        if (!this.settingsIcon || !this.settingsMenu) return;
        
        const iconRect = this.settingsIcon.getBoundingClientRect();
        const menuRect = this.settingsMenu.getBoundingClientRect();
        
        // Position menu below and to the right of the icon
        const top = iconRect.bottom + 10;
        const left = iconRect.left - (menuRect.width - iconRect.width);
        
        this.settingsMenu.style.top = `${top}px`;
        this.settingsMenu.style.left = `${left}px`;
    }

    setTextSize(size) {
        // Remove existing text size classes
        document.documentElement.classList.remove('text-size-small', 'text-size-standard', 'text-size-large', 'text-size-extra-large');
        
        // Add new text size class
        document.documentElement.classList.add(`text-size-${size}`);
        
        // Update active button
        this.updateActiveButton('.text-size-btn', size);
        
        // Save preference
        this.saveUserPreference('textSize', size);
        
        // Apply text size changes
        this.applyTextSize(size);
    }

    setColorTheme(theme) {
        // Remove existing theme classes
        document.documentElement.classList.remove('theme-auto', 'theme-light', 'theme-dark');
        
        // Add new theme class
        document.documentElement.classList.add(`theme-${theme}`);
        
        // Update active button
        this.updateActiveButton('.color-theme-btn', theme);
        
        // Save preference
        this.saveUserPreference('colorTheme', theme);
        
        // Apply theme changes
        this.applyColorTheme(theme);
    }

    setWidth(width) {
        // Remove existing width classes
        document.documentElement.classList.remove('width-standard', 'width-wide', 'width-full');
        
        // Add new width class
        document.documentElement.classList.add(`width-${width}`);
        
        // Update active button
        this.updateActiveButton('.width-btn', width);
        
        // Save preference
        this.saveUserPreference('width', width);
        
        // Apply width changes
        this.applyWidth(width);
    }

    updateActiveButton(selector, value) {
        const buttons = this.settingsMenu?.querySelectorAll(selector);
        if (buttons) {
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.size === value || btn.dataset.theme === value || btn.dataset.width === value) {
                    btn.classList.add('active');
                }
            });
        }
    }

    applyTextSize(size) {
        const sizes = {
            'small': '14px',
            'standard': '16px',
            'large': '18px',
            'extra-large': '20px'
        };
        
        if (sizes[size]) {
            document.documentElement.style.fontSize = sizes[size];
        }
    }

    applyColorTheme(theme) {
        const themes = {
            'auto': () => {
                // Auto theme - detect system preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('theme-dark', prefersDark);
                document.documentElement.classList.toggle('theme-light', !prefersDark);
            },
            'light': () => {
                document.documentElement.classList.remove('theme-dark');
                document.documentElement.classList.add('theme-light');
            },
            'dark': () => {
                document.documentElement.classList.remove('theme-light');
                document.documentElement.classList.add('theme-dark');
            }
        };
        
        if (themes[theme]) {
            themes[theme]();
        }
    }

    applyWidth(width) {
        const widths = {
            'standard': '1200px',
            'wide': '1400px',
            'full': '100%'
        };
        
        if (widths[width]) {
            const mainContainer = document.querySelector('.main-content-wrapper');
            if (mainContainer) {
                mainContainer.style.maxWidth = widths[width];
            }
        }
    }

    loadUserPreferences() {
        try {
            const preferences = JSON.parse(localStorage.getItem('bismillah-preferences')) || {};
            
            // Apply saved preferences
            if (preferences.textSize) {
                this.setTextSize(preferences.textSize);
            }
            
            if (preferences.colorTheme) {
                this.setColorTheme(preferences.colorTheme);
            }
            
            if (preferences.width) {
                this.setWidth(preferences.width);
            }
        } catch (error) {
            console.error('Failed to load user preferences:', error);
        }
    }

    saveUserPreference(key, value) {
        try {
            const preferences = JSON.parse(localStorage.getItem('bismillah-preferences')) || {};
            preferences[key] = value;
            localStorage.setItem('bismillah-preferences', JSON.stringify(preferences));
        } catch (error) {
            console.error('Failed to save user preference:', error);
        }
    }

    isVisible() {
        return this.settingsMenu?.classList.contains('active') || false;
    }

    // Public methods for external control
    show() {
        this.showSettingsMenu();
    }

    hide() {
        this.hideSettingsMenu();
    }

    toggle() {
        this.toggleSettingsMenu();
    }

    // Method to get current preferences
    getCurrentPreferences() {
        try {
            return JSON.parse(localStorage.getItem('bismillah-preferences')) || {};
        } catch (error) {
            return {};
        }
    }

    // Method to reset all preferences to default
    resetPreferences() {
        try {
            localStorage.removeItem('bismillah-preferences');
            
            // Reset to default values
            this.setTextSize('standard');
            this.setColorTheme('light');
            this.setWidth('standard');
            
            // Remove all custom classes
            document.documentElement.className = '';
            
            console.log('Preferences reset to default');
        } catch (error) {
            console.error('Failed to reset preferences:', error);
        }
    }

    // Method to export preferences
    exportPreferences() {
        try {
            const preferences = this.getCurrentPreferences();
            const dataStr = JSON.stringify(preferences, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = 'bismillah-preferences.json';
            link.click();
            
            URL.revokeObjectURL(link.href);
        } catch (error) {
            console.error('Failed to export preferences:', error);
        }
    }

    // Method to import preferences
    importPreferences(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const preferences = JSON.parse(e.target.result);
                    
                    // Validate preferences
                    if (this.validatePreferences(preferences)) {
                        // Apply imported preferences
                        if (preferences.textSize) this.setTextSize(preferences.textSize);
                        if (preferences.colorTheme) this.setColorTheme(preferences.colorTheme);
                        if (preferences.width) this.setWidth(preferences.width);
                        
                        // Save to localStorage
                        localStorage.setItem('bismillah-preferences', JSON.stringify(preferences));
                        
                        resolve(preferences);
                    } else {
                        reject(new Error('Invalid preferences format'));
                    }
                } catch (error) {
                    reject(error);
                }
            };
            
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsText(file);
        });
    }

    validatePreferences(preferences) {
        const validTextSizes = ['small', 'standard', 'large', 'extra-large'];
        const validColorThemes = ['auto', 'light', 'dark'];
        const validWidths = ['standard', 'wide', 'full'];
        
        return (
            (!preferences.textSize || validTextSizes.includes(preferences.textSize)) &&
            (!preferences.colorTheme || validColorThemes.includes(preferences.colorTheme)) &&
            (!preferences.width || validWidths.includes(preferences.width))
        );
    }
}

// Export for global use
window.SettingsMenu = SettingsMenu; 
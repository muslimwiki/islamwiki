/**
 * LanguageSwitch Component JavaScript
 * Provides comprehensive language switching functionality with RTL support
 */

(function() {
    'use strict';

    // Language configuration
    const LANGUAGES = {
        en: {
            code: 'en',
            name: 'English',
            native: 'English',
            flag: '🇺🇸',
            direction: 'ltr',
            isRTL: false
        },
        ar: {
            code: 'ar',
            name: 'Arabic',
            native: 'العربية',
            flag: '🇸🇦',
            direction: 'rtl',
            isRTL: true
        }
    };

    // Default language
    const DEFAULT_LANGUAGE = 'en';

    // Storage key for language preference
    const LANGUAGE_STORAGE_KEY = 'islamwiki_language';

    /**
     * LanguageSwitch Class
     */
    class LanguageSwitch {
        constructor() {
            this.currentLanguage = this.getStoredLanguage() || DEFAULT_LANGUAGE;
            this.init();
        }

        /**
         * Initialize the language switch
         */
        init() {
            this.setupEventListeners();
            this.updateInterface();
            this.applyLanguage(this.currentLanguage);
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Language switch button click
            document.addEventListener('click', (e) => {
                if (e.target.closest('.language-switch-button')) {
                    this.toggleDropdown();
                }
            });

            // Language option selection
            document.addEventListener('click', (e) => {
                if (e.target.closest('.language-option')) {
                    const languageCode = e.target.closest('.language-option').dataset.language;
                    if (languageCode && LANGUAGES[languageCode]) {
                        this.switchLanguage(languageCode);
                    }
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.language-switch')) {
                    this.closeDropdown();
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeDropdown();
                }
            });

            // Listen for language changes from other sources
            document.addEventListener('languageChanged', (e) => {
                if (e.detail && e.detail.language) {
                    this.switchLanguage(e.detail.language);
                }
            });
        }

        /**
         * Toggle language dropdown
         */
        toggleDropdown() {
            const dropdown = document.querySelector('.language-dropdown');
            if (dropdown) {
                const isVisible = dropdown.style.visibility === 'visible' || 
                                 dropdown.classList.contains('visible');
                
                if (isVisible) {
                    this.closeDropdown();
                } else {
                    this.openDropdown();
                }
            }
        }

        /**
         * Open language dropdown
         */
        openDropdown() {
            const dropdown = document.querySelector('.language-dropdown');
            if (dropdown) {
                dropdown.style.visibility = 'visible';
                dropdown.style.opacity = '1';
                dropdown.style.transform = 'translateY(0)';
                dropdown.classList.add('visible');
                
                // Focus first language option for keyboard navigation
                const firstOption = dropdown.querySelector('.language-option');
                if (firstOption) {
                    firstOption.focus();
                }
            }
        }

        /**
         * Close language dropdown
         */
        closeDropdown() {
            const dropdown = document.querySelector('.language-dropdown');
            if (dropdown) {
                dropdown.style.visibility = 'hidden';
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
                dropdown.classList.remove('visible');
            }
        }

        /**
         * Switch to a specific language
         */
        switchLanguage(languageCode) {
            if (!LANGUAGES[languageCode]) {
                console.warn(`Language ${languageCode} is not supported`);
                return;
            }

            const previousLanguage = this.currentLanguage;
            this.currentLanguage = languageCode;

            // Update interface
            this.updateInterface();
            
            // Apply language changes
            this.applyLanguage(languageCode);
            
            // Store preference
            this.storeLanguage(languageCode);
            
            // Close dropdown
            this.closeDropdown();
            
            // Dispatch custom event
            this.dispatchLanguageChangeEvent(languageCode, previousLanguage);
            
            // Update other language switches on the page
            this.updateOtherLanguageSwitches(languageCode);
        }

        /**
         * Update the interface to reflect current language
         */
        updateInterface() {
            const languageSwitch = document.querySelector('.language-switch');
            if (!languageSwitch) return;

            const language = LANGUAGES[this.currentLanguage];
            if (!language) return;

            // Update button content
            const button = languageSwitch.querySelector('.language-switch-button');
            if (button) {
                const flag = button.querySelector('.language-flag');
                const text = button.querySelector('.language-text');
                
                if (flag) flag.textContent = language.flag;
                if (text) text.textContent = language.native;
            }

            // Update data attribute
            languageSwitch.setAttribute('data-lang', language.code);

            // Update dropdown options
            this.updateDropdownOptions();
        }

        /**
         * Update dropdown language options
         */
        updateDropdownOptions() {
            const dropdown = document.querySelector('.language-dropdown');
            if (!dropdown) return;

            // Clear existing options
            dropdown.innerHTML = '';

            // Add language options
            Object.values(LANGUAGES).forEach(language => {
                const option = this.createLanguageOption(language);
                dropdown.appendChild(option);
            });
        }

        /**
         * Create a language option element
         */
        createLanguageOption(language) {
            const option = document.createElement('div');
            option.className = 'language-option';
            option.dataset.language = language.code;
            
            if (language.code === this.currentLanguage) {
                option.classList.add('active');
            }

            option.innerHTML = `
                <span class="language-option-flag">${language.flag}</span>
                <div class="language-option-content">
                    <div class="language-option-name">${language.name}</div>
                    <div class="language-option-native">${language.native}</div>
                    ${language.isRTL ? '<div class="language-option-rtl">RTL</div>' : ''}
                </div>
            `;

            // Add click handler
            option.addEventListener('click', () => {
                this.switchLanguage(language.code);
            });

            return option;
        }

        /**
         * Apply language changes to the page
         */
        applyLanguage(languageCode) {
            const language = LANGUAGES[languageCode];
            if (!language) return;

            const html = document.documentElement;
            const body = document.body;

            // Update HTML attributes
            html.setAttribute('lang', language.code);
            html.setAttribute('dir', language.direction);

            // Update body classes
            body.classList.remove('ltr', 'rtl');
            body.classList.add(language.direction);

            // Add transition class for smooth changes
            body.classList.add('language-switch-transition');
            
            // Remove transition class after animation
            setTimeout(() => {
                body.classList.remove('language-switch-transition');
            }, 300);

            // Update any existing language toggles to stay in sync
            this.updateOtherLanguageToggles(languageCode);
        }

        /**
         * Update other language switches on the page
         */
        updateOtherLanguageSwitches(languageCode) {
            const otherSwitches = document.querySelectorAll('[id*="langToggle"], [id*="languageToggle"]');
            otherSwitches.forEach(switchElement => {
                if (switchElement.classList.contains('language-switch')) return;
                
                switchElement.setAttribute('data-lang', languageCode);
                
                const icon = switchElement.querySelector('.lang-icon, .rtl-icon');
                const text = switchElement.querySelector('.lang-text, .rtl-text');
                
                if (icon && text) {
                    const language = LANGUAGES[languageCode];
                    if (language) {
                        icon.textContent = language.flag;
                        text.textContent = language.native;
                    }
                }
            });
        }

        /**
         * Update other language toggles (legacy support)
         */
        updateOtherLanguageToggles(languageCode) {
            const otherToggles = document.querySelectorAll('[id*="langToggle"], [id*="languageToggle"]');
            otherToggles.forEach(toggle => {
                if (toggle.classList.contains('language-switch')) return;
                
                toggle.setAttribute('data-lang', languageCode);
                const icon = toggle.querySelector('.lang-icon, .rtl-icon');
                const text = toggle.querySelector('.lang-text, .rtl-text');
                
                if (icon && text) {
                    const language = LANGUAGES[languageCode];
                    if (language) {
                        icon.textContent = language.flag;
                        text.textContent = language.native;
                    }
                }
            });
        }

        /**
         * Store language preference
         */
        storeLanguage(languageCode) {
            try {
                localStorage.setItem(LANGUAGE_STORAGE_KEY, languageCode);
            } catch (e) {
                console.warn('Could not store language preference:', e);
            }
        }

        /**
         * Get stored language preference
         */
        getStoredLanguage() {
            try {
                return localStorage.getItem(LANGUAGE_STORAGE_KEY);
            } catch (e) {
                console.warn('Could not retrieve language preference:', e);
                return null;
            }
        }

        /**
         * Dispatch language change event
         */
        dispatchLanguageChangeEvent(newLanguage, previousLanguage) {
            const event = new CustomEvent('languageChanged', {
                detail: {
                    language: newLanguage,
                    previousLanguage: previousLanguage,
                    direction: LANGUAGES[newLanguage]?.direction || 'ltr',
                    isRTL: LANGUAGES[newLanguage]?.isRTL || false
                }
            });
            document.dispatchEvent(event);
        }

        /**
         * Get current language
         */
        getCurrentLanguage() {
            return this.currentLanguage;
        }

        /**
         * Check if current language is RTL
         */
        isCurrentLanguageRTL() {
            return LANGUAGES[this.currentLanguage]?.isRTL || false;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new LanguageSwitch();
        });
    } else {
        new LanguageSwitch();
    }

    // Export for global access if needed
    window.LanguageSwitch = LanguageSwitch;

})(); 
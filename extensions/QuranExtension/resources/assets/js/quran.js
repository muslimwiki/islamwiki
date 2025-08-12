/**
 * QuranExtension JavaScript
 * Handles interactive functionality for Quran browsing and search
 */

class QuranExtension {
    constructor() {
        this.currentSurah = 1;
        this.currentAyah = 1;
        this.currentJuz = 1;
        this.currentPage = 1;
        this.searchResults = [];
        this.isPlaying = false;
        this.currentAudio = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeSearch();
        this.initializeNavigation();
        this.initializeAudioControls();
        this.initializeBookmarks();
        
        // Auto-refresh daily ayah widget
        this.startDailyAyahRefresh();
    }

    bindEvents() {
        // Search form submission
        const searchForm = document.querySelector('.quran-search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => this.handleSearch(e));
        }

        // Surah card clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.quran-surah-card')) {
                const card = e.target.closest('.quran-surah-card');
                const surahNumber = card.dataset.surah;
                if (surahNumber) {
                    this.navigateToSurah(parseInt(surahNumber));
                }
            }
        });

        // Navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quran-nav-btn')) {
                const action = e.target.dataset.action;
                this.handleNavigation(action);
            }
        });

        // Audio controls
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quran-audio-btn')) {
                const ayahId = e.target.dataset.ayahId;
                this.toggleAudio(ayahId);
            }
        });

        // Bookmark functionality
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quran-bookmark-btn')) {
                const ayahId = e.target.dataset.ayahId;
                this.toggleBookmark(ayahId);
            }
        });

        // Copy ayah text
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quran-copy-btn')) {
                const ayahText = e.target.dataset.ayahText;
                this.copyToClipboard(ayahText);
            }
        });
    }

    initializeSearch() {
        const searchInput = document.querySelector('.quran-search-input');
        if (searchInput) {
            // Debounced search
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    }

    initializeNavigation() {
        // Set current page from URL or localStorage
        const urlParams = new URLSearchParams(window.location.search);
        this.currentSurah = parseInt(urlParams.get('surah')) || 1;
        this.currentAyah = parseInt(urlParams.get('ayah')) || 1;
        this.currentJuz = parseInt(urlParams.get('juz')) || 1;
        this.currentPage = parseInt(urlParams.get('page')) || 1;

        // Update navigation state
        this.updateNavigationState();
    }

    initializeAudioControls() {
        // Check if audio is supported
        if (typeof Audio !== 'undefined') {
            this.audioSupported = true;
        } else {
            this.audioSupported = false;
            this.disableAudioControls();
        }
    }

    initializeBookmarks() {
        // Load bookmarks from localStorage
        this.bookmarks = JSON.parse(localStorage.getItem('quran_bookmarks') || '[]');
        this.updateBookmarkDisplay();
    }

    async handleSearch(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const query = formData.get('query');
        const language = formData.get('language') || 'english';
        const translator = formData.get('translator') || 'Saheeh International';

        if (!query.trim()) {
            this.showMessage('Please enter a search term', 'error');
            return;
        }

        this.showLoading(true);
        try {
            const results = await this.performSearch(query, language, translator);
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.showMessage('Search failed. Please try again.', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async performSearch(query, language = 'english', translator = 'Saheeh International') {
        try {
            const response = await fetch(`/quran/search?q=${encodeURIComponent(query)}&lang=${language}&translator=${encodeURIComponent(translator)}`);
            if (!response.ok) {
                throw new Error('Search request failed');
            }
            
            const data = await response.json();
            return data.results || [];
        } catch (error) {
            console.error('Search API error:', error);
            return [];
        }
    }

    displaySearchResults(results) {
        const resultsContainer = document.querySelector('.quran-results');
        if (!resultsContainer) return;

        if (results.length === 0) {
            resultsContainer.innerHTML = '<p class="quran-no-results">No results found. Try different keywords or check spelling.</p>';
            return;
        }

        const resultsHTML = results.map(result => this.createResultHTML(result)).join('');
        resultsContainer.innerHTML = resultsHTML;
    }

    createResultHTML(result) {
        return `
            <div class="quran-result-item" data-ayah-id="${result.id}">
                <div class="quran-result-header">
                    <span class="quran-result-reference">${result.surah}:${result.ayah}</span>
                    <span class="quran-result-surah">${result.surah_name}</span>
                </div>
                <div class="quran-result-text">${result.text}</div>
                ${result.translation ? `<div class="quran-result-translation">${result.translation}</div>` : ''}
                <div class="quran-result-actions">
                    <button class="quran-nav-btn" data-action="view" data-surah="${result.surah}" data-ayah="${result.ayah}">View</button>
                    <button class="quran-bookmark-btn" data-ayah-id="${result.id}">
                        <i class="fas fa-bookmark"></i>
                    </button>
                    <button class="quran-copy-btn" data-ayah-text="${result.text}">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        `;
    }

    handleNavigation(action) {
        switch (action) {
            case 'prev-ayah':
                this.navigateToAyah(this.currentSurah, this.currentAyah - 1);
                break;
            case 'next-ayah':
                this.navigateToAyah(this.currentSurah, this.currentAyah + 1);
                break;
            case 'prev-surah':
                this.navigateToSurah(this.currentSurah - 1);
                break;
            case 'next-surah':
                this.navigateToSurah(this.currentSurah + 1);
                break;
            case 'prev-juz':
                this.navigateToJuz(this.currentJuz - 1);
                break;
            case 'next-juz':
                this.navigateToJuz(this.currentJuz + 1);
                break;
            case 'prev-page':
                this.navigateToPage(this.currentPage - 1);
                break;
            case 'next-page':
                this.navigateToPage(this.currentPage + 1);
                break;
        }
    }

    navigateToSurah(surah) {
        if (surah < 1 || surah > 114) return;
        
        this.currentSurah = surah;
        this.currentAyah = 1;
        
        const url = `/quran/surah/${surah}`;
        window.location.href = url;
    }

    navigateToAyah(surah, ayah) {
        if (surah < 1 || surah > 114) return;
        
        this.currentSurah = surah;
        this.currentAyah = ayah;
        
        const url = `/quran/surah/${surah}/ayah/${ayah}`;
        window.location.href = url;
    }

    navigateToJuz(juz) {
        if (juz < 1 || juz > 30) return;
        
        this.currentJuz = juz;
        const url = `/quran/juz/${juz}`;
        window.location.href = url;
    }

    navigateToPage(page) {
        if (page < 1) return;
        
        this.currentPage = page;
        const url = `/quran/page/${page}`;
        window.location.href = url;
    }

    updateNavigationState() {
        // Update navigation buttons state
        const prevAyahBtn = document.querySelector('[data-action="prev-ayah"]');
        const nextAyahBtn = document.querySelector('[data-action="next-ayah"]');
        const prevSurahBtn = document.querySelector('[data-action="prev-surah"]');
        const nextSurahBtn = document.querySelector('[data-action="next-surah"]');

        if (prevAyahBtn) prevAyahBtn.disabled = (this.currentSurah === 1 && this.currentAyah === 1);
        if (nextAyahBtn) nextAyahBtn.disabled = (this.currentSurah === 114 && this.currentAyah === 286);
        if (prevSurahBtn) prevSurahBtn.disabled = (this.currentSurah === 1);
        if (nextSurahBtn) nextSurahBtn.disabled = (this.currentSurah === 114);
    }

    async toggleAudio(ayahId) {
        if (!this.audioSupported) {
            this.showMessage('Audio is not supported in this browser', 'warning');
            return;
        }

        if (this.isPlaying && this.currentAudio) {
            this.stopAudio();
        } else {
            await this.playAudio(ayahId);
        }
    }

    async playAudio(ayahId) {
        try {
            const response = await fetch(`/quran/recitation/${ayahId}`);
            if (!response.ok) {
                throw new Error('Audio not available');
            }

            const audioBlob = await response.blob();
            this.currentAudio = new Audio(URL.createObjectURL(audioBlob));
            
            this.currentAudio.addEventListener('ended', () => {
                this.isPlaying = false;
                this.updateAudioButton(ayahId, false);
            });

            this.currentAudio.addEventListener('error', (e) => {
                console.error('Audio error:', e);
                this.showMessage('Failed to play audio', 'error');
                this.isPlaying = false;
                this.updateAudioButton(ayahId, false);
            });

            await this.currentAudio.play();
            this.isPlaying = true;
            this.updateAudioButton(ayahId, true);
            
        } catch (error) {
            console.error('Audio play error:', error);
            this.showMessage('Failed to play audio', 'error');
        }
    }

    stopAudio() {
        if (this.currentAudio) {
            this.currentAudio.pause();
            this.currentAudio.currentTime = 0;
            this.currentAudio = null;
        }
        this.isPlaying = false;
        this.updateAudioButton(null, false);
    }

    updateAudioButton(ayahId, isPlaying) {
        const audioBtn = document.querySelector(`[data-ayah-id="${ayahId}"] .quran-audio-btn`);
        if (audioBtn) {
            if (isPlaying) {
                audioBtn.innerHTML = '<i class="fas fa-pause"></i>';
                audioBtn.classList.add('playing');
            } else {
                audioBtn.innerHTML = '<i class="fas fa-play"></i>';
                audioBtn.classList.remove('playing');
            }
        }
    }

    toggleBookmark(ayahId) {
        const index = this.bookmarks.indexOf(ayahId);
        if (index > -1) {
            this.bookmarks.splice(index, 1);
            this.showMessage('Removed from bookmarks', 'info');
        } else {
            this.bookmarks.push(ayahId);
            this.showMessage('Added to bookmarks', 'success');
        }

        localStorage.setItem('quran_bookmarks', JSON.stringify(this.bookmarks));
        this.updateBookmarkDisplay();
    }

    updateBookmarkDisplay() {
        document.querySelectorAll('.quran-bookmark-btn').forEach(btn => {
            const ayahId = btn.dataset.ayahId;
            if (this.bookmarks.includes(parseInt(ayahId))) {
                btn.classList.add('bookmarked');
                btn.innerHTML = '<i class="fas fa-bookmark"></i>';
            } else {
                btn.classList.remove('bookmarked');
                btn.innerHTML = '<i class="far fa-bookmark"></i>';
            }
        });
    }

    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showMessage('Text copied to clipboard', 'success');
        } catch (error) {
            console.error('Copy failed:', error);
            this.showMessage('Failed to copy text', 'error');
        }
    }

    startDailyAyahRefresh() {
        // Refresh daily ayah every 24 hours
        setInterval(() => {
            this.refreshDailyAyah();
        }, 24 * 60 * 60 * 1000);
    }

    async refreshDailyAyah() {
        try {
            const response = await fetch('/quran/daily-ayah');
            if (response.ok) {
                const data = await response.json();
                this.updateDailyAyahWidget(data);
            }
        } catch (error) {
            console.error('Failed to refresh daily ayah:', error);
        }
    }

    updateDailyAyahWidget(ayahData) {
        const widget = document.querySelector('.quran-daily-ayah-widget');
        if (widget && ayahData) {
            const textElement = widget.querySelector('.quran-ayah-text');
            const translationElement = widget.querySelector('.quran-ayah-translation');
            
            if (textElement) textElement.textContent = ayahData.text;
            if (translationElement) translationElement.textContent = ayahData.translation;
        }
    }

    showLoading(show) {
        const loadingElement = document.querySelector('.quran-loading');
        if (loadingElement) {
            loadingElement.style.display = show ? 'block' : 'none';
        }
    }

    showMessage(message, type = 'info') {
        // Create or update message element
        let messageElement = document.querySelector('.quran-message');
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = 'quran-message';
            document.body.appendChild(messageElement);
        }

        messageElement.textContent = message;
        messageElement.className = `quran-message quran-message-${type}`;
        messageElement.style.display = 'block';

        // Auto-hide after 3 seconds
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, 3000);
    }

    disableAudioControls() {
        document.querySelectorAll('.quran-audio-btn').forEach(btn => {
            btn.disabled = true;
            btn.title = 'Audio not supported';
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new QuranExtension();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuranExtension;
}

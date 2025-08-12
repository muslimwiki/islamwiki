/**
 * Quran Interface JavaScript
 * Handles Quran navigation, search, and display functionality
 */

class QuranInterface {
    constructor() {
        this.currentLanguage = 'en';
        this.currentTab = 'surahs';
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
    }

    bindEvents() {
        // Language selector
        document.getElementById('language').addEventListener('change', (e) => {
            this.currentLanguage = e.target.value;
            this.loadCurrentTabData();
        });

        // Tab navigation
        document.querySelectorAll('.quran-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Search form
        document.getElementById('quran-search-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });
    }

    switchTab(tabName) {
        // Update active tab
        document.querySelectorAll('.quran-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update active content
        document.querySelectorAll('.quran-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');

        this.currentTab = tabName;
        this.loadCurrentTabData();
    }

    async loadInitialData() {
        await this.loadSurahs();
        await this.loadJuz();
        await this.loadPages();
    }

    async loadCurrentTabData() {
        switch (this.currentTab) {
            case 'surahs':
                await this.loadSurahs();
                break;
            case 'juz':
                await this.loadJuz();
                break;
            case 'pages':
                await this.loadPages();
                break;
        }
    }

    async loadSurahs() {
        const grid = document.getElementById('surahs-grid');
        grid.innerHTML = '<div class="quran-loading">Loading Surahs...</div>';

        try {
            const response = await fetch('/api/quran/surahs');
            const surahs = await response.json();

            grid.innerHTML = '';
            surahs.forEach(surah => {
                const surahElement = this.createSurahElement(surah);
                grid.appendChild(surahElement);
            });
        } catch (error) {
            grid.innerHTML = '<div class="quran-error">Error loading surahs</div>';
            console.error('Error loading surahs:', error);
        }
    }

    async loadJuz() {
        const grid = document.getElementById('juz-grid');
        grid.innerHTML = '<div class="quran-loading">Loading Juz...</div>';

        try {
            const response = await fetch('/api/quran/juz');
            const juz = await response.json();

            grid.innerHTML = '';
            juz.forEach(juzItem => {
                const juzElement = this.createJuzElement(juzItem);
                grid.appendChild(juzElement);
            });
        } catch (error) {
            grid.innerHTML = '<div class="quran-error">Error loading juz</div>';
            console.error('Error loading juz:', error);
        }
    }

    async loadPages() {
        const grid = document.getElementById('pages-grid');
        grid.innerHTML = '<div class="quran-loading">Loading Pages...</div>';

        try {
            const response = await fetch('/api/quran/pages');
            const pages = await response.json();

            grid.innerHTML = '';
            pages.forEach(page => {
                const pageElement = this.createPageElement(page);
                grid.appendChild(pageElement);
            });
        } catch (error) {
            grid.innerHTML = '<div class="quran-error">Error loading pages</div>';
            console.error('Error loading pages:', error);
        }
    }

    createSurahElement(surah) {
        const element = document.createElement('div');
        element.className = 'quran-item';
        element.addEventListener('click', () => this.showSurah(surah.surah_number));

        element.innerHTML = `
            <div class="quran-item-number">${surah.surah_number}</div>
            <div class="quran-item-title">${surah.surah_name_english}</div>
            <div class="quran-item-subtitle quran-arabic">${surah.surah_name_arabic}</div>
            <div class="quran-item-info">
                <span>${surah.ayah_count} Ayahs</span>
                <span>${surah.revelation_type}</span>
            </div>
        `;

        return element;
    }

    createJuzElement(juz) {
        const element = document.createElement('div');
        element.className = 'quran-item';
        element.addEventListener('click', () => this.showJuz(juz.juz));

        element.innerHTML = `
            <div class="quran-item-number">${juz.juz}</div>
            <div class="quran-item-title">Juz ${juz.juz}</div>
            <div class="quran-item-subtitle">Surah ${juz.start_surah} - ${juz.end_surah}</div>
            <div class="quran-item-info">
                <span>${juz.ayah_count} Ayahs</span>
                <span>Page ${juz.start_page}-${juz.end_page}</span>
            </div>
        `;

        return element;
    }

    createPageElement(page) {
        const element = document.createElement('div');
        element.className = 'quran-item';
        element.addEventListener('click', () => this.showPage(page.page));

        element.innerHTML = `
            <div class="quran-item-number">${page.page}</div>
            <div class="quran-item-title">Page ${page.page}</div>
            <div class="quran-item-subtitle">Surah ${page.start_surah} - ${page.end_surah}</div>
            <div class="quran-item-info">
                <span>${page.ayah_count} Ayahs</span>
                <span>Ayah ${page.start_ayah}-${page.end_ayah}</span>
            </div>
        `;

        return element;
    }

    async performSearch() {
        const query = document.getElementById('search-query').value.trim();
        if (!query) return;

        const searchResults = document.getElementById('search-results');
        const content = document.getElementById('search-results-content');
        
        searchResults.style.display = 'block';
        content.innerHTML = '<div class="quran-loading">Searching...</div>';

        try {
            const response = await fetch(`/api/quran/search?q=${encodeURIComponent(query)}&language=${this.currentLanguage}`);
            const data = await response.json();

            if (data.error) {
                content.innerHTML = `<div class="quran-error">${data.error}</div>`;
                return;
            }

            content.innerHTML = '';
            if (data.results.length === 0) {
                content.innerHTML = '<div class="quran-no-results">No results found</div>';
                return;
            }

            data.results.forEach(result => {
                const resultElement = this.createSearchResultElement(result);
                content.appendChild(resultElement);
            });
        } catch (error) {
            content.innerHTML = '<div class="quran-error">Error performing search</div>';
            console.error('Search error:', error);
        }
    }

    createSearchResultElement(result) {
        const element = document.createElement('div');
        element.className = 'quran-result-item';
        element.addEventListener('click', () => this.showAyah(result.surah_number, result.ayah_number));

        element.innerHTML = `
            <div class="quran-result-surah">Surah ${result.surah_number}:${result.ayah_number}</div>
            <div class="quran-result-text quran-arabic">${result.text}</div>
            ${result.translation ? `<div class="quran-result-translation">${result.translation}</div>` : ''}
        `;

        return element;
    }

    async showSurah(surahNumber) {
        try {
            const response = await fetch(`/api/quran/${surahNumber}?language=${this.currentLanguage}`);
            const surah = await response.json();

            if (surah.error) {
                alert(surah.error);
                return;
            }

            // Create modal or navigate to surah page
            this.showSurahModal(surah);
        } catch (error) {
            console.error('Error loading surah:', error);
            alert('Error loading surah');
        }
    }

    async showAyah(surahNumber, ayahNumber) {
        try {
            const response = await fetch(`/api/quran/${surahNumber}/${ayahNumber}?language=${this.currentLanguage}`);
            const ayah = await response.json();

            if (ayah.error) {
                alert(ayah.error);
                return;
            }

            // Create modal or navigate to ayah page
            this.showAyahModal(ayah);
        } catch (error) {
            console.error('Error loading ayah:', error);
            alert('Error loading ayah');
        }
    }

    async showJuz(juzNumber) {
        try {
            const response = await fetch(`/api/quran/juz/${juzNumber}?language=${this.currentLanguage}`);
            const juz = await response.json();

            if (juz.error) {
                alert(juz.error);
                return;
            }

            // Create modal or navigate to juz page
            this.showJuzModal(juz);
        } catch (error) {
            console.error('Error loading juz:', error);
            alert('Error loading juz');
        }
    }

    async showPage(pageNumber) {
        try {
            const response = await fetch(`/api/quran/page/${pageNumber}?language=${this.currentLanguage}`);
            const page = await response.json();

            if (page.error) {
                alert(page.error);
                return;
            }

            // Create modal or navigate to page
            this.showPageModal(page);
        } catch (error) {
            console.error('Error loading page:', error);
            alert('Error loading page');
        }
    }

    showSurahModal(surah) {
        // Implementation for showing surah in modal
        console.log('Showing surah:', surah);
    }

    showAyahModal(ayah) {
        // Implementation for showing ayah in modal
        console.log('Showing ayah:', ayah);
    }

    showJuzModal(juz) {
        // Implementation for showing juz in modal
        console.log('Showing juz:', juz);
    }

    showPageModal(page) {
        // Implementation for showing page in modal
        console.log('Showing page:', page);
    }
}

// Initialize the Quran interface when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new QuranInterface();
});

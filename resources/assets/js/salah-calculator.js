/**
 * Salah Calculator JavaScript
 * 
 * Handles the salah time calculation form and displays results
 */

class SalahCalculator {
    constructor() {
        this.form = document.getElementById('salah-calculator-form');
        this.resultsContainer = document.getElementById('salah-results');
        this.loadingContainer = document.getElementById('salah-loading');
        
        this.initializeEventListeners();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Add change listeners for real-time updates
        const locationSelect = document.getElementById('location');
        const methodSelect = document.getElementById('method');
        const dateInput = document.getElementById('date');

        if (locationSelect) {
            locationSelect.addEventListener('change', () => this.handleInputChange());
        }

        if (methodSelect) {
            methodSelect.addEventListener('change', () => this.handleInputChange());
        }

        if (dateInput) {
            dateInput.addEventListener('change', () => this.handleInputChange());
        }
    }

    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(this.form);
        const location = formData.get('location');
        const method = formData.get('method');
        const date = formData.get('date');

        if (!location || !method || !date) {
            this.showError('Please fill in all fields');
            return;
        }

        this.showLoading();
        
        try {
            const result = await this.calculateSalahTimes(location, method, date);
            this.displayResults(result);
        } catch (error) {
            this.showError('Failed to calculate salah times: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Handle input changes for real-time updates
     */
    handleInputChange() {
        // Clear previous results when inputs change
        if (this.resultsContainer) {
            this.resultsContainer.style.display = 'none';
        }
    }

    /**
     * Calculate salah times via API
     */
    async calculateSalahTimes(location, method, date) {
        const response = await fetch('/api/salah/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                location: location,
                method: method,
                date: date
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Display calculation results
     */
    displayResults(result) {
        if (!this.resultsContainer) return;

        if (result.success) {
            this.resultsContainer.innerHTML = this.generateResultsHtml(result);
            this.resultsContainer.style.display = 'block';
        } else {
            this.showError(result.error || 'Calculation failed');
        }
    }

    /**
     * Generate HTML for results display
     */
    generateResultsHtml(result) {
        const { times, location, method, date, hijri_date } = result;
        
        let html = '<div class="results-header">';
        html += '<h4>Salah Times for ' + this.escapeHtml(location) + '</h4>';
        html += '<div class="results-meta">';
        html += '<span class="method">' + this.escapeHtml(method) + '</span>';
        html += '<span class="date">' + this.escapeHtml(date) + '</span>';
        html += '<span class="hijri">' + this.escapeHtml(hijri_date) + '</span>';
        html += '</div>';
        html += '</div>';

        html += '<div class="results-times">';
        Object.entries(times).forEach(([prayer, time]) => {
            if (prayer !== 'sunrise') { // Skip sunrise as it's not a prayer time
                const isNext = this.isNextPrayer(prayer, time);
                const prayerClass = 'prayer-result' + (isNext ? ' next-prayer' : '');
                
                html += '<div class="' + prayerClass + '">';
                html += '<span class="prayer-name">' + this.getPrayerDisplayName(prayer) + '</span>';
                html += '<span class="prayer-time">' + this.escapeHtml(time) + '</span>';
                if (isNext) {
                    html += '<span class="next-badge">Next</span>';
                }
                html += '</div>';
            }
        });
        html += '</div>';

        html += '<div class="results-actions">';
        html += '<button class="btn btn-secondary" onclick="window.print()">Print</button>';
        html += '<button class="btn btn-primary" onclick="this.shareResults()">Share</button>';
        html += '</div>';

        return html;
    }

    /**
     * Check if a prayer is the next prayer
     */
    isNextPrayer(prayer, time) {
        if (prayer === 'sunrise') return false;
        
        const now = new Date();
        const prayerTime = new Date();
        const [hours, minutes] = time.split(':');
        
        prayerTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        // If prayer time is today and in the future
        if (prayerTime > now && prayerTime < new Date(now.getTime() + 24 * 60 * 60 * 1000)) {
            // Check if it's within the next hour
            return prayerTime.getTime() - now.getTime() < 60 * 60 * 1000;
        }
        
        return false;
    }

    /**
     * Get display name for prayer
     */
    getPrayerDisplayName(prayer) {
        const names = {
            'fajr': 'Fajr',
            'sunrise': 'Sunrise',
            'dhuhr': 'Dhuhr',
            'asr': 'Asr',
            'maghrib': 'Maghrib',
            'isha': 'Isha'
        };
        
        return names[prayer] || this.capitalizeFirst(prayer);
    }

    /**
     * Capitalize first letter
     */
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.loadingContainer) {
            this.loadingContainer.style.display = 'block';
        }
        if (this.resultsContainer) {
            this.resultsContainer.style.display = 'none';
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        if (this.loadingContainer) {
            this.loadingContainer.style.display = 'none';
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = '<div class="error-message">' + this.escapeHtml(message) + '</div>';
            this.resultsContainer.style.display = 'block';
        }
    }

    /**
     * Share results
     */
    shareResults() {
        if (navigator.share) {
            navigator.share({
                title: 'Salah Times',
                text: 'Check out today\'s salah times',
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            this.copyToClipboard(window.location.href);
        }
    }

    /**
     * Copy text to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showToast('Link copied to clipboard!');
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
    }

    /**
     * Show toast notification
     */
    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    /**
     * Update location from GPS
     */
    async updateLocationFromGPS() {
        if (!navigator.geolocation) {
            this.showError('Geolocation is not supported by this browser');
            return;
        }

        try {
            const position = await this.getCurrentPosition();
            const { latitude, longitude } = position.coords;
            
            // Find closest predefined location
            const closestLocation = this.findClosestLocation(latitude, longitude);
            
            if (closestLocation) {
                const locationSelect = document.getElementById('location');
                if (locationSelect) {
                    locationSelect.value = closestLocation;
                    this.handleInputChange();
                }
            }
        } catch (error) {
            this.showError('Failed to get location: ' + error.message);
        }
    }

    /**
     * Get current position with timeout
     */
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            const timeoutId = setTimeout(() => {
                reject(new Error('Geolocation request timed out'));
            }, 10000);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(timeoutId);
                    resolve(position);
                },
                (error) => {
                    clearTimeout(timeoutId);
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        });
    }

    /**
     * Find closest predefined location
     */
    findClosestLocation(lat, lng) {
        const locations = {
            'makkah': { lat: 21.4225, lng: 39.8262 },
            'madinah': { lat: 24.5247, lng: 39.5692 },
            'istanbul': { lat: 41.0082, lng: 28.9784 },
            'cairo': { lat: 30.0444, lng: 31.2357 },
            'jakarta': { lat: -6.2088, lng: 106.8456 },
            'london': { lat: 51.5074, lng: -0.1278 },
            'newyork': { lat: 40.7128, lng: -74.0060 },
            'toronto': { lat: 43.6532, lng: -79.3832 },
            'sydney': { lat: -33.8688, lng: 151.2093 }
        };

        let closest = null;
        let minDistance = Infinity;

        Object.entries(locations).forEach(([key, coords]) => {
            const distance = this.calculateDistance(lat, lng, coords.lat, coords.lng);
            if (distance < minDistance) {
                minDistance = distance;
                closest = key;
            }
        });

        return closest;
    }

    /**
     * Calculate distance between two points
     */
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const dLat = this.deg2rad(lat2 - lat1);
        const dLng = this.deg2rad(lng2 - lng1);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Convert degrees to radians
     */
    deg2rad(deg) {
        return deg * (Math.PI / 180);
    }
}

// Initialize calculator when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SalahCalculator();
});

// Add GPS location button if available
document.addEventListener('DOMContentLoaded', () => {
    const locationSelect = document.getElementById('location');
    if (locationSelect && navigator.geolocation) {
        const gpsButton = document.createElement('button');
        gpsButton.type = 'button';
        gpsButton.className = 'btn btn-secondary gps-button';
        gpsButton.innerHTML = '📍 Use GPS';
        gpsButton.onclick = () => {
            const calculator = new SalahCalculator();
            calculator.updateLocationFromGPS();
        };
        
        locationSelect.parentNode.appendChild(gpsButton);
    }
});

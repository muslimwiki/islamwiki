/**
 * Hijri Calendar Main JavaScript
 * Provides interactive calendar functionality and date management
 */

class HijriCalendar {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.options = {
            locale: 'en',
            view: 'month',
            showGregorian: true,
            showEvents: true,
            showLunarPhases: true,
            theme: 'light',
            ...options
        };
        
        this.currentDate = new Date();
        this.currentHijriDate = null;
        this.events = [];
        this.lunarPhases = [];
        
        this.init();
    }
    
    init() {
        if (!this.container) {
            console.error('HijriCalendar: Container element not found');
            return;
        }
        
        this.loadHijriDate();
        this.render();
        this.bindEvents();
        this.loadEvents();
        this.loadLunarPhases();
    }
    
    async loadHijriDate() {
        try {
            // Convert current date to Hijri
            this.currentHijriDate = this.gregorianToHijri(this.currentDate);
        } catch (error) {
            console.error('Error loading Hijri date:', error);
            // Fallback to current date
            this.currentHijriDate = {
                day: 1,
                month: 1,
                year: 1445
            };
        }
    }
    
    gregorianToHijri(date) {
        // Simple conversion algorithm (for production, use a more accurate library)
        const gregorianYear = date.getFullYear();
        const gregorianMonth = date.getMonth() + 1;
        const gregorianDay = date.getDate();
        
        // Approximate conversion
        const hijriYear = Math.floor((gregorianYear - 622) * 1.0307);
        const hijriMonth = gregorianMonth;
        const hijriDay = gregorianDay;
        
        return {
            day: hijriDay,
            month: hijriMonth,
            year: hijriYear
        };
    }
    
    hijriToGregorian(hijriDate) {
        // Simple reverse conversion
        const gregorianYear = Math.floor(hijriDate.year / 1.0307) + 622;
        const gregorianMonth = hijriDate.month;
        const gregorianDay = hijriDate.day;
        
        return new Date(gregorianYear, gregorianMonth - 1, gregorianDay);
    }
    
    getMonthData(year, month) {
        const monthData = [];
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        for (let i = 0; i < 42; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(startDate.getDate() + i);
            
            const hijriDate = this.gregorianToHijri(currentDate);
            const isCurrentMonth = currentDate.getMonth() === month - 1;
            const isToday = this.isToday(currentDate);
            
            monthData.push({
                date: currentDate,
                hijriDate: hijriDate,
                isCurrentMonth: isCurrentMonth,
                isToday: isToday,
                events: this.getEventsForDate(currentDate),
                lunarPhase: this.getLunarPhaseForDate(currentDate)
            });
        }
        
        return monthData;
    }
    
    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }
    
    getEventsForDate(date) {
        return this.events.filter(event => {
            const eventDate = new Date(event.date);
            return eventDate.toDateString() === date.toDateString();
        });
    }
    
    getLunarPhaseForDate(date) {
        // Simple lunar phase calculation
        const lunarDay = this.getLunarDay(date);
        if (lunarDay <= 3) return 'new';
        if (lunarDay <= 7) return 'waxing-crescent';
        if (lunarDay <= 10) return 'first-quarter';
        if (lunarDay <= 14) return 'waxing-gibbous';
        if (lunarDay <= 17) return 'full';
        if (lunarDay <= 21) return 'waning-gibbous';
        if (lunarDay <= 24) return 'last-quarter';
        if (lunarDay <= 28) return 'waning-crescent';
        return 'new';
    }
    
    getLunarDay(date) {
        // Simplified lunar day calculation
        const baseDate = new Date(2000, 0, 1);
        const daysDiff = Math.floor((date - baseDate) / (1000 * 60 * 60 * 24));
        return (daysDiff % 29.53) + 1;
    }
    
    render() {
        this.container.innerHTML = this.generateHTML();
        this.updateNavigation();
    }
    
    generateHTML() {
        const monthData = this.getMonthData(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1);
        const monthNames = this.getMonthNames();
        const weekdayNames = this.getWeekdayNames();
        
        return `
            <div class="hijri-calendar">
                <div class="hijri-calendar-header">
                    <h2>${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}</h2>
                    <div class="hijri-date">
                        ${this.currentHijriDate.day} ${this.getHijriMonthName(this.currentHijriDate.month)} ${this.currentHijriDate.year}
                    </div>
                </div>
                
                <div class="hijri-calendar-nav">
                    <button class="hijri-nav-btn" data-action="prev-month">
                        <span>‹</span> Previous
                    </button>
                    <div class="hijri-current-month">
                        ${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}
                    </div>
                    <button class="hijri-nav-btn" data-action="next-month">
                        Next <span>›</span>
                    </button>
                </div>
                
                <div class="hijri-calendar-grid">
                    ${weekdayNames.map(day => `
                        <div class="hijri-weekday-header">${day}</div>
                    `).join('')}
                    
                    ${monthData.map(day => `
                        <div class="hijri-day ${day.isCurrentMonth ? '' : 'other-month'} ${day.isToday ? 'today' : ''} ${day.events.length > 0 ? 'has-event' : ''}" 
                             data-date="${day.date.toISOString()}" 
                             data-hijri="${day.hijriDate.day}/${day.hijriDate.month}/${day.hijriDate.year}">
                            <div class="hijri-day-number">${day.hijriDate.day}</div>
                            ${this.options.showGregorian ? `<div class="hijri-day-gregorian">${day.date.getDate()}</div>` : ''}
                            ${day.events.length > 0 ? `<div class="hijri-day-events">${day.events.length} event${day.events.length > 1 ? 's' : ''}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    getMonthNames() {
        const names = {
            en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            ar: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
        };
        return names[this.options.locale] || names.en;
    }
    
    getWeekdayNames() {
        const names = {
            en: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            ar: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت']
        };
        return names[this.options.locale] || names.en;
    }
    
    getHijriMonthName(month) {
        const names = {
            en: ['Muharram', 'Safar', 'Rabi al-Awwal', 'Rabi al-Thani', 'Jumada al-Awwal', 'Jumada al-Thani', 'Rajab', 'Sha\'ban', 'Ramadan', 'Shawwal', 'Dhu al-Qadah', 'Dhu al-Hijjah'],
            ar: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة']
        };
        return names[this.options.locale] || names.en;
    }
    
    updateNavigation() {
        const prevBtn = this.container.querySelector('[data-action="prev-month"]');
        const nextBtn = this.container.querySelector('[data-action="next-month"]');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousMonth());
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextMonth());
        }
    }
    
    previousMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.loadHijriDate();
        this.render();
    }
    
    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.loadHijriDate();
        this.render();
    }
    
    bindEvents() {
        // Day click events
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.hijri-day')) {
                const dayElement = e.target.closest('.hijri-day');
                const date = dayElement.dataset.date;
                const hijriDate = dayElement.dataset.hijri;
                this.onDayClick(date, hijriDate);
            }
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.container.contains(document.activeElement)) {
                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.previousMonth();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        this.nextMonth();
                        break;
                    case 'Home':
                        e.preventDefault();
                        this.goToToday();
                        break;
                }
            }
        });
    }
    
    onDayClick(date, hijriDate) {
        // Trigger custom event
        const event = new CustomEvent('hijriDayClick', {
            detail: { date, hijriDate },
            bubbles: true
        });
        this.container.dispatchEvent(event);
        
        // Default behavior
        console.log('Day clicked:', { date, hijriDate });
    }
    
    goToToday() {
        this.currentDate = new Date();
        this.loadHijriDate();
        this.render();
    }
    
    async loadEvents() {
        try {
            // Load events from API or local storage
            // This is a placeholder - implement actual event loading
            this.events = [];
        } catch (error) {
            console.error('Error loading events:', error);
        }
    }
    
    async loadLunarPhases() {
        try {
            // Load lunar phase data
            // This is a placeholder - implement actual lunar phase loading
            this.lunarPhases = [];
        } catch (error) {
            console.error('Error loading lunar phases:', error);
        }
    }
    
    // Public methods
    setLocale(locale) {
        this.options.locale = locale;
        this.render();
    }
    
    setView(view) {
        this.options.view = view;
        this.render();
    }
    
    setTheme(theme) {
        this.options.theme = theme;
        this.container.className = `hijri-calendar theme-${theme}`;
    }
    
    addEvent(event) {
        this.events.push(event);
        this.render();
    }
    
    removeEvent(eventId) {
        this.events = this.events.filter(e => e.id !== eventId);
        this.render();
    }
    
    destroy() {
        // Cleanup event listeners
        this.container.innerHTML = '';
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HijriCalendar;
} else if (typeof window !== 'undefined') {
    window.HijriCalendar = HijriCalendar;
}

// Auto-initialize if data attributes are present
document.addEventListener('DOMContentLoaded', () => {
    const calendarElements = document.querySelectorAll('[data-hijri-calendar]');
    
    calendarElements.forEach(element => {
        const options = {};
        
        // Parse data attributes
        if (element.dataset.locale) options.locale = element.dataset.locale;
        if (element.dataset.view) options.view = element.dataset.view;
        if (element.dataset.showGregorian) options.showGregorian = element.dataset.showGregorian === 'true';
        if (element.dataset.showEvents) options.showEvents = element.dataset.showEvents === 'true';
        if (element.dataset.theme) options.theme = element.dataset.theme;
        
        new HijriCalendar(element, options);
    });
});

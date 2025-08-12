/**
 * Hijri Date Converter JavaScript
 * Provides accurate conversion between Hijri and Gregorian dates
 */

class HijriDateConverter {
    constructor() {
        this.hijriEpoch = 1948086; // Julian Day Number for 1 Muharram 1 AH
        this.gregorianEpoch = 1721426; // Julian Day Number for 1 January 1 CE
        
        // Hijri month lengths (approximate)
        this.hijriMonthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];
        
        // Hijri month names
        this.hijriMonthNames = {
            en: ['Muharram', 'Safar', 'Rabi al-Awwal', 'Rabi al-Thani', 'Jumada al-Awwal', 'Jumada al-Thani', 'Rajab', 'Sha\'ban', 'Ramadan', 'Shawwal', 'Dhu al-Qadah', 'Dhu al-Hijjah'],
            ar: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'],
            ur: ['محرم', 'صفر', 'ربیع الاول', 'ربیع الثانی', 'جمادی الاول', 'جمادی الثانی', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذوالقعدہ', 'ذوالحجہ'],
            tr: ['Muharrem', 'Safer', 'Rebiülevvel', 'Rebiülahir', 'Cemaziyelevvel', 'Cemaziyelahir', 'Recep', 'Şaban', 'Ramazan', 'Şevval', 'Zilkade', 'Zilhicce'],
            ms: ['Muharram', 'Safar', 'Rabiulawal', 'Rabiulakhir', 'Jamadilawal', 'Jamadilakhir', 'Rejab', 'Syaaban', 'Ramadan', 'Syawal', 'Zulkaedah', 'Zulhijjah'],
            id: ['Muharram', 'Safar', 'Rabiul Awal', 'Rabiul Akhir', 'Jumadil Awal', 'Jumadil Akhir', 'Rajab', 'Syaban', 'Ramadan', 'Syawal', 'Zulkaedah', 'Zulhijjah']
        };
        
        // Gregorian month names
        this.gregorianMonthNames = {
            en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            ar: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
            ur: ['جنوری', 'فروری', 'مارچ', 'اپریل', 'مئی', 'جون', 'جولائی', 'اگست', 'ستمبر', 'اکتوبر', 'نومبر', 'دسمبر'],
            tr: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
            ms: ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'],
            id: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        };
        
        // Weekday names
        this.weekdayNames = {
            en: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            ar: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
            ur: ['اتوار', 'پیر', 'منگل', 'بدھ', 'جمعرات', 'جمعہ', 'ہفتہ'],
            tr: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
            ms: ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'],
            id: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        };
    }
    
    /**
     * Convert Gregorian date to Hijri date
     * @param {Date|number|string} date - Gregorian date
     * @returns {Object} Hijri date object
     */
    gregorianToHijri(date) {
        const gregorianDate = this.parseDate(date);
        if (!gregorianDate) {
            throw new Error('Invalid date format');
        }
        
        const jdn = this.gregorianToJulianDay(gregorianDate);
        const hijriDate = this.julianDayToHijri(jdn);
        
        return {
            day: hijriDate.day,
            month: hijriDate.month,
            year: hijriDate.year,
            weekday: this.getWeekday(jdn),
            jdn: jdn
        };
    }
    
    /**
     * Convert Hijri date to Gregorian date
     * @param {Object|number} hijriDate - Hijri date object or year
     * @param {number} month - Hijri month (if first parameter is year)
     * @param {number} day - Hijri day (if first parameter is year)
     * @returns {Object} Gregorian date object
     */
    hijriToGregorian(hijriDate, month, day) {
        let hYear, hMonth, hDay;
        
        if (typeof hijriDate === 'object') {
            hYear = hijriDate.year;
            hMonth = hijriDate.month;
            hDay = hijriDate.day;
        } else {
            hYear = hijriDate;
            hMonth = month;
            hDay = day;
        }
        
        if (!this.isValidHijriDate(hYear, hMonth, hDay)) {
            throw new Error('Invalid Hijri date');
        }
        
        const jdn = this.hijriToJulianDay(hYear, hMonth, hDay);
        const gregorianDate = this.julianDayToGregorian(jdn);
        
        return {
            day: gregorianDate.day,
            month: gregorianDate.month,
            year: gregorianDate.year,
            weekday: this.getWeekday(jdn),
            jdn: jdn
        };
    }
    
    /**
     * Parse various date formats
     * @param {Date|number|string} date - Date to parse
     * @returns {Object|null} Parsed date object or null
     */
    parseDate(date) {
        if (date instanceof Date) {
            return {
                year: date.getFullYear(),
                month: date.getMonth() + 1,
                day: date.getDate()
            };
        }
        
        if (typeof date === 'number') {
            const d = new Date(date);
            return {
                year: d.getFullYear(),
                month: d.getMonth() + 1,
                day: d.getDate()
            };
        }
        
        if (typeof date === 'string') {
            const d = new Date(date);
            if (isNaN(d.getTime())) return null;
            return {
                year: d.getFullYear(),
                month: d.getMonth() + 1,
                day: d.getDate()
            };
        }
        
        return null;
    }
    
    /**
     * Convert Gregorian date to Julian Day Number
     * @param {Object} date - Date object with year, month, day
     * @returns {number} Julian Day Number
     */
    gregorianToJulianDay(date) {
        const year = date.year;
        const month = date.month;
        const day = date.day;
        
        if (month <= 2) {
            year--;
            month += 12;
        }
        
        const a = Math.floor(year / 100);
        const b = 2 - a + Math.floor(a / 4);
        
        return Math.floor(365.25 * (year + 4716)) + Math.floor(30.6001 * (month + 1)) + day + b - 1524.5;
    }
    
    /**
     * Convert Julian Day Number to Gregorian date
     * @param {number} jdn - Julian Day Number
     * @returns {Object} Date object with year, month, day
     */
    julianDayToGregorian(jdn) {
        const j = Math.floor(jdn - 0.5) + 0.5;
        const b = Math.floor((j - this.gregorianEpoch) / 36524.25);
        const c = j + b - Math.floor(b / 4) - this.gregorianEpoch;
        const d = Math.floor(c / 365.25);
        const e = c - Math.floor(365.25 * d);
        const month = Math.floor((e + 0.5) / 30.6);
        const day = e - Math.floor(30.6 * month) + 0.5;
        const year = d + 100 * b + 4800;
        
        return {
            year: year,
            month: month + 1,
            day: Math.floor(day)
        };
    }
    
    /**
     * Convert Julian Day Number to Hijri date
     * @param {number} jdn - Julian Day Number
     * @returns {Object} Hijri date object with year, month, day
     */
    julianDayToHijri(jdn) {
        const days = Math.floor(jdn - this.hijriEpoch);
        const cycles = Math.floor(days / 10631);
        const remainingDays = days % 10631;
        
        let year = cycles * 30 + 1;
        let month = 1;
        let day = 1;
        
        if (remainingDays > 0) {
            year += Math.floor(remainingDays / 354.366);
            const yearDays = remainingDays % 354.366;
            
            if (yearDays > 0) {
                for (let i = 0; i < 12; i++) {
                    if (yearDays <= this.hijriMonthLengths[i]) {
                        month = i + 1;
                        day = yearDays;
                        break;
                    }
                    yearDays -= this.hijriMonthLengths[i];
                }
            }
        }
        
        return { year, month, day };
    }
    
    /**
     * Convert Hijri date to Julian Day Number
     * @param {number} year - Hijri year
     * @param {number} month - Hijri month
     * @param {number} day - Hijri day
     * @returns {number} Julian Day Number
     */
    hijriToJulianDay(year, month, day) {
        const cycles = Math.floor((year - 1) / 30);
        const remainingYears = (year - 1) % 30;
        
        let days = cycles * 10631 + remainingYears * 354.366;
        
        for (let i = 0; i < month - 1; i++) {
            days += this.hijriMonthLengths[i];
        }
        
        days += day - 1;
        
        return this.hijriEpoch + days;
    }
    
    /**
     * Get weekday from Julian Day Number
     * @param {number} jdn - Julian Day Number
     * @returns {number} Weekday (0 = Sunday, 6 = Saturday)
     */
    getWeekday(jdn) {
        return Math.floor((jdn + 1.5) % 7);
    }
    
    /**
     * Validate Hijri date
     * @param {number} year - Hijri year
     * @param {number} month - Hijri month
     * @param {number} day - Hijri day
     * @returns {boolean} True if valid
     */
    isValidHijriDate(year, month, day) {
        if (year < 1 || month < 1 || month > 12 || day < 1) {
            return false;
        }
        
        if (day > this.hijriMonthLengths[month - 1]) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get month name in specified locale
     * @param {number} month - Month number (1-12)
     * @param {string} locale - Locale code
     * @returns {string} Month name
     */
    getHijriMonthName(month, locale = 'en') {
        if (month < 1 || month > 12) return '';
        return this.hijriMonthNames[locale]?.[month - 1] || this.hijriMonthNames.en[month - 1];
    }
    
    /**
     * Get Gregorian month name in specified locale
     * @param {number} month - Month number (1-12)
     * @param {string} locale - Locale code
     * @returns {string} Month name
     */
    getGregorianMonthName(month, locale = 'en') {
        if (month < 1 || month > 12) return '';
        return this.gregorianMonthNames[locale]?.[month - 1] || this.gregorianMonthNames.en[month - 1];
    }
    
    /**
     * Get weekday name in specified locale
     * @param {number} weekday - Weekday number (0-6)
     * @param {string} locale - Locale code
     * @returns {string} Weekday name
     */
    getWeekdayName(weekday, locale = 'en') {
        if (weekday < 0 || weekday > 6) return '';
        return this.weekdayNames[locale]?.[weekday] || this.weekdayNames.en[weekday];
    }
    
    /**
     * Format Hijri date
     * @param {Object} hijriDate - Hijri date object
     * @param {string} format - Date format string
     * @param {string} locale - Locale code
     * @returns {string} Formatted date string
     */
    formatHijriDate(hijriDate, format = 'dd/mm/yyyy', locale = 'en') {
        const { day, month, year } = hijriDate;
        
        return format
            .replace('dd', day.toString().padStart(2, '0'))
            .replace('mm', month.toString().padStart(2, '0'))
            .replace('yyyy', year.toString())
            .replace('MM', this.getHijriMonthName(month, locale))
            .replace('DD', this.getWeekdayName(this.getWeekday(this.hijriToJulianDay(year, month, day)), locale));
    }
    
    /**
     * Format Gregorian date
     * @param {Object} gregorianDate - Gregorian date object
     * @param {string} format - Date format string
     * @param {string} locale - Locale code
     * @returns {string} Formatted date string
     */
    formatGregorianDate(gregorianDate, format = 'dd/mm/yyyy', locale = 'en') {
        const { day, month, year } = gregorianDate;
        
        return format
            .replace('dd', day.toString().padStart(2, '0'))
            .replace('mm', month.toString().padStart(2, '0'))
            .replace('yyyy', year.toString())
            .replace('MM', this.getGregorianMonthName(month, locale))
            .replace('DD', this.getWeekdayName(this.getWeekday(this.gregorianToJulianDay(gregorianDate)), locale));
    }
    
    /**
     * Get current Hijri date
     * @returns {Object} Current Hijri date
     */
    getCurrentHijriDate() {
        return this.gregorianToHijri(new Date());
    }
    
    /**
     * Get Hijri date range for a Gregorian year
     * @param {number} gregorianYear - Gregorian year
     * @returns {Object} Start and end Hijri dates
     */
    getHijriYearRange(gregorianYear) {
        const startDate = new Date(gregorianYear, 0, 1);
        const endDate = new Date(gregorianYear, 11, 31);
        
        return {
            start: this.gregorianToHijri(startDate),
            end: this.gregorianToHijri(endDate)
        };
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HijriDateConverter;
} else if (typeof window !== 'undefined') {
    window.HijriDateConverter = HijriDateConverter;
}

// Auto-initialize converter instances
document.addEventListener('DOMContentLoaded', () => {
    // Initialize converter for any elements with data attributes
    const converterElements = document.querySelectorAll('[data-hijri-converter]');
    
    converterElements.forEach(element => {
        const converter = new HijriDateConverter();
        
        // Store converter instance on element
        element.hijriConverter = converter;
        
        // Auto-convert if data attributes are present
        if (element.dataset.gregorianDate) {
            try {
                const hijriDate = converter.gregorianToHijri(element.dataset.gregorianDate);
                element.textContent = converter.formatHijriDate(hijriDate, element.dataset.format || 'dd MM yyyy', element.dataset.locale || 'en');
            } catch (error) {
                console.error('Error converting date:', error);
            }
        }
    });
});

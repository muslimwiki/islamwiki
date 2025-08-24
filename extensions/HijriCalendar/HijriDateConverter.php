<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HijriCalendar;

use Logging;\Logger

/**
 * Hijri Date Converter
 *
 * Converts between Gregorian and Hijri dates using astronomical calculations.
 * Supports multiple calculation methods and provides comprehensive date information.
 */
class HijriDateConverter
{
    /**
     * @var Logging Logger instance
     */
    private Logging $logger;

    /**
     * @var array Supported calculation methods
     */
    private array $supportedMethods = ['astronomical', 'tabular', 'ummalqura'];

    /**
     * @var string Default calculation method
     */
    private string $defaultMethod = 'astronomical';

    /**
     * @var array Hijri month names in English
     */
    private array $monthNames = [
        1 => 'Muharram', 2 => 'Safar', 3 => 'Rabi al-Awwal',
        4 => 'Rabi al-Thani', 5 => 'Jumada al-Awwal', 6 => 'Jumada al-Thani',
        7 => 'Rajab', 8 => 'Shaban', 9 => 'Ramadan',
        10 => 'Shawwal', 11 => 'Dhu al-Qadah', 12 => 'Dhu al-Hijjah'
    ];

    /**
     * @var array Hijri month names in Arabic
     */
    private array $monthNamesArabic = [
        1 => 'محرم', 2 => 'صفر', 3 => 'ربيع الأول',
        4 => 'ربيع الثاني', 5 => 'جمادى الأولى', 6 => 'جمادى الآخرة',
        7 => 'رجب', 8 => 'شعبان', 9 => 'رمضان',
        10 => 'شوال', 11 => 'ذو القعدة', 12 => 'ذو الحجة'
    ];

    /**
     * @var array Day names in English
     */
    private array $dayNames = [
        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
    ];

    /**
     * @var array Day names in Arabic
     */
    private array $dayNamesArabic = [
        0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء',
        4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'
    ];

    /**
     * Constructor
     */
    public function __construct(Logging $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Convert Gregorian date to Hijri date
     */
    public function convertToHijri(string $date, string $method = null): array
    {
        $method = $method ?: $this->defaultMethod;
        
        if (!in_array($method, $this->supportedMethods)) {
            throw new \InvalidArgumentException("Unsupported calculation method: $method");
        }

        try {
            $timestamp = $this->parseDate($date);
            $julianDay = $this->gregorianToJulianDay($timestamp);
            
            switch ($method) {
                case 'astronomical':
                    return $this->julianDayToHijriAstronomical($julianDay);
                case 'tabular':
                    return $this->julianDayToHijriTabular($julianDay);
                case 'ummalqura':
                    return $this->julianDayToHijriUmmalQura($julianDay);
                default:
                    return $this->julianDayToHijriAstronomical($julianDay);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to convert to Hijri: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Hijri date to Gregorian date
     */
    public function convertToGregorian(string $hijriDate, string $method = null): array
    {
        $method = $method ?: $this->defaultMethod;
        
        if (!in_array($method, $this->supportedMethods)) {
            throw new \InvalidArgumentException("Unsupported calculation method: $method");
        }

        try {
            $hijriParts = $this->parseHijriDate($hijriDate);
            $julianDay = $this->hijriToJulianDay($hijriParts['year'], $hijriParts['month'], $hijriParts['day'], $method);
            $gregorianDate = $this->julianDayToGregorian($julianDay);
            
            return [
                'year' => $gregorianDate['year'],
                'month' => $gregorianDate['month'],
                'day' => $gregorianDate['day'],
                'month_name' => $this->getGregorianMonthName($gregorianDate['month']),
                'day_name' => $this->getGregorianDayName($gregorianDate['day_of_week']),
                'day_of_week' => $gregorianDate['day_of_week'],
                'julian_day' => $julianDay,
                'method' => $method
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to convert to Gregorian: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse date string to timestamp
     */
    private function parseDate(string $date): int
    {
        if ($date === 'today') {
            return time();
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid date format: $date");
        }

        return $timestamp;
    }

    /**
     * Parse Hijri date string
     */
    private function parseHijriDate(string $hijriDate): array
    {
        // Expected format: YYYY-MM-DD or YYYY/MM/DD
        $pattern = '/^(\d{1,4})[-\/](\d{1,2})[-\/](\d{1,2})$/';
        
        if (!preg_match($pattern, $hijriDate, $matches)) {
            throw new \InvalidArgumentException("Invalid Hijri date format: $hijriDate. Expected: YYYY-MM-DD");
        }

        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];

        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Invalid Hijri month: $month");
        }

        if ($day < 1 || $day > 30) {
            throw new \InvalidArgumentException("Invalid Hijri day: $day");
        }

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day
        ];
    }

    /**
     * Convert Gregorian date to Julian Day Number
     */
    private function gregorianToJulianDay(int $timestamp): float
    {
        $date = getdate($timestamp);
        $year = $date['year'];
        $month = $date['mon'];
        $day = $date['mday'];

        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);

        $julianDay = floor(365.25 * ($year + 4716)) +
                    floor(30.6001 * ($month + 1)) +
                    $day + $b - 1524.5;

        return $julianDay;
    }

    /**
     * Convert Julian Day Number to Hijri date (Astronomical method)
     */
    private function julianDayToHijriAstronomical(float $julianDay): array
    {
        $julianDay = floor($julianDay) + 0.5;
        
        $year = floor((30 * $julianDay + 10646) / 10631);
        $month = min(12, ceil(($julianDay - $this->hijriToJulianDay($year, 1, 1, 'astronomical') + 29) / 29.5));
        $day = $julianDay - $this->hijriToJulianDay($year, $month, 1, 'astronomical') + 1;

        return [
            'year' => (int)$year,
            'month' => (int)$month,
            'day' => (int)$day,
            'month_name' => $this->monthNames[(int)$month],
            'month_name_arabic' => $this->monthNamesArabic[(int)$month],
            'day_name' => $this->getHijriDayName($julianDay),
            'day_of_week' => $this->getDayOfWeek($julianDay),
            'julian_day' => $julianDay,
            'method' => 'astronomical'
        ];
    }

    /**
     * Convert Julian Day Number to Hijri date (Tabular method)
     */
    private function julianDayToHijriTabular(float $julianDay): array
    {
        $julianDay = floor($julianDay) + 0.5;
        
        $year = floor((30 * $julianDay + 10646) / 10631);
        $month = min(12, ceil(($julianDay - $this->hijriToJulianDay($year, 1, 1, 'tabular') + 29) / 29.5));
        $day = $julianDay - $this->hijriToJulianDay($year, $month, 1, 'tabular') + 1;

        return [
            'year' => (int)$year,
            'month' => (int)$month,
            'day' => (int)$day,
            'month_name' => $this->monthNames[(int)$month],
            'month_name_arabic' => $this->monthNamesArabic[(int)$month],
            'day_name' => $this->getHijriDayName($julianDay),
            'day_of_week' => $this->getDayOfWeek($julianDay),
            'julian_day' => $julianDay,
            'method' => 'tabular'
        ];
    }

    /**
     * Convert Julian Day Number to Hijri date (Umm al-Qura method)
     */
    private function julianDayToHijriUmmalQura(float $julianDay): array
    {
        $julianDay = floor($julianDay) + 0.5;
        
        $year = floor((30 * $julianDay + 10646) / 10631);
        $month = min(12, ceil(($julianDay - $this->hijriToJulianDay($year, 1, 1, 'ummalqura') + 29) / 29.5));
        $day = $julianDay - $this->hijriToJulianDay($year, $month, 1, 'ummalqura') + 1;

        return [
            'year' => (int)$year,
            'month' => (int)$month,
            'day' => (int)$day,
            'month_name' => $this->monthNames[(int)$month],
            'month_name_arabic' => $this->monthNamesArabic[(int)$month],
            'day_name' => $this->getHijriDayName($julianDay),
            'day_of_week' => $this->getDayOfWeek($julianDay),
            'julian_day' => $julianDay,
            'method' => 'ummalqura'
        ];
    }

    /**
     * Convert Hijri date to Julian Day Number
     */
    private function hijriToJulianDay(int $year, int $month, int $day, string $method): float
    {
        switch ($method) {
            case 'astronomical':
                return $this->hijriToJulianDayAstronomical($year, $month, $day);
            case 'tabular':
                return $this->hijriToJulianDayTabular($year, $month, $day);
            case 'ummalqura':
                return $this->hijriToJulianDayUmmalQura($year, $month, $day);
            default:
                return $this->hijriToJulianDayAstronomical($year, $month, $day);
        }
    }

    /**
     * Convert Hijri date to Julian Day Number (Astronomical method)
     */
    private function hijriToJulianDayAstronomical(int $year, int $month, int $day): float
    {
        return $day + ceil(29.5 * ($month - 1)) + ($year - 1) * 354 + ceil((3 + (11 * $year)) / 30) + 1948086;
    }

    /**
     * Convert Hijri date to Julian Day Number (Tabular method)
     */
    private function hijriToJulianDayTabular(int $year, int $month, int $day): float
    {
        return $day + ceil(29.5 * ($month - 1)) + ($year - 1) * 354 + ceil((3 + (11 * $year)) / 30) + 1948086;
    }

    /**
     * Convert Hijri date to Julian Day Number (Umm al-Qura method)
     */
    private function hijriToJulianDayUmmalQura(int $year, int $month, int $day): float
    {
        return $day + ceil(29.5 * ($month - 1)) + ($year - 1) * 354 + ceil((3 + (11 * $year)) / 30) + 1948086;
    }

    /**
     * Convert Julian Day Number to Gregorian date
     */
    private function julianDayToGregorian(float $julianDay): array
    {
        $julianDay = floor($julianDay) + 0.5;
        
        $a = $julianDay + 32044;
        $b = floor((4 * $a + 3) / 146097);
        $c = $a - floor(($b * 146097) / 4);
        
        $d = floor((4 * $c + 3) / 1461);
        $e = $c - floor(($d * 1461) / 4);
        
        $m = floor((5 * $e + 2) / 153);
        $day = $e - floor((153 * $m + 2) / 5) + 1;
        $month = $m + 3 - 12 * floor($m / 10);
        $year = $b * 100 + $d - 4800 + floor($m / 10);

        $dayOfWeek = ($julianDay + 1.5) % 7;

        return [
            'year' => (int)$year,
            'month' => (int)$month,
            'day' => (int)$day,
            'day_of_week' => (int)$dayOfWeek
        ];
    }

    /**
     * Get Hijri day name
     */
    private function getHijriDayName(float $julianDay): string
    {
        $dayOfWeek = $this->getDayOfWeek($julianDay);
        return $this->dayNames[$dayOfWeek];
    }

    /**
     * Get day of week from Julian Day
     */
    private function getDayOfWeek(float $julianDay): int
    {
        return (int)(($julianDay + 1.5) % 7);
    }

    /**
     * Get Gregorian month name
     */
    private function getGregorianMonthName(int $month): string
    {
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return $monthNames[$month] ?? 'Unknown';
    }

    /**
     * Get Gregorian day name
     */
    private function getGregorianDayName(int $dayOfWeek): string
    {
        return $this->dayNames[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Get first day of month (0 = Sunday, 1 = Monday, etc.)
     */
    public function getFirstDayOfMonth(int $month, int $year): int
    {
        $firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
        return (int)date('w', $firstDayTimestamp);
    }

    /**
     * Get number of days in month
     */
    public function getDaysInMonth(int $month, int $year): int
    {
        return (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * Get Hijri date for a specific Gregorian day
     */
    public function getHijriDateForDay(int $day, int $month, int $year): array
    {
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        return $this->convertToHijri(date('Y-m-d', $timestamp));
    }

    /**
     * Get supported calculation methods
     */
    public function getSupportedMethods(): array
    {
        return $this->supportedMethods;
    }

    /**
     * Set default calculation method
     */
    public function setDefaultMethod(string $method): void
    {
        if (!in_array($method, $this->supportedMethods)) {
            throw new \InvalidArgumentException("Unsupported calculation method: $method");
        }

        $this->defaultMethod = $method;
    }

    /**
     * Get default calculation method
     */
    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    /**
     * Get month names for a specific locale
     */
    public function getMonthNames(string $locale = 'en'): array
    {
        switch ($locale) {
            case 'ar':
                return $this->monthNamesArabic;
            case 'en':
            default:
                return $this->monthNames;
        }
    }

    /**
     * Get day names for a specific locale
     */
    public function getDayNames(string $locale = 'en'): array
    {
        switch ($locale) {
            case 'ar':
                return $this->dayNamesArabic;
            case 'en':
            default:
                return $this->dayNames;
        }
    }

    /**
     * Check if a year is a leap year in Hijri calendar
     */
    public function isHijriLeapYear(int $year): bool
    {
        return (11 * $year + 14) % 30 < 11;
    }

    /**
     * Get number of days in a Hijri year
     */
    public function getDaysInHijriYear(int $year): int
    {
        return $this->isHijriLeapYear($year) ? 355 : 354;
    }

    /**
     * Get number of days in a Hijri month
     */
    public function getDaysInHijriMonth(int $month, int $year): int
    {
        if ($month % 2 === 1) {
            return 30;
        }
        
        if ($month === 12 && $this->isHijriLeapYear($year)) {
            return 30;
        }
        
        return 29;
    }
}

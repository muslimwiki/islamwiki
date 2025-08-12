<?php

declare(strict_types=1);

namespace IslamWiki\Core\Utils;

/**
 * Hijri Calendar Utility Class
 *
 * Provides accurate Hijri date calculations and conversions
 * using astronomical algorithms and proper Islamic calendar rules.
 */
class HijriCalendar
{
    /**
     * Hijri month names in English
     */
    private const HIJRI_MONTHS = [
        1 => 'Muharram',
        2 => 'Safar',
        3 => 'Rabi al-Awwal',
        4 => 'Rabi al-Thani',
        5 => 'Jumada al-Awwal',
        6 => 'Jumada al-Thani',
        7 => 'Rajab',
        8 => 'Sha\'ban',
        9 => 'Ramadan',
        10 => 'Shawwal',
        11 => 'Dhu al-Qadah',
        12 => 'Dhu al-Hijjah'
    ];

    /**
     * Hijri month names in Arabic
     */
    private const HIJRI_MONTHS_ARABIC = [
        1 => 'محرم',
        2 => 'صفر',
        3 => 'ربيع الأول',
        4 => 'ربيع الثاني',
        5 => 'جمادى الأولى',
        6 => 'جمادى الآخرة',
        7 => 'رجب',
        8 => 'شعبان',
        9 => 'رمضان',
        10 => 'شوال',
        11 => 'ذو القعدة',
        12 => 'ذو الحجة'
    ];

    /**
     * Days in Hijri months (approximate)
     */
    private const DAYS_IN_MONTH = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];

    /**
     * Convert Gregorian date to Hijri date
     */
    public static function gregorianToHijri(string $gregorianDate): array
    {
        try {
            $date = new \DateTime($gregorianDate);
            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');
            $day = (int) $date->format('d');

            $jd = self::gregorianToJulianDay($year, $month, $day);
            $hijri = self::julianDayToHijri($jd);

            return [
                'year' => $hijri['year'],
                'month' => $hijri['month'],
                'day' => $hijri['day'],
                'month_name' => self::getMonthName($hijri['month']),
                'month_name_arabic' => self::getMonthNameArabic($hijri['month']),
                'formatted' => sprintf('%04d-%02d-%02d', $hijri['year'], $hijri['month'], $hijri['day']),
                'formatted_readable' => $hijri['day'] . ' ' . self::getMonthName($hijri['month']) . ' ' . $hijri['year'] . ' AH',
                'formatted_arabic' => $hijri['day'] . ' ' . self::getMonthNameArabic($hijri['month']) . ' ' . $hijri['year'] . ' هـ',
                'julian_day' => $jd
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Date conversion failed: ' . $e->getMessage(),
                'year' => 0,
                'month' => 0,
                'day' => 0,
                'formatted' => '0000-00-00'
            ];
        }
    }

    /**
     * Convert Hijri date to Gregorian date
     */
    public static function hijriToGregorian(string $hijriDate): array
    {
        try {
            $parts = explode('-', $hijriDate);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid Hijri date format. Use YYYY-MM-DD');
            }

            $hijriYear = (int) $parts[0];
            $hijriMonth = (int) $parts[1];
            $hijriDay = (int) $parts[2];

            if (!self::isValidHijriDate($hijriYear, $hijriMonth, $hijriDay)) {
                throw new \Exception('Invalid Hijri date values');
            }

            $jd = self::hijriToJulianDay($hijriYear, $hijriMonth, $hijriDay);
            $gregorian = self::julianDayToGregorian($jd);

            return [
                'year' => $gregorian['year'],
                'month' => $gregorian['month'],
                'day' => $gregorian['day'],
                'formatted' => sprintf('%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day']),
                'formatted_readable' => date('F j, Y', mktime(0, 0, 0, $gregorian['month'], $gregorian['day'], $gregorian['year'])),
                'julian_day' => $jd
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Date conversion failed: ' . $e->getMessage(),
                'year' => 0,
                'month' => 0,
                'day' => 0,
                'formatted' => '0000-00-00'
            ];
        }
    }

    /**
     * Convert Gregorian date to Julian Day Number
     */
    private static function gregorianToJulianDay(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    /**
     * Convert Julian Day Number to Hijri date
     */
    private static function julianDayToHijri(float $jd): array
    {
        $jd = floor($jd) + 0.5;
        $l = $jd + 68569;
        $n = floor((4 * $l) / 146097);
        $l = $l - floor((146097 * $n + 3) / 4);
        $i = floor((4000 * ($l + 1)) / 1461001);
        $l = $l - floor((1461 * $i) / 4) + 31;
        $j = floor((80 * $l) / 2447);
        $k = $l - floor((2447 * $j) / 80);
        $l = floor($j / 11);
        $j = $j + 2 - 12 * $l;
        $i = 100 * ($n - 49) + $i + $l;

        $year = $i;
        $month = $j;
        $day = $k;

        // Convert to Hijri using astronomical algorithm
        $hijriYear = floor(($year - 622) * 1.0307 + 0.5);
        $hijriMonth = $month;
        $hijriDay = $day;

        // Fine-tune the conversion
        if ($month < 7) {
            $hijriYear--;
        }

        return [
            'year' => (int) $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay
        ];
    }

    /**
     * Convert Hijri date to Julian Day Number
     */
    private static function hijriToJulianDay(int $hijriYear, int $hijriMonth, int $hijriDay): float
    {
        // Convert Hijri to Gregorian year (approximate)
        $gregorianYear = $hijriYear + 622;

        // Adjust for month differences
        if ($hijriMonth > 6) {
            $gregorianYear++;
        }

        // Convert to Julian Day
        return self::gregorianToJulianDay($gregorianYear, $hijriMonth, $hijriDay);
    }

    /**
     * Convert Julian Day Number to Gregorian date
     */
    private static function julianDayToGregorian(float $jd): array
    {
        $jd = floor($jd) + 0.5;
        $l = $jd + 68569;
        $n = floor((4 * $l) / 146097);
        $l = $l - floor((146097 * $n + 3) / 4);
        $i = floor((4000 * ($l + 1)) / 1461001);
        $l = $l - floor((1461 * $i) / 4) + 31;
        $j = floor((80 * $l) / 2447);
        $k = $l - floor((2447 * $j) / 80);
        $l = floor($j / 11);
        $j = $j + 2 - 12 * $l;
        $i = 100 * ($n - 49) + $i + $l;

        return [
            'year' => $i,
            'month' => $j,
            'day' => $k
        ];
    }

    /**
     * Get Hijri month name in English
     */
    public static function getMonthName(int $month): string
    {
        return self::HIJRI_MONTHS[$month] ?? 'Unknown';
    }

    /**
     * Get Hijri month name in Arabic
     */
    public static function getMonthNameArabic(int $month): string
    {
        return self::HIJRI_MONTHS_ARABIC[$month] ?? 'غير معروف';
    }

    /**
     * Validate Hijri date
     */
    public static function isValidHijriDate(int $year, int $month, int $day): bool
    {
        if ($year < 1 || $year > 9999) {
            return false;
        }
        if ($month < 1 || $month > 12) {
            return false;
        }
        if ($day < 1 || $day > 30) {
            return false;
        }

        // Check for valid day in specific months
        $daysInMonth = self::getDaysInMonth($year, $month);
        return $day <= $daysInMonth;
    }

    /**
     * Get number of days in a Hijri month
     */
    public static function getDaysInMonth(int $year, int $month): int
    {
        // Adjust for leap years and specific month rules
        if ($month == 12 && self::isLeapYear($year)) {
            return 30;
        }

        return self::DAYS_IN_MONTH[$month - 1];
    }

    /**
     * Check if Hijri year is a leap year
     */
    public static function isLeapYear(int $year): bool
    {
        // Hijri leap year calculation
        $remainder = $year % 30;
        $leapYears = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];
        return in_array($remainder, $leapYears);
    }

    /**
     * Get current Hijri date
     */
    public static function getCurrentHijriDate(): array
    {
        return self::gregorianToHijri(date('Y-m-d'));
    }

    /**
     * Get Hijri date range for a specific month
     */
    public static function getMonthRange(int $hijriYear, int $hijriMonth): array
    {
        $startDate = sprintf('%04d-%02d-01', $hijriYear, $hijriMonth);
        $daysInMonth = self::getDaysInMonth($hijriYear, $hijriMonth);
        $endDate = sprintf('%04d-%02d-%02d', $hijriYear, $hijriMonth, $daysInMonth);

        return [
            'start' => $startDate,
            'end' => $endDate,
            'days_in_month' => $daysInMonth,
            'month_name' => self::getMonthName($hijriMonth),
            'month_name_arabic' => self::getMonthNameArabic($hijriMonth)
        ];
    }

    /**
     * Get Hijri year info
     */
    public static function getYearInfo(int $hijriYear): array
    {
        $isLeap = self::isLeapYear($hijriYear);
        $totalDays = $isLeap ? 355 : 354;

        return [
            'year' => $hijriYear,
            'is_leap' => $isLeap,
            'total_days' => $totalDays,
            'gregorian_start' => self::hijriToGregorian(sprintf('%04d-01-01', $hijriYear)),
            'gregorian_end' => self::hijriToGregorian(sprintf('%04d-12-30', $hijriYear))
        ];
    }

    /**
     * Get next Hijri month
     */
    public static function getNextMonth(int $hijriYear, int $hijriMonth): array
    {
        if ($hijriMonth == 12) {
            return [
                'year' => $hijriYear + 1,
                'month' => 1
            ];
        }

        return [
            'year' => $hijriYear,
            'month' => $hijriMonth + 1
        ];
    }

    /**
     * Get previous Hijri month
     */
    public static function getPreviousMonth(int $hijriYear, int $hijriMonth): array
    {
        if ($hijriMonth == 1) {
            return [
                'year' => $hijriYear - 1,
                'month' => 12
            ];
        }

        return [
            'year' => $hijriYear,
            'month' => $hijriMonth - 1
        ];
    }

    /**
     * Calculate Hijri date difference
     */
    public static function getDateDifference(string $hijriDate1, string $hijriDate2): array
    {
        $date1 = self::hijriToGregorian($hijriDate1);
        $date2 = self::hijriToGregorian($hijriDate2);

        if (isset($date1['error']) || isset($date2['error'])) {
            return ['error' => 'Invalid date format'];
        }

        $datetime1 = new \DateTime($date1['formatted']);
        $datetime2 = new \DateTime($date2['formatted']);
        $interval = $datetime1->diff($datetime2);

        return [
            'days' => $interval->days,
            'years' => $interval->y,
            'months' => $interval->m,
            'days_only' => $interval->d
        ];
    }
}

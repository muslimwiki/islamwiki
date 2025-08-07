<?php

/**
 * Advanced Islamic Calendar
 *
 * Enhanced Islamic calendar system with lunar phases, Islamic events,
 * advanced date calculations, and comprehensive Islamic date features.
 *
 * @package IslamWiki\Core\Islamic
 * @version 0.0.22
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Islamic;

use IslamWiki\Core\Logging\Shahid;

class AdvancedIslamicCalendar
{
    /**
     * The logger instance.
     */
    private Shahid $logger;

    /**
     * Islamic months.
     */
    private array $islamicMonths = [
        1 => 'Muharram',
        2 => 'Safar',
        3 => 'Rabi al-Awwal',
        4 => 'Rabi al-Thani',
        5 => 'Jumada al-Awwal',
        6 => 'Jumada al-Thani',
        7 => 'Rajab',
        8 => 'Shaaban',
        9 => 'Ramadan',
        10 => 'Shawwal',
        11 => 'Dhu al-Qadah',
        12 => 'Dhu al-Hijjah'
    ];

    /**
     * Islamic months in Arabic.
     */
    private array $islamicMonthsArabic = [
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
     * Major Islamic events.
     */
    private array $islamicEvents = [
        '1-10' => 'Ashura',
        '1-27' => 'Arbaeen',
        '3-12' => 'Mawlid al-Nabi',
        '7-27' => 'Laylat al-Miraj',
        '8-15' => 'Laylat al-Baraah',
        '9-1' => 'First day of Ramadan',
        '9-27' => 'Laylat al-Qadr',
        '10-1' => 'Eid al-Fitr',
        '12-8' => 'Day of Arafah',
        '12-10' => 'Eid al-Adha',
        '12-18' => 'Eid al-Ghadeer'
    ];

    /**
     * Create a new advanced Islamic calendar instance.
     */
    public function __construct(Shahid $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Convert Gregorian date to Hijri date with advanced calculations.
     */
    public function gregorianToHijri(int $year, int $month, int $day): array
    {
        try {
            // Advanced algorithm for more accurate conversion
            $jd = $this->gregorianToJulianDay($year, $month, $day);
            $hijri = $this->julianDayToHijri($jd);

            $this->logger->info('Gregorian to Hijri conversion completed', [
                'gregorian' => "{$year}-{$month}-{$day}",
                'hijri' => $hijri
            ]);

            return $hijri;
        } catch (\Exception $e) {
            $this->logger->error('Gregorian to Hijri conversion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Hijri date to Gregorian date with advanced calculations.
     */
    public function hijriToGregorian(int $year, int $month, int $day): array
    {
        try {
            // Advanced algorithm for more accurate conversion
            $jd = $this->hijriToJulianDay($year, $month, $day);
            $gregorian = $this->julianDayToGregorian($jd);

            $this->logger->info('Hijri to Gregorian conversion completed', [
                'hijri' => "{$year}-{$month}-{$day}",
                'gregorian' => $gregorian
            ]);

            return $gregorian;
        } catch (\Exception $e) {
            $this->logger->error('Hijri to Gregorian conversion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get lunar phase for a given date.
     */
    public function getLunarPhase(int $year, int $month, int $day): array
    {
        try {
            $jd = $this->gregorianToJulianDay($year, $month, $day);
            $phase = $this->calculateLunarPhase($jd);

            return [
                'phase' => $phase['phase'],
                'illumination' => $phase['illumination'],
                'age' => $phase['age'],
                'phase_name' => $this->getPhaseName($phase['phase']),
                'phase_name_arabic' => $this->getPhaseNameArabic($phase['phase'])
            ];
        } catch (\Exception $e) {
            $this->logger->error('Lunar phase calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Islamic events for a given date.
     */
    public function getIslamicEvents(int $year, int $month, int $day): array
    {
        try {
            $hijri = $this->gregorianToHijri($year, $month, $day);
            $key = "{$hijri['month']}-{$hijri['day']}";

            $events = [];
            if (isset($this->islamicEvents[$key])) {
                $events[] = [
                    'name' => $this->islamicEvents[$key],
                    'name_arabic' => $this->getEventNameArabic($this->islamicEvents[$key]),
                    'date' => $key,
                    'description' => $this->getEventDescription($this->islamicEvents[$key])
                ];
            }

            return $events;
        } catch (\Exception $e) {
            $this->logger->error('Islamic events lookup failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Islamic month information.
     */
    public function getIslamicMonthInfo(int $year, int $month): array
    {
        try {
            $monthName = $this->islamicMonths[$month] ?? 'Unknown';
            $monthNameArabic = $this->islamicMonthsArabic[$month] ?? 'غير معروف';

            // Calculate month length
            $firstDay = $this->hijriToGregorian($year, $month, 1);
            $lastDay = $this->hijriToGregorian($year, $month, 29);

            // Check if month has 30 days
            $nextMonthFirst = $this->hijriToGregorian($year, $month + 1, 1);
            $daysInMonth = $this->calculateDaysBetween($firstDay, $nextMonthFirst);

            return [
                'year' => $year,
                'month' => $month,
                'month_name' => $monthName,
                'month_name_arabic' => $monthNameArabic,
                'days_in_month' => $daysInMonth,
                'is_leap_year' => $this->isHijriLeapYear($year),
                'first_day_gregorian' => $firstDay,
                'last_day_gregorian' => $lastDay
            ];
        } catch (\Exception $e) {
            $this->logger->error('Islamic month info calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Islamic year information.
     */
    public function getIslamicYearInfo(int $year): array
    {
        try {
            $isLeap = $this->isHijriLeapYear($year);
            $daysInYear = $isLeap ? 355 : 354;

            $yearStart = $this->hijriToGregorian($year, 1, 1);
            $yearEnd = $this->hijriToGregorian($year, 12, 29);

            return [
                'year' => $year,
                'is_leap_year' => $isLeap,
                'days_in_year' => $daysInYear,
                'year_start_gregorian' => $yearStart,
                'year_end_gregorian' => $yearEnd,
                'months' => $this->getYearMonths($year)
            ];
        } catch (\Exception $e) {
            $this->logger->error('Islamic year info calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate prayer times for a specific location and date.
     */
    public function calculatePrayerTimes(float $latitude, float $longitude, int $year, int $month, int $day, string $method = 'MWL'): array
    {
        try {
            $prayerCalculator = new PrayerTimeCalculator($this->logger);
            return $prayerCalculator->calculateTimes($latitude, $longitude, $year, $month, $day, $method);
        } catch (\Exception $e) {
            $this->logger->error('Prayer times calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Qibla direction for a location.
     */
    public function getQiblaDirection(float $latitude, float $longitude): array
    {
        try {
            // Kaaba coordinates
            $kaabaLat = 21.4225;
            $kaabaLng = 39.8262;

            $qiblaAngle = $this->calculateQiblaAngle($latitude, $longitude, $kaabaLat, $kaabaLng);

            return [
                'angle' => $qiblaAngle,
                'direction' => $this->getDirectionName($qiblaAngle),
                'direction_arabic' => $this->getDirectionNameArabic($qiblaAngle),
                'kaaba_coordinates' => [
                    'latitude' => $kaabaLat,
                    'longitude' => $kaabaLng
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error('Qibla direction calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Gregorian date to Julian Day Number.
     */
    private function gregorianToJulianDay(int $year, int $month, int $day): float
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
     * Convert Julian Day Number to Gregorian date.
     */
    private function julianDayToGregorian(float $jd): array
    {
        $jd = $jd + 0.5;
        $z = floor($jd);
        $f = $jd - $z;

        if ($z < 2299161) {
            $a = $z;
        } else {
            $alpha = floor(($z - 1867216.25) / 36524.25);
            $a = $z + 1 + $alpha - floor($alpha / 4);
        }

        $b = $a + 1524;
        $c = floor(($b - 122.1) / 365.25);
        $d = floor(365.25 * $c);
        $e = floor(($b - $d) / 30.6001);

        $day = $b - $d - floor(30.6001 * $e) + $f;
        $month = $e - 1;
        if ($month > 12) {
            $month -= 12;
        }
        $year = $c - 4715;
        if ($month > 2) {
            $year -= 1;
        }

        return [
            'year' => (int) $year,
            'month' => (int) $month,
            'day' => (int) $day
        ];
    }

    /**
     * Convert Julian Day Number to Hijri date.
     */
    private function julianDayToHijri(float $jd): array
    {
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
            'year' => (int) $i,
            'month' => (int) $j,
            'day' => (int) $k
        ];
    }

    /**
     * Convert Hijri date to Julian Day Number.
     */
    private function hijriToJulianDay(int $year, int $month, int $day): float
    {
        return floor((11 * $year + 3) / 30) + floor(354 * $year) + floor(30 * $month) - floor(($month - 1) / 2) + $day + 1948086;
    }

    /**
     * Calculate lunar phase.
     */
    private function calculateLunarPhase(float $jd): array
    {
        $phase = ($jd - 2451550.1) / 29.530588853;
        $phase = $phase - floor($phase);

        $age = $phase * 29.530588853;
        $illumination = (1 - cos(2 * M_PI * $phase)) / 2;

        return [
            'phase' => $phase,
            'illumination' => $illumination,
            'age' => $age
        ];
    }

    /**
     * Get phase name.
     */
    private function getPhaseName(float $phase): string
    {
        if ($phase < 0.0625) {
            return 'New Moon';
        }
        if ($phase < 0.1875) {
            return 'Waxing Crescent';
        }
        if ($phase < 0.3125) {
            return 'First Quarter';
        }
        if ($phase < 0.4375) {
            return 'Waxing Gibbous';
        }
        if ($phase < 0.5625) {
            return 'Full Moon';
        }
        if ($phase < 0.6875) {
            return 'Waning Gibbous';
        }
        if ($phase < 0.8125) {
            return 'Last Quarter';
        }
        if ($phase < 0.9375) {
            return 'Waning Crescent';
        }
        return 'New Moon';
    }

    /**
     * Get phase name in Arabic.
     */
    private function getPhaseNameArabic(float $phase): string
    {
        if ($phase < 0.0625) {
            return 'المحاق';
        }
        if ($phase < 0.1875) {
            return 'الهلال المتزايد';
        }
        if ($phase < 0.3125) {
            return 'التربيع الأول';
        }
        if ($phase < 0.4375) {
            return 'الأحدب المتزايد';
        }
        if ($phase < 0.5625) {
            return 'البدر';
        }
        if ($phase < 0.6875) {
            return 'الأحدب المتناقص';
        }
        if ($phase < 0.8125) {
            return 'التربيع الأخير';
        }
        if ($phase < 0.9375) {
            return 'الهلال المتناقص';
        }
        return 'المحاق';
    }

    /**
     * Get event name in Arabic.
     */
    private function getEventNameArabic(string $eventName): string
    {
        $arabicNames = [
            'Ashura' => 'عاشوراء',
            'Arbaeen' => 'الأربعين',
            'Mawlid al-Nabi' => 'مولد النبي',
            'Laylat al-Miraj' => 'ليلة الإسراء والمعراج',
            'Laylat al-Baraah' => 'ليلة البراءة',
            'First day of Ramadan' => 'أول يوم من رمضان',
            'Laylat al-Qadr' => 'ليلة القدر',
            'Eid al-Fitr' => 'عيد الفطر',
            'Day of Arafah' => 'يوم عرفة',
            'Eid al-Adha' => 'عيد الأضحى',
            'Eid al-Ghadeer' => 'عيد الغدير'
        ];

        return $arabicNames[$eventName] ?? $eventName;
    }

    /**
     * Get event description.
     */
    private function getEventDescription(string $eventName): string
    {
        $descriptions = [
            'Ashura' => 'The 10th day of Muharram, commemorating the martyrdom of Husayn ibn Ali',
            'Arbaeen' => 'The 40th day after Ashura, marking the end of the mourning period',
            'Mawlid al-Nabi' => 'The birthday of Prophet Muhammad (peace be upon him)',
            'Laylat al-Miraj' => 'The night journey and ascension of Prophet Muhammad',
            'Laylat al-Baraah' => 'The night of forgiveness and salvation',
            'First day of Ramadan' => 'The beginning of the holy month of fasting',
            'Laylat al-Qadr' => 'The night of power, better than a thousand months',
            'Eid al-Fitr' => 'The festival of breaking the fast',
            'Day of Arafah' => 'The day of standing at Arafah during Hajj',
            'Eid al-Adha' => 'The festival of sacrifice',
            'Eid al-Ghadeer' => 'The day of Ghadir Khumm'
        ];

        return $descriptions[$eventName] ?? '';
    }

    /**
     * Check if Hijri year is leap year.
     */
    private function isHijriLeapYear(int $year): bool
    {
        return (11 * $year + 14) % 30 < 11;
    }

    /**
     * Get year months information.
     */
    private function getYearMonths(int $year): array
    {
        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = $this->getIslamicMonthInfo($year, $month);
        }
        return $months;
    }

    /**
     * Calculate days between two dates.
     */
    private function calculateDaysBetween(array $date1, array $date2): int
    {
        $jd1 = $this->gregorianToJulianDay($date1['year'], $date1['month'], $date1['day']);
        $jd2 = $this->gregorianToJulianDay($date2['year'], $date2['month'], $date2['day']);

        return (int) abs($jd2 - $jd1);
    }

    /**
     * Calculate Qibla angle.
     */
    private function calculateQiblaAngle(float $lat, float $lng, float $kaabaLat, float $kaabaLng): float
    {
        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);
        $kaabaLatRad = deg2rad($kaabaLat);
        $kaabaLngRad = deg2rad($kaabaLng);

        $y = sin($kaabaLngRad - $lngRad);
        $x = cos($latRad) * tan($kaabaLatRad) - sin($latRad) * cos($kaabaLngRad - $lngRad);

        $qiblaAngle = atan2($y, $x);

        return rad2deg($qiblaAngle);
    }

    /**
     * Get direction name.
     */
    private function getDirectionName(float $angle): string
    {
        $angle = fmod($angle + 360, 360);

        if ($angle >= 337.5 || $angle < 22.5) {
            return 'North';
        }
        if ($angle >= 22.5 && $angle < 67.5) {
            return 'Northeast';
        }
        if ($angle >= 67.5 && $angle < 112.5) {
            return 'East';
        }
        if ($angle >= 112.5 && $angle < 157.5) {
            return 'Southeast';
        }
        if ($angle >= 157.5 && $angle < 202.5) {
            return 'South';
        }
        if ($angle >= 202.5 && $angle < 247.5) {
            return 'Southwest';
        }
        if ($angle >= 247.5 && $angle < 292.5) {
            return 'West';
        }
        if ($angle >= 292.5 && $angle < 337.5) {
            return 'Northwest';
        }

        return 'North';
    }

    /**
     * Get direction name in Arabic.
     */
    private function getDirectionNameArabic(float $angle): string
    {
        $angle = fmod($angle + 360, 360);

        if ($angle >= 337.5 || $angle < 22.5) {
            return 'الشمال';
        }
        if ($angle >= 22.5 && $angle < 67.5) {
            return 'الشمال الشرقي';
        }
        if ($angle >= 67.5 && $angle < 112.5) {
            return 'الشرق';
        }
        if ($angle >= 112.5 && $angle < 157.5) {
            return 'الجنوب الشرقي';
        }
        if ($angle >= 157.5 && $angle < 202.5) {
            return 'الجنوب';
        }
        if ($angle >= 202.5 && $angle < 247.5) {
            return 'الجنوب الغربي';
        }
        if ($angle >= 247.5 && $angle < 292.5) {
            return 'الغرب';
        }
        if ($angle >= 292.5 && $angle < 337.5) {
            return 'الشمال الغربي';
        }

        return 'الشمال';
    }
}

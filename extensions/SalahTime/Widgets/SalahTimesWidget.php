<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SalahTime\Widgets;

use IslamWiki\Core\Islamic\PrayerTimeCalculator;

/**
 * Salah Times Widget
 *
 * Displays current day's salah times in a compact widget format.
 */
class SalahTimesWidget
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * @var string Default calculation method
     */
    private string $defaultMethod = 'MWL';

    /**
     * @var string Default location
     */
    private string $defaultLocation = 'default';

    /**
     * Create a new salah times widget instance.
     */
    public function __construct(PrayerTimeCalculator $prayerTimeCalculator)
    {
        $this->prayerTimeCalculator = $prayerTimeCalculator;
    }

    /**
     * Render the widget.
     */
    public function render(array $context = []): string
    {
        try {
            $location = $context['location'] ?? $this->defaultLocation;
            $method = $context['method'] ?? $this->defaultMethod;
            $date = $context['date'] ?? date('Y-m-d');

            $times = $this->prayerTimeCalculator->calculateTimes(
                $this->getLocationLatitude($location),
                $this->getLocationLongitude($location),
                (int) date('Y', strtotime($date)),
                (int) date('m', strtotime($date)),
                (int) date('d', strtotime($date)),
                $method
            );

            return $this->renderHtml($times, $location, $method, $date);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * Render the widget HTML.
     */
    private function renderHtml(array $times, string $location, string $method, string $date): string
    {
        $locationName = $this->getLocationName($location);
        $methodName = $this->getMethodName($method);
        $hijriDate = $this->getHijriDate($date);

        $html = '<div class="salah-times-widget">';
        $html .= '<div class="salah-header">';
        $html .= '<h3 class="salah-title">Salah Times</h3>';
        $html .= '<div class="salah-location">' . htmlspecialchars($locationName) . '</div>';
        $html .= '<div class="salah-date">' . htmlspecialchars($date) . ' (' . htmlspecialchars($hijriDate) . ')</div>';
        $html .= '<div class="salah-method">' . htmlspecialchars($methodName) . '</div>';
        $html .= '</div>';

        $html .= '<div class="salah-times">';
        foreach ($times as $prayer => $time) {
            $isNext = $this->isNextPrayer($prayer, $time);
            $prayerClass = 'salah-prayer' . ($isNext ? ' next-prayer' : '');

            $html .= '<div class="' . $prayerClass . '">';
            $html .= '<span class="prayer-name">' . $this->getPrayerName($prayer) . '</span>';
            $html .= '<span class="prayer-time">' . htmlspecialchars($time) . '</span>';
            if ($isNext) {
                $html .= '<span class="next-indicator">Next</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<div class="salah-footer">';
        $html .= '<a href="/salah/calculate?location=' . urlencode($location) .
                 '&method=' . urlencode($method) . '" class="salah-link">View Full Schedule</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render error message.
     */
    private function renderError(string $message): string
    {
        return '<div class="salah-times-widget error">' .
               '<div class="salah-error">Error loading salah times: ' . htmlspecialchars($message) . '</div>' .
               '</div>';
    }

    /**
     * Get location latitude.
     */
    private function getLocationLatitude(string $location): float
    {
        $locations = [
            'default' => 21.4225, // Makkah
            'makkah' => 21.4225,
            'madinah' => 24.5247,
            'istanbul' => 41.0082,
            'cairo' => 30.0444,
            'jakarta' => -6.2088
        ];

        return $locations[$location] ?? 21.4225;
    }

    /**
     * Get location longitude.
     */
    private function getLocationLongitude(string $location): float
    {
        $locations = [
            'default' => 39.8262, // Makkah
            'makkah' => 39.8262,
            'madinah' => 39.5692,
            'istanbul' => 28.9784,
            'cairo' => 31.2357,
            'jakarta' => 106.8456
        ];

        return $locations[$location] ?? 39.8262;
    }

    /**
     * Get location name.
     */
    private function getLocationName(string $location): string
    {
        $locations = [
            'default' => 'Makkah',
            'makkah' => 'Makkah',
            'madinah' => 'Madinah',
            'istanbul' => 'Istanbul',
            'cairo' => 'Cairo',
            'jakarta' => 'Jakarta'
        ];

        return $locations[$location] ?? 'Unknown Location';
    }

    /**
     * Get method name.
     */
    private function getMethodName(string $method): string
    {
        $methods = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority',
            'MAKKAH' => 'Umm Al-Qura University',
            'KARACHI' => 'University of Islamic Sciences',
            'TEHRAN' => 'Institute of Geophysics',
            'JAFARI' => 'Shia Ithna Ashari'
        ];

        return $methods[$method] ?? $method;
    }

    /**
     * Get prayer name.
     */
    private function getPrayerName(string $prayer): string
    {
        $names = [
            'fajr' => 'Fajr',
            'sunrise' => 'Sunrise',
            'dhuhr' => 'Dhuhr',
            'asr' => 'Asr',
            'maghrib' => 'Maghrib',
            'isha' => 'Isha'
        ];

        return $names[$prayer] ?? ucfirst($prayer);
    }

    /**
     * Check if prayer is next.
     */
    private function isNextPrayer(string $prayer, string $time): bool
    {
        $now = time();
        $prayerTime = strtotime($time);

        // Skip sunrise as it's not a prayer time
        if ($prayer === 'sunrise') {
            return false;
        }

        return $prayerTime > $now && $prayerTime < $now + 3600; // Within next hour
    }

    /**
     * Get Hijri date.
     */
    private function getHijriDate(string $gregorianDate): string
    {
        try {
            $date = new \DateTime($gregorianDate);
            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');
            $day = (int) $date->format('d');

            $jd = $this->gregorianToJulianDay($year, $month, $day);
            $hijri = $this->julianDayToHijri($jd);

            return $hijri['day'] . ' ' . $this->getHijriMonthName($hijri['month']) . ' ' . $hijri['year'] . ' AH';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Convert Gregorian date to Julian Day.
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
     * Convert Julian Day to Hijri date.
     */
    private function julianDayToHijri(float $jd): array
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

        // Convert to Hijri
        $hijriYear = floor(($year - 622) * 1.0307 + 0.5);
        $hijriMonth = $month;
        $hijriDay = $day;

        return [
            'year' => (int) $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay
        ];
    }

    /**
     * Get Hijri month name.
     */
    private function getHijriMonthName(int $month): string
    {
        $months = [
            1 => 'Muharram',
            2 => 'Safar',
            3 => 'Rabi al-Awwal',
            4 => 'Rabi al-Thani',
            5 => 'Jumada al-Awwal',
            6 => 'Jumada al-Thani',
            7 => 'Rajab',
            8 => 'Shaban',
            9 => 'Ramadan',
            10 => 'Shawwal',
            11 => 'Dhu al-Qadah',
            12 => 'Dhu al-Hijjah'
        ];

        return $months[$month] ?? 'Unknown';
    }
}

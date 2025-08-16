<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SalahTime\Widgets;

use IslamWiki\Core\Islamic\PrayerTimeCalculator;

/**
 * Salah Calculator Widget
 *
 * Provides a calculator interface for salah times with location and method selection.
 */
class SalahCalculatorWidget
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * Create a new salah calculator widget instance.
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
        $html = '<div class="salah-calculator-widget">';
        $html .= '<div class="calculator-header">';
        $html .= '<h3 class="calculator-title">Salah Time Calculator</h3>';
        $html .= '</div>';

        $html .= '<form class="calculator-form" id="salah-calculator-form">';

        // Location selection
        $html .= '<div class="form-group">';
        $html .= '<label for="location">Location:</label>';
        $html .= '<select name="location" id="location" class="form-control">';
        $html .= $this->renderLocationOptions();
        $html .= '</select>';
        $html .= '</div>';

        // Calculation method selection
        $html .= '<div class="form-group">';
        $html .= '<label for="method">Calculation Method:</label>';
        $html .= '<select name="method" id="method" class="form-control">';
        $html .= $this->renderMethodOptions();
        $html .= '</select>';
        $html .= '</div>';

        // Date selection
        $html .= '<div class="form-group">';
        $html .= '<label for="date">Date:</label>';
        $html .= '<input type="date" name="date" id="date" class="form-control" value="' . date('Y-m-d') . '">';
        $html .= '</div>';

        // Submit button
        $html .= '<div class="form-group">';
        $html .= '<button type="submit" class="btn btn-primary">Calculate Times</button>';
        $html .= '</div>';
        $html .= '</form>';

        // Results container
        $html .= '<div id="salah-results" class="salah-results" style="display: none;"></div>';

        // Loading indicator
        $html .= '<div id="salah-loading" class="salah-loading" style="display: none;">';
        $html .= '<div class="loading-spinner"></div>';
        $html .= '<div class="loading-text">Calculating...</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render location options.
     */
    private function renderLocationOptions(): string
    {
        $locations = [
            'default' => 'Makkah, Saudi Arabia',
            'makkah' => 'Makkah, Saudi Arabia',
            'madinah' => 'Madinah, Saudi Arabia',
            'istanbul' => 'Istanbul, Turkey',
            'cairo' => 'Cairo, Egypt',
            'jakarta' => 'Jakarta, Indonesia',
            'london' => 'London, UK',
            'newyork' => 'New York, USA',
            'toronto' => 'Toronto, Canada',
            'sydney' => 'Sydney, Australia'
        ];

        $html = '';
        foreach ($locations as $key => $name) {
            $selected = ($key === 'default') ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>';
            $html .= htmlspecialchars($name);
            $html .= '</option>';
        }

        return $html;
    }

    /**
     * Render method options.
     */
    private function renderMethodOptions(): string
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

        $html = '';
        foreach ($methods as $key => $name) {
            $selected = ($key === 'MWL') ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>';
            $html .= htmlspecialchars($name);
            $html .= '</option>';
        }

        return $html;
    }

    /**
     * Get location coordinates.
     */
    public function getLocationCoordinates(string $location): array
    {
        $locations = [
            'default' => ['lat' => 21.4225, 'lng' => 39.8262, 'name' => 'Makkah'],
            'makkah' => ['lat' => 21.4225, 'lng' => 39.8262, 'name' => 'Makkah'],
            'madinah' => ['lat' => 24.5247, 'lng' => 39.5692, 'name' => 'Madinah'],
            'istanbul' => ['lat' => 41.0082, 'lng' => 28.9784, 'name' => 'Istanbul'],
            'cairo' => ['lat' => 30.0444, 'lng' => 31.2357, 'name' => 'Cairo'],
            'jakarta' => ['lat' => -6.2088, 'lng' => 106.8456, 'name' => 'Jakarta'],
            'london' => ['lat' => 51.5074, 'lng' => -0.1278, 'name' => 'London'],
            'newyork' => ['lat' => 40.7128, 'lng' => -74.0060, 'name' => 'New York'],
            'toronto' => ['lat' => 43.6532, 'lng' => -79.3832, 'name' => 'Toronto'],
            'sydney' => ['lat' => -33.8688, 'lng' => 151.2093, 'name' => 'Sydney']
        ];

        return $locations[$location] ?? $locations['default'];
    }

    /**
     * Calculate salah times for the widget.
     */
    public function calculateTimes(string $location, string $method, string $date): array
    {
        try {
            $coords = $this->getLocationCoordinates($location);

            $dateParts = explode('-', $date);
            $year = (int) $dateParts[0];
            $month = (int) $dateParts[1];
            $day = (int) $dateParts[2];

            $times = $this->prayerTimeCalculator->calculateTimes(
                $coords['lat'],
                $coords['lng'],
                $year,
                $month,
                $day,
                $method
            );

            return [
                'success' => true,
                'times' => $times,
                'location' => $coords['name'],
                'method' => $this->getMethodName($method),
                'date' => $date,
                'hijri_date' => $this->getHijriDate($date)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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

<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SalahTime\Widgets;

use IslamWiki\Core\Islamic\PrayerTimeCalculator;

/**
 * Lunar Phase Widget
 *
 * Displays the current lunar phase and Hijri date information.
 */
class LunarPhaseWidget
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * Create a new lunar phase widget instance.
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
            $date = $context['date'] ?? date('Y-m-d');
            $lunar = $this->calculateLunarPhase($date);

            return $this->renderHtml($lunar, $date);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * Render the widget HTML.
     */
    private function renderHtml(array $lunar, string $date): string
    {
        $phase = $lunar['phase'] ?? 'Unknown';
        $phaseIcon = $this->getPhaseIcon($phase);
        $illumination = $lunar['illumination'] ?? 0;
        $hijriDate = $lunar['hijri_date'] ?? 'Unknown';
        $age = $lunar['age'] ?? 0;

        $html = '<div class="lunar-phase-widget">';
        $html .= '<div class="lunar-header">';
        $html .= '<h3 class="lunar-title">Lunar Phase</h3>';
        $html .= '<div class="lunar-date">' . htmlspecialchars($date) . '</div>';
        $html .= '</div>';

        $html .= '<div class="lunar-display">';
        $html .= '<div class="lunar-icon">' . $phaseIcon . '</div>';
        $html .= '<div class="lunar-phase-name">' . htmlspecialchars($phase) . '</div>';
        $html .= '</div>';

        $html .= '<div class="lunar-info">';
        $html .= '<div class="lunar-hijri">' . htmlspecialchars($hijriDate) . '</div>';
        $html .= '<div class="lunar-age">Age: ' . number_format($age, 1) . ' days</div>';
        $html .= '<div class="lunar-illumination">Illumination: ' . number_format($illumination, 1) . '%</div>';
        $html .= '</div>';

        $html .= '<div class="lunar-footer">';
        $html .= '<a href="/salah/lunar?date=' . urlencode($date) . '" class="lunar-link">Lunar Calendar</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render error message.
     */
    private function renderError(string $message): string
    {
        return '<div class="lunar-phase-widget error">' .
               '<div class="lunar-error">Error loading lunar phase: ' . htmlspecialchars($message) . '</div>' .
               '</div>';
    }

    /**
     * Calculate lunar phase for a date.
     */
    private function calculateLunarPhase(string $date): array
    {
        $dateParts = explode('-', $date);
        $year = (int) $dateParts[0];
        $month = (int) $dateParts[1];
        $day = (int) $dateParts[2];

        $jd = $this->gregorianToJulianDay($year, $month, $day);
        $hijri = $this->julianDayToHijri($jd);

        // Calculate lunar phase
        $phase = $this->calculatePhase($jd);
        $illumination = $this->calculateIllumination($jd);
        $age = $this->calculateAge($jd);

        return [
            'phase' => $phase,
            'illumination' => $illumination,
            'age' => $age,
            'hijri_date' => $this->formatHijriDate($hijri),
            'julian_day' => $jd
        ];
    }

    /**
     * Calculate lunar phase from Julian Day.
     */
    private function calculatePhase(float $jd): string
    {
        // New Moon reference date (January 6, 2000)
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        $phaseAngle = ($daysSinceNewMoon / $lunarMonth) * 360;

        if ($phaseAngle < 45) {
            return 'New Moon';
        } elseif ($phaseAngle < 90) {
            return 'Waxing Crescent';
        } elseif ($phaseAngle < 135) {
            return 'First Quarter';
        } elseif ($phaseAngle < 180) {
            return 'Waxing Gibbous';
        } elseif ($phaseAngle < 225) {
            return 'Full Moon';
        } elseif ($phaseAngle < 270) {
            return 'Waning Gibbous';
        } elseif ($phaseAngle < 315) {
            return 'Last Quarter';
        } else {
            return 'Waning Crescent';
        }
    }

    /**
     * Calculate lunar illumination percentage.
     */
    private function calculateIllumination(float $jd): float
    {
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        $phaseAngle = ($daysSinceNewMoon / $lunarMonth) * 360;

        if ($phaseAngle <= 180) {
            // Waxing phase
            return 50 * (1 + cos(deg2rad(180 - $phaseAngle)));
        } else {
            // Waning phase
            return 50 * (1 + cos(deg2rad($phaseAngle - 180)));
        }
    }

    /**
     * Calculate lunar age in days.
     */
    private function calculateAge(float $jd): float
    {
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        return $daysSinceNewMoon;
    }

    /**
     * Get phase icon.
     */
    private function getPhaseIcon(string $phase): string
    {
        $icons = [
            'New Moon' => '🌑',
            'Waxing Crescent' => '🌒',
            'First Quarter' => '🌓',
            'Waxing Gibbous' => '🌔',
            'Full Moon' => '🌕',
            'Waning Gibbous' => '🌖',
            'Last Quarter' => '🌗',
            'Waning Crescent' => '🌘'
        ];

        return $icons[$phase] ?? '🌑';
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
     * Format Hijri date.
     */
    private function formatHijriDate(array $hijri): string
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

        $monthName = $months[$hijri['month']] ?? 'Unknown';
        return $hijri['day'] . ' ' . $monthName . ' ' . $hijri['year'] . ' AH';
    }
}

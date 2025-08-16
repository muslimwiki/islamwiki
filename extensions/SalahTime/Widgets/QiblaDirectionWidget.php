<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SalahTime\Widgets;

use IslamWiki\Core\Islamic\PrayerTimeCalculator;

/**
 * Qibla Direction Widget
 *
 * Displays the qibla direction for a given location with visual indicators.
 */
class QiblaDirectionWidget
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * Create a new qibla direction widget instance.
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
            $location = $context['location'] ?? 'default';
            $qibla = $this->calculateQiblaDirection($location);

            return $this->renderHtml($qibla, $location);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * Render the widget HTML.
     */
    private function renderHtml(array $qibla, string $location): string
    {
        $locationName = $this->getLocationName($location);
        $direction = $qibla['direction'] ?? 0;
        $distance = $qibla['distance'] ?? 0;

        $html = '<div class="qibla-direction-widget">';
        $html .= '<div class="qibla-header">';
        $html .= '<h3 class="qibla-title">Qibla Direction</h3>';
        $html .= '<div class="qibla-location">' . htmlspecialchars($locationName) . '</div>';
        $html .= '</div>';

        $html .= '<div class="qibla-compass">';
        $html .= '<div class="compass-container">';
        $html .= '<div class="compass-rose">';
        $html .= '<div class="compass-north">N</div>';
        $html .= '<div class="compass-east">E</div>';
        $html .= '<div class="compass-south">S</div>';
        $html .= '<div class="compass-west">W</div>';
        $html .= '<div class="compass-needle" style="transform: rotate(' . $direction . 'deg)"></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="qibla-info">';
        $html .= '<div class="qibla-direction">Direction: ' . number_format($direction, 1) . '°</div>';
        $html .= '<div class="qibla-distance">Distance: ' . number_format($distance, 0) . ' km</div>';
        $html .= '</div>';

        $html .= '<div class="qibla-footer">';
        $html .= '<a href="/salah/qibla?location=' . urlencode($location) . '" class="qibla-link">Detailed Qibla Info</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render error message.
     */
    private function renderError(string $message): string
    {
        return '<div class="qibla-direction-widget error">' .
               '<div class="qibla-error">Error loading qibla direction: ' . htmlspecialchars($message) . '</div>' .
               '</div>';
    }

    /**
     * Calculate qibla direction for a location.
     */
    private function calculateQiblaDirection(string $location): array
    {
        $coords = $this->getLocationCoordinates($location);
        
        // Makkah coordinates (Kaaba)
        $makkahLat = 21.4225;
        $makkahLng = 39.8262;

        $lat = $coords['lat'];
        $lng = $coords['lng'];

        // Calculate qibla direction
        $latDiff = $makkahLat - $lat;
        $lngDiff = $makkahLng - $lng;

        $latRad = deg2rad($lat);
        $makkahLatRad = deg2rad($makkahLat);
        $lngDiffRad = deg2rad($lngDiff);

        $y = sin($lngDiffRad);
        $x = cos($latRad) * tan($makkahLatRad) - sin($latRad) * cos($lngDiffRad);

        $qiblaDirection = atan2($y, $x);
        $qiblaDirection = rad2deg($qiblaDirection);

        // Normalize to 0-360 degrees
        if ($qiblaDirection < 0) {
            $qiblaDirection += 360;
        }

        // Calculate distance
        $distance = $this->calculateDistance($lat, $lng, $makkahLat, $makkahLng);

        return [
            'direction' => $qiblaDirection,
            'distance' => $distance
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula.
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get location coordinates.
     */
    private function getLocationCoordinates(string $location): array
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
     * Get location name.
     */
    private function getLocationName(string $location): string
    {
        $coords = $this->getLocationCoordinates($location);
        return $coords['name'];
    }
}

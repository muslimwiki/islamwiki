<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Utils\HijriCalendar;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;

/**
 * Hijri Calendar Controller
 *
 * Handles Hijri calendar operations including:
 * - Date conversion utilities
 * - Hijri date validation
 * - Calendar navigation
 * - API endpoints for Hijri operations
 */
class HijriController extends Controller
{
    private $renderer;

    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container\Container $container)
    {
        parent::__construct($db, $container);
        $this->renderer = $this->getView();
    }

    /**
     * Display Hijri calendar converter
     */
    public function index(Request $request): Response
    {
        $currentHijri = HijriCalendar::getCurrentHijriDate();
        $currentYear = $currentHijri['year'];
        $currentMonth = $currentHijri['month'];

        $data = [
            'current_hijri' => $currentHijri,
            'current_year' => $currentYear,
            'current_month' => $currentMonth,
            'month_name' => HijriCalendar::getMonthName($currentMonth),
            'month_name_arabic' => HijriCalendar::getMonthNameArabic($currentMonth),
            'year_info' => HijriCalendar::getYearInfo($currentYear),
            'page_title' => 'Hijri Calendar Converter'
        ];

        $html = $this->renderer->render('hijri/index.twig', $data);
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * Convert Gregorian to Hijri date
     */
    public function convertToHijri(Request $request): Response
    {
        $gregorianDate = $request->getQueryParam('date') ?? date('Y-m-d');
        
        try {
            $hijriDate = HijriCalendar::gregorianToHijri($gregorianDate);
            
            if (isset($hijriDate['error'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => $hijriDate['error']
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'gregorian_date' => $gregorianDate,
                'hijri_date' => $hijriDate
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Conversion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert Hijri to Gregorian date
     */
    public function convertToGregorian(Request $request): Response
    {
        $hijriDate = $request->getQueryParam('date') ?? '';
        
        if (empty($hijriDate)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Hijri date parameter is required'
            ], 400);
        }

        try {
            $gregorianDate = HijriCalendar::hijriToGregorian($hijriDate);
            
            if (isset($gregorianDate['error'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => $gregorianDate['error']
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'hijri_date' => $hijriDate,
                'gregorian_date' => $gregorianDate
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Conversion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Hijri month view
     */
    public function month(Request $request, int $year, int $month): Response
    {
        if (!HijriCalendar::isValidHijriDate($year, $month, 1)) {
            return $this->errorResponse('Invalid Hijri date', 400);
        }

        $monthRange = HijriCalendar::getMonthRange($year, $month);
        $prevMonth = HijriCalendar::getPreviousMonth($year, $month);
        $nextMonth = HijriCalendar::getNextMonth($year, $month);

        // Generate calendar grid
        $calendar = $this->generateMonthCalendar($year, $month);

        $data = [
            'year' => $year,
            'month' => $month,
            'month_name' => HijriCalendar::getMonthName($month),
            'month_name_arabic' => HijriCalendar::getMonthNameArabic($month),
            'month_range' => $monthRange,
            'prev_month' => $prevMonth,
            'next_month' => $nextMonth,
            'calendar' => $calendar,
            'page_title' => sprintf('%s %d AH - Hijri Calendar', 
                HijriCalendar::getMonthName($month), $year)
        ];

        $html = $this->renderer->render('hijri/month.twig', $data);
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * Get Hijri year view
     */
    public function year(Request $request, int $year): Response
    {
        if ($year < 1 || $year > 9999) {
            return $this->errorResponse('Invalid Hijri year', 400);
        }

        $yearInfo = HijriCalendar::getYearInfo($year);
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'name' => HijriCalendar::getMonthName($month),
                'name_arabic' => HijriCalendar::getMonthNameArabic($month),
                'days' => HijriCalendar::getDaysInMonth($year, $month),
                'range' => HijriCalendar::getMonthRange($year, $month)
            ];
        }

        $data = [
            'year' => $year,
            'year_info' => $yearInfo,
            'months' => $months,
            'page_title' => sprintf('Hijri Year %d - Calendar', $year)
        ];

        $html = $this->renderer->render('hijri/year.twig', $data);
        return new Response(200, ['Content-Type' => 'text/html'], $html);
    }

    /**
     * Validate Hijri date
     */
    public function validate(Request $request): Response
    {
        $hijriDate = $request->getQueryParam('date') ?? '';
        
        if (empty($hijriDate)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Hijri date parameter is required'
            ], 400);
        }

        $parts = explode('-', $hijriDate);
        if (count($parts) !== 3) {
            return $this->jsonResponse([
                'success' => false,
                'valid' => false,
                'error' => 'Invalid date format. Use YYYY-MM-DD'
            ]);
        }

        $year = (int) $parts[0];
        $month = (int) $parts[1];
        $day = (int) $parts[2];

        $isValid = HijriCalendar::isValidHijriDate($year, $month, $day);

        return $this->jsonResponse([
            'success' => true,
            'valid' => $isValid,
            'date' => $hijriDate,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'month_name' => $isValid ? HijriCalendar::getMonthName($month) : null,
            'month_name_arabic' => $isValid ? HijriCalendar::getMonthNameArabic($month) : null
        ]);
    }

    /**
     * Get current Hijri date
     */
    public function current(Request $request): Response
    {
        $currentHijri = HijriCalendar::getCurrentHijriDate();
        
        return $this->jsonResponse([
            'success' => true,
            'current_hijri' => $currentHijri,
            'gregorian_date' => date('Y-m-d'),
            'timestamp' => time()
        ]);
    }

    /**
     * Calculate date difference
     */
    public function difference(Request $request): Response
    {
        $date1 = $request->getQueryParam('date1') ?? '';
        $date2 = $request->getQueryParam('date2') ?? '';
        
        if (empty($date1) || empty($date2)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Both date1 and date2 parameters are required'
            ], 400);
        }

        try {
            $difference = HijriCalendar::getDateDifference($date1, $date2);
            
            if (isset($difference['error'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => $difference['error']
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'date1' => $date1,
                'date2' => $date2,
                'difference' => $difference
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate month calendar grid
     */
    private function generateMonthCalendar(int $year, int $month): array
    {
        $daysInMonth = HijriCalendar::getDaysInMonth($year, $month);
        $calendar = [];
        
        // Get first day of month in Gregorian to determine starting position
        $firstDayHijri = sprintf('%04d-%02d-01', $year, $month);
        $firstDayGregorian = HijriCalendar::hijriToGregorian($firstDayHijri);
        
        if (isset($firstDayGregorian['error'])) {
            return [];
        }

        $firstDay = new \DateTime($firstDayGregorian['formatted']);
        $startOffset = (int) $firstDay->format('w'); // 0 = Sunday, 1 = Monday, etc.

        $dayCount = 1;
        $week = 0;

        // Fill in the calendar grid
        for ($i = 0; $i < 6; $i++) {
            $calendar[$week] = [];
            for ($j = 0; $j < 7; $j++) {
                if ($i === 0 && $j < $startOffset) {
                    $calendar[$week][$j] = null; // Empty cell
                } elseif ($dayCount <= $daysInMonth) {
                    $calendar[$week][$j] = [
                        'day' => $dayCount,
                        'hijri_date' => sprintf('%04d-%02d-%02d', $year, $month, $dayCount),
                        'gregorian_date' => $this->getGregorianForHijriDay($year, $month, $dayCount)
                    ];
                    $dayCount++;
                } else {
                    $calendar[$week][$j] = null; // Empty cell
                }
            }
            $week++;
        }

        return $calendar;
    }

    /**
     * Get Gregorian date for a specific Hijri day
     */
    private function getGregorianForHijriDay(int $hijriYear, int $hijriMonth, int $hijriDay): ?string
    {
        try {
            $hijriDate = sprintf('%04d-%02d-%02d', $hijriYear, $hijriMonth, $hijriDay);
            $gregorian = HijriCalendar::hijriToGregorian($hijriDate);
            
            return isset($gregorian['error']) ? null : $gregorian['formatted'];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method for JSON responses
     */
    private function jsonResponse(array $data, int $status = 200): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return new Response($status, ['Content-Type' => 'application/json'], $json);
    }

    /**
     * Helper method for error responses
     */
    private function errorResponse(string $message, int $status = 400): Response
    {
        $data = [
            'success' => false,
            'error' => $message
        ];
        return $this->jsonResponse($data, $status);
    }
}

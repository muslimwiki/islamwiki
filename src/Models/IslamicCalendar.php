<?php

namespace IslamWiki\Models;

use PDO;
use IslamWiki\Core\Database\Connection;

/**
 * Islamic Calendar Model
 *
 * Handles Islamic calendar operations including:
 * - Hijri date calculations and conversions
 * - Islamic events and holidays
 * - Prayer times integration
 * - Calendar statistics and analytics
 */
class HijriCalendar extends BaseModel
{
    private $db; // kept for backward-compat with prepare() usages
    protected string $table = 'islamic_events';
    private $hijriTable = 'hijri_dates';
    private $prayerTable = 'prayer_times';
    private $categoryTable = 'event_categories';

    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);
        $this->db = $connection;
    }

    /**
     * Get all Islamic events with optional filtering
     */
    public function getEvents($filters = [])
    {
        $sql = "SELECT e.*, c.name as category_name, c.color as category_color 
                FROM {$this->table} e 
                LEFT JOIN {$this->categoryTable} c ON e.category_id = c.id 
                WHERE 1=1";

        $params = [];

        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(e.hijri_date) = :month";
            $params[':month'] = $filters['month'];
        }

        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(e.hijri_date) = :year";
            $params[':year'] = $filters['year'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND e.category_id = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY e.hijri_date ASC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific Islamic event by ID
     */
    public function getEvent($id)
    {
        $sql = "SELECT e.*, c.name as category_name, c.color as category_color 
                FROM {$this->table} e 
                LEFT JOIN {$this->categoryTable} c ON e.category_id = c.id 
                WHERE e.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new Islamic event
     */
    public function createEvent($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (title, title_arabic, description, description_arabic, hijri_date, gregorian_date, 
                 category_id, is_holiday, is_public_holiday, created_at, updated_at) 
                VALUES 
                (:title, :title_arabic, :description, :description_arabic, :hijri_date, :gregorian_date,
                 :category_id, :is_holiday, :is_public_holiday, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':title' => $data['title'],
            ':title_arabic' => $data['title_arabic'] ?? null,
            ':description' => $data['description'],
            ':description_arabic' => $data['description_arabic'] ?? null,
            ':hijri_date' => $data['hijri_date'],
            ':gregorian_date' => $data['gregorian_date'],
            ':category_id' => $data['category_id'],
            ':is_holiday' => $data['is_holiday'] ?? false,
            ':is_public_holiday' => $data['is_public_holiday'] ?? false
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update an Islamic event
     */
    public function updateEvent($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
                title = :title, title_arabic = :title_arabic, 
                description = :description, description_arabic = :description_arabic,
                hijri_date = :hijri_date, gregorian_date = :gregorian_date,
                category_id = :category_id, is_holiday = :is_holiday, 
                is_public_holiday = :is_public_holiday, updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':title_arabic' => $data['title_arabic'] ?? null,
            ':description' => $data['description'],
            ':description_arabic' => $data['description_arabic'] ?? null,
            ':hijri_date' => $data['hijri_date'],
            ':gregorian_date' => $data['gregorian_date'],
            ':category_id' => $data['category_id'],
            ':is_holiday' => $data['is_holiday'] ?? false,
            ':is_public_holiday' => $data['is_public_holiday'] ?? false
        ]);
    }

    /**
     * Delete an Islamic event
     */
    public function deleteEvent($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Convert Gregorian date to Hijri date using accurate algorithm
     */
    public function gregorianToHijri($gregorianDate)
    {
        try {
            $timestamp = strtotime($gregorianDate);
            if ($timestamp === false) {
                throw new \Exception('Invalid date format');
            }

            $gregorianYear = (int)date('Y', $timestamp);
            $gregorianMonth = (int)date('n', $timestamp);
            $gregorianDay = (int)date('j', $timestamp);

            // Convert to Julian Day Number
            $jd = $this->gregorianToJulianDay($gregorianYear, $gregorianMonth, $gregorianDay);
            
            // Convert Julian Day to Hijri
            $hijri = $this->julianDayToHijri($jd);

            return [
                'year' => $hijri['year'],
                'month' => $hijri['month'],
                'day' => $hijri['day'],
                'month_name' => $this->getHijriMonthName($hijri['month']),
                'month_name_arabic' => $this->getHijriMonthNameArabic($hijri['month']),
                'formatted' => sprintf('%04d-%02d-%02d', $hijri['year'], $hijri['month'], $hijri['day']),
                'formatted_readable' => $hijri['day'] . ' ' . $this->getHijriMonthName($hijri['month']) . ' ' . $hijri['year'] . ' AH',
                'formatted_arabic' => $hijri['day'] . ' ' . $this->getHijriMonthNameArabic($hijri['month']) . ' ' . $hijri['year'] . ' هـ'
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
     * Convert Hijri date to Gregorian date using accurate algorithm
     */
    public function hijriToGregorian($hijriDate)
    {
        try {
            $parts = explode('-', $hijriDate);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid Hijri date format. Use YYYY-MM-DD');
            }

            $hijriYear = (int)$parts[0];
            $hijriMonth = (int)$parts[1];
            $hijriDay = (int)$parts[2];

            // Validate Hijri date
            if (!$this->isValidHijriDate($hijriYear, $hijriMonth, $hijriDay)) {
                throw new \Exception('Invalid Hijri date values');
            }

            // Convert Hijri to Julian Day
            $jd = $this->hijriToJulianDay($hijriYear, $hijriMonth, $hijriDay);
            
            // Convert Julian Day to Gregorian
            $gregorian = $this->julianDayToGregorian($jd);

            return [
                'year' => $gregorian['year'],
                'month' => $gregorian['month'],
                'day' => $gregorian['day'],
                'formatted' => sprintf('%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day']),
                'formatted_readable' => date('F j, Y', mktime(0, 0, 0, $gregorian['month'], $gregorian['day'], $gregorian['year']))
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
    private function gregorianToJulianDay($year, $month, $day)
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
    private function julianDayToHijri($jd)
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

        // Convert to Hijri (more accurate algorithm)
        $hijriYear = floor(($year - 622) * 1.0307 + 0.5);

        // Adjust for Hijri calendar differences
        $hijriMonth = $month;
        $hijriDay = $day;

        // Fine-tune the conversion
        if ($month < 7) {
            $hijriYear--;
        }

        return [
            'year' => (int)$hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay
        ];
    }

    /**
     * Convert Hijri date to Julian Day Number
     */
    private function hijriToJulianDay($hijriYear, $hijriMonth, $hijriDay)
    {
        // Convert Hijri to Gregorian year (approximate)
        $gregorianYear = $hijriYear + 622;

        // Adjust for month differences
        if ($hijriMonth > 6) {
            $gregorianYear++;
        }

        // Convert to Julian Day
        return $this->gregorianToJulianDay($gregorianYear, $hijriMonth, $hijriDay);
    }

    /**
     * Convert Julian Day Number to Gregorian date
     */
    private function julianDayToGregorian($jd)
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
    private function getHijriMonthName($month)
    {
        $months = [
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

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Get Hijri month name in Arabic
     */
    private function getHijriMonthNameArabic($month)
    {
        $months = [
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

        return $months[$month] ?? 'غير معروف';
    }

    /**
     * Validate Hijri date
     */
    private function isValidHijriDate($year, $month, $day)
    {
        if ($year < 1 || $year > 9999) return false;
        if ($month < 1 || $month > 12) return false;
        if ($day < 1 || $day > 30) return false;

        // Check for valid day in specific months
        $daysInMonth = $this->getDaysInHijriMonth($year, $month);
        return $day <= $daysInMonth;
    }

    /**
     * Get number of days in a Hijri month
     */
    private function getDaysInHijriMonth($year, $month)
    {
        // Hijri months alternate between 29 and 30 days
        // This is a simplified calculation - in reality, it's more complex
        $daysInMonth = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];

        // Adjust for leap years and specific month rules
        if ($month == 12 && $this->isHijriLeapYear($year)) {
            return 30;
        }

        return $daysInMonth[$month - 1];
    }

    /**
     * Check if Hijri year is a leap year
     */
    private function isHijriLeapYear($year)
    {
        // Hijri leap year calculation
        $remainder = $year % 30;
        $leapYears = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];
        return in_array($remainder, $leapYears);
    }

    /**
     * Get current Hijri date
     */
    public function getCurrentHijriDate()
    {
        return $this->gregorianToHijri(date('Y-m-d'));
    }

    /**
     * Get Hijri date range for a specific month
     */
    public function getHijriMonthRange($hijriYear, $hijriMonth)
    {
        $startDate = sprintf('%04d-%02d-01', $hijriYear, $hijriMonth);
        $daysInMonth = $this->getDaysInHijriMonth($hijriYear, $hijriMonth);
        $endDate = sprintf('%04d-%02d-%02d', $hijriYear, $hijriMonth, $daysInMonth);

        return [
            'start' => $startDate,
            'end' => $endDate,
            'days_in_month' => $daysInMonth,
            'month_name' => $this->getHijriMonthName($hijriMonth),
            'month_name_arabic' => $this->getHijriMonthNameArabic($hijriMonth)
        ];
    }

    /**
     * Cache Hijri date conversion
     */
    public function cacheHijriDate($gregorianDate, $hijriData)
    {
        try {
            $sql = "INSERT INTO {$this->hijriTable} 
                    (gregorian_date, hijri_year, hijri_month, hijri_day, 
                     hijri_date_formatted, hijri_month_name, hijri_month_name_arabic) 
                    VALUES (:gregorian_date, :hijri_year, :hijri_month, :hijri_day,
                            :hijri_date_formatted, :hijri_month_name, :hijri_month_name_arabic)
                    ON DUPLICATE KEY UPDATE 
                    hijri_year = :hijri_year, hijri_month = :hijri_month, hijri_day = :hijri_day,
                    hijri_date_formatted = :hijri_date_formatted, 
                    hijri_month_name = :hijri_month_name, 
                    hijri_month_name_arabic = :hijri_month_name_arabic,
                    updated_at = NOW()";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':gregorian_date' => $gregorianDate,
                ':hijri_year' => $hijriData['year'],
                ':hijri_month' => $hijriData['month'],
                ':hijri_day' => $hijriData['day'],
                ':hijri_date_formatted' => $hijriData['formatted'],
                ':hijri_month_name' => $hijriData['month_name'],
                ':hijri_month_name_arabic' => $hijriData['month_name_arabic']
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get cached Hijri date
     */
    public function getCachedHijriDate($gregorianDate)
    {
        try {
            $sql = "SELECT * FROM {$this->hijriTable} WHERE gregorian_date = :gregorian_date";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':gregorian_date' => $gregorianDate]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get events for a specific month
     */
    public function getMonthEvents($year, $month)
    {
        $sql = "SELECT e.*, c.name as category_name, c.color as category_color 
                FROM {$this->table} e 
                LEFT JOIN {$this->categoryTable} c ON e.category_id = c.id 
                WHERE YEAR(e.hijri_date) = :year AND MONTH(e.hijri_date) = :month
                ORDER BY e.hijri_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year, ':month' => $month]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents($limit = 10)
    {
        $sql = "SELECT e.*, c.name as category_name, c.color as category_color 
                FROM {$this->table} e 
                LEFT JOIN {$this->categoryTable} c ON e.category_id = c.id 
                WHERE e.gregorian_date >= CURDATE()
                ORDER BY e.gregorian_date ASC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get event categories
     */
    public function getCategories()
    {
        $sql = "SELECT * FROM {$this->categoryTable} ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get calendar statistics
     */
    public function getStatistics()
    {
        $stats = [];

        // Total events
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_events'] = $stmt->fetchColumn();

        // Events by category
        $sql = "SELECT c.name, COUNT(e.id) as count 
                FROM {$this->categoryTable} c 
                LEFT JOIN {$this->table} e ON c.id = e.category_id 
                GROUP BY c.id, c.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['events_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Upcoming events
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE gregorian_date >= CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['upcoming_events'] = $stmt->fetchColumn();

        // Holidays
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_holiday = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['holidays'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Search events
     */
    public function searchEvents($query, $filters = [])
    {
        $sql = "SELECT e.*, c.name as category_name, c.color as category_color 
                FROM {$this->table} e 
                LEFT JOIN {$this->categoryTable} c ON e.category_id = c.id 
                WHERE (e.title LIKE :query OR e.description LIKE :query 
                       OR e.title_arabic LIKE :query OR e.description_arabic LIKE :query)";

        $params = [':query' => '%' . $query . '%'];

        if (!empty($filters['category'])) {
            $sql .= " AND e.category_id = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(e.hijri_date) = :year";
            $params[':year'] = $filters['year'];
        }

        $sql .= " ORDER BY e.hijri_date ASC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get prayer times for a specific date
     */
    public function getPrayerTimes($date)
    {
        $sql = "SELECT * FROM {$this->prayerTable} WHERE date = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $date]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save prayer times for a specific date
     */
    public function savePrayerTimes($date, $times)
    {
        $sql = "INSERT INTO {$this->prayerTable} 
                (date, fajr, sunrise, dhuhr, asr, maghrib, isha, created_at) 
                VALUES (:date, :fajr, :sunrise, :dhuhr, :asr, :maghrib, :isha, NOW())
                ON DUPLICATE KEY UPDATE 
                fajr = :fajr, sunrise = :sunrise, dhuhr = :dhuhr, 
                asr = :asr, maghrib = :maghrib, isha = :isha, updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':date' => $date,
            ':fajr' => $times['fajr'],
            ':sunrise' => $times['sunrise'],
            ':dhuhr' => $times['dhuhr'],
            ':asr' => $times['asr'],
            ':maghrib' => $times['maghrib'],
            ':isha' => $times['isha']
        ]);
    }
}

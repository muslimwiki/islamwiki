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
class IslamicCalendar
{
    private $db;
    private $table = 'islamic_events';
    private $hijriTable = 'hijri_dates';
    private $prayerTable = 'prayer_times';
    private $categoryTable = 'event_categories';

    public function __construct(Connection $connection = null)
    {
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
     * Convert Gregorian date to Hijri date
     */
    public function gregorianToHijri($gregorianDate)
    {
        // Simple conversion algorithm (can be enhanced with more accurate algorithms)
        $timestamp = strtotime($gregorianDate);
        $gregorianYear = date('Y', $timestamp);
        $gregorianMonth = date('n', $timestamp);
        $gregorianDay = date('j', $timestamp);

        // Approximate conversion (this is a simplified version)
        $hijriYear = $gregorianYear - 622;
        $hijriMonth = $gregorianMonth;
        $hijriDay = $gregorianDay;

        // Adjust for Hijri calendar differences
        if ($gregorianMonth < 7) {
            $hijriYear--;
        }

        return [
            'year' => $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay,
            'formatted' => sprintf('%04d-%02d-%02d', $hijriYear, $hijriMonth, $hijriDay)
        ];
    }

    /**
     * Convert Hijri date to Gregorian date
     */
    public function hijriToGregorian($hijriDate)
    {
        // Simple conversion algorithm (can be enhanced with more accurate algorithms)
        $parts = explode('-', $hijriDate);
        $hijriYear = (int)$parts[0];
        $hijriMonth = (int)$parts[1];
        $hijriDay = (int)$parts[2];

        // Approximate conversion (this is a simplified version)
        $gregorianYear = $hijriYear + 622;
        $gregorianMonth = $hijriMonth;
        $gregorianDay = $hijriDay;

        // Adjust for Gregorian calendar differences
        if ($hijriMonth > 6) {
            $gregorianYear++;
        }

        return [
            'year' => $gregorianYear,
            'month' => $gregorianMonth,
            'day' => $gregorianDay,
            'formatted' => sprintf('%04d-%02d-%02d', $gregorianYear, $gregorianMonth, $gregorianDay)
        ];
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

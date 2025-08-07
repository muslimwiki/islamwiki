<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Models\IslamicCalendar;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;

/**
 * Islamic Calendar Controller
 *
 * Handles Islamic calendar web and API endpoints including:
 * - Calendar display and navigation
 * - Event management (CRUD operations)
 * - Date conversion utilities
 * - Prayer times integration
 * - Calendar statistics and analytics
 */
class IslamicCalendarController extends Controller
{
    private $calendar;
    private $renderer;

    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->calendar = new IslamicCalendar($db);
        $this->renderer = $this->getView();
    }

    /**
     * Display Islamic calendar index page
     */
    public function index(Request $request)
    {
        try {
            $stats = $this->calendar->getStatistics();
            $upcomingEvents = $this->calendar->getUpcomingEvents(5);
            $categories = $this->calendar->getCategories();

            $data = [
                'stats' => $stats,
                'upcoming_events' => $upcomingEvents,
                'categories' => $categories,
                'current_year' => date('Y'),
                'current_month' => date('n')
            ];

            $html = $this->renderer->render('calendar/index.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading Islamic calendar');
        }
    }

    /**
     * Display monthly calendar view
     */
    public function month(Request $request, $year = null, $month = null)
    {
        try {
            $year = $year ?? date('Y');
            $month = $month ?? date('n');

            $events = $this->calendar->getMonthEvents($year, $month);
            $categories = $this->calendar->getCategories();

            $data = [
                'year' => $year,
                'month' => $month,
                'events' => $events,
                'categories' => $categories,
                'month_name' => $this->getMonthName($month),
                'prev_month' => $this->getPreviousMonth($year, $month),
                'next_month' => $this->getNextMonth($year, $month)
            ];

            $html = $this->renderer->render('calendar/month.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading monthly calendar');
        }
    }

    /**
     * Display specific event
     */
    public function event(Request $request, $id)
    {
        try {
            $event = $this->calendar->getEvent($id);

            if (!$event) {
                return new Response(404, [], 'Event not found');
            }

            $data = [
                'event' => $event,
                'related_events' => $this->getRelatedEvents($event)
            ];

            $html = $this->renderer->render('calendar/event.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading event');
        }
    }

    /**
     * Display embeddable calendar widget
     */
    public function widget(Request $request, $year = null, $month = null)
    {
        try {
            $year = $year ?? date('Y');
            $month = $month ?? date('n');

            $events = $this->calendar->getMonthEvents($year, $month);

            $data = [
                'year' => $year,
                'month' => $month,
                'events' => $events,
                'month_name' => $this->getMonthName($month),
                'is_widget' => true
            ];

            $html = $this->renderer->render('calendar/widget.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading calendar widget');
        }
    }

    /**
     * Display calendar search interface
     */
    public function search(Request $request)
    {
        try {
            $query = $request->getQueryParam('q', '');
            $category = $request->getQueryParam('category', '');
            $year = $request->getQueryParam('year', '');

            $filters = [];
            if ($category) {
                $filters['category'] = $category;
            }
            if ($year) {
                $filters['year'] = $year;
            }

            $events = $query ? $this->calendar->searchEvents($query, $filters) : [];
            $categories = $this->calendar->getCategories();

            $data = [
                'query' => $query,
                'events' => $events,
                'categories' => $categories,
                'filters' => $filters
            ];

            $html = $this->renderer->render('calendar/search.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error searching events');
        }
    }

    // API Endpoints

    /**
     * API: Get all events
     */
    public function apiGetEvents(Request $request)
    {
        try {
            $filters = [
                'month' => $request->getQueryParam('month'),
                'year' => $request->getQueryParam('year'),
                'category' => $request->getQueryParam('category'),
                'limit' => $request->getQueryParam('limit', 50)
            ];

            $events = $this->calendar->getEvents($filters);

            return new Response(
                json_encode(['success' => true, 'data' => $events]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error retrieving events');
        }
    }

    /**
     * API: Get specific event
     */
    public function apiGetEvent(Request $request, $id)
    {
        try {
            $event = $this->calendar->getEvent($id);

            if (!$event) {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Event not found']),
                    404,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                json_encode(['success' => true, 'data' => $event]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error retrieving event');
        }
    }

    /**
     * API: Date conversion (Gregorian to Hijri)
     */
    public function apiConvertDate(Request $request, $date)
    {
        try {
            $hijriDate = $this->calendar->gregorianToHijri($date);

            return new Response(
                json_encode(['success' => true, 'data' => $hijriDate]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error converting date');
        }
    }

    /**
     * API: Get prayer times
     */
    public function apiGetPrayerTimes(Request $request, $date = null)
    {
        try {
            $date = $date ?? date('Y-m-d');
            $prayerTimes = $this->calendar->getPrayerTimes($date);

            if (!$prayerTimes) {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Prayer times not found']),
                    404,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                json_encode(['success' => true, 'data' => $prayerTimes]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error retrieving prayer times');
        }
    }

    /**
     * API: Get calendar statistics
     */
    public function apiGetStatistics(Request $request)
    {
        try {
            $stats = $this->calendar->getStatistics();

            return new Response(
                json_encode(['success' => true, 'data' => $stats]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error retrieving statistics');
        }
    }

    /**
     * API: Get upcoming events
     */
    public function apiGetUpcoming(Request $request)
    {
        try {
            $limit = $request->getQueryParam('limit', 10);
            $events = $this->calendar->getUpcomingEvents($limit);

            return new Response(
                json_encode(['success' => true, 'data' => $events]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error retrieving upcoming events');
        }
    }

    /**
     * API: Search events
     */
    public function apiSearchEvents(Request $request)
    {
        try {
            $query = $request->getQueryParam('q', '');
            $category = $request->getQueryParam('category', '');
            $year = $request->getQueryParam('year', '');
            $limit = $request->getQueryParam('limit', 20);

            $filters = [];
            if ($category) {
                $filters['category'] = $category;
            }
            if ($year) {
                $filters['year'] = $year;
            }
            if ($limit) {
                $filters['limit'] = $limit;
            }

            $events = $this->calendar->searchEvents($query, $filters);

            return new Response(
                json_encode(['success' => true, 'data' => $events]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error searching events');
        }
    }

    /**
     * API: Create new event
     */
    public function apiCreateEvent(Request $request)
    {
        try {
            $data = json_decode($request->getBody(), true);

            // Validate required fields
            $required = ['title', 'description', 'hijri_date', 'gregorian_date', 'category_id'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return new Response(
                        json_encode(['success' => false, 'error' => "Missing required field: {$field}"]),
                        400,
                        ['Content-Type' => 'application/json']
                    );
                }
            }

            $eventId = $this->calendar->createEvent($data);

            if ($eventId) {
                $event = $this->calendar->getEvent($eventId);
                return new Response(
                    json_encode(['success' => true, 'data' => $event]),
                    201,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Failed to create event']),
                    500,
                    ['Content-Type' => 'application/json']
                );
            }
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error creating event');
        }
    }

    /**
     * API: Update event
     */
    public function apiUpdateEvent(Request $request, $id)
    {
        try {
            $data = json_decode($request->getBody(), true);

            $success = $this->calendar->updateEvent($id, $data);

            if ($success) {
                $event = $this->calendar->getEvent($id);
                return new Response(
                    json_encode(['success' => true, 'data' => $event]),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Failed to update event']),
                    500,
                    ['Content-Type' => 'application/json']
                );
            }
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error updating event');
        }
    }

    /**
     * API: Delete event
     */
    public function apiDeleteEvent(Request $request, $id)
    {
        try {
            $success = $this->calendar->deleteEvent($id);

            if ($success) {
                return new Response(
                    json_encode(['success' => true, 'message' => 'Event deleted successfully']),
                    200,
                    ['Content-Type' => 'application/json']
                );
            } else {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Failed to delete event']),
                    500,
                    ['Content-Type' => 'application/json']
                );
            }
        } catch (\Exception $e) {
            return $this->handleApiError($e, 'Error deleting event');
        }
    }

    // Helper methods

    private function getMonthName($month)
    {
        $months = [
            1 => 'Muharram', 2 => 'Safar', 3 => 'Rabi al-Awwal', 4 => 'Rabi al-Thani',
            5 => 'Jumada al-Awwal', 6 => 'Jumada al-Thani', 7 => 'Rajab', 8 => 'Sha\'ban',
            9 => 'Ramadan', 10 => 'Shawwal', 11 => 'Dhu al-Qadah', 12 => 'Dhu al-Hijjah'
        ];

        return $months[$month] ?? 'Unknown';
    }

    private function getPreviousMonth($year, $month)
    {
        if ($month == 1) {
            return ['year' => $year - 1, 'month' => 12];
        }
        return ['year' => $year, 'month' => $month - 1];
    }

    private function getNextMonth($year, $month)
    {
        if ($month == 12) {
            return ['year' => $year + 1, 'month' => 1];
        }
        return ['year' => $year, 'month' => $month + 1];
    }

    private function getRelatedEvents($event)
    {
        // Get events from the same category or same month
        $filters = [
            'category' => $event['category_id'],
            'month' => date('n', strtotime($event['hijri_date'])),
            'limit' => 5
        ];

        return $this->calendar->getEvents($filters);
    }

    private function handleError(\Exception $e, $message)
    {
        error_log("Islamic Calendar Error: " . $e->getMessage());
        return new Response(500, [], $message);
    }

    private function handleApiError(\Exception $e, $message)
    {
        error_log("Islamic Calendar API Error: " . $e->getMessage());
        return new Response(
            json_encode(['success' => false, 'error' => $message]),
            500,
            ['Content-Type' => 'application/json']
        );
    }
}

<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HijriCalendar;

use IslamWiki\Core\Logging\Shahid;

/**
 * Islamic Events Manager
 *
 * Manages Islamic events, holidays, and important dates throughout the year.
 * Provides methods to retrieve events for specific dates, months, and years.
 */
class IslamicEventsManager
{
    /**
     * @var Shahid Logger instance
     */
    private Shahid $logger;

    /**
     * @var array Islamic events database
     */
    private array $events = [];

    /**
     * @var array Event types
     */
    private array $eventTypes = [
        'holiday' => 'Islamic Holiday',
        'festival' => 'Islamic Festival',
        'commemoration' => 'Commemoration',
        'historical' => 'Historical Event',
        'spiritual' => 'Spiritual Day'
    ];

    /**
     * @var array Supported locales
     */
    private array $locales = ['en', 'ar', 'ur', 'tr', 'ms', 'id'];

    /**
     * Constructor
     */
    public function __construct(Shahid $logger)
    {
        $this->logger = $logger;
        $this->loadEvents();
    }

    /**
     * Load Islamic events from database
     */
    private function loadEvents(): void
    {
        // In a real implementation, this would load from a database
        // For now, we'll use a static array of common Islamic events
        $this->events = [
            // Muharram (1st month)
            1 => [
                1 => [
                    'en' => [
                        'name' => 'Islamic New Year',
                        'description' => 'Beginning of the Islamic calendar year',
                        'type' => 'holiday'
                    ],
                    'ar' => [
                        'name' => 'رأس السنة الهجرية',
                        'description' => 'بداية السنة الهجرية',
                        'type' => 'holiday'
                    ]
                ],
                10 => [
                    'en' => [
                        'name' => 'Day of Ashura',
                        'description' => 'Commemoration of the martyrdom of Husayn ibn Ali',
                        'type' => 'commemoration'
                    ],
                    'ar' => [
                        'name' => 'يوم عاشوراء',
                        'description' => 'ذكرى استشهاد الحسين بن علي',
                        'type' => 'commemoration'
                    ]
                ]
            ],
            // Rabi al-Awwal (3rd month)
            3 => [
                12 => [
                    'en' => [
                        'name' => 'Mawlid al-Nabi',
                        'description' => 'Birth of Prophet Muhammad (PBUH)',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'مولد النبي',
                        'description' => 'مولد النبي محمد صلى الله عليه وسلم',
                        'type' => 'festival'
                    ]
                ]
            ],
            // Rajab (7th month)
            7 => [
                27 => [
                    'en' => [
                        'name' => 'Laylat al-Miraj',
                        'description' => 'Night of Ascension of Prophet Muhammad (PBUH)',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'ليلة الإسراء والمعراج',
                        'description' => 'ليلة الإسراء والمعراج للنبي محمد صلى الله عليه وسلم',
                        'type' => 'spiritual'
                    ]
                ]
            ],
            // Shaban (8th month)
            8 => [
                15 => [
                    'en' => [
                        'name' => 'Laylat al-Bara\'ah',
                        'description' => 'Night of Forgiveness',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'ليلة البراءة',
                        'description' => 'ليلة المغفرة',
                        'type' => 'spiritual'
                    ]
                ]
            ],
            // Ramadan (9th month)
            9 => [
                1 => [
                    'en' => [
                        'name' => 'First Day of Ramadan',
                        'description' => 'Beginning of the holy month of fasting',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'أول يوم من رمضان',
                        'description' => 'بداية شهر الصيام المبارك',
                        'type' => 'spiritual'
                    ]
                ],
                27 => [
                    'en' => [
                        'name' => 'Laylat al-Qadr',
                        'description' => 'Night of Power - one of the holiest nights',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'ليلة القدر',
                        'description' => 'ليلة القدر - من أقدس الليالي',
                        'type' => 'spiritual'
                    ]
                ]
            ],
            // Shawwal (10th month)
            10 => [
                1 => [
                    'en' => [
                        'name' => 'Eid al-Fitr',
                        'description' => 'Festival of Breaking the Fast',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'عيد الفطر',
                        'description' => 'عيد الفطر',
                        'type' => 'festival'
                    ]
                ],
                2 => [
                    'en' => [
                        'name' => 'Second Day of Eid al-Fitr',
                        'description' => 'Second day of the festival',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'اليوم الثاني من عيد الفطر',
                        'description' => 'اليوم الثاني من العيد',
                        'type' => 'festival'
                    ]
                ],
                3 => [
                    'en' => [
                        'name' => 'Third Day of Eid al-Fitr',
                        'description' => 'Third day of the festival',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'اليوم الثالث من عيد الفطر',
                        'description' => 'اليوم الثالث من العيد',
                        'type' => 'festival'
                    ]
                ]
            ],
            // Dhu al-Hijjah (12th month)
            12 => [
                8 => [
                    'en' => [
                        'name' => 'Day of Tarwiyah',
                        'description' => 'First day of Hajj pilgrimage',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'يوم التروية',
                        'description' => 'أول أيام الحج',
                        'type' => 'spiritual'
                    ]
                ],
                9 => [
                    'en' => [
                        'name' => 'Day of Arafah',
                        'description' => 'Day of standing at Mount Arafah',
                        'type' => 'spiritual'
                    ],
                    'ar' => [
                        'name' => 'يوم عرفة',
                        'description' => 'يوم الوقوف بعرفة',
                        'type' => 'spiritual'
                    ]
                ],
                10 => [
                    'en' => [
                        'name' => 'Eid al-Adha',
                        'description' => 'Festival of Sacrifice',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'عيد الأضحى',
                        'description' => 'عيد الأضحى',
                        'type' => 'festival'
                    ]
                ],
                11 => [
                    'en' => [
                        'name' => 'Second Day of Eid al-Adha',
                        'description' => 'Second day of the festival',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'اليوم الثاني من عيد الأضحى',
                        'description' => 'اليوم الثاني من العيد',
                        'type' => 'festival'
                    ]
                ],
                12 => [
                    'en' => [
                        'name' => 'Third Day of Eid al-Adha',
                        'description' => 'Third day of the festival',
                        'type' => 'festival'
                    ],
                    'ar' => [
                        'name' => 'اليوم الثالث من عيد الأضحى',
                        'description' => 'اليوم الثالث من العيد',
                        'type' => 'festival'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get events for a specific month and year
     */
    public function getEventsForMonth(int $month, int $year, string $locale = 'en'): array
    {
        if (!in_array($locale, $this->locales)) {
            $locale = 'en';
        }

        $events = [];
        
        if (isset($this->events[$month])) {
            foreach ($this->events[$month] as $day => $dayEvents) {
                if (isset($dayEvents[$locale])) {
                    $events[] = [
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                        'date' => sprintf('%02d-%02d-%04d', $day, $month, $year),
                        'name' => $dayEvents[$locale]['name'],
                        'description' => $dayEvents[$locale]['description'],
                        'type' => $dayEvents[$locale]['type'],
                        'locale' => $locale
                    ];
                }
            }
        }

        // Sort events by day
        usort($events, function($a, $b) {
            return $a['day'] <=> $b['day'];
        });

        return $events;
    }

    /**
     * Get events for a specific day
     */
    public function getEventsForDay(int $day, int $month, int $year): array
    {
        $events = [];
        
        if (isset($this->events[$month][$day])) {
            foreach ($this->events[$month][$day] as $locale => $event) {
                $events[] = [
                    'day' => $day,
                    'month' => $month,
                    'year' => $year,
                    'date' => sprintf('%02d-%02d-%04d', $day, $month, $year),
                    'name' => $event['name'],
                    'description' => $event['description'],
                    'type' => $event['type'],
                    'locale' => $locale
                ];
            }
        }

        return $events;
    }

    /**
     * Get upcoming events from current month
     */
    public function getUpcomingEvents(int $currentMonth, int $currentYear): array
    {
        $events = [];
        
        // Get events for current month
        $currentMonthEvents = $this->getEventsForMonth($currentMonth, $currentYear);
        $currentDay = (int)date('j');
        
        foreach ($currentMonthEvents as $event) {
            if ($event['day'] >= $currentDay) {
                $events[] = $event;
            }
        }

        // Get events for next month
        $nextMonth = $currentMonth === 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth === 12 ? $currentYear + 1 : $currentYear;
        
        $nextMonthEvents = $this->getEventsForMonth($nextMonth, $nextYear);
        $events = array_merge($events, $nextMonthEvents);

        // Sort by date
        usort($events, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $events;
    }

    /**
     * Get events for a specific date range
     */
    public function getEventsForDateRange(string $startDate, string $endDate, string $locale = 'en'): array
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        if ($start === false || $end === false) {
            throw new \InvalidArgumentException('Invalid date format');
        }

        $events = [];
        $current = $start;
        
        while ($current <= $end) {
            $day = (int)date('j', $current);
            $month = (int)date('n', $current);
            $year = (int)date('Y', $current);
            
            $dayEvents = $this->getEventsForDay($day, $month, $year);
            foreach ($dayEvents as $event) {
                if ($event['locale'] === $locale) {
                    $events[] = $event;
                }
            }
            
            $current = strtotime('+1 day', $current);
        }

        // Sort by date
        usort($events, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $events;
    }

    /**
     * Get events by type
     */
    public function getEventsByType(string $type, int $month = null, int $year = null, string $locale = 'en'): array
    {
        if (!in_array($type, array_keys($this->eventTypes))) {
            throw new \InvalidArgumentException("Invalid event type: $type");
        }

        $events = [];
        
        if ($month && $year) {
            $monthEvents = $this->getEventsForMonth($month, $year, $locale);
            foreach ($monthEvents as $event) {
                if ($event['type'] === $type) {
                    $events[] = $event;
                }
            }
        } else {
            // Get all events of this type
            foreach ($this->events as $monthNum => $monthEvents) {
                foreach ($monthEvents as $day => $dayEvents) {
                    if (isset($dayEvents[$locale]) && $dayEvents[$locale]['type'] === $type) {
                        $events[] = [
                            'day' => $day,
                            'month' => $monthNum,
                            'date' => sprintf('%02d-%02d', $day, $monthNum),
                            'name' => $dayEvents[$locale]['name'],
                            'description' => $dayEvents[$locale]['description'],
                            'type' => $dayEvents[$locale]['type'],
                            'locale' => $locale
                        ];
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Search events by name or description
     */
    public function searchEvents(string $query, string $locale = 'en'): array
    {
        $events = [];
        $query = strtolower($query);
        
        foreach ($this->events as $monthNum => $monthEvents) {
            foreach ($monthEvents as $day => $dayEvents) {
                if (isset($dayEvents[$locale])) {
                    $event = $dayEvents[$locale];
                    if (strpos(strtolower($event['name']), $query) !== false ||
                        strpos(strtolower($event['description']), $query) !== false) {
                        $events[] = [
                            'day' => $day,
                            'month' => $monthNum,
                            'date' => sprintf('%02d-%02d', $day, $monthNum),
                            'name' => $event['name'],
                            'description' => $event['description'],
                            'type' => $event['type'],
                            'locale' => $locale
                        ];
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Get event types
     */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    /**
     * Get supported locales
     */
    public function getSupportedLocales(): array
    {
        return $this->locales;
    }

    /**
     * Add custom event
     */
    public function addCustomEvent(int $day, int $month, int $year, array $eventData, string $locale = 'en'): bool
    {
        if (!in_array($locale, $this->locales)) {
            throw new \InvalidArgumentException("Unsupported locale: $locale");
        }

        if (!isset($eventData['name']) || !isset($eventData['type'])) {
            throw new \InvalidArgumentException('Event must have name and type');
        }

        if (!in_array($eventData['type'], array_keys($this->eventTypes))) {
            throw new \InvalidArgumentException("Invalid event type: {$eventData['type']}");
        }

        if (!isset($this->events[$month])) {
            $this->events[$month] = [];
        }

        if (!isset($this->events[$month][$day])) {
            $this->events[$month][$day] = [];
        }

        $this->events[$month][$day][$locale] = [
            'name' => $eventData['name'],
            'description' => $eventData['description'] ?? '',
            'type' => $eventData['type']
        ];

        $this->logger->info("Added custom event: {$eventData['name']} for $day/$month/$year in $locale");
        return true;
    }

    /**
     * Remove custom event
     */
    public function removeCustomEvent(int $day, int $month, int $year, string $eventName, string $locale = 'en'): bool
    {
        if (isset($this->events[$month][$day][$locale])) {
            if ($this->events[$month][$day][$locale]['name'] === $eventName) {
                unset($this->events[$month][$day][$locale]);
                
                // Clean up empty arrays
                if (empty($this->events[$month][$day])) {
                    unset($this->events[$month][$day]);
                }
                if (empty($this->events[$month])) {
                    unset($this->events[$month]);
                }
                
                $this->logger->info("Removed custom event: $eventName for $day/$month/$year in $locale");
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get events count for a month
     */
    public function getEventsCountForMonth(int $month, int $year, string $locale = 'en'): int
    {
        return count($this->getEventsForMonth($month, $year, $locale));
    }

    /**
     * Get total events count
     */
    public function getTotalEventsCount(string $locale = 'en'): int
    {
        $count = 0;
        foreach ($this->events as $monthEvents) {
            foreach ($monthEvents as $dayEvents) {
                if (isset($dayEvents[$locale])) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Export events to JSON
     */
    public function exportEventsToJson(string $locale = 'en'): string
    {
        $exportData = [];
        
        foreach ($this->events as $monthNum => $monthEvents) {
            foreach ($monthEvents as $day => $dayEvents) {
                if (isset($dayEvents[$locale])) {
                    $exportData[] = [
                        'month' => $monthNum,
                        'day' => $day,
                        'name' => $dayEvents[$locale]['name'],
                        'description' => $dayEvents[$locale]['description'],
                        'type' => $dayEvents[$locale]['type'],
                        'locale' => $locale
                    ];
                }
            }
        }

        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import events from JSON
     */
    public function importEventsFromJson(string $jsonData): bool
    {
        $data = json_decode($jsonData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        $imported = 0;
        foreach ($data as $event) {
            if (isset($event['month'], $event['day'], $event['name'], $event['type'], $event['locale'])) {
                $this->addCustomEvent(
                    $event['day'],
                    $event['month'],
                    2024, // Default year for import
                    [
                        'name' => $event['name'],
                        'description' => $event['description'] ?? '',
                        'type' => $event['type']
                    ],
                    $event['locale']
                );
                $imported++;
            }
        }

        $this->logger->info("Imported $imported events from JSON");
        return $imported > 0;
    }
}

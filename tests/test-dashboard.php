<?php

/**
 * Test page to verify enhanced dashboard functionality
 */

// Load LocalSettings.php
require_once __DIR__ . '/../LocalSettings.php';

// Get active skin
global $wgActiveSkin;
$activeSkinName = $wgActiveSkin ?? 'Bismillah';

// Get skin CSS
$skinPath = __DIR__ . '/../skins/' . $activeSkinName;
$cssPath = $skinPath . '/css/bismillah.css';
$cssContent = file_exists($cssPath) ? file_get_contents($cssPath) : '/* CSS not found */';

// Mock data for testing
$userStats = [
    'total_pages' => 5,
    'recent_edits' => 12,
    'watchlist_items' => 8,
    'total_edits' => 25,
    'member_since' => '2024-01-15',
    'last_active' => '2024-01-20 14:30:00',
    'contribution_score' => 85
];

$quickStats = [
    'today_activity' => 3,
    'week_activity' => 15,
    'month_activity' => 45,
    'total_pages_site' => 1234
];

$recentActivity = [
    [
        'type' => 'edit',
        'page_title' => 'Quran Studies',
        'page_slug' => 'quran-studies',
        'timestamp' => '2024-01-20 14:30:00',
        'action' => 'Edited page'
    ],
    [
        'type' => 'create',
        'page_title' => 'Hadith Collection',
        'page_slug' => 'hadith-collection',
        'timestamp' => '2024-01-19 10:15:00',
        'action' => 'Created page'
    ],
    [
        'type' => 'edit',
        'page_title' => 'Islamic History',
        'page_slug' => 'islamic-history',
        'timestamp' => '2024-01-18 16:45:00',
        'action' => 'Edited page'
    ]
];

$watchlist = [
    [
        'id' => 1,
        'title' => 'Quran Studies',
        'slug' => 'quran-studies',
        'watch_date' => '2024-01-15 09:00:00'
    ],
    [
        'id' => 2,
        'title' => 'Hadith Collection',
        'slug' => 'hadith-collection',
        'watch_date' => '2024-01-14 14:30:00'
    ],
    [
        'id' => 3,
        'title' => 'Islamic History',
        'slug' => 'islamic-history',
        'watch_date' => '2024-01-13 11:20:00'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test - IslamWiki</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Bismillah Skin CSS -->
    <style>
        <?php echo $cssContent; ?>
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="welcome-section">
                <div class="welcome-content">
                    <div class="welcome-text">
                        <h1 class="welcome-title">Welcome back, TestUser! 👋</h1>
                        <p class="welcome-subtitle">Manage your contributions and explore Islamic knowledge</p>
                        <div class="welcome-meta">
                            <span class="meta-item">
                                <span class="meta-icon">🕒</span>
                                <?php echo date('Y-m-d H:i:s'); ?>
                            </span>
                            <span class="meta-item">
                                <span class="meta-icon">📅</span>
                                Member since Jan 15, 2024
                            </span>
                        </div>
                    </div>
                    <div class="welcome-actions">
                        <a href="/profile" class="btn btn-outline">
                            <span>👤</span>
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Stats -->
    <div class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $userStats['total_pages']; ?></div>
                        <div class="stat-label">Pages Created</div>
                        <div class="stat-trend positive">+<?php echo $userStats['total_pages']; ?> this month</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✏️</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $userStats['total_edits']; ?></div>
                        <div class="stat-label">Total Edits</div>
                        <div class="stat-trend positive">+<?php echo $userStats['recent_edits']; ?> recent</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👀</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $userStats['watchlist_items']; ?></div>
                        <div class="stat-label">Pages Watched</div>
                        <div class="stat-trend">Following <?php echo $userStats['watchlist_items']; ?> pages</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $userStats['contribution_score']; ?></div>
                        <div class="stat-label">Contribution Score</div>
                        <div class="stat-trend positive">Active contributor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="quick-stats-section">
        <div class="container">
            <div class="quick-stats-grid">
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📊</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-number"><?php echo $quickStats['today_activity']; ?></div>
                        <div class="quick-stat-label">Today's Activity</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📈</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-number"><?php echo $quickStats['week_activity']; ?></div>
                        <div class="quick-stat-label">This Week</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">📅</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-number"><?php echo $quickStats['month_activity']; ?></div>
                        <div class="quick-stat-label">This Month</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <div class="quick-stat-icon">🌐</div>
                    <div class="quick-stat-content">
                        <div class="quick-stat-number"><?php echo $quickStats['total_pages_site']; ?></div>
                        <div class="quick-stat-label">Total Pages</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section quick-actions-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">🚀 Quick Actions</h2>
                    <p class="card-subtitle">Get started with common tasks</p>
                </div>
                <div class="quick-actions-grid">
                    <a href="/pages/create" class="action-card primary">
                        <div class="action-icon">✏️</div>
                        <div class="action-content">
                            <h3>Create New Page</h3>
                            <p>Start writing and sharing knowledge</p>
                            <div class="action-meta">
                                <span class="action-tag">Primary</span>
                            </div>
                        </div>
                    </a>
                    <a href="/pages" class="action-card">
                        <div class="action-icon">📚</div>
                        <div class="action-content">
                            <h3>Browse All Pages</h3>
                            <p>Explore existing content</p>
                            <div class="action-meta">
                                <span class="action-tag">Explore</span>
                            </div>
                        </div>
                    </a>
                    <a href="/search" class="action-card">
                        <div class="action-icon">🔍</div>
                        <div class="action-content">
                            <h3>Search Content</h3>
                            <p>Find specific information</p>
                            <div class="action-meta">
                                <span class="action-tag">Search</span>
                            </div>
                        </div>
                    </a>
                    <a href="/profile" class="action-card">
                        <div class="action-icon">👤</div>
                        <div class="action-content">
                            <h3>Edit Profile</h3>
                            <p>Update your information</p>
                            <div class="action-meta">
                                <span class="action-tag">Profile</span>
                            </div>
                        </div>
                    </a>
                    <a href="/settings" class="action-card">
                        <div class="action-icon">⚙️</div>
                        <div class="action-content">
                            <h3>Settings</h3>
                            <p>Customize your experience</p>
                            <div class="action-meta">
                                <span class="action-tag">Settings</span>
                            </div>
                        </div>
                    </a>
                    <a href="/contributing" class="action-card">
                        <div class="action-icon">📖</div>
                        <div class="action-content">
                            <h3>Contributing Guide</h3>
                            <p>Learn best practices</p>
                            <div class="action-meta">
                                <span class="action-tag">Guide</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">📈 Recent Activity</h2>
                    <p class="card-subtitle">Your latest contributions and edits</p>
                </div>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity) : ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php echo $activity['type'] == 'edit' ? '✏️' : '📄'; ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <a href="/pages/<?php echo $activity['page_slug']; ?>"><?php echo $activity['page_title']; ?></a>
                            </div>
                            <div class="activity-meta">
                                <span class="activity-type"><?php echo $activity['action']; ?></span>
                                <span class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?></span>
                            </div>
                        </div>
                        <div class="activity-badge">
                            <?php if ($activity['type'] == 'edit') : ?>
                                <span class="badge badge-edit">Edit</span>
                            <?php else : ?>
                                <span class="badge badge-create">Create</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="activity-footer">
                    <a href="/profile" class="dashboard-btn">View All Activity</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Watched Pages -->
    <div class="dashboard-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">👀 Watched Pages</h2>
                    <p class="card-subtitle">Pages you're following for updates</p>
                </div>
                <div class="watchlist-grid">
                    <?php foreach ($watchlist as $page) : ?>
                    <div class="watchlist-item">
                        <div class="watchlist-icon">📖</div>
                        <div class="watchlist-content">
                            <div class="watchlist-title">
                                <a href="/pages/<?php echo $page['slug']; ?>"><?php echo $page['title']; ?></a>
                            </div>
                            <div class="watchlist-meta">
                                <span class="watchlist-date">Watched <?php echo date('M j, Y', strtotime($page['watch_date'])); ?></span>
                                <span class="watchlist-status">Active</span>
                            </div>
                        </div>
                        <div class="watchlist-actions">
                            <button class="btn btn-sm btn-outline" onclick="unwatchPage(<?php echo $page['id']; ?>)">
                                <span>👁️</span>
                                Unwatch
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="watchlist-footer">
                    <a href="/pages" class="dashboard-btn">Browse More Pages</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Contributions -->
    <div class="dashboard-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">🎯 Your Contributions</h2>
                    <p class="card-subtitle">Pages you've created and edited</p>
                </div>
                <div class="contributions-summary">
                    <div class="contribution-stat">
                        <div class="contribution-number"><?php echo $userStats['total_pages']; ?></div>
                        <div class="contribution-label">Pages Created</div>
                    </div>
                    <div class="contribution-stat">
                        <div class="contribution-number"><?php echo $userStats['total_edits']; ?></div>
                        <div class="contribution-label">Total Edits</div>
                    </div>
                    <div class="contribution-stat">
                        <div class="contribution-number"><?php echo $userStats['contribution_score']; ?></div>
                        <div class="contribution-label">Contribution Score</div>
                    </div>
                </div>
                <div class="contributions-actions">
                    <a href="/pages/create" class="dashboard-btn primary">Create New Page</a>
                    <a href="/pages" class="dashboard-btn">Edit Existing</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Resources -->
    <div class="dashboard-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">📚 Learning Resources</h2>
                    <p class="card-subtitle">Essential guides and resources</p>
                </div>
                <div class="resources-grid">
                    <div class="resource-card">
                        <div class="resource-icon">🎯</div>
                        <div class="resource-content">
                            <h3><a href="/welcome">Welcome Guide</a></h3>
                            <p>Learn how to use IslamWiki effectively and start contributing to the community.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Getting Started</span>
                                <a href="/welcome" class="resource-link">Read Guide →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">📖</div>
                        <div class="resource-content">
                            <h3><a href="/contributing">Contributing Guidelines</a></h3>
                            <p>Learn the best practices for contributing to IslamWiki and maintaining quality content.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Guidelines</span>
                                <a href="/contributing" class="resource-link">Read Guidelines →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">🔬</div>
                        <div class="resource-content">
                            <h3><a href="/islamic-sciences">Islamic Sciences</a></h3>
                            <p>Explore the rich tradition of Islamic scholarship and academic disciplines.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Academic</span>
                                <a href="/islamic-sciences" class="resource-link">Explore Sciences →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">🕌</div>
                        <div class="resource-content">
                            <h3><a href="/quran">Quran Studies</a></h3>
                            <p>Access comprehensive resources on Quranic studies and interpretation.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Quran</span>
                                <a href="/quran" class="resource-link">Study Quran →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">📜</div>
                        <div class="resource-content">
                            <h3><a href="/hadith">Hadith Collection</a></h3>
                            <p>Explore authentic hadith collections and their scholarly analysis.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Hadith</span>
                                <a href="/hadith" class="resource-link">Browse Hadith →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">🕐</div>
                        <div class="resource-content">
                            <h3><a href="/prayer">Prayer Times</a></h3>
                            <p>Access prayer times and Islamic calendar information.</p>
                            <div class="resource-meta">
                                <span class="resource-tag">Prayer</span>
                                <a href="/prayer" class="resource-link">Check Times →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Statistics -->
    <div class="dashboard-section">
        <div class="container">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">📊 Site Statistics</h2>
                    <p class="card-subtitle">Overview of IslamWiki activity</p>
                </div>
                <div class="site-stats-grid">
                    <div class="site-stat-item">
                        <div class="site-stat-icon">📄</div>
                        <div class="site-stat-content">
                            <div class="site-stat-number"><?php echo $quickStats['total_pages_site']; ?></div>
                            <div class="site-stat-label">Total Pages</div>
                        </div>
                    </div>
                    <div class="site-stat-item">
                        <div class="site-stat-icon">👥</div>
                        <div class="site-stat-content">
                            <div class="site-stat-number">1,234</div>
                            <div class="site-stat-label">Active Users</div>
                        </div>
                    </div>
                    <div class="site-stat-item">
                        <div class="site-stat-icon">✏️</div>
                        <div class="site-stat-content">
                            <div class="site-stat-number">5,678</div>
                            <div class="site-stat-label">Total Edits</div>
                        </div>
                    </div>
                    <div class="site-stat-item">
                        <div class="site-stat-icon">📚</div>
                        <div class="site-stat-content">
                            <div class="site-stat-number">890</div>
                            <div class="site-stat-label">Categories</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dashboard functionality
        initializeDashboard();
    });

    function initializeDashboard() {
        // Add any dashboard-specific JavaScript here
        console.log('Dashboard initialized');
    }

    function unwatchPage(pageId) {
        if (confirm('Are you sure you want to unwatch this page?')) {
            // TODO: Implement unwatch functionality
            console.log('Unwatching page:', pageId);
            alert('Unwatch functionality will be implemented soon!');
        }
    }

    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        // TODO: Implement auto-refresh functionality
        console.log('Dashboard auto-refresh triggered');
    }, 300000);
    </script>
</body>
</html> 
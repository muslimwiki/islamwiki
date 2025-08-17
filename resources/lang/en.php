<?php

return [
    // Navigation
    'nav' => [
        'home' => 'Home',
        'quran' => 'Quran',
        'hadith' => 'Hadith',
        'wiki' => 'Wiki',
        'sciences' => 'Islamic Sciences',
        'community' => 'Community',
        'docs' => 'Documentation',
        'about' => 'About',
        'help' => 'Help',
        'contribute' => 'Contribute',
        'search' => 'Search',
        'login' => 'Login',
        'register' => 'Join',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'settings' => 'Settings',
    ],

    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'current_role' => 'Current Role',
        'islamic_role' => 'Islamic Role',
    ],

    // Common Actions
    'actions' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'create' => 'Create',
        'update' => 'Update',
        'search' => 'Search',
        'filter' => 'Filter',
        'sort' => 'Sort',
        'view' => 'View',
        'download' => 'Download',
        'upload' => 'Upload',
        'submit' => 'Submit',
        'reset' => 'Reset',
        'close' => 'Close',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
    ],

    // Settings
    'settings' => [
        'title' => 'Settings',
        'subtitle' => 'Manage your account preferences and language settings',
        'language' => [
            'title' => 'Language Preference',
            'description' => 'Choose your preferred language for the site interface.',
            'update' => 'Update Language',
            'current' => 'Current',
        ],
        'theme' => [
            'title' => 'Interface Theme',
            'description' => 'Choose your preferred visual theme for the site.',
            'update' => 'Update Theme',
        ],
        'skins' => [
            'bismillah' => [
                'name' => 'Bismillah',
                'description' => 'Islamic green theme with traditional styling',
            ],
            'muslim' => [
                'name' => 'Muslim',
                'description' => 'Clean, modern interface with blue accents',
            ],
        ],
    ],

    // Authentication
    'auth' => [
        'login' => [
            'title' => 'Login',
            'subtitle' => 'Sign in to your account',
            'email' => 'Email Address',
            'password' => 'Password',
            'remember' => 'Remember me',
            'forgot' => 'Forgot your password?',
            'submit' => 'Sign In',
            'no_account' => "Don't have an account?",
            'signup' => 'Sign up',
        ],
        'register' => [
            'title' => 'Create Account',
            'subtitle' => 'Join our community',
            'name' => 'Full Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'agree_terms' => 'I agree to the terms and conditions',
            'submit' => 'Create Account',
            'have_account' => 'Already have an account?',
            'signin' => 'Sign in',
        ],
        'logout' => [
            'title' => 'Logout',
            'message' => 'Are you sure you want to logout?',
            'confirm' => 'Yes, Logout',
            'cancel' => 'Cancel',
        ],
    ],

    // Messages
    'messages' => [
        'success' => [
            'language_updated' => 'Language updated successfully',
            'settings_saved' => 'Settings saved successfully',
            'profile_updated' => 'Profile updated successfully',
        ],
        'error' => [
            'invalid_language' => 'Invalid language selected',
            'settings_save_failed' => 'Failed to save settings',
            'unauthorized' => 'You are not authorized to perform this action',
            'not_found' => 'The requested resource was not found',
            'server_error' => 'An internal server error occurred',
        ],
        'info' => [
            'loading' => 'Loading...',
            'no_results' => 'No results found',
            'select_language' => 'Please select a language',
        ],
    ],

    // Quran
    'quran' => [
        'title' => 'Quran',
        'browse' => 'Browse Quran',
        'search' => 'Search Quran',
        'juz_view' => 'Juz View',
        'page_view' => 'Page View',
        'surah' => 'Surah',
        'ayah' => 'Ayah',
        'juz' => 'Juz',
        'page' => 'Page',
        'translation' => 'Translation',
        'tafsir' => 'Tafsir',
        'audio' => 'Audio',
    ],

    // Hadith
    'hadith' => [
        'title' => 'Hadith',
        'browse' => 'Browse Hadith',
        'search' => 'Search Hadith',
        'collection' => 'Collection',
        'narrator' => 'Narrator',
        'grade' => 'Grade',
        'category' => 'Category',
        'book' => 'Book',
        'chapter' => 'Chapter',
    ],

    // Wiki
    'wiki' => [
        'title' => 'Wiki',
        'browse' => 'Browse Articles',
        'search' => 'Search Articles',
        'create' => 'Create Article',
        'edit' => 'Edit Article',
        'history' => 'Article History',
        'recent' => 'Recent Changes',
        'categories' => 'Categories',
        'tags' => 'Tags',
    ],

    // Community
    'community' => [
        'title' => 'Community',
        'users' => 'Users',
        'discussions' => 'Discussions',
        'forums' => 'Forums',
        'groups' => 'Groups',
        'events' => 'Events',
        'contribute' => 'Contribute',
        'volunteer' => 'Volunteer',
    ],

    // Footer
    'footer' => [
        'about' => 'About IslamWiki',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms of Service',
        'contact' => 'Contact Us',
        'help' => 'Help & Support',
        'donate' => 'Donate',
        'copyright' => '© 2024 IslamWiki. All rights reserved.',
    ],

    // Search
    'search' => [
        'placeholder' => 'Search Quran, Hadith, Scholars, Articles...',
        'results' => 'Search Results',
        'no_results' => 'No results found for "{{query}}"',
        'suggestions' => 'Suggestions',
        'filters' => 'Filters',
        'sort_by' => 'Sort by',
        'relevance' => 'Relevance',
        'date' => 'Date',
        'title' => 'Title',
    ],

    // Pagination
    'pagination' => [
        'previous' => 'Previous',
        'next' => 'Next',
        'page' => 'Page {{current}} of {{total}}',
        'showing' => 'Showing {{from}} to {{to}} of {{total}} results',
        'first' => 'First',
        'last' => 'Last',
    ],

    // Date and Time
    'datetime' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'tomorrow' => 'Tomorrow',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
        'ago' => '{{time}} ago',
        'in' => 'in {{time}}',
        'just_now' => 'Just now',
        'minutes' => '{{count}} minute|{{count}} minutes',
        'hours' => '{{count}} hour|{{count}} hours',
        'days' => '{{count}} day|{{count}} days',
        'weeks' => '{{count}} week|{{count}} weeks',
        'months' => '{{count}} month|{{count}} months',
        'years' => '{{count}} year|{{count}} years',
    ],

    // Islamic Terms
    'islamic' => [
        'salah' => 'Prayer',
        'dua' => 'Supplication',
        'dhikr' => 'Remembrance',
        'sadaqah' => 'Charity',
        'zakat' => 'Alms',
        'hajj' => 'Pilgrimage',
        'umrah' => 'Minor Pilgrimage',
        'ramadan' => 'Ramadan',
        'eid' => 'Eid',
        'muharram' => 'Muharram',
        'safar' => 'Safar',
        'rabi_al_awwal' => "Rabi' al-Awwal",
        'rabi_al_thani' => "Rabi' al-Thani",
        'jumada_al_ula' => 'Jumada al-Ula',
        'jumada_al_thaniyah' => 'Jumada al-Thaniyah',
        'rajab' => 'Rajab',
        'shaban' => "Sha'ban",
        'shawwal' => 'Shawwal',
        'dhu_al_qaadah' => 'Dhu al-Qadah',
        'dhu_al_hijjah' => 'Dhu al-Hijjah',
    ],
]; 
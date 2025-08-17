<?php

return [
    // Navigation
    'nav' => [
        'home' => 'الرئيسية',
        'quran' => 'القرآن',
        'hadith' => 'الحديث',
        'wiki' => 'الموسوعة',
        'sciences' => 'العلوم الإسلامية',
        'community' => 'المجتمع',
        'docs' => 'الوثائق',
        'about' => 'حول',
        'help' => 'المساعدة',
        'contribute' => 'المساهمة',
        'search' => 'البحث',
        'login' => 'تسجيل الدخول',
        'register' => 'انضم',
        'logout' => 'تسجيل الخروج',
        'dashboard' => 'لوحة التحكم',
        'profile' => 'الملف الشخصي',
        'settings' => 'الإعدادات',
    ],

    // Common Actions
    'actions' => [
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'create' => 'إنشاء',
        'update' => 'تحديث',
        'search' => 'البحث',
        'filter' => 'تصفية',
        'sort' => 'ترتيب',
        'view' => 'عرض',
        'download' => 'تحميل',
        'upload' => 'رفع',
        'submit' => 'إرسال',
        'reset' => 'إعادة تعيين',
        'close' => 'إغلاق',
        'back' => 'رجوع',
        'next' => 'التالي',
        'previous' => 'السابق',
    ],

    // Settings
    'settings' => [
        'title' => 'الإعدادات',
        'subtitle' => 'إدارة تفضيلات حسابك وإعدادات اللغة',
        'language' => [
            'title' => 'تفضيل اللغة',
            'description' => 'اختر لغتك المفضلة لواجهة الموقع.',
            'update' => 'تحديث اللغة',
            'current' => 'الحالية',
        ],
        'theme' => [
            'title' => 'مظهر الواجهة',
            'description' => 'اختر المظهر المرئي المفضل لديك للموقع.',
            'update' => 'تحديث المظهر',
        ],
        'skins' => [
            'bismillah' => [
                'name' => 'بسم الله',
                'description' => 'مظهر إسلامي أخضر مع تصميم تقليدي',
            ],
            'muslim' => [
                'name' => 'مسلم',
                'description' => 'واجهة نظيفة وعصرية مع لمسات زرقاء',
            ],
        ],
    ],

    // Authentication
    'auth' => [
        'login' => [
            'title' => 'تسجيل الدخول',
            'subtitle' => 'سجل دخولك إلى حسابك',
            'email' => 'عنوان البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'remember' => 'تذكرني',
            'forgot' => 'نسيت كلمة المرور؟',
            'submit' => 'تسجيل الدخول',
            'no_account' => 'ليس لديك حساب؟',
            'signup' => 'إنشاء حساب',
        ],
        'register' => [
            'title' => 'إنشاء حساب',
            'subtitle' => 'انضم إلى مجتمعنا',
            'name' => 'الاسم الكامل',
            'email' => 'عنوان البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'confirm_password' => 'تأكيد كلمة المرور',
            'agree_terms' => 'أوافق على الشروط والأحكام',
            'submit' => 'إنشاء حساب',
            'have_account' => 'لديك حساب بالفعل؟',
            'signin' => 'تسجيل الدخول',
        ],
        'logout' => [
            'title' => 'تسجيل الخروج',
            'message' => 'هل أنت متأكد من أنك تريد تسجيل الخروج؟',
            'confirm' => 'نعم، تسجيل الخروج',
            'cancel' => 'إلغاء',
        ],
    ],

    // Messages
    'messages' => [
        'success' => [
            'language_updated' => 'تم تحديث اللغة بنجاح',
            'settings_saved' => 'تم حفظ الإعدادات بنجاح',
            'profile_updated' => 'تم تحديث الملف الشخصي بنجاح',
        ],
        'error' => [
            'invalid_language' => 'لغة غير صالحة تم اختيارها',
            'settings_save_failed' => 'فشل في حفظ الإعدادات',
            'unauthorized' => 'غير مصرح لك بتنفيذ هذا الإجراء',
            'not_found' => 'لم يتم العثور على المورد المطلوب',
            'server_error' => 'حدث خطأ داخلي في الخادم',
        ],
        'info' => [
            'loading' => 'جاري التحميل...',
            'no_results' => 'لم يتم العثور على نتائج',
            'select_language' => 'يرجى اختيار لغة',
        ],
    ],

    // Quran
    'quran' => [
        'title' => 'القرآن',
        'browse' => 'تصفح القرآن',
        'search' => 'البحث في القرآن',
        'juz_view' => 'عرض الجزء',
        'page_view' => 'عرض الصفحة',
        'surah' => 'السورة',
        'ayah' => 'الآية',
        'juz' => 'الجزء',
        'page' => 'الصفحة',
        'translation' => 'الترجمة',
        'tafsir' => 'التفسير',
        'audio' => 'الصوت',
    ],

    // Hadith
    'hadith' => [
        'title' => 'الحديث',
        'browse' => 'تصفح الحديث',
        'search' => 'البحث في الحديث',
        'collection' => 'المجموعة',
        'narrator' => 'الراوي',
        'grade' => 'الدرجة',
        'category' => 'الفئة',
        'book' => 'الكتاب',
        'chapter' => 'الباب',
    ],

    // Wiki
    'wiki' => [
        'title' => 'الموسوعة',
        'browse' => 'تصفح المقالات',
        'search' => 'البحث في المقالات',
        'create' => 'إنشاء مقال',
        'edit' => 'تعديل المقال',
        'history' => 'تاريخ المقال',
        'recent' => 'التغييرات الأخيرة',
        'categories' => 'الفئات',
        'tags' => 'العلامات',
    ],

    // Community
    'community' => [
        'title' => 'المجتمع',
        'users' => 'المستخدمون',
        'discussions' => 'المناقشات',
        'forums' => 'المنتديات',
        'groups' => 'المجموعات',
        'events' => 'الأحداث',
        'contribute' => 'المساهمة',
        'volunteer' => 'التطوع',
    ],

    // Footer
    'footer' => [
        'about' => 'حول إسلام ويكي',
        'privacy' => 'سياسة الخصوصية',
        'terms' => 'شروط الخدمة',
        'contact' => 'اتصل بنا',
        'help' => 'المساعدة والدعم',
        'donate' => 'تبرع',
        'copyright' => '© 2024 إسلام ويكي. جميع الحقوق محفوظة.',
    ],

    // Search
    'search' => [
        'placeholder' => 'البحث في القرآن والحديث والعلماء والمقالات...',
        'results' => 'نتائج البحث',
        'no_results' => 'لم يتم العثور على نتائج لـ "{{query}}"',
        'suggestions' => 'الاقتراحات',
        'filters' => 'المرشحات',
        'sort_by' => 'ترتيب حسب',
        'relevance' => 'الأهمية',
        'date' => 'التاريخ',
        'title' => 'العنوان',
    ],

    // Pagination
    'pagination' => [
        'previous' => 'السابق',
        'next' => 'التالي',
        'page' => 'الصفحة {{current}} من {{total}}',
        'showing' => 'عرض {{from}} إلى {{to}} من {{total}} نتيجة',
        'first' => 'الأول',
        'last' => 'الأخير',
    ],

    // Date and Time
    'datetime' => [
        'today' => 'اليوم',
        'yesterday' => 'أمس',
        'tomorrow' => 'غداً',
        'this_week' => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'this_year' => 'هذا العام',
        'ago' => 'منذ {{time}}',
        'in' => 'في {{time}}',
        'just_now' => 'الآن',
        'minutes' => '{{count}} دقيقة|{{count}} دقائق',
        'hours' => '{{count}} ساعة|{{count}} ساعات',
        'days' => '{{count}} يوم|{{count}} أيام',
        'weeks' => '{{count}} أسبوع|{{count}} أسابيع',
        'months' => '{{count}} شهر|{{count}} أشهر',
        'years' => '{{count}} سنة|{{count}} سنوات',
    ],

    // Islamic Terms
    'islamic' => [
        'salah' => 'الصلاة',
        'dua' => 'الدعاء',
        'dhikr' => 'الذكر',
        'sadaqah' => 'الصدقة',
        'zakat' => 'الزكاة',
        'hajj' => 'الحج',
        'umrah' => 'العمرة',
        'ramadan' => 'رمضان',
        'eid' => 'العيد',
        'muharram' => 'محرم',
        'safar' => 'صفر',
        'rabi_al_awwal' => 'ربيع الأول',
        'rabi_al_thani' => 'ربيع الثاني',
        'jumada_al_ula' => 'جمادى الأولى',
        'jumada_al_thaniyah' => 'جمادى الآخرة',
        'rajab' => 'رجب',
        'shaban' => 'شعبان',
        'shawwal' => 'شوال',
        'dhu_al_qaadah' => 'ذو القعدة',
        'dhu_al_hijjah' => 'ذو الحجة',
    ],
]; 
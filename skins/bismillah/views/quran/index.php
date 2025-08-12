<?php
/**
 * Quran Index View
 * Displays the main Quran interface with navigation
 */
?>
<!DOCTYPE html>
<html lang="<?php echo $language ?? 'en'; ?>" dir="<?php echo $direction ?? 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Quran - Islam Wiki'; ?></title>
    <link rel="stylesheet" href="/skins/bismillah/css/bismillah.css">
    <link rel="stylesheet" href="/skins/bismillah/css/quran.css">
</head>
<body class="bismillah-body">
    <header class="bismillah-header">
        <div class="bismillah-container">
            <h1 class="bismillah-title">القرآن الكريم</h1>
            <p class="bismillah-subtitle">The Noble Quran</p>
        </div>
    </header>

    <main class="bismillah-main">
        <div class="bismillah-container">
            <!-- Language Selection -->
            <div class="quran-language-selector">
                <label for="language">Language:</label>
                <select id="language" class="quran-select">
                    <option value="en">English</option>
                    <option value="ar">العربية</option>
                    <option value="ur">اردو</option>
                    <option value="tr">Türkçe</option>
                    <option value="id">Bahasa Indonesia</option>
                </select>
            </div>

            <!-- Search Section -->
            <div class="quran-search-section">
                <form id="quran-search-form" class="quran-search-form">
                    <input type="text" id="search-query" placeholder="Search Quran..." class="quran-search-input">
                    <button type="submit" class="quran-search-btn">Search</button>
                </form>
            </div>

            <!-- Navigation Tabs -->
            <div class="quran-tabs">
                <button class="quran-tab active" data-tab="surahs">Surahs</button>
                <button class="quran-tab" data-tab="juz">Juz</button>
                <button class="quran-tab" data-tab="pages">Pages</button>
            </div>

            <!-- Surahs Tab -->
            <div id="surahs-tab" class="quran-tab-content active">
                <div class="quran-grid" id="surahs-grid">
                    <!-- Surahs will be loaded here -->
                </div>
            </div>

            <!-- Juz Tab -->
            <div id="juz-tab" class="quran-tab-content">
                <div class="quran-grid" id="juz-grid">
                    <!-- Juz will be loaded here -->
                </div>
            </div>

            <!-- Pages Tab -->
            <div id="pages-tab" class="quran-tab-content">
                <div class="quran-grid" id="pages-grid">
                    <!-- Pages will be loaded here -->
                </div>
            </div>

            <!-- Search Results -->
            <div id="search-results" class="quran-search-results" style="display: none;">
                <h3>Search Results</h3>
                <div id="search-results-content"></div>
            </div>
        </div>
    </main>

    <footer class="bismillah-footer">
        <div class="bismillah-container">
            <p>&copy; <?php echo date('Y'); ?> Islam Wiki. All rights reserved.</p>
        </div>
    </footer>

    <script src="/resources/assets/js/zamzam.js"></script>
    <script src="/skins/bismillah/js/quran.js"></script>
</body>
</html>

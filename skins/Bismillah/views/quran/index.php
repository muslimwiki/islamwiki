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
    <?php
    // Use absolute path from document root for reliability
    $basePath = '/local.islam.wiki';
    $skinPath = $basePath . '/skins/Bismillah';
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Quran - Islam Wiki'; ?></title>
    <link rel="stylesheet" href="<?php echo $skinPath; ?>/css/bismillah.css">
    <link rel="stylesheet" href="<?php echo $skinPath; ?>/css/quran.css">
    <style>
        /* Test style - should make background red if CSS is loading */
        body { background-color: red !important; }
    </style>
</head>
<body class="quran-page">
    <header class="quran-header">
        <div class="quran-header-content">
            <div>
                <h1 class="quran-title">Quran</h1>
                <p class="quran-subtitle">The Noble Quran</p>
            </div>
        </div>
    </header>

    <main class="quran-main">
        <!-- Search Section -->
        <div class="quran-search-section">
            <form id="quran-search-form" class="quran-search-form">
                <input type="text" id="search-query" placeholder="Search Quran..." class="quran-search-input">
            </form>
        </div>

        <?php if (!empty($randomAyah)): ?>
            <!-- Random Ayah Section -->
            <div class="random-ayah-section">
                <h2>Random Ayah</h2>
                <div class="ayah-card">
                    <div class="ayah-arabic">
                        <span class="ayah-number"><?php echo $randomAyah['surah_number'] ?? ''; ?>:<?php echo $randomAyah['ayah_number'] ?? ''; ?></span>
                        <?php echo $randomAyah['text_arabic'] ?? ''; ?>
                    </div>
                    <?php if (!empty($randomAyah['translation_text'])): ?>
                        <div class="ayah-translation">
                            <p><?php echo $randomAyah['translation_text']; ?></p>
                            <?php if (!empty($randomAyah['translator_name'])): ?>
                                <div class="translation-meta">
                                    <span class="translator">— <?php echo htmlspecialchars($randomAyah['translator_name']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="ayah-reference">
                        <a href="/quran/<?php echo $randomAyah['surah_number'] ?? ''; ?>/<?php echo $randomAyah['ayah_number'] ?? ''; ?>">View Full Ayah</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Translation Selector (Moved to bottom) -->
        <div class="translation-selector">
            <form method="get" action="" class="translation-form">
                <label for="translator">Translation:</label>
                <select name="translator" id="translator" onchange="this.form.submit()" class="translation-dropdown">
                    <?php foreach ($translators ?? [] as $trans): ?>
                        <?php $selected = ($translator ?? 'Saheeh International') === ($trans['translator'] ?? $trans['name'] ?? '') ? 'selected' : ''; ?>
                        <option value="<?php echo htmlspecialchars($trans['translator'] ?? $trans['name'] ?? ''); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($trans['name'] ?? $trans['translator'] ?? 'Unknown'); ?>
                            <?php if (!empty($trans['language_name'])): ?>
                                (<?php echo htmlspecialchars($trans['language_name']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="lang" value="<?php echo htmlspecialchars($language ?? 'en'); ?>">
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

    <footer class="quran-footer">
        <div class="quran-container">
            <p>&copy; <?php echo date('Y'); ?> Islam Wiki. All rights reserved.</p>
        </div>
    </footer>

    <script src="/resources/assets/js/zamzam.js"></script>
    <script src="/skins/bismillah/js/quran.js"></script>
</body>
</html>

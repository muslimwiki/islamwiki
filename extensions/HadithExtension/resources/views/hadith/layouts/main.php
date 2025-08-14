<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Hadith' ?> - <?= config('app.name', 'Islam Wiki') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/hadith.css">
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="/assets/js/hadith.js" defer></script>
    
    <?= $this->section('head') ?>
</head>
<body class="hadith-page">
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">
                    <a href="/hadith" class="text-white text-decoration-none">
                        <i class="bi bi-book"></i> Hadith
                    </a>
                </h1>
                
                <div>
                    <a href="/" class="text-white me-3">Home</a>
                    <a href="/quran" class="text-white me-3">Quran</a>
                    <a href="/hadith" class="text-white fw-bold">Hadith</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <?= $this->section('content') ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Hadith Collections</h5>
                    <ul class="list-unstyled">
                        <?php foreach ($this->get('collections', []) as $collection): ?>
                            <li>
                                <a href="/hadith/collection/<?= $collection->slug ?>">
                                    <?= $this->e($collection->name) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?= date('Y') ?> <?= config('app.name', 'Islam Wiki') ?>. All rights reserved.</p>
                    <p class="text-muted small">Hadith data is sourced from authentic collections.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <?= $this->section('scripts') ?>
</body>
</html>

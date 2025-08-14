<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <h1 class="mb-4">Hadith Collections</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <form action="/hadith/search" method="get" class="mb-0">
                    <div class="input-group">
                        <input type="text" 
                               name="q" 
                               class="form-control form-control-lg" 
                               placeholder="Search hadith..."
                               value="<?= $this->e($query ?? '') ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($featuredHadith)): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">Featured Hadith</h2>
                </div>
                <div class="card-body">
                    <div class="hadith-text arabic-text mb-3">
                        <?= $featuredHadith->arabic_text ?>
                    </div>
                    <div class="hadith-translation mb-3">
                        <?= $featuredHadith->english_text ?: $featuredHadith->translation ?>
                    </div>
                    <div class="text-muted small">
                        <a href="/hadiths/collection/<?= $featuredHadith->collection()->slug ?>/hadith/<?= $featuredHadith->hadith_number ?>">
                            <?= $featuredHadith->collection()->name ?>
                            <?= $featuredHadith->hadith_number ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-light">
                <h1 class="h3 mb-4">Hadith Collections</h1>
                
                <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                    <?php foreach ($collections as $collection): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h2 class="h5 card-title">
                                        <a href="/hadiths/collection/<?= $collection->slug ?>" class="text-decoration-none">
                                            <?= $this->e($collection->name) ?>
                                        </a>
                                    </h2>
                                    <p class="card-text">
                                        <?= number_format($collection->total_hadith) ?> hadith in <?= $collection->total_books ?> books
                                    </p>
                                    <a href="/hadiths/collection/<?= $collection->slug ?>" class="btn btn-sm btn-outline-primary mt-3">
                                        Browse Collection
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">About Hadith</h2>
            </div>
            <div class="card-body">
                <p>Hadith are the sayings, actions, and approvals of the Prophet Muhammad (peace be upon him) as reported by his companions.</p>
                <p>They are the second primary source of Islamic guidance after the Quran.</p>
                <hr>
                <h3 class="h6">Major Hadith Collections</h3>
                <ul class="list-unstyled">
                    <li><a href="/hadith/collection/sahih-bukhari">Sahih al-Bukhari</a></li>
                    <li><a href="/hadith/collection/sahih-muslim">Sahih Muslim</a></li>
                    <li><a href="/hadith/collection/sunan-abu-dawud">Sunan Abu Dawud</a></li>
                    <li><a href="/hadith/collection/jami-at-tirmidhi">Jami` at-Tirmidhi</a></li>
                    <li><a href="/hadith/collection/sunan-nasai">Sunan an-Nasa'i</a></li>
                    <li><a href="/hadith/collection/sunan-ibn-majah">Sunan Ibn Majah</a></li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Quick Links</h2>
            </div>
            <div class="list-group list-group-flush">
                <a href="/hadith/random" class="list-group-item list-group-item-action">
                    <i class="bi bi-shuffle me-2"></i> Random Hadith
                </a>
                <a href="/hadith/daily" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar3 me-2"></i> Hadith of the Day
                </a>
                <a href="/hadith/favorites" class="list-group-item list-group-item-action">
                    <i class="bi bi-bookmark-heart me-2"></i> My Favorites
                </a>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

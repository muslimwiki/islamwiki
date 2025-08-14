<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/hadiths">Hadith</a></li>
                <li class="breadcrumb-item"><a href="/hadiths/collection/<?= $collection->slug ?>"><?= $this->e($collection->name) ?></a></li>
                <?php if ($book): ?>
                    <li class="breadcrumb-item"><a href="/hadiths/collection/<?= $collection->slug ?>/book/<?= $book->slug ?>"><?= $this->e($book->name) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">Hadith <?= $hadith->hadith_number ?></li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h3 mb-4">
                    <?= $collection->name ?> <?= $hadith->hadith_number ?>
                    <?php if (!empty($hadith->secondary_number)): ?>
                        <small class="text-muted">(<?= $hadith->secondary_number ?>)</small>
                    <?php endif; ?>
                </h1>
                
                <!-- Arabic Text -->
                <div class="arabic-text mb-4" style="font-size: 1.5rem; line-height: 2.5; text-align: right; direction: rtl;">
                    <?= $hadith->arabic_text ?>
                </div>
                
                <!-- Translation -->
                <?php if (!empty($hadith->english_text) || !empty($hadith->translation)): ?>
                    <div class="translation mb-4 p-3 bg-light rounded">
                        <h3 class="h5 mb-3">Translation</h3>
                        <div style="font-size: 1.1rem; line-height: 1.8;">
                            <?= $hadith->english_text ?: $hadith->translation ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Grade -->
                <?php if (!empty($hadith->grade)): ?>
                    <div class="grade mb-4">
                        <h3 class="h5 d-inline-block me-2">Grade:</h3>
                        <span class="badge bg-<?= $this->getGradeBadgeClass($hadith->grade) ?>">
                            <?= $this->e(ucfirst($hadith->grade)) ?>
                        </span>
                        <?php if (!empty($hadith->graded_by)): ?>
                            <span class="text-muted ms-2">(Graded by <?= $this->e($hadith->graded_by) ?>)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Navigation -->
                <?php if (isset($navigation['previous']) || isset($navigation['next'])): ?>
                    <div class="hadith-navigation d-flex justify-content-between mt-5 pt-3 border-top">
                        <?php if (isset($navigation['previous'])): ?>
                            <a href="/hadiths/collection/<?= $collection->slug ?>/hadith/<?= $navigation['previous']->hadith_number ?>" class="btn btn-outline-primary">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        
                        <?php if (isset($navigation['next'])): ?>
                            <a href="/hadiths/collection/<?= $collection->slug ?>/hadith/<?= $navigation['next']->hadith_number ?>" class="btn btn-outline-primary">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Collection Info -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">About This Collection</h2>
            </div>
            <div class="card-body">
                <h3 class="h6"><?= $this->e($collection->name) ?></h3>
                <p class="small">
                    <?= $this->e(mb_substr($collection->description, 0, 200)) ?><?= mb_strlen($collection->description) > 200 ? '...' : '' ?>
                </p>
                <a href="/hadith/collection/<?= $collection->slug ?>" class="btn btn-sm btn-outline-primary">
                    View Collection
                </a>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Quick Links</h2>
            </div>
            <div class="list-group list-group-flush">
                <?php if ($book): ?>
                    <a href="/hadith/collection/<?= $collection->slug ?>/book/<?= $book->slug ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-book me-2"></i> Back to <?= $this->e($book->name) ?>
                    </a>
                <?php else: ?>
                    <a href="/hadith/collection/<?= $collection->slug ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-collection me-2"></i> Back to <?= $this->e($collection->name) ?>
                    </a>
                <?php endif; ?>
                
                <a href="/hadith/collection/<?= $collection->slug ?>/random" class="list-group-item list-group-item-action">
                    <i class="bi bi-shuffle me-2"></i> Random Hadith
                </a>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/hadiths">Hadith</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $this->e($collection->name) ?></li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h3 mb-3"><?= $this->e($collection->name) ?></h1>
                
                <?php if (!empty($collection->description)): ?>
                    <div class="mb-4"><?= nl2br($this->e($collection->description)) ?></div>
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="h5 mb-1">Total Hadith</h3>
                                <p class="h2 text-primary mb-0"><?= number_format($collection->total_hadith) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="h5 mb-1">Books</h3>
                                <p class="h2 text-primary mb-0"><?= number_format($collection->total_books) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Books in This Collection</h2>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($books as $book): ?>
                    <a href="/hadiths/collection/<?= $collection->slug ?>/book/<?= $book->slug ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h6 mb-1"><?= $this->e($book->name) ?></h3>
                                <?php if (!empty($book->description)): ?>
                                    <p class="mb-0 text-muted small"><?= $this->e($book->description) ?></p>
                                <?php endif; ?>
                                <?php if ($book->total_hadith > 0): ?>
                                    <span class="badge bg-light text-dark mt-1">
                                        <?= number_format($book->total_hadith) ?> hadith
                                    </span>
                                <?php endif; ?>
                            </div>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <?php if (!empty($sampleHadith)): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">Sample Hadith</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($sampleHadith as $hadith): ?>
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="hadith-text arabic-text mb-2">
                                <?= $hadith->arabic_text ?>
                            </div>
                            <div class="hadith-translation mb-2">
                                <?= $hadith->english_text ?: $hadith->translation ?>
                            </div>
                            <div class="text-muted small">
                                <a href="/hadith/collection/<?= $collection->slug ?>/hadith/<?= $hadith->hadith_number ?>">
                                    <?= $collection->name ?> <?= $hadith->hadith_number ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <a href="/hadith/collection/<?= $collection->slug ?>/random" class="btn btn-outline-primary w-100">
                        <i class="bi bi-shuffle"></i> Random Hadith
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">About This Collection</h2>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Compiler</dt>
                    <dd><?= $this->e($collection->compiler ?? 'Unknown') ?></dd>
                    
                    <?php if (!empty($collection->compiled_year)): ?>
                        <dt>Compiled Year</dt>
                        <dd><?= $this->e($collection->compiled_year) ?> AH</dd>
                    <?php endif; ?>
                    
                    <?php if (!empty($collection->total_hadith)): ?>
                        <dt>Total Hadith</dt>
                        <dd><?= number_format($collection->total_hadith) ?></dd>
                    <?php endif; ?>
                    
                    <?php if (!empty($collection->total_books)): ?>
                        <dt>Books</dt>
                        <dd><?= number_format($collection->total_books) ?></dd>
                    <?php endif; ?>
                    
                    <?php if (!empty($collection->status)): ?>
                        <dt>Status</dt>
                        <dd>
                            <span class="badge bg-<?= $collection->status === 'sahih' ? 'success' : 'info' ?>">
                                <?= ucfirst($collection->status) ?>
                            </span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

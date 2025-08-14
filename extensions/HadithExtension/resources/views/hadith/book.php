<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/hadiths">Hadith</a></li>
                <li class="breadcrumb-item"><a href="/hadiths/collection/<?= $collection->slug ?>"><?= $this->e($collection->name) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $this->e($book->name) ?></li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h3 mb-3"><?= $this->e($book->name) ?></h1>
                
                <?php if (!empty($book->description)): ?>
                    <div class="mb-4"><?= nl2br($this->e($book->description)) ?></div>
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="h5 mb-1">Total Hadith</h3>
                                <p class="h2 text-primary mb-0"><?= number_format($totalHadith) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="h5 mb-1">Collection</h3>
                                <p class="h4 mb-0">
                                    <a href="/hadith/collection/<?= $collection->slug ?>">
                                        <?= $this->e($collection->name) ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Hadith in This Book</h2>
                    <div class="btn-group" role="group">
                        <a href="?view=list" class="btn btn-sm btn-outline-secondary <?= ($view ?? 'list') === 'list' ? 'active' : '' ?>">
                            <i class="bi bi-list-ul"></i> List
                        </a>
                        <a href="?view=grid" class="btn btn-sm btn-outline-secondary <?= ($view ?? '') === 'grid' ? 'active' : '' ?>">
                            <i class="bi bi-grid"></i> Grid
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (empty($hadith)): ?>
                <div class="card-body text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="bi bi-book" style="font-size: 3rem;"></i>
                    </div>
                    <h3>No hadith found</h3>
                    <p class="text-muted">There are no hadith available in this book.</p>
                </div>
            <?php else: ?>
                <?php if (($view ?? 'list') === 'grid'): ?>
                    <div class="row row-cols-1 row-cols-md-2 g-0">
                        <?php foreach ($hadith as $h): ?>
                            <div class="col border-bottom border-end">
                                <a href="/hadith/collection/<?= $collection->slug ?>/hadith/<?= $h->hadith_number ?>" class="text-decoration-none text-dark d-block p-3">
                                    <div class="arabic-text mb-2" style="font-size: 1.1rem; line-height: 1.8;">
                                        <?= $h->arabic_text ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= $collection->name ?> <?= $h->hadith_number ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($hadith as $h): ?>
                            <a href="/hadith/collection/<?= $collection->slug ?>/hadith/<?= $h->hadith_number ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-3">
                                        <span class="badge bg-light text-dark mb-2">
                                            <?= $h->hadith_number ?>
                                        </span>
                                        <div class="arabic-text mb-2" style="font-size: 1.1rem; line-height: 1.8;">
                                            <?= $h->arabic_text ?>
                                        </div>
                                        <?php if (!empty($h->english_text) || !empty($h->translation)): ?>
                                            <div class="text-muted small">
                                                <?= $this->e(mb_substr($h->english_text ?: $h->translation, 0, 150)) ?><?= mb_strlen($h->english_text ?: $h->translation) > 150 ? '...' : '' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav aria-label="Hadith pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        Previous
                                    </a>
                                </li>
                                
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $startPage + 4);
                                $startPage = max(1, $endPage - 4);
                                ?>
                                
                                <?php if ($startPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                                <?php endif; ?>
                                
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>" <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        Next
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            Page <?= $currentPage ?> of <?= $totalPages ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">About This Book</h2>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Book Name</dt>
                    <dd><?= $this->e($book->name) ?></dd>
                    
                    <dt>Collection</dt>
                    <dd>
                        <a href="/hadith/collection/<?= $collection->slug ?>">
                            <?= $this->e($collection->name) ?>
                        </a>
                    </dd>
                    
                    <dt>Total Hadith</dt>
                    <dd><?= number_format($totalHadith) ?></dd>
                    
                    <?php if (!empty($book->book_number)): ?>
                        <dt>Book Number</dt>
                        <dd><?= $book->book_number ?></dd>
                    <?php endif; ?>
                    
                    <?php if (!empty($book->status)): ?>
                        <dt>Status</dt>
                        <dd>
                            <span class="badge bg-<?= $book->status === 'sahih' ? 'success' : 'info' ?>">
                                <?= ucfirst($book->status) ?>
                            </span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Quick Links</h2>
            </div>
            <div class="list-group list-group-flush">
                <a href="/hadith/collection/<?= $collection->slug ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i> Back to <?= $this->e($collection->name) ?>
                </a>
                <a href="/hadiths/collection/<?= $collection->slug ?>/book/<?= $book->slug ?>?view=<?= ($view ?? 'list') === 'list' ? 'grid' : 'list' ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-<?= ($view ?? 'list') === 'list' ? 'grid' : 'list-ul' ?> me-2"></i>
                    <?= ($view ?? 'list') === 'list' ? 'Grid View' : 'List View' ?>
                </a>
                <a href="/hadiths/collection/<?= $collection->slug ?>/book/<?= $book->slug ?>/random" class="list-group-item list-group-item-action">
                    <i class="bi bi-shuffle me-2"></i> Random Hadith from This Book
                </a>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

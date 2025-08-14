<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?n<div class="row">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/hadiths">Hadith</a></li>
                <li class="breadcrumb-item"><a href="/hadiths/narrators">Narrators</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $this->e($narrator->name) ?></li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="me-4 text-center">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px;">
                            <i class="bi bi-person" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                        <?php if (!empty($narrator->reliability_grade)): ?>
                            <span class="badge bg-<?= $this->getNarratorBadgeClass($narrator->reliability_grade) ?> mt-2">
                                <?= $this->e(ucfirst($narrator->reliability_grade)) ?> Narrator
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-grow-1">
                        <h1 class="h3 mb-2"><?= $this->e($narrator->name) ?></h1>
                        
                        <?php if (!empty($narrator->arabic_name)): ?>
                            <div class="arabic-text mb-3" style="font-size: 1.25rem; direction: rtl;">
                                <?= $narrator->arabic_name ?>
                            </div>
                        <?php endif; ?>
                        
                        <dl class="row mb-0">
                            <?php if (!empty($narrator->kunya)): ?>
                                <dt class="col-sm-3">Kunya</dt>
                                <dd class="col-sm-9"><?= $this->e($narrator->kunya) ?></dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->born)): ?>
                                <dt class="col-sm-3">Born</dt>
                                <dd class="col-sm-9">
                                    <?= $narrator->born ?>
                                    <?php if (!empty($narrator->birth_place)): ?>
                                        in <?= $this->e($narrator->birth_place) ?>
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->died)): ?>
                                <dt class="col-sm-3">Died</dt>
                                <dd class="col-sm-9">
                                    <?= $narrator->died ?>
                                    <?php if (!empty($narrator->death_place)): ?>
                                        in <?= $this->e($narrator->death_place) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($narrator->age)): ?>
                                        (aged <?= $narrator->age ?>)
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->tribe)): ?>
                                <dt class="col-sm-3">Tribe</dt>
                                <dd class="col-sm-9">
                                    <?= $this->e($narrator->tribe) ?>
                                    <?php if (!empty($narrator->arabic_tribe)): ?>
                                        (<span class="arabic-text"><?= $narrator->arabic_tribe ?></span>)
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->generation)): ?>
                                <dt class="col-sm-3">Generation</dt>
                                <dd class="col-sm-9">
                                    <?= $this->e(ucfirst($narrator->generation)) ?>
                                    <?php if (!empty($narrator->arabic_generation)): ?>
                                        (<span class="arabic-text"><?= $narrator->arabic_generation ?></span>)
                                    <?php endif; ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->teachers)): ?>
                                <dt class="col-sm-3">Teachers</dt>
                                <dd class="col-sm-9">
                                    <?= $this->e($narrator->teachers) ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->students)): ?>
                                <dt class="col-sm-3">Students</dt>
                                <dd class="col-sm-9">
                                    <?= $this->e($narrator->students) ?>
                                </dd>
                            <?php endif; ?>
                            
                            <?php if (!empty($narrator->total_hadith)): ?>
                                <dt class="col-sm-3">Total Narrations</dt>
                                <dd class="col-sm-9">
                                    <?= number_format($narrator->total_hadith) ?> hadith
                                </dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
                
                <?php if (!empty($narrator->biography)): ?>
                    <div class="mt-4">
                        <h3 class="h5">Biography</h3>
                        <div class="bio-content">
                            <?= $narrator->biography ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($narrator->reliability_notes)): ?>
                    <div class="mt-4">
                        <h3 class="h5">Reliability Assessment</h3>
                        <div class="reliability-notes">
                            <?= $narrator->reliability_notes ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($hadith)): ?>
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Narrated Hadith</h2>
                        <div class="text-muted small">
                            <?= number_format($totalHadith) ?> hadith
                        </div>
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <?php foreach ($hadith as $h): ?>
                        <a href="/hadith/collection/<?= $h->collection()->slug ?>/hadith/<?= $h->hadith_number ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-3">
                                    <div class="fw-bold">
                                        <?= $h->collection()->name ?> <?= $h->hadith_number ?>
                                        <?php if (!empty($h->secondary_number)): ?>
                                            <small class="text-muted">(<?= $h->secondary_number ?>)</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="arabic-text my-2" style="font-size: 1.1rem; line-height: 1.8; direction: rtl;">
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
                
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav aria-label="Narrated hadith pagination">
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
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Quick Links</h2>
            </div>
            <div class="list-group list-group-flush">
                <a href="/hadith/narrators" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i> Back to All Narrators
                </a>
                <a href="/hadith/search?narrator=<?= urlencode($narrator->name) ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-search me-2"></i> Search Hadith by <?= $this->e($narrator->name) ?>
                </a>
                <?php if (!empty($narrator->wikipedia_url)): ?>
                    <a href="<?= $narrator->wikipedia_url ?>" class="list-group-item list-group-item-action" target="_blank" rel="noopener">
                        <i class="bi bi-wikipedia me-2"></i> Wikipedia Page
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($relatedNarrators)): ?>
            <div class="card">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">Related Narrators</h2>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($relatedNarrators as $related): ?>
                        <a href="/hadiths/narrator/<?= $related->id ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?= $this->e($related->name) ?>
                                    <?php if (!empty($related->arabic_name)): ?>
                                        <div class="text-muted small"><?= $related->arabic_name ?></div>
                                    <?php endif; ?>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>

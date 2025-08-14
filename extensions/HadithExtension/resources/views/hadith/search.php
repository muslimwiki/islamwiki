<?php $this->extend('hadith/layouts/main'); ?>

<?php $this->section('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/hadiths">Hadith</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search</li>
            </ol>
        </nav>
        
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="h3 mb-4">Search Hadith</h1>
                
                <form action="/hadiths/search" method="get" class="mb-4">
                    <div class="input-group">
                        <input type="text" 
                               name="q" 
                               class="form-control form-control-lg" 
                               placeholder="Search hadith..."
                               value="<?= $this->e($query ?? '') ?>"
                               required>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="collection" class="form-label">Collection</label>
                            <select name="collection" id="collection" class="form-select">
                                <option value="">All Collections</option>
                                <?php foreach ($collections as $collection): ?>
                                    <option value="<?= $collection->id ?>" <?= ($selectedCollectionId ?? '') == $collection->id ? 'selected' : '' ?>>
                                        <?= $this->e($collection->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="language" class="form-label">Language</label>
                            <select name="lang" id="language" class="form-select">
                                <option value="en" <?= ($language ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="ar" <?= ($language ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
                                <option value="ur" <?= ($language ?? '') === 'ur' ? 'selected' : '' ?>>اردو</option>
                                <option value="id" <?= ($language ?? '') === 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                            </select>
                        </div>
                    </div>
                </form>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-warning">
                        <?= $this->e($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($results)): ?>
                    <div class="search-results">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h5 mb-0">
                                <?php if ($totalResults === 0): ?>
                                    No results found
                                <?php else: ?>
                                    <?= number_format($totalResults) ?> result<?= $totalResults !== 1 ? 's' : '' ?> found
                                <?php endif; ?>
                                <?php if (!empty($query)): ?>
                                    for "<?= $this->e($query) ?>"
                                <?php endif; ?>
                            </h2>
                            
                            <?php if ($totalResults > 0): ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Sort by: <?= $sort === 'relevance' ? 'Relevance' : 'Hadith Number' ?>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                        <li>
                                            <a class="dropdown-item <?= $sort === 'relevance' ? 'active' : '' ?>" 
                                               href="?q=<?= urlencode($query) ?>&sort=relevance">
                                                Relevance
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item <?= $sort === 'number' ? 'active' : '' ?>" 
                                               href="?q=<?= urlencode($query) ?>&sort=number">
                                                Hadith Number
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($totalResults > 0): ?>
                            <div class="list-group list-group-flush mb-4">
                                <?php foreach ($results as $hadith): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="me-3">
                                                <h3 class="h5 mb-2">
                                                    <a href="/hadith/collection/<?= $hadith->collection()->slug ?>/hadith/<?= $hadith->hadith_number ?>" class="text-decoration-none">
                                                        <?= $hadith->collection()->name ?> <?= $hadith->hadith_number ?>
                                                        <?php if (!empty($hadith->secondary_number)): ?>
                                                            <small class="text-muted">(<?= $hadith->secondary_number ?>)</small>
                                                        <?php endif; ?>
                                                    </a>
                                                </h3>
                                                
                                                <?php if (!empty($hadith->arabic_text)): ?>
                                                    <div class="arabic-text mb-2" style="font-size: 1.1rem; line-height: 1.8; text-align: right; direction: rtl;">
                                                        <?= $hadith->arabic_text ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($hadith->english_text) || !empty($hadith->translation)): ?>
                                                    <div class="translation mb-2" style="font-size: 1rem; line-height: 1.6;">
                                                        <?= $this->highlight($hadith->english_text ?: $hadith->translation, $query) ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($hadith->book())): ?>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-book"></i> 
                                                        <a href="/hadith/collection/<?= $hadith->collection()->slug ?>/book/<?= $hadith->book()->slug ?>" class="text-muted">
                                                            <?= $this->e($hadith->book()->name) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($hadith->grade)): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-<?= $this->getGradeBadgeClass($hadith->grade) ?>">
                                                            <?= $this->e(ucfirst($hadith->grade)) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Search results pagination" class="d-flex justify-content-center">
                                    <ul class="pagination">
                                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?q=<?= urlencode($query) ?>&page=<?= $currentPage - 1 ?><?= !empty($selectedCollectionId) ? '&collection=' . $selectedCollectionId : '' ?><?= !empty($language) ? '&lang=' . $language : '' ?>" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                                Previous
                                            </a>
                                        </li>
                                        
                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $startPage + 4);
                                        $startPage = max(1, $endPage - 4);
                                        ?>
                                        
                                        <?php if ($startPage > 1): ?>
                                            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($query) ?>&page=1<?= !empty($selectedCollectionId) ? '&collection=' . $selectedCollectionId : '' ?><?= !empty($language) ? '&lang=' . $language : '' ?>">1</a></li>
                                            <?php if ($startPage > 2): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?q=<?= urlencode($query) ?>&page=<?= $i ?><?= !empty($selectedCollectionId) ? '&collection=' . $selectedCollectionId : '' ?><?= !empty($language) ? '&lang=' . $language : '' ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($endPage < $totalPages): ?>
                                            <?php if ($endPage < $totalPages - 1): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>
                                            <li class="page-item"><a class="page-link" href="?q=<?= urlencode($query) ?>&page=<?= $totalPages ?><?= !empty($selectedCollectionId) ? '&collection=' . $selectedCollectionId : '' ?><?= !empty($language) ? '&lang=' . $language : '' ?>"><?= $totalPages ?></a></li>
                                        <?php endif; ?>
                                        
                                        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?q=<?= urlencode($query) ?>&page=<?= $currentPage + 1 ?><?= !empty($selectedCollectionId) ? '&collection=' . $selectedCollectionId : '' ?><?= !empty($language) ? '&lang=' . $language : '' ?>" <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                                Next
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="text-center text-muted small mt-2">
                                    Page <?= $currentPage ?> of <?= $totalPages ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="bi bi-search" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                        <h3>Search for Hadith</h3>
                        <p class="text-muted">Enter a search term to find hadith in our database.</p>
                        <p class="small text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Tip: Use quotes for exact matches, e.g., "prayer is the pillar of religion"
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Search Tips</h2>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>Exact phrase:</strong> Use quotes<br>
                        <code>"prayer is the key to paradise"</code>
                    </li>
                    <li class="mb-2">
                        <strong>Exclude words:</strong> Use - (minus sign)<br>
                        <code>prayer -friday</code>
                    </li>
                    <li class="mb-2">
                        <strong>Either word:</strong> Use OR (uppercase)<br>
                        <code>prayer OR salah</code>
                    </li>
                    <li class="mb-2">
                        <strong>Specific collection:</strong> Use collection:name<br>
                        <code>collection:sahih-bukhari prayer</code>
                    </li>
                    <li class="mb-2">
                        <strong>Specific book:</strong> Use book:name<br>
                        <code>book:faith prayer</code>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Popular Searches</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="?q=prayer" class="btn btn-sm btn-outline-secondary">Prayer</a>
                    <a href="?q=charity" class="btn btn-sm btn-outline-secondary">Charity</a>
                    <a href="?q=fasting" class="btn btn-sm btn-outline-secondary">Fasting</a>
                    <a href="?q=patience" class="btn btn-sm btn-outline-secondary">Patience</a>
                    <a href="?q=forgiveness" class="btn btn-sm btn-outline-secondary">Forgiveness</a>
                    <a href="?q=paradise" class="btn btn-sm btn-outline-secondary">Paradise</a>
                    <a href="?q=hellfire" class="btn btn-sm btn-outline-secondary">Hellfire</a>
                    <a href="?q=prophet+muhammad" class="btn btn-sm btn-outline-secondary">Prophet Muhammad</a>
                    <a href="?q=companions" class="btn btn-sm btn-outline-secondary">Companions</a>
                    <a href="?q=day+of+judgment" class="btn btn-sm btn-outline-secondary">Day of Judgment</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->section('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit collection/language filters
    const filters = document.querySelectorAll('#collection, #language');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Copy search URL to clipboard
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', function() {
            const urlInput = document.getElementById('shareUrl');
            urlInput.select();
            document.execCommand('copy');
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check"></i> Copied!';
            
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    }
});
</script>
<?php $this->endSection(); ?>

<?php $this->endSection(); ?>

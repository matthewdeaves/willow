<?php
/**
 * Gallery Picker - For selecting galleries to insert into content
 * 
 * @var \App\View\AppView $this
 * @var iterable $results Gallery results
 * @var string|null $search Current search term
 * @var string $viewType Current view type (grid/list)
 */

$results = $results ?? [];
$search = $search ?? '';
$viewType = $viewType ?? 'grid';
?>

<div id="gallery-selector" class="gallery-picker">
    <!-- Search Form -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       id="gallerySearch" 
                       placeholder="<?= __('Search galleries...') ?>"
                       value="<?= h($search) ?>"
                       autocomplete="off">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="viewType" id="gridView" value="grid" <?= $viewType === 'grid' ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary" for="gridView">
                    <i class="fas fa-th"></i>
                </label>
                <input type="radio" class="btn-check" name="viewType" id="listView" value="list" <?= $viewType === 'list' ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary" for="listView">
                    <i class="fas fa-list"></i>
                </label>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="row" id="gallery-grid">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $gallery): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card gallery-picker-card h-100">
                        <!-- Gallery Preview -->
                        <div class="gallery-preview">
                            <?php if (!empty($gallery->images)): ?>
                                <div class="preview-grid">
                                    <?php foreach (array_slice($gallery->images, 0, 4) as $index => $image): ?>
                                        <div class="preview-item preview-<?= $index + 1 ?>">
                                            <img src="<?= h($image->thumbnailImageUrl ?: $image->getImageUrlBySize('thumbnail')) ?>" 
                                                 alt="<?= h($image->alt_text ?: $image->name) ?>"
                                                 class="img-fluid">
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($gallery->images) > 4): ?>
                                        <div class="preview-overlay">
                                            <span class="badge bg-dark">
                                                +<?= count($gallery->images) - 4 ?> <?= __('more') ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-preview">
                                    <i class="fas fa-images fa-3x text-muted"></i>
                                    <p class="text-muted mt-2"><?= __('Empty Gallery') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Gallery Info -->
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= h($gallery->name) ?></h6>
                                <span class="badge <?= $gallery->is_published ? 'bg-success' : 'bg-secondary' ?> ms-2">
                                    <?= $gallery->is_published ? __('Published') : __('Draft') ?>
                                </span>
                            </div>
                            <?php if ($gallery->description): ?>
                                <p class="card-text text-muted small">
                                    <?= h(mb_substr($gallery->description, 0, 100)) ?>
                                    <?= mb_strlen($gallery->description) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-images me-1"></i>
                                    <?= count($gallery->images) ?> <?= __('images') ?>
                                </small>
                                <small class="text-muted">
                                    <?= $gallery->created->format('M j, Y') ?>
                                </small>
                            </div>
                        </div>

                        <!-- Selection Button -->
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <button type="button" 
                                    class="btn btn-primary btn-sm w-100 select-gallery"
                                    data-gallery-id="<?= h($gallery->id) ?>"
                                    data-gallery-name="<?= h($gallery->name) ?>"
                                    data-gallery-slug="<?= h($gallery->slug) ?>"
                                    data-theme="default"
                                    data-image-count="<?= count($gallery->images) ?>">
                                <i class="fas fa-plus me-2"></i>
                                <?= __('Insert Gallery') ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-images fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('No galleries found') ?></h5>
                    <?php if ($search): ?>
                        <p class="text-muted">
                            <?= __('No galleries match your search for "{0}"', h($search)) ?>
                        </p>
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('gallerySearch').value = ''; document.getElementById('gallerySearch').dispatchEvent(new Event('input'));">
                            <?= __('Clear Search') ?>
                        </button>
                    <?php else: ?>
                        <p class="text-muted">
                            <?= __('Create some galleries first to insert them into your content.') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($this->Paginator->total() > $this->Paginator->param('perPage')): ?>
        <div class="d-flex justify-content-center mt-4">
            <?= $this->element('pagination') ?>
        </div>
    <?php endif; ?>
</div>

<style>
.gallery-picker-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.gallery-picker-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.gallery-preview {
    height: 150px;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
}

.preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    height: 100%;
    gap: 2px;
    position: relative;
}

.preview-item {
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dee2e6;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-1 {
    grid-column: 1 / 3;
    grid-row: 1 / 3;
}

.preview-grid .preview-item:nth-child(2) {
    grid-column: 2;
    grid-row: 1;
}

.preview-grid .preview-item:nth-child(3) {
    grid-column: 2;
    grid-row: 2;
}

.preview-overlay {
    position: absolute;
    bottom: 5px;
    right: 5px;
}

.empty-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
}

.select-gallery:hover {
    transform: none !important;
}
</style>
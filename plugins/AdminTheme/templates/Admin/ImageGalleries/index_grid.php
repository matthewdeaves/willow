<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 */

// Load gallery search and grid interaction JavaScript
$this->Html->script('gallery-search', ['block' => true]);
$this->Html->script('gallery-grid-interactions', ['block' => true]);

// Load consolidated images-grid CSS (includes gallery styles)
$this->Html->css('images-grid', ['block' => true]);
?>

<!-- Header with view switcher and search -->
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- View Switcher -->
            <?= $this->element('view_switcher', [
                'currentView' => $viewType,
                'queryParams' => $this->request->getQueryParams()
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'id' => 'gallery-search-form',
                'inputId' => 'gallery-search',
                'placeholder' => __('Search galleries...'),
                'showClearButton' => true
            ]) ?>
            
            <!-- Status Filter -->
            <?= $this->element('status_filter') ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Gallery'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<!-- Content Target for AJAX updates -->
<div id="ajax-target">
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'icon' => 'fas fa-images',
            'title' => __('No Image Galleries Found'),
            'message' => __('Create your first gallery to get started.'),
            'actionText' => __('Create Gallery'),
            'actionUrl' => ['action' => 'add']
        ]) ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($imageGalleries as $gallery): ?>
            <div class="col">
                <div class="card h-100 gallery-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?= h($gallery->name) ?></h6>
                        <?= $this->Gallery->statusBadge($gallery) ?>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (!empty($gallery->images)): ?>
                            <!-- Use photo_gallery element for inline slideshow -->
                            <div class="position-relative">
                                <!-- Preview image overlay -->
                                <?php if ($gallery->hasPreviewImage()): ?>
                                    <div class="gallery-preview-overlay" data-gallery-id="gallery-<?= $gallery->id ?>">
                                        <img src="<?= h($gallery->getPreviewImageUrl()) ?>" 
                                             alt="<?= h($gallery->name) ?>"
                                             class="gallery-preview-image">
                                        
                                        <!-- Image count overlay -->
                                        <div class="gallery-image-count">
                                            <i class="fas fa-images me-1"></i><?= $gallery->getImageCount() ?>
                                        </div>
                                        
                                        <!-- Play button overlay -->
                                        <div class="position-absolute top-50 start-50 translate-middle gallery-play-button">
                                            <i class="fas fa-play-circle fa-3x text-white"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Hidden photo gallery for slideshow -->
                                <div class="d-none">
                                    <?= $this->element('shared_photo_gallery', [
                                        'images' => $gallery->images,
                                        'title' => $gallery->name,
                                        'gallery_id' => 'gallery-' . $gallery->id,
                                        'theme' => 'admin'
                                    ]) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- No images state -->
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-images fa-2x mb-2"></i>
                                <p><?= __('No images') ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Gallery info -->
                        <div class="p-3">
                            <?php if ($gallery->description): ?>
                                <p class="card-text small">
                                    <?= $this->Text->truncate(h($gallery->description), 100) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <strong><?= __('Slug') ?>:</strong> <code><?= h($gallery->slug) ?></code>
                                </small>
                                <small class="text-muted">
                                    <?= $gallery->created->format('M j, Y') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex gap-2">
                            <?= $this->Html->link(
                                '<i class="fas fa-eye"></i> ' . __('View'),
                                ['action' => 'view', $gallery->id],
                                ['class' => 'btn btn-outline-primary btn-sm flex-fill', 'escape' => false]
                            ) ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-edit"></i> ' . __('Edit'),
                                ['action' => 'edit', $gallery->id],
                                ['class' => 'btn btn-outline-secondary btn-sm flex-fill', 'escape' => false]
                            ) ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= $this->Html->link(__('Manage Images'), 
                                        ['action' => 'manageImages', $gallery->id], 
                                        ['class' => 'dropdown-item']) ?></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><?= $this->Form->postLink(__('Delete'), 
                                        ['action' => 'delete', $gallery->id], 
                                        ['class' => 'dropdown-item text-danger', 'confirm' => __('Are you sure you want to delete this gallery?')]) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>

<script>
// Initialize gallery search and grid interactions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search with grid interaction refresh callback
    if (window.GallerySearch) {
        window.GallerySearch.init({
            onSearchComplete: function() {
                // Refresh grid interactions after AJAX search
                if (window.GalleryGridInteractions) {
                    window.GalleryGridInteractions.refresh();
                }
            }
        });
    }
    
    // Initialize grid interactions
    if (window.GalleryGridInteractions) {
        window.GalleryGridInteractions.init();
    }
});
</script>


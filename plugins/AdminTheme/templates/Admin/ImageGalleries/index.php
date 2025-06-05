<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 */

// Load gallery search JavaScript
$this->Html->script('AdminTheme.gallery-search', ['block' => 'scriptBottom']);
?>

<!-- Header with view switcher and search -->
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- View Switcher -->
            <?= $this->Gallery->viewSwitcher($viewType, $this->request->getQueryParams()) ?>
            
            <!-- Search Form -->
            <?= $this->Gallery->searchForm($this->request->getQuery('search')) ?>
            
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
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= __('Preview') ?></th>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= $this->Paginator->sort('slug') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Images') ?></th>
                        <th><?= $this->Paginator->sort('created') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imageGalleries as $gallery): ?>
                    <tr>
                        <td>
                            <?= $this->Gallery->previewImage($gallery, [
                                'style' => 'width: 60px; height: 45px; object-fit: cover;',
                                'class' => 'img-thumbnail gallery-preview-thumb',
                                'popover' => true
                            ]) ?>
                        </td>
                        <td>
                            <strong><?= h($gallery->name) ?></strong>
                            <?php if ($gallery->description): ?>
                                <br><small class="text-muted"><?= $this->Text->truncate(h($gallery->description), 50) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= h($gallery->slug) ?></code></td>
                        <td>
                            <?= $this->Gallery->statusBadge($gallery) ?>
                        </td>
                        <td>
                            <?= $this->Gallery->imageCountBadge($gallery) ?>
                        </td>
                        <td><?= $gallery->created->format('M j, Y') ?></td>
                        <td>
                            <?= $this->element('evd_dropdown', [
                                'model' => $gallery,
                                'display' => 'name',
                                'controller' => 'ImageGalleries'
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>

<script>
// Initialize gallery search with popover reinitialization callback
document.addEventListener('DOMContentLoaded', function() {
    if (window.GallerySearch) {
        window.GallerySearch.init({
            onSearchComplete: function() {
                // Re-initialize popovers after AJAX search
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    // Dispose existing popover to avoid duplicates
                    const existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
                    if (existingPopover) {
                        existingPopover.dispose();
                    }
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }
        });
    }
    
    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>
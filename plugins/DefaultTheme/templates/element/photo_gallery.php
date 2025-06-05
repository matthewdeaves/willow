<?php
/**
 * Photo Gallery Element
 * 
 * @var \App\View\AppView $this
 * @var array $images Array of image entities
 * @var string $title Gallery title
 * @var string $theme Gallery theme ('admin' or 'default')
 * @var bool $showActions Whether to show management actions
 * @var string $galleryId Gallery ID for management links
 */

$theme = $theme ?? 'default';
$showActions = $showActions ?? false;
$title = $title ?? __('Gallery Images');
$images = $images ?? [];
?>

<?php if (!empty($images)) : ?>
    <?php if (!empty($title)): ?>
        <div class="gallery-header">
            <h4 class="gallery-title"><?= h($title) ?></h4>
        </div>
    <?php endif; ?>
    
    <div class="<?= $theme === 'admin' ? 'admin-gallery' : 'default-gallery' ?>">
        <div class="photo-gallery">
            <?php foreach ($images as $image) : ?>
                <div class="gallery-item">
                    <a href="<?= h($image->massiveImageUrl ?: $image->extraLargeImageUrl ?: $image->getImageUrlBySize('massive') ?: $image->getImageUrlBySize('extraLarge')) ?>" 
                       data-title="<?= h($image->name) ?>"
                       data-caption="<?= h($image->alt_text) ?>"
                       data-pswp-width="800"
                       data-pswp-height="600">
                        <img src="<?= h($image->largeImageUrl ?: $image->getImageUrlBySize('large')) ?>" 
                             alt="<?= h($image->alt_text ?: $image->name) ?>"
                             loading="lazy"
                             class="gallery-image" />
                        
                        <div class="gallery-item-overlay">
                            <div class="gallery-item-title"><?= h($image->name) ?></div>
                            <?php if ($image->alt_text): ?>
                                <div class="gallery-item-caption"><?= h($image->alt_text) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php if ($showActions && !empty($galleryId)): ?>
        <!-- Image Management Actions -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= $this->Html->link(
                        '<i class="fas fa-edit me-2"></i>' . __('Manage Images'),
                        ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                    
                    <?= $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>' . __('Add More Images'),
                        ['controller' => 'ImageGalleries', 'action' => 'edit', $galleryId],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                </div>
                
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Click any image to view slideshow') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="gallery-empty">
        <div class="gallery-empty-icon">
            <i class="fas fa-images"></i>
        </div>
        <div class="gallery-empty-text"><?= __('No images in this gallery') ?></div>
        <div class="gallery-empty-subtext"><?= __('Add some images to get started') ?></div>
        
        <?php if ($showActions && !empty($galleryId)): ?>
            <div class="mt-3">
                <?= $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>' . __('Add Images'),
                    ['controller' => 'ImageGalleries', 'action' => 'edit', $galleryId],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Auto-load PhotoSwipe Gallery assets
if ($theme === 'admin') {
    echo $this->Html->css('AdminTheme.photo-gallery', ['block' => true]);
    echo $this->Html->script('AdminTheme.photoswipe-gallery', ['block' => true]);
} else {
    echo $this->Html->css('DefaultTheme.photo-gallery', ['block' => true]);
    echo $this->Html->script('DefaultTheme.photoswipe-gallery', ['block' => true]);
}
?>
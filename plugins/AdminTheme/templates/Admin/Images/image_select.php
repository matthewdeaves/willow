<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php use Cake\Core\Configure; ?>

<?php if (!$this->request->getQuery('gallery_only')): ?>
<div class="mb-3">
    <?php $searchQuery = $this->request->getQuery('search', ''); ?>
    <input type="text" id="imageSearch" class="form-control" placeholder="<?= __('Search images...') ?>" value="<?= h($searchQuery) ?>">
</div>
<?php endif; ?>
<div id="image-gallery" class="flex-shrink-0">
    <?php include 'image_gallery.php'; ?>
</div>
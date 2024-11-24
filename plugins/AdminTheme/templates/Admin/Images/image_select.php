<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * 
 * This view is loaded in a popup either by Trumbowyg using its own popup system
 * or via the willowModal system.
 * 
 * Trumbowyg:
 * - Uses its own built-in popup system to load the view.
 * - Integrates with the Trumbowyg editor to allow image selection directly within the editor interface.
 * - Requires the 'AdminTheme.trumbowyg-image-select' script to be included for image selection functionality.
 * 
 * Markdown-it:
 * - Does not have a built-in popup system like Trumbowyg.
 * - Relies on external modal systems such as willowModal to display the view.
 * - Image selection and insertion might require additional custom handling or plugins.
 */
?>
<?php use App\Utility\SettingsManager; ?>

<?php if (!$this->request->getQuery('gallery_only')): ?>
<div class="mb-3">
    <?php $searchQuery = $this->request->getQuery('search', ''); ?>
    <input type="text" id="imageSearch" class="form-control" placeholder="<?= __('Search images...') ?>" value="<?= h($searchQuery) ?>">
</div>
<?php endif; ?>
<div id="image-gallery" class="flex-shrink-0">
    <?php include 'image_gallery.php'; ?>
</div>
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

<div id="image-gallery" class="image-picker">
    <!-- Search Form -->
    <?php if (!$this->request->getQuery('gallery_only')): ?>
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       id="imageSearch" 
                       placeholder="<?= __('Search images...') ?>"
                       value="<?= h($this->request->getQuery('search', '')) ?>"
                       autocomplete="off">
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Image Gallery Content -->
    <div class="image-gallery-content">
        <?php include 'image_gallery.php'; ?>
    </div>
</div>
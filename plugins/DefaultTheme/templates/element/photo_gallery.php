<?php
/**
 * DefaultTheme Photo Gallery Element
 * 
 * This template now uses the shared photo gallery element from the main app.
 * All functionality has been moved to /templates/element/photo_gallery.php
 * which automatically detects theme context and provides appropriate features.
 */

// Use shared photo gallery element from main app
echo $this->element('shared_photo_gallery', [
    'images' => $images ?? [],
    'title' => $title ?? __('Gallery Images'),
    'theme' => 'default',
    'showActions' => $showActions ?? false,
    'galleryId' => $galleryId ?? null,
]);
?>
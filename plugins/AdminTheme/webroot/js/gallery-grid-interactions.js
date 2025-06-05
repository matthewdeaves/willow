/**
 * Gallery Grid Interactions
 * Handles preview hover effects, slideshow initialization, and PhotoSwipe integration
 */
(function() {
    'use strict';

    let config = {};

    /**
     * Initialize gallery grid interactions
     */
    function init(options = {}) {
        config = Object.assign({
            previewSelector: '.gallery-preview-overlay',
            playButtonSelector: '.gallery-play-button',
            galleryCardSelector: '.card',
            photoGallerySelector: '.photo-gallery',
            galleryItemSelector: '.gallery-item',
            popoverSelector: '[data-bs-toggle="popover"]',
            enableHoverEffects: true,
            enablePhotoSwipe: true
        }, options);

        initializePreviewInteractions(false);
    }

    /**
     * Initialize preview interactions and effects
     */
    function initializePreviewInteractions(isAjaxLoad = false) {
        if (config.enableHoverEffects) {
            initializeHoverEffects();
        }

        if (config.enablePhotoSwipe && isAjaxLoad) {
            reinitializePhotoSwipe();
        }

        // Initialize Bootstrap popovers for AJAX content
        if (isAjaxLoad) {
            initializePopovers();
        }
    }

    /**
     * Initialize hover effects for gallery previews
     */
    function initializeHoverEffects() {
        const previews = document.querySelectorAll(config.previewSelector);
        
        previews.forEach(container => {
            const playButton = container.querySelector(config.playButtonSelector);
            
            if (!playButton) return;

            // Check if already initialized to prevent duplicate handlers
            if (container.hasAttribute('data-gallery-initialized')) {
                return;
            }
            container.setAttribute('data-gallery-initialized', 'true');

            // Add hover event listeners
            container.addEventListener('mouseenter', function() {
                playButton.style.display = 'block';
            });
            
            container.addEventListener('mouseleave', function() {
                playButton.style.display = 'none';
            });

            // Add click handler for slideshow
            container.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const galleryId = extractGalleryId(container);
                if (galleryId) {
                    startGallerySlideshow(galleryId);
                }
            });
        });
    }

    /**
     * Extract gallery ID from container onclick attribute or data
     */
    function extractGalleryId(container) {
        // Try to get from onclick attribute first (legacy support)
        const onclick = container.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/startGallerySlideshow\(['"]([^'"]+)['"]\)/);
            if (match) {
                return match[1];
            }
        }

        // Try to get from data attribute
        return container.dataset.galleryId;
    }

    /**
     * Start gallery slideshow by finding and clicking first image
     */
    function startGallerySlideshow(galleryId) {
        let galleryContainer = null;
        
        // Look for the gallery container that contains this galleryId
        const galleryCards = document.querySelectorAll(config.galleryCardSelector);
        
        for (let card of galleryCards) {
            // Look for preview overlay with matching data-gallery-id
            const previewOverlay = card.querySelector(`[data-gallery-id="${galleryId}"]`);
            if (previewOverlay) {
                // Find the hidden photo gallery in the same card
                galleryContainer = card.querySelector(config.photoGallerySelector);
                break;
            }
        }
        
        // If we found the gallery, click the first image link to start PhotoSwipe
        if (galleryContainer) {
            const firstImageLink = galleryContainer.querySelector(`${config.galleryItemSelector} a[href]`);
            if (firstImageLink) {
                console.log('Starting gallery slideshow for:', galleryId);
                
                // Ensure PhotoSwipe is properly initialized for this gallery
                setTimeout(() => {
                    firstImageLink.click();
                }, 50);
                return;
            } else {
                console.warn('No image links found in gallery:', galleryId);
            }
        } else {
            console.warn('Gallery container not found for:', galleryId);
        }

        // Fallback: redirect to view page
        const realGalleryId = galleryId.replace('gallery-', '');
        const viewUrl = `/admin/image-galleries/view/${realGalleryId}`;
        console.log('Falling back to view page:', viewUrl);
        window.location.href = viewUrl;
    }

    /**
     * Reinitialize PhotoSwipe galleries for AJAX-loaded content
     */
    function reinitializePhotoSwipe() {
        setTimeout(() => {
            if (window.PhotoSwipeGallery) {
                console.log('Re-initializing PhotoSwipe galleries after AJAX load...');
                try {
                    const ajaxPhotoSwipeInstance = new PhotoSwipeGallery({
                        gallerySelector: config.photoGallerySelector,
                        itemSelector: config.galleryItemSelector
                    });
                } catch (error) {
                    console.error('Error reinitializing PhotoSwipe:', error);
                }
            } else {
                console.warn('PhotoSwipeGallery not available for AJAX content');
            }
        }, 100);
    }

    /**
     * Initialize Bootstrap popovers
     */
    function initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll(config.popoverSelector));
        popoverTriggerList.map(function (popoverTriggerEl) {
            // Dispose existing popover to avoid duplicates
            const existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
            if (existingPopover) {
                existingPopover.dispose();
            }
            
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    /**
     * Clean up event listeners (useful for AJAX reloads)
     */
    function cleanup() {
        // Remove existing event listeners by cloning and replacing elements
        const previews = document.querySelectorAll(config.previewSelector);
        previews.forEach(container => {
            const newContainer = container.cloneNode(true);
            container.parentNode.replaceChild(newContainer, container);
        });
    }

    /**
     * Refresh interactions after content change (AJAX callback)
     */
    function refresh() {
        cleanup();
        initializePreviewInteractions(true);
    }

    // Auto-initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on a gallery grid page
        if (document.querySelector('.gallery-preview-overlay')) {
            init();
        }
    });

    // Export for manual initialization and control
    window.GalleryGridInteractions = {
        init: init,
        refresh: refresh,
        startGallerySlideshow: startGallerySlideshow,
        initializePreviewInteractions: initializePreviewInteractions
    };

    // Export global function for backward compatibility
    window.startGallerySlideshow = startGallerySlideshow;

})();
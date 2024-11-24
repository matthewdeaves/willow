/**
 * MarkdownImageSelect - Handles image selection for markdown-it editor
 */
const MarkdownImageSelect = {
    /**
     * Initialize the image selection functionality
     */
    init: function() {
        this.removeExistingListeners();
        this.bindEvents();
    },

    /**
     * Remove existing event listeners to prevent duplicates
     */
    removeExistingListeners: function() {
        const searchInput = document.getElementById('imageSearch');
        const gallery = document.getElementById('image-gallery');

        if (searchInput) {
            searchInput.replaceWith(searchInput.cloneNode(true));
        }

        if (gallery) {
            const newGallery = gallery.cloneNode(true);
            gallery.parentNode.replaceChild(newGallery, gallery);
        }
    },

    /**
     * Bind all event handlers for image selection
     */
    bindEvents: function() {
        this.bindSearchBoxEvents();
        this.bindGalleryEvents();
    },

    /**
     * Bind search box events with debouncing
     */
    bindSearchBoxEvents: function() {
        const searchInput = document.getElementById('imageSearch');
        if (!searchInput) return;

        let debounceTimer;
        const handleSearch = (event) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = event.target.value.trim();
                let url = '/admin/images/imageSelect?gallery_only=1';
                if (searchTerm.length > 0) {
                    url += '&search=' + encodeURIComponent(searchTerm);
                }
                this.loadImages(url);
            }, 300);
        };

        searchInput.addEventListener('input', handleSearch, { passive: true });
    },

    /**
     * Bind gallery events using event delegation
     */
    bindGalleryEvents: function() {
        const gallery = document.getElementById('image-gallery');
        if (!gallery) return;

        gallery.addEventListener('click', (e) => {
            // Handle pagination links
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                const url = new URL(paginationLink.href);
                url.searchParams.set('gallery_only', '1');
                this.loadImages(url.toString());
                return;
            }

            // Handle image selection
            const image = e.target.closest('.insert-image');
            if (image) {
                e.preventDefault();
                this.handleImageSelect(e, image);
            }
        });
    },

    /**
     * Load images via AJAX
     * @param {string} url - The URL to load images from
     */
    loadImages: function(url) {
        // Ensure gallery_only=1 is always present
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.set('gallery_only', '1');

        fetch(urlObj.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const gallery = document.getElementById('image-gallery');
            if (gallery) {
                gallery.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading images:', error);
        });
    },

    /**
     * Handle image selection and insertion
     * @param {Event} event - The click event
     * @param {HTMLElement} imageElement - The clicked image element
     */
    handleImageSelect: function(event, imageElement) {
        event.preventDefault();
        event.stopPropagation();

        const sizeSelect = document.getElementById(imageElement.dataset.id + '_size');
        const size = sizeSelect ? sizeSelect.value : '';
        
        const altText = imageElement.dataset.alt || imageElement.dataset.name || '';
        const imagePath = `/files/Images/image/${size}/${imageElement.dataset.src}`;
        
        // Create markdown image syntax
        const markdownImage = `![${altText}](${imagePath})`;
        
        // Insert at cursor position in the markdown textarea
        const textarea = document.getElementById('article-markdown');
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            
            textarea.value = text.substring(0, start) + markdownImage + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + markdownImage.length, start + markdownImage.length);
            
            // Trigger input event to update preview
            textarea.dispatchEvent(new Event('input', {
                bubbles: true,
                cancelable: true
            }));
        }

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('dynamicModal'));
        if (modal) {
            modal.hide();
        }
    }
};

// Export for use in other scripts
window.MarkdownImageSelect = MarkdownImageSelect;
/**
 * MarkdownImageSelect - Handles image selection for markdown-it editor
 */
const MarkdownImageSelect = {
    /**
     * Initialize the image selection functionality
     */
    init: function() {
        this.bindEvents();
    },

    /**
     * Bind event handlers for image selection
     */
    bindEvents: function() {
        this.bindSearchBoxEvents();
        this.bindImageSelectEvents();
        this.bindPaginationEvents();
    },

    /**
     * Bind search box events with debouncing
     */
    bindSearchBoxEvents: function() {
        const searchInput = document.getElementById('imageSearch');
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value.trim();
                let url = '/admin/images/imageSelect';
                if (searchTerm.length > 0) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                MarkdownImageSelect.loadImages(url);
            }, 300);
        });
    },

    /**
     * Bind image selection events
     */
    bindImageSelectEvents: function() {
        const images = document.querySelectorAll('.insert-image');
        images.forEach(image => {
            image.addEventListener('click', this.handleImageSelect.bind(this));
        });
    },

    /**
     * Bind pagination events
     */
    bindPaginationEvents: function() {
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadImages(link.href);
            });
        });
    },

    /**
     * Load images via AJAX
     * @param {string} url - The URL to load images from
     */
    loadImages: function(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const gallery = document.getElementById('image-gallery');
            if (gallery) {
                gallery.innerHTML = html;
                this.bindEvents();
            }
        })
        .catch(error => {
            console.error('Error loading images:', error);
        });
    },

    /**
     * Handle image selection and insertion
     * @param {Event} event - The click event
     */
    handleImageSelect: function(event) {
        const img = event.target;
        const sizeSelect = document.getElementById(img.dataset.id + '_size');
        const size = sizeSelect ? sizeSelect.value : '';
        
        const altText = img.dataset.alt || img.dataset.name || '';
        const imagePath = `/files/Images/image/${size}/${img.dataset.src}`;
        
        // Create markdown image syntax
        const markdownImage = `![${altText}](${imagePath})`;
        
        // Insert at cursor position in the markdown textarea
        const textarea = document.getElementById('article-markdown');
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            
            textarea.value = text.substring(0, start) + markdownImage + text.substring(end);
            
            // Trigger input event to update preview
            textarea.dispatchEvent(new Event('input'));
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
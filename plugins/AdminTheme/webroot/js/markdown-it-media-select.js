/**
 * MediaSelect - Handles media (image/video) selection for markdown-it editor
 */
class MediaSelect {
    static instance = null;

    constructor(type) {
        // Ensure only one instance per type
        if (MediaSelect.instance) {
            MediaSelect.instance.removeExistingListeners();
        }
        MediaSelect.instance = this;

        this.type = type; // 'image' or 'video'
        this.endpoints = {
            image: '/admin/images/imageSelect',
            video: '/admin/videos/video_select'
        };
        this.boundHandleSearch = null;
        this.boundHandleChannelFilter = null;
        this.boundHandleGalleryClick = null;
    }

    /**
     * Initialize the media selection functionality
     */
    init() {
        this.removeExistingListeners();
        this.bindEvents();
    }

    /**
     * Remove existing event listeners to prevent duplicates
     */
    removeExistingListeners() {
        const searchInput = document.getElementById(`${this.type}Search`);
        const gallery = document.getElementById(`${this.type}-gallery`);
        const channelFilter = document.getElementById('channelFilter');

        // Remove search event listener
        if (searchInput && this.boundHandleSearch) {
            searchInput.removeEventListener('input', this.boundHandleSearch);
        }

        // Remove gallery event listener
        if (gallery && this.boundHandleGalleryClick) {
            gallery.removeEventListener('click', this.boundHandleGalleryClick);
        }

        // Remove channel filter event listener
        if (channelFilter && this.boundHandleChannelFilter) {
            channelFilter.removeEventListener('change', this.boundHandleChannelFilter);
        }
    }

    /**
     * Bind all event handlers for media selection
     */
    bindEvents() {
        this.bindSearchBoxEvents();
        this.bindGalleryEvents();
        if (this.type === 'video') {
            this.bindChannelFilterEvents();
        }
    }

    /**
     * Bind search box events with debouncing
     */
    bindSearchBoxEvents() {
        const searchInput = document.getElementById(`${this.type}Search`);
        if (!searchInput) return;

        let debounceTimer;
        this.boundHandleSearch = (event) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = event.target.value.trim();
                let url = `${this.endpoints[this.type]}?gallery_only=1`;
                if (searchTerm.length > 0) {
                    url += '&search=' + encodeURIComponent(searchTerm);
                }
                this.loadContent(url);
            }, 300);
        };

        searchInput.addEventListener('input', this.boundHandleSearch, { passive: true });
    }

    /**
     * Bind channel filter events (videos only)
     */
    bindChannelFilterEvents() {
        const channelFilter = document.getElementById('channelFilter');
        if (!channelFilter) return;

        this.boundHandleChannelFilter = () => {
            const searchInput = document.getElementById('videoSearch');
            const searchTerm = searchInput ? searchInput.value.trim() : '';
            let url = `${this.endpoints.video}?gallery_only=1`;
            if (searchTerm) {
                url += '&search=' + encodeURIComponent(searchTerm);
            }
            url += '&channel_filter=' + channelFilter.checked;
            this.loadContent(url);
        };

        channelFilter.addEventListener('change', this.boundHandleChannelFilter);
    }

    /**
     * Bind gallery events using event delegation
     */
    bindGalleryEvents() {
        const gallery = document.getElementById(`${this.type}-gallery`);
        if (!gallery) return;

        this.boundHandleGalleryClick = (e) => {
            // Handle pagination links
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                const url = new URL(paginationLink.href);
                url.searchParams.set('gallery_only', '1');
                this.loadContent(url.toString());
                return;
            }

            // Handle media selection
            const mediaElement = this.type === 'image' 
                ? e.target.closest('.insert-image')
                : e.target.closest('.select-video');
                
            if (mediaElement) {
                e.preventDefault();
                this.handleMediaSelect(e, mediaElement);
            }
        };

        gallery.addEventListener('click', this.boundHandleGalleryClick);
    }

    /**
     * Load content via AJAX
     * @param {string} url - The URL to load content from
     */
    loadContent(url) {
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.set('gallery_only', '1');

        fetch(urlObj.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            const gallery = document.getElementById(`${this.type}-gallery`);
            if (gallery) {
                this.removeExistingListeners();
                gallery.innerHTML = html;
                this.bindEvents();
            }
        })
        .catch(error => console.error(`Error loading ${this.type}s:`, error));
    }

    /**
     * Handle media selection and insertion
     * @param {Event} event - The click event
     * @param {HTMLElement} element - The clicked element
     */
    handleMediaSelect(event, element) {
        event.preventDefault();
        event.stopPropagation();

        const markdownContent = this.type === 'image' 
            ? this.createImageMarkdown(element)
            : this.createVideoMarkdown(element);

        this.insertIntoEditor(markdownContent);
        this.closeModal();
    }

    /**
     * Create markdown syntax for image
     * @param {HTMLElement} imageElement - The image element
     * @returns {string} Markdown syntax
     */
    createImageMarkdown(imageElement) {
        const sizeSelect = document.getElementById(imageElement.dataset.id + '_size');
        const size = sizeSelect ? sizeSelect.value : '';
        const altText = imageElement.dataset.alt || imageElement.dataset.name || '';
        const imagePath = `/files/Images/image/${size}/${imageElement.dataset.src}`;
        
        return `![${altText}](${imagePath})`;
    }

    /**
     * Create markdown syntax for video
     * @param {HTMLElement} videoElement - The video element
     * @returns {string} Video placeholder syntax
     */
    createVideoMarkdown(videoElement) {
        const videoId = videoElement.dataset.videoId;
        const videoTitle = videoElement.dataset.videoTitle;
        return `[youtube:${videoId}:560:315:${videoTitle}]`;
    }

    /**
     * Insert content into the markdown editor
     * @param {string} content - The content to insert
     */
    insertIntoEditor(content) {
        const textarea = document.getElementById('article-markdown');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        
        textarea.value = text.substring(0, start) + content + text.substring(end);
        textarea.focus();
        textarea.setSelectionRange(start + content.length, start + content.length);
        
        textarea.dispatchEvent(new Event('input', {
            bubbles: true,
            cancelable: true
        }));
    }

    /**
     * Close the modal dialog
     */
    closeModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('dynamicModal'));
        if (modal) modal.hide();
    }
}

// Export for use in other scripts
window.MediaSelect = MediaSelect;
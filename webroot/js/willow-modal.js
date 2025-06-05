/**
 * WillowModal - Dynamic Bootstrap Modal Manager
 * ==========================================
 * 
 * A comprehensive utility for creating and managing Bootstrap modals with AJAX content loading,
 * form handling, and specialized media selection capabilities.
 * 
 * Features:
 * - Dynamic modal creation and cleanup
 * - AJAX content loading with error handling
 * - Automatic form submission handling with CSRF protection
 * - Support for submit button data attributes
 * - Comprehensive event callbacks and lifecycle management
 * - Specialized media selection methods for editors
 * - Search and pagination handling for media pickers
 * - Content insertion strategies for different editor types
 * 
 * Basic Usage:
 * ```javascript
 * WillowModal.show('/path/to/content', {
 *     title: 'Modal Title',
 *     closeable: true
 * });
 * ```
 * 
 * Media Selection Usage:
 * ```javascript
 * WillowModal.showImageSelector(trumbowygEditor, {
 *     title: 'Select Image from Library'
 * });
 * 
 * WillowModal.showGallerySelector(markdownEditor, {
 *     title: 'Insert Photo Gallery'
 * });
 * ```
 * 
 * @namespace WillowModal
 */

/**
 * Creates and displays a Bootstrap modal with content loaded from a URL
 * 
 * @method show
 * @memberof WillowModal
 * 
 * @param {string} url - The URL from which to load the modal content
 * @param {Object} [options={}] - Configuration options for the modal
 * 
 * @param {string} [options.title] - The title to display in the modal header
 * @param {string} [options.dialogClass] - Additional CSS classes for the modal dialog
 * @param {boolean} [options.closeable=false] - Whether to show a close button in the header
 * @param {boolean} [options.static=false] - If true, modal won't close on backdrop click or Escape key
 * @param {boolean} [options.handleForm=true] - Whether to automatically handle form submissions
 * @param {boolean} [options.reload=false] - Whether to reload the page after successful form submission
 * 
 * @param {Function} [options.onShown] - Callback fired when modal is fully shown
 * @param {Function} [options.onContentLoaded] - Callback fired after content is loaded
 * @param {Function} [options.onSuccess] - Callback fired after successful form submission
 * @param {Function} [options.onError] - Callback fired on any error (loading/submission)
 * @param {Function} [options.onHidden] - Callback fired when modal is fully hidden
 * 
 * @returns {bootstrap.Modal} The Bootstrap modal instance
 * 
 * @example
 * // Basic modal with content
 * WillowModal.show('/users/edit/1', {
 *     title: 'Edit User',
 *     closeable: true
 * });
 * 
 * @example
 * // Form handling with callbacks
 * WillowModal.show('/users/add', {
 *     title: 'Add New User',
 *     handleForm: true,
 *     onSuccess: (data) => {
 *         console.log('User added:', data);
 *     },
 *     onError: (error) => {
 *         console.error('Failed:', error);
 *     }
 * });
 * 
 * Technical Details:
 * -----------------
 * 
 * Modal Structure:
 * The modal is created using Bootstrap's modal component structure and inserted
 * into the document body. It includes:
 * - Modal dialog with configurable classes
 * - Header with optional title and close button
 * - Body container for AJAX content
 * 
 * Form Handling:
 * When handleForm is true (default):
 * 1. Captures all form submissions within the modal
 * 2. Collects form data including:
 *    - All form fields
 *    - Data attributes from clicked submit buttons
 *    - Submit button name/value if present
 * 3. Submits via AJAX with CSRF token
 * 4. Handles success/error responses
 * 
 * CSRF Protection:
 * Requires a global csrfToken variable to be defined.
 * Automatically includes the token in all AJAX requests.
 * 
 * Cleanup:
 * The modal automatically removes itself from the DOM when closed.
 * 
 * Error Handling:
 * - Logs errors to console
 * - Calls onError callback if provided
 * - Handles both network and application errors
 * 
 * Dependencies:
 * - Bootstrap 5.x
 * - Modern browser with fetch API support
 */
window.WillowModal = {
    show: function(url, options = {}) {
        const modalHtml = `
            <div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered ${options.dialogClass || ''}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5">${options.title || ''}</h1>
                            ${options.closeable ? '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' : ''}
                        </div>
                        <div class="modal-body">
                            <div id="dynamicModalContent"></div>
                        </div>
                    </div>
                </div>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalEl = document.getElementById('dynamicModal');
        const modal = new bootstrap.Modal(modalEl, {
            backdrop: options.static ? 'static' : true,
            keyboard: !options.static
        });

        // Add shown.bs.modal event listener before showing the modal
        modalEl.addEventListener('shown.bs.modal', function() {
            if (typeof options.onShown === 'function') {
                options.onShown();
            }
        });

        modalEl.addEventListener('show.bs.modal', function() {
            fetch(url, {
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
                document.getElementById('dynamicModalContent').innerHTML = html;
                
                // Handle form submission if present
                const form = modalEl.querySelector('form');
                if (form && options.handleForm !== false) {
                    let lastClickedButton = null;

                    // Handle any button clicks within the form
                    form.addEventListener('click', function(e) {
                        if (e.target.matches('button[type="submit"]')) {
                            lastClickedButton = e.target;
                        }
                    });

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(form);
                        
                        // Add any data attributes from the clicked button to the form data
                        if (lastClickedButton) {
                            Object.entries(lastClickedButton.dataset).forEach(([key, value]) => {
                                formData.append(key, value);
                            });
                            // Also add the button's name and value if present
                            if (lastClickedButton.name && lastClickedButton.value) {
                                formData.append(lastClickedButton.name, lastClickedButton.value);
                            }
                        }

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': csrfToken
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                modal.hide();
                                if (options.reload) {
                                    window.location.reload();
                                }
                                if (typeof options.onSuccess === 'function') {
                                    options.onSuccess(data);
                                }
                            } else {
                                console.error('Form submission failed:', data);
                                if (typeof options.onError === 'function') {
                                    options.onError(data);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (typeof options.onError === 'function') {
                                options.onError(error);
                            }
                        });
                    });
                }

                // Call onContentLoaded callback if provided
                if (typeof options.onContentLoaded === 'function') {
                    options.onContentLoaded();
                }
            })
            .catch(error => {
                console.error('Error loading modal content:', error);
                if (typeof options.onError === 'function') {
                    options.onError(error);
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            modalEl.remove();
            if (typeof options.onHidden === 'function') {
                options.onHidden();
            }
        });

        modal.show();
        return modal;
    },

    /**
     * ========================================
     * MEDIA SELECTION METHODS
     * ========================================
     */

    /**
     * Show image selector modal for editors
     * @param {Object} editor - Editor instance (Trumbowyg or detected from DOM)
     * @param {Object} [options={}] - Modal configuration options
     * @returns {bootstrap.Modal} The Bootstrap modal instance
     */
    showImageSelector: function(editor, options = {}) {
        return this._showMediaSelector('image', editor, {
            title: 'Insert Image from Library',
            ...options
        });
    },

    /**
     * Show video selector modal for editors
     * @param {Object} editor - Editor instance (Trumbowyg or detected from DOM)
     * @param {Object} [options={}] - Modal configuration options
     * @returns {bootstrap.Modal} The Bootstrap modal instance
     */
    showVideoSelector: function(editor, options = {}) {
        return this._showMediaSelector('video', editor, {
            title: 'Insert YouTube Video',
            ...options
        });
    },

    /**
     * Show gallery selector modal for editors
     * @param {Object} editor - Editor instance (Trumbowyg or detected from DOM)
     * @param {Object} [options={}] - Modal configuration options
     * @returns {bootstrap.Modal} The Bootstrap modal instance
     */
    showGallerySelector: function(editor, options = {}) {
        return this._showMediaSelector('gallery', editor, {
            title: 'Insert Image Gallery',
            ...options
        });
    },

    /**
     * Internal method for handling media selection modals
     * @private
     */
    _showMediaSelector: function(type, editor, options = {}) {
        const endpoints = {
            image: '/admin/images/imageSelect',
            video: '/admin/videos/video_select',
            gallery: '/admin/image-galleries/picker'
        };

        const defaults = {
            dialogClass: 'modal-lg',
            closeable: true,
            handleForm: false
        };

        const config = { ...defaults, ...options };
        const url = endpoints[type];

        // Store media context for callbacks
        this._currentMediaContext = {
            type: type,
            editor: editor,
            editorType: this._detectEditorType(editor)
        };

        // Enhanced content loaded handler for media
        const originalOnContentLoaded = config.onContentLoaded;
        config.onContentLoaded = () => {
            this._initializeMediaHandlers();
            if (originalOnContentLoaded) originalOnContentLoaded();
        };

        return this.show(url, config);
    },

    /**
     * Initialize media-specific handlers after content loads
     * @private
     */
    _initializeMediaHandlers: function() {
        const context = this._currentMediaContext;
        if (!context) return;

        if (context.type === 'image') {
            this._initializeImageHandlers();
        } else if (context.type === 'video') {
            this._initializeVideoHandlers();
        } else if (context.type === 'gallery') {
            this._initializeGalleryHandlers();
        }
    },

    /**
     * Initialize image-specific handlers
     * @private
     */
    _initializeImageHandlers: function() {
        const container = document.getElementById('image-gallery');
        if (!container) return;

        // Search handler with debouncing
        const searchInput = document.getElementById('imageSearch');
        if (searchInput) {
            this._bindSearchHandler(searchInput, 'image');
        }

        // Image selection handler
        this._bindImageSelection(container);

        // Pagination handler
        this._bindPaginationHandler(container);
    },

    /**
     * Initialize video-specific handlers  
     * @private
     */
    _initializeVideoHandlers: function() {
        const container = document.getElementById('video-gallery');
        if (!container) return;

        // Search handler with debouncing
        const searchInput = document.getElementById('videoSearch');
        if (searchInput) {
            this._bindSearchHandler(searchInput, 'video');
        }

        // Video selection handler
        this._bindVideoSelection(container);

        // Pagination handler
        this._bindPaginationHandler(container);
    },

    /**
     * Bind image selection events
     * @private
     */
    _bindImageSelection: function(container) {
        // Check if already bound to prevent duplicate handlers
        if (container.dataset.imageHandlerBound === 'true') {
            return;
        }
        
        container.addEventListener('click', (e) => {
            const imageElement = e.target.closest('img.insert-image');
            if (imageElement) {
                e.preventDefault();
                this._handleImageSelect(imageElement);
            }
        });
        
        // Mark as bound
        container.dataset.imageHandlerBound = 'true';
    },

    /**
     * Bind video selection events
     * @private
     */
    _bindVideoSelection: function(container) {
        // Check if already bound to prevent duplicate handlers
        if (container.dataset.videoHandlerBound === 'true') {
            return;
        }
        
        container.addEventListener('click', (e) => {
            const videoElement = e.target.closest('.select-video');
            if (videoElement) {
                e.preventDefault();
                this._handleVideoSelect(videoElement);
            }
        });
        
        // Mark as bound
        container.dataset.videoHandlerBound = 'true';
    },

    /**
     * Handle image selection and insertion
     * @private
     */
    _handleImageSelect: function(imageElement) {
        const imageData = {
            id: imageElement.dataset.id,
            src: imageElement.dataset.src,
            name: imageElement.dataset.name,
            alt: imageElement.dataset.alt || imageElement.dataset.name
        };

        // Get the selected image size from the dropdown
        const sizeDropdown = document.getElementById(imageData.id + '_size');
        const selectedSize = sizeDropdown ? sizeDropdown.value : 'large';

        // Update imageData with selected size
        imageData.selectedSize = selectedSize;

        const context = this._currentMediaContext;
        if (!context) return;

        // Insert image using content insertion strategy
        const inserter = this._createContentInserter(context.editorType, context.editor);
        if (inserter && inserter.insertImage) {
            inserter.insertImage(imageData);
        } else {
            // Fallback: insert as HTML with selected size
            const imgHtml = `<img src="/files/Images/image/${selectedSize}/${imageData.src}" alt="${imageData.alt}" title="${imageData.name}">`;
            const contentInserter = this._createContentInserter(context.editorType, context.editor);
            if (contentInserter) {
                contentInserter.insertContent(imgHtml);
            }
        }

        this._closeCurrentModal();
    },

    /**
     * Handle video selection and insertion
     * @private
     */
    _handleVideoSelect: function(videoElement) {
        const videoData = {
            id: videoElement.dataset.videoId,
            title: videoElement.dataset.title || 'YouTube Video'
        };

        const context = this._currentMediaContext;
        if (!context) return;

        // Insert video using content insertion strategy
        const inserter = this._createContentInserter(context.editorType, context.editor);
        if (inserter && inserter.insertVideo) {
            inserter.insertVideo(videoData);
        } else {
            // Fallback: insert as placeholder
            const videoPlaceholder = `[youtube:${videoData.id}:${videoData.title}]`;
            const contentInserter = this._createContentInserter(context.editorType, context.editor);
            if (contentInserter) {
                contentInserter.insertContent(videoPlaceholder);
            }
        }

        this._closeCurrentModal();
    },

    /**
     * Initialize gallery-specific handlers
     * @private
     */
    _initializeGalleryHandlers: function() {
        const container = document.getElementById('gallery-selector');
        if (!container) return;

        // Search handler with debouncing
        const searchInput = document.getElementById('gallerySearch');
        if (searchInput) {
            this._bindSearchHandler(searchInput, 'gallery');
        }

        // Gallery selection handler
        this._bindGallerySelection(container);

        // Pagination handler
        this._bindPaginationHandler(container);
    },

    /**
     * Bind search functionality with debouncing
     * @private
     */
    _bindSearchHandler: function(searchInput, type) {
        // Check if already bound to prevent duplicate handlers
        if (searchInput.dataset.searchHandlerBound === 'true') {
            return;
        }
        
        let debounceTimer;
        const handler = (event) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = event.target.value.trim();
                this._loadMediaContent(this._buildSearchUrl(type, searchTerm));
            }, 300);
        };

        searchInput.addEventListener('input', handler, { passive: true });
        
        // Mark as bound
        searchInput.dataset.searchHandlerBound = 'true';
    },

    /**
     * Bind gallery selection events
     * @private
     */
    _bindGallerySelection: function(container) {
        // Check if already bound to prevent duplicate handlers
        if (container.dataset.galleryHandlerBound === 'true') {
            return;
        }
        
        container.addEventListener('click', (e) => {
            const galleryElement = e.target.closest('.select-gallery');
            if (galleryElement) {
                e.preventDefault();
                this._handleGallerySelect(galleryElement);
            }
        });
        
        // Mark as bound
        container.dataset.galleryHandlerBound = 'true';
    },

    /**
     * Bind pagination handler
     * @private
     */
    _bindPaginationHandler: function(container) {
        // Check if already bound to prevent duplicate handlers
        if (container.dataset.paginationHandlerBound === 'true') {
            return;
        }
        
        container.addEventListener('click', (e) => {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                const url = new URL(paginationLink.href);
                url.searchParams.set('gallery_only', '1');
                this._loadMediaContent(url.toString());
            }
        });
        
        // Mark as bound
        container.dataset.paginationHandlerBound = 'true';
    },

    /**
     * Handle gallery selection and insertion
     * @private
     */
    _handleGallerySelect: function(galleryElement) {
        const galleryData = {
            id: galleryElement.dataset.galleryId,
            name: galleryElement.dataset.galleryName,
            slug: galleryElement.dataset.gallerySlug,
            imageCount: galleryElement.dataset.imageCount,
            theme: galleryElement.dataset.theme || 'default'
        };

        const context = this._currentMediaContext;
        if (!context) return;

        // Insert gallery using content insertion strategy
        const inserter = this._createContentInserter(context.editorType, context.editor);
        if (inserter) {
            const placeholder = `[gallery:${galleryData.id}:${galleryData.theme}:${galleryData.name}]`;
            inserter.insertContent(placeholder);
        }

        this._closeCurrentModal();
    },

    /**
     * Content insertion strategy factory
     * @private
     */
    _createContentInserter: function(editorType, editor) {
        switch (editorType) {
            case 'trumbowyg':
                return new this._TrumbowygInserter(editor);
            case 'markdown':
                return new this._MarkdownInserter();
            default:
                console.warn('Unknown editor type:', editorType);
                return null;
        }
    },

    /**
     * Trumbowyg content inserter
     * @private
     */
    _TrumbowygInserter: function(editor) {
        this.editor = editor;
        this.insertContent = function(content) {
            if (!this.editor) return;
            this.editor.restoreRange();
            this.editor.execCmd('insertHTML', content, false, true);
        };
        this.insertImage = function(imageData) {
            if (!this.editor || !imageData) return;
            const size = imageData.selectedSize || 'large';
            const imgHtml = `<img src="/files/Images/image/${size}/${imageData.src}" alt="${imageData.alt}" title="${imageData.name}">`;
            this.insertContent(imgHtml);
        };
        this.insertVideo = function(videoData) {
            if (!this.editor || !videoData) return;
            const videoPlaceholder = `[youtube:${videoData.id}:${videoData.title}]`;
            this.insertContent(videoPlaceholder);
        };
    },

    /**
     * Markdown content inserter
     * @private
     */
    _MarkdownInserter: function() {
        this.insertContent = function(content) {
            const textarea = document.getElementById('article-markdown');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            
            textarea.value = text.substring(0, start) + content + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + content.length, start + content.length);
            
            // Trigger input event for markdown preview
            textarea.dispatchEvent(new Event('input', {
                bubbles: true,
                cancelable: true
            }));
        };
        this.insertImage = function(imageData) {
            if (!imageData) return;
            const size = imageData.selectedSize || 'large';
            const imgMarkdown = `![${imageData.alt}](/files/Images/image/${size}/${imageData.src} "${imageData.name}")`;
            this.insertContent(imgMarkdown);
        };
        this.insertVideo = function(videoData) {
            if (!videoData) return;
            const videoPlaceholder = `[youtube:${videoData.id}:${videoData.title}]`;
            this.insertContent(videoPlaceholder);
        };
    },

    /**
     * Load media content via AJAX
     * @private
     */
    _loadMediaContent: function(url) {
        const container = document.querySelector('#gallery-selector, #image-gallery, #video-gallery');
        if (!container) return;

        container.innerHTML = '<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.csrfToken || ''
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            this._initializeMediaHandlers();
        })
        .catch(error => {
            console.error('Error loading media content:', error);
            container.innerHTML = '<div class="alert alert-danger" role="alert">Error loading content. Please try again.</div>';
        });
    },

    /**
     * Build search URL with parameters
     * @private
     */
    _buildSearchUrl: function(type, searchTerm = '') {
        const endpoints = {
            image: '/admin/images/imageSelect',
            video: '/admin/videos/video_select',
            gallery: '/admin/image-galleries/picker'
        };

        const url = new URL(endpoints[type], window.location.origin);
        
        // Set appropriate parameters for each type
        if (type === 'image') {
            url.searchParams.set('gallery_only', '1');
        } else if (type === 'video') {
            url.searchParams.set('gallery_only', '1');
        } else if (type === 'gallery') {
            url.searchParams.set('gallery_only', '1');
        }
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        }

        return url.toString();
    },

    /**
     * Detect editor type from editor instance or DOM context
     * @private
     */
    _detectEditorType: function(editor) {
        if (!editor) {
            // Try to detect from DOM context
            if (document.getElementById('article-markdown')) return 'markdown';
            return 'unknown';
        }
        
        // Check for Trumbowyg
        if (editor.o && editor.execCmd) return 'trumbowyg';
        
        // Check for markdown editor context
        if (document.getElementById('article-markdown')) return 'markdown';
        
        return 'unknown';
    },

    /**
     * Close the current modal
     * @private
     */
    _closeCurrentModal: function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('dynamicModal'));
        if (modal) {
            modal.hide();
            return;
        }

        // Fallback: find any open modal and close it
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modalInstance = bootstrap.Modal.getInstance(openModal);
            if (modalInstance) modalInstance.hide();
        }
    }
};
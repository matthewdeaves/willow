/**
 * Main editor initialization and handlers
 * This file handles the Trumbowyg editor setup, image/video insertion, and code highlighting
 */
$(document).ready(function() {
    /**
     * Safely escapes HTML content to prevent XSS attacks
     * @param {string} unsafe - The unsafe string to escape
     * @returns {string} The escaped string
     */
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Safely highlights code blocks using highlight.js
     * Ensures proper escaping and prevents double-highlighting
     */
    function safeHighlight() {
        document.querySelectorAll('pre code:not(.hljs)').forEach(block => {
            if (!block.classList.contains('hljs')) {
                hljs.highlightElement(block);
            }
        });
    }

    // Initialize Highlight.js on page load
    safeHighlight();

    // Add Highlight.js initialization after Trumbowyg content changes
    $('#article-body').on('tbwchange', function() {
        safeHighlight();
    });

    /**
     * Image handling functionality
     */
    const imageHandlers = {
        bindEvents: function(trumbowyg) {
            // Image click handler
            $('#selectImageWindow').off('click', 'img').on('click', 'img', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const imageSrc = escapeHtml($(this).data('src'));
                const imageId = escapeHtml($(this).data('id'));
                const imageAlt = escapeHtml($(this).data('alt'));
                const imageSize = escapeHtml($('#' + imageId + '_size').val());
                const imageHtml = `<img src="/files/Images/image/${imageSize}/${imageSrc}" alt="${imageAlt}" class="img-fluid" />`;

                trumbowyg.restoreRange();
                trumbowyg.execCmd('insertHTML', imageHtml, false, true);
                $('#imageSelectModal').modal('hide');

                return false;
            });

            // Pagination handler
            $('.pagination a').off('click').on('click', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                imageHandlers.loadImages(url, trumbowyg);
            });

            // Search handler with debounce
            const searchInput = document.getElementById('imageSearch');
            if (searchInput) {
                let debounceTimer;
                $(searchInput).off('input').on('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const searchTerm = this.value.trim();
                        let url = '/admin/images/imageSelect';
                        if (searchTerm.length > 0) {
                            url += '?search=' + encodeURIComponent(searchTerm);
                        }
                        imageHandlers.loadImages(url, trumbowyg);
                    }, 300);
                });
            }
        },

        loadImages: function(url, trumbowyg) {
            $.ajax({
                url: url,
                type: 'GET',
                data: { gallery_only: true },
                success: (response) => {
                    $('#image-gallery').html(response);
                    this.bindEvents(trumbowyg);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading images:", error);
                }
            });
        }
    };

    /**
     * Video handling functionality
     */
    const videoHandlers = {
        bindEvents: function(trumbowyg) {
            // Video click handler
            $('#selectVideoWindow').off('click', 'button.select-video').on('click', 'button.select-video', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const videoId = escapeHtml($(this).data('video-id'));
                const videoTitle = escapeHtml($(this).data('video-title'));
                const placeholder = `[youtube:${videoId}:560:315:${videoTitle}]`;

                trumbowyg.restoreRange();
                trumbowyg.execCmd('insertHTML', placeholder, false, true);
                $('#videoSelectModal').modal('hide');

                return false;
            });

            // Channel filter handler
            $('#channelFilter').off('change').on('change', function() {
                const searchInput = document.getElementById('videoSearch');
                const searchTerm = searchInput ? searchInput.value.trim() : '';
                videoHandlers.loadVideos(searchTerm, trumbowyg);
            });

            // Search handler with debounce
            const searchInput = document.getElementById('videoSearch');
            if (searchInput) {
                let debounceTimer;
                $(searchInput).off('input').on('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        const searchTerm = this.value.trim();
                        videoHandlers.loadVideos(searchTerm, trumbowyg);
                    }, 300);
                });
            }
        },

        loadVideos: function(searchTerm, trumbowyg) {
            const channelFilter = $('#channelFilter').is(':checked');
            const params = {
                gallery_only: true,
                channel_filter: channelFilter
            };

            if (searchTerm) {
                params.search = searchTerm;
            }

            $.ajax({
                url: '/admin/videos/video_select',
                type: 'GET',
                data: params,
                success: (response) => {
                    $('#video-gallery').html(response);
                    this.bindEvents(trumbowyg);
                },
                error: function(xhr, status, error) {
                    console.error("Error loading videos:", error);
                }
            });
        }
    };

    // Extend Trumbowyg with custom plugins
    $.extend(true, $.trumbowyg, {
        plugins: {
            insertImageFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertImageFromLibrary = {};

                    trumbowyg.addBtnDef('insertImageFromLibrary', {
                        fn: function() {
                            trumbowyg.saveRange();

                            const $modal = $('<div>', {
                                class: 'modal fade',
                                id: 'imageSelectModal',
                                tabindex: '-1',
                                role: 'dialog',
                                'aria-labelledby': 'imageSelectModalLabel',
                                'aria-hidden': 'true'
                            }).css('z-index', 99999);

                            const modalContent = `
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="imageSelectModalLabel">Insert Image from Library</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="selectImageWindow"></div>
                                    </div>
                                </div>
                            `;

                            $modal.html(modalContent);
                            $('#imageSelectModal').remove();
                            $('body').append($modal);

                            $.ajax({
                                url: '/admin/images/image_select',
                                method: 'GET',
                                success: function(data) {
                                    $('#selectImageWindow').html(data);
                                    imageHandlers.bindEvents(trumbowyg);
                                    $('#imageSelectModal').modal('show');
                                },
                                error: function() {
                                    alert('Failed to load images.');
                                }
                            });
                        },
                        title: 'Insert Image from Library',
                        ico: 'insertImage'
                    });
                }
            },

            insertVideoFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertVideoFromLibrary = {};

                    trumbowyg.addBtnDef('insertVideoFromLibrary', {
                        fn: function() {
                            trumbowyg.saveRange();

                            const $modal = $('<div>', {
                                class: 'modal fade',
                                id: 'videoSelectModal',
                                tabindex: '-1',
                                role: 'dialog',
                                'aria-labelledby': 'videoSelectModalLabel',
                                'aria-hidden': 'true'
                            }).css('z-index', 99999);

                            const modalContent = `
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="videoSelectModalLabel">Insert YouTube Video</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="selectVideoWindow"></div>
                                    </div>
                                </div>
                            `;

                            $modal.html(modalContent);
                            $('#videoSelectModal').remove();
                            $('body').append($modal);

                            $.ajax({
                                url: '/admin/videos/video_select',
                                method: 'GET',
                                success: function(data) {
                                    $('#selectVideoWindow').html(data);
                                    videoHandlers.bindEvents(trumbowyg);
                                    $('#videoSelectModal').modal('show');
                                },
                                error: function() {
                                    alert('Failed to load videos.');
                                }
                            });
                        },
                        title: 'Insert Video from Library',
                        ico: 'camera-reels'
                    });
                }
            },

            highlight: {
                init: function(trumbowyg) {
                    trumbowyg.addBtnDef('highlight', {
                        fn: function() {
                            trumbowyg.saveRange();

                            const modal = $('<div>', {
                                class: 'modal fade',
                                id: 'highlightModal',
                                tabindex: '-1',
                                role: 'dialog',
                                'aria-labelledby': 'highlightModalLabel',
                                'aria-hidden': 'true'
                            }).css('z-index', 99999);

                            const content = `
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="highlightModalLabel">Insert Code</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group mb-3">
                                                <label for="code-language">Language</label>
                                                <select class="form-select" id="code-language">
                                                    <option value="php">PHP</option>
                                                    <option value="javascript">JavaScript</option>
                                                    <option value="css">CSS</option>
                                                    <option value="html">HTML</option>
                                                    <option value="sql">SQL</option>
                                                    <option value="bash">Bash</option>
                                                    <option value="json">JSON</option>
                                                    <option value="xml">XML</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="code-content">Code</label>
                                                <textarea class="form-control" id="code-content" rows="10"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary" id="insertCode">Insert</button>
                                        </div>
                                    </div>
                                </div>
                            `;

                            modal.html(content);
                            $('body').append(modal);

                            $('#insertCode').on('click', function() {
                                const language = escapeHtml($('#code-language').val());
                                let code = $('#code-content').val();
                                code = escapeHtml(code);
                                const html = `<pre><code class="language-${language}">${code}</code></pre>`;
                                
                                trumbowyg.restoreRange();
                                trumbowyg.execCmd('insertHTML', html);
                                
                                // Add a small delay to allow DOM update
                                setTimeout(() => {
                                    safeHighlight();
                                }, 100);
                                
                                $('#highlightModal').modal('hide');
                                setTimeout(() => {
                                    $('#highlightModal').remove();
                                }, 250);
                            });

                            $('#highlightModal').modal('show');
                        },
                        ico: 'code'
                    });
                }
            }
        }
    });

    // Initialize Trumbowyg editor
    $('#article-body').trumbowyg({
        btns: [
            ['viewHTML'],
            ['formatting'],
            ['textFormat'],
            ['link'],
            ['insertImageFromLibrary', 'insertVideoFromLibrary'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['table'],
            ['highlight'],
            ['tableCellBackgroundColor', 'tableBorderColor'],
            ['removeformat'],
            ['fullscreen']
        ],
        btnsDef: {
            textFormat: {
                dropdown: ['bold', 'italic', 'underline', 'strikethrough', 'preformatted', 'superscript', 'subscript'],
                ico: 'strong'
            }
        },
        plugins: {
            insertImageFromLibrary: {},
            insertVideoFromLibrary: {},
            table: {},
            colors: {},
            highlight: {}
        },
        autogrow: true,
        autogrowOnEnter: true,
        minHeight: 400
    }).on('tbwinit', function() {
        safeHighlight();
    });
});
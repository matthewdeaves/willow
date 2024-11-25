$(document).ready(function() {
    const imageHandlers = {
        bindEvents: function(trumbowyg) {
            // Image click handler
            $('#selectImageWindow').off('click', 'img').on('click', 'img', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var imageUrl = $(this).attr('src');
                var imageAlt = $(this).attr('alt') || '';
                var imageSize = $(this).data('size') || '';
                
                // Create the image HTML
                var imageHtml = '<img src="' + imageUrl + '" alt="' + imageAlt + '" class="' + imageSize + '">';
                
                // Restore the range before inserting
                trumbowyg.restoreRange();
                
                // Insert the HTML directly
                trumbowyg.execCmd('insertHTML', imageHtml, false, true);
                
                // Close the modal
                $('#imageSelectModal').modal('hide');
                
                return false;
            });

            // Pagination handler
            $('.pagination a').off('click').on('click', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                imageHandlers.loadImages(url, trumbowyg);
            });

            // Search handler
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

    $.extend(true, $.trumbowyg, {
        plugins: {
            insertImageFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertImageFromLibrary = {};

                    trumbowyg.addBtnDef('insertImageFromLibrary', {
                        fn: function() {
                            // Save the range before opening the modal
                            trumbowyg.saveRange();

                            // Create Bootstrap modal
                            var $modal = $('<div>', {
                                class: 'modal fade',
                                id: 'imageSelectModal',
                                tabindex: '-1',
                                role: 'dialog',
                                'aria-labelledby': 'imageSelectModalLabel',
                                'aria-hidden': 'true'
                            }).css({
                                'z-index': 99999
                            }).append(
                                $('<div>', {
                                    class: 'modal-dialog modal-lg',
                                    role: 'document'
                                }).append(
                                    $('<div>', {
                                        class: 'modal-content'
                                    }).append(
                                        $('<div>', {
                                            class: 'modal-header'
                                        }).append(
                                            $('<h5>', {
                                                class: 'modal-title',
                                                id: 'imageSelectModalLabel',
                                                text: 'Insert Image from Library'
                                            }),
                                            $('<button>', {
                                                type: 'button',
                                                class: 'close',
                                                'data-dismiss': 'modal',
                                                'aria-label': 'Close'
                                            }).append(
                                                $('<span>', {
                                                    'aria-hidden': 'true',
                                                    html: '&times;'
                                                })
                                            ).on('click', function() {
                                                $('#imageSelectModal').modal('hide');
                                            }),
                                        ),
                                        $('<div>', {
                                            class: 'modal-body',
                                            id: 'selectImageWindow'
                                        })
                                    )
                                )
                            );

                            // Remove any existing modal and append the new one
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
            }
        }
    });

    $('#article-body').trumbowyg({
        btns: [
            ['viewHTML'],
            ['preformatted'],
            ['formatting'],
            ['bold', 'italic', 'underline', 'strikethrough'],
            ['superscript', 'subscript'],
            ['link'],
            ['insertImageFromLibrary'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['table'],
            ['tableCellBackgroundColor', 'tableBorderColor'],
            ['removeformat'],
            ['fullscreen'],
        ],
        plugins: {
            insertImageFromLibrary: {},
            table: {},
            colors: {}
        }
    });
});
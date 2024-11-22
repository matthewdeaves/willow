$(document).ready(function() {
    $.extend(true, $.trumbowyg, {
        plugins: {
            insertImageFromLibrary: {
                init: function(trumbowyg) {
                    trumbowyg.o.plugins.insertImageFromLibrary = {};

                    trumbowyg.addBtnDef('insertImageFromLibrary', {
                        fn: function() {
                            var prefix = trumbowyg.o.prefix;

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
                            // Remove any existing model and append the new one
                            $('#imageSelectModal').remove();
                            $('body').append($modal);

                            $.ajax({
                                url: '/admin/images/image_select',
                                method: 'GET',
                                success: function(data) {
                                    $('#selectImageWindow').html(data);

                                    // Add click event to images
                                    $('#selectImageWindow').on('click', 'img', function() {
                                        var imageUrl = $(this).attr('src');
                                        var imageSize = $(this).data('size') || '';

                                        trumbowyg.execCmd('insertImage', imageUrl);
                                        var $img = $('img[src="' + imageUrl + '"]:not([alt])', trumbowyg.$box);
                                        $img.attr('alt', $(this).attr('alt') || '');
                                        $img.addClass(imageSize);

                                        $('#imageSelectModal').modal('hide');
                                    });

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
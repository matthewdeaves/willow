<?= $this->Html->script('https://code.jquery.com/jquery-3.7.1.min.js'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
<?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/ui/trumbowyg.table.min.css'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/trumbowyg.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/upload/trumbowyg.upload.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/table/trumbowyg.table.min.js'); ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/plugins/preformatted/trumbowyg.preformatted.min.js'); ?>
<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
<style>
.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); /* Adjust minmax values as needed */
    gap: 5px; /* Space between grid items */
}

.image-item {
    border: 1px solid #ccc; /* Optional: Add border */
    padding: 5px; /* Optional: Add padding */
    text-align: center; /* Center text */
}

.image-item img {
    max-width: 100%; /* Ensure images are responsive */
    height: auto; /* Maintain aspect ratio */
}
#imageSelectModal {
    z-index: 99999 !important;
}
.trumbowyg-fullscreen .trumbowyg-editor {
    color: black !important;
}
</style>
<script>
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
                                url: '/admin/images/trumbowygSelect',
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
            insertImageFromLibrary: {}
        }
    });

    
});
</script>
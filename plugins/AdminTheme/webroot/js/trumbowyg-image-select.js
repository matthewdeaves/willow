$(document).ready(function() {
    function bindImageInsertEvents() {
        $('.insert-image').off('click').on('click', function() {
            var imageSrc = $(this).data('src');
            var imageId = $(this).data('id');
            var imageAlt = $(this).data('alt');
            var imageSize = $('#' + imageId + '_size').val();

            var imageHtml = '<img src="/files/Images/image/' + imageSize + '/' + imageSrc + '" alt="' + imageAlt + '" class="img-fluid" />';

            var trumbowyg = $('#article-body').data('trumbowyg');
            if (trumbowyg) {
                trumbowyg.execCmd('insertHTML', imageHtml);
                trumbowyg.closeModal();
            }

            $('.modal').modal('hide');
            $('.trumbowyg-modal-box').hide();
            $('.trumbowyg-modal-overlay').hide();

            return false;
        });
    }

    function loadImages(url) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { gallery_only: true },
            success: function(response) {
                $('#image-gallery').html(response);
                bindImageInsertEvents();
                bindPaginationEvents();
            },
            error: function(xhr, status, error) {
                console.error("<?= __('Error loading images:') ?>", error);
            }
        });
    }

    function bindPaginationEvents() {
        $('.pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadImages(url);
        });
    }

    function bindSearchBoxEvents() {
        const searchInput = document.getElementById('imageSearch');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value.trim();
                let url = '/admin/images/imageSelect';
                if (searchTerm.length > 0) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                loadImages(url);
            }, 300);
        });
    }
    
    bindImageInsertEvents();
    bindPaginationEvents();
    bindSearchBoxEvents();
});
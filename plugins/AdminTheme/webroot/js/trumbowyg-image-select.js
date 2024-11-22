const TrumbowygImageSelect = {
    init: function() {
        $(document).ready(() => {
            this.bindImageInsertEvents();
            this.bindPaginationEvents();
            this.bindSearchBoxEvents();
        });
    },

    bindImageInsertEvents: function() {
        $('.insert-image').off('click').on('click', function() {
            const imageSrc = $(this).data('src');
            const imageId = $(this).data('id');
            const imageAlt = $(this).data('alt');
            const imageSize = $('#' + imageId + '_size').val();

            const imageHtml = '<img src="/files/Images/image/' + imageSize + '/' + 
                imageSrc + '" alt="' + imageAlt + '" class="img-fluid" />';

            const trumbowyg = $('#article-body').data('trumbowyg');
            if (trumbowyg) {
                trumbowyg.execCmd('insertHTML', imageHtml);
                trumbowyg.closeModal();
            }

            $('.modal').modal('hide');
            $('.trumbowyg-modal-box').hide();
            $('.trumbowyg-modal-overlay').hide();

            return false;
        });
    },

    loadImages: function(url) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { gallery_only: true },
            success: (response) => {
                $('#image-gallery').html(response);
                this.bindImageInsertEvents();
                this.bindPaginationEvents();
            },
            error: function(xhr, status, error) {
                console.error("Error loading images:", error);
            }
        });
    },

    bindPaginationEvents: function() {
        $('.pagination a').off('click').on('click', (e) => {
            e.preventDefault();
            const url = $(e.currentTarget).attr('href');
            this.loadImages(url);
        });
    },

    bindSearchBoxEvents: function() {
        const searchInput = document.getElementById('imageSearch');
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = searchInput.value.trim();
                let url = '/admin/images/imageSelect';
                if (searchTerm.length > 0) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                this.loadImages(url);
            }, 300);
        });
    }
};

// Auto-initialize
TrumbowygImageSelect.init();
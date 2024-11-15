<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use Cake\Core\Configure; ?>
<?php if (!$this->request->getQuery('gallery_only')): ?>
<div class="mb-3">
    <?php $searchQuery = $this->request->getQuery('search', ''); ?>
    <input type="text" id="imageSearch" class="form-control" placeholder="<?= __('Search images...') ?>" value="<?= h($searchQuery) ?>">
</div>
<?php endif; ?>
<div id="image-gallery" class="flex-shrink-0">
    <?php include 'image_gallery.php'; ?>
</div>
<script>
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
                let url = '/admin/images/trumbowygSelect';
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
</script>
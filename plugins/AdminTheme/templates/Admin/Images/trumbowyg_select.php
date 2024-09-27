<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use Cake\Core\Configure; ?>
<div class="container-fluid" id="image-gallery">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($images as $image): ?>
            <div class="col">
                <div class="card h-100">
                    <?= $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.small'), 
                        [
                            'pathPrefix' => 'files/Images/image_file/',
                            'alt' => 'Picture',
                            'class' => 'card-img-top insert-image',
                            'data-src' => $image->image_file,
                            'data-id' => $image->id
                        ]
                    ) ?>
                    <div class="card-body">
                        <h6 class="card-title text-truncate"><?= h($image->name) ?></h6>
                        <?= $this->Form->select(
                            'size',
                            array_flip(Configure::read('SiteSettings.ImageSizes')),
                            [
                                'hiddenField' => false,
                                'id' => $image->id . '_size',
                                'class' => 'form-select'
                            ]
                        ); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= $this->element('pagination') ?>
</div>
<script>
$(document).ready(function() {
    function bindImageInsertEvents() {
        $('.insert-image').off('click').on('click', function() {
            var imageSrc = $(this).data('src');
            var imageId = $(this).data('id');
            var imageSize = $('#' + imageId + '_size').val();

            var imageHtml = '<img src="/files/Images/image_file/' + imageSrc + '_' + imageSize + '" class="img-fluid" />';

            var trumbowyg = $('#article-body').data('trumbowyg');
            if (trumbowyg) {
                trumbowyg.execCmd('insertHTML', imageHtml);
                trumbowyg.closeModal();
            }

            // Close any Bootstrap modal
            $('.modal').modal('hide');

            // Force Trumbowyg to close its modal
            $('.trumbowyg-modal-box').hide();
            $('.trumbowyg-modal-overlay').hide();

            return false; // Prevent any default action
        });
    }

    function loadImages(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#image-gallery').html(response);
                bindImageInsertEvents();
                bindPaginationEvents();
            },
            error: function(xhr, status, error) {
                console.error("Error loading images:", error);
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

    // Initial binding
    bindImageInsertEvents();
    bindPaginationEvents();
});
</script>
<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<div class="container-fluid">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($images as $image): ?>
            <div class="col">
                <div class="card h-100">
                    <?= $this->Html->image($image->path . '_' . Configure::read('SiteSettings.ImageSizes.small'), 
                        [
                            'pathPrefix' => 'files/Images/path/',
                            'alt' => 'Picture',
                            'class' => 'card-img-top insert-image',
                            'data-src' => $image->path,
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
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?= $this->Paginator->first('<< ' . __('first'), ['class' => 'page-item']) ?>
                    <?= $this->Paginator->prev('< ' . __('previous'), ['class' => 'page-item']) ?>
                    <?= $this->Paginator->numbers(['class' => 'page-item']) ?>
                    <?= $this->Paginator->next(__('next') . ' >', ['class' => 'page-item']) ?>
                    <?= $this->Paginator->last(__('last') . ' >>', ['class' => 'page-item']) ?>
                </ul>
            </nav>
            <p class="text-center"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.insert-image').on('click', function() {
        var imageSrc = $(this).data('src');
        var imageId = $(this).data('id');
        var imageSize = $('#' + imageId + '_size').val();

        var imageHtml = '<img src="/files/Images/path/' + imageSrc + '_' + imageSize + '" class="img-fluid" />';

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
});
</script>
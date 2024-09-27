<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<div>
    <div>
        <div class="images-grid">
            <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <?= $this->Html->image($image->path . '_' . Configure::read('ImageSizes.small'), 
                        [
                            'pathPrefix' => 'files/Images/path/',
                            'alt' => 'Picture',
                            'class' => 'insert-image',
                            'data-src' => $image->path,
                            'data-id' => $image->id
                        ]
                    ) ?>
                    <div class="image-name"><?= h($image->name) ?></div>
                    <?= $this->Form->select(
                        'size',
                        array_flip(Configure::read('ImageSizes')),
                        [
                            'hiddenField' => false,
                            'id' => $image->id . '_size'
                        ]
                    ); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <div><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></div>
    </div>
</div>
<script>
    $(document).ready(function() {
    // Add click event listener to images
    $('.insert-image').on('click', function() {
        // Get the image source from the data attribute
        var imageSrc = $(this).data('src');
        var imageId = $(this).data('id');

        var imageSize = $('#' + imageId + '_size').val();
        
        // Create the HTML for the image
        var imageHtml = '<img src="/files/Images/path/' + imageSrc + '_' + imageSize + '" />';
        
        // Insert the image into the Trumbowyg editor
        $('#article-body').trumbowyg('execCmd', {
            cmd: 'insertHTML',
            param: imageHtml,
            forceCss: false
        });
        // Close current modal box
        $('#article-body').trumbowyg('closeModal');
    });
});
</script>

<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use Cake\Core\Configure; ?>
<div class="container-fluid">
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
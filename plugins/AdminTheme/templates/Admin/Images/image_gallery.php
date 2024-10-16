<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container-fluid">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($images as $image): ?>
            <div class="col">
                <div class="card h-100">
                    <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $image->file, 
                        [
                            'pathPrefix' => 'files/Images/file/',
                            'alt' => $image->alt_text,
                            'class' => 'card-img-top insert-image',
                            'data-src' => $image->file,
                            'data-id' => $image->id,
                            'data-alt' => $image->alt_text
                        ]
                    ) ?>
                    <div class="card-body">
                        <h6 class="card-title text-truncate"><?= h($image->name) ?></h6>
                        <?php
                        $imageSizes = SettingsManager::read('ImageSizes');
                        arsort($imageSizes);
                        $imageSizes = array_flip($imageSizes);
                        echo $this->Form->select(
                            'size',
                            $imageSizes,
                            [
                                'hiddenField' => false,
                                'id' => $image->id . '_size',
                                'class' => 'form-select'
                            ]
                        );
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= $this->element('pagination') ?>
</div>
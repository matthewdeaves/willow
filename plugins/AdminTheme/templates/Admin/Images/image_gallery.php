<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="album bg-body-tertiary mb-3">
    <div class="container">
        <div id="imageResults" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($images as $image): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <?= $this->Html->image(SettingsManager::read('ImageSizes.large') . '/' . $image->image, 
                            [
                                'pathPrefix' => 'files/Images/image/',
                                'alt' => $image->alt_text,
                                'class' => 'card-img-top insert-image',
                                'data-src' => $image->image,
                                'data-name' => $image->name,
                                'data-id' => $image->id,
                                'data-alt' => $image->alt_text
                            ]
                        ) ?>
                        <div class="card-body">
                        <p class="card-text"><?= h($image->name) ?></p>
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
                                    'class' => 'form-select',
                                    'value' => SettingsManager::read('ImageSizes.large')
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="container my-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Image',
            'controllerName' => 'Images',
            'entity' => $image,
            'entityDisplayName' => $image->name
        ]);
        ?>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($image->name) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($image->name) ?></td>
                        </tr>
                        <tr>
                            <?php if (!empty($image->file)): ?>
                                <div class="mb-3">
                                <?= $this->Html->image(SettingsManager::read('ImageSizes.large', '200') . '/' . $image->file, 
                                    [
                                        'pathPrefix' => 'files/Images/file/',
                                        'alt' => $image->alt_text,
                                        'class' => 'img-thumbnail',
                                    ])?>
                                </div>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <th><?= __('Alt Text') ?></th>
                            <td><?= h($image->alt_text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Keywords') ?></th>
                            <td><?= h($image->keywords) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($image->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($image->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
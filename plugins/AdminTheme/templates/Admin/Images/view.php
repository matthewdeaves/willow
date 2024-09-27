<?php use Cake\Core\Configure; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Image',
                'controllerName' => 'Images',
                'entity' => $image,
                'entityDisplayName' => $image->name
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= h($image->name) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th class="w-25"><?= __('Image') ?></th>
                            <td>
                                <?= $this->Html->image($image->image_file . '_' . Configure::read('SiteSettings.ImageSizes.large'), 
                                ['pathPrefix' => 'files/Images/image_file/', 'alt' => 'Picture', 'class' => 'img-fluid']) ?>
                            </td>
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
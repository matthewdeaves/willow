<?php use App\Utility\SettingsManager; ?>
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
                    <h3 class="mb-0"><?= __('Edit Image') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($image, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('name', [
                                'class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('alt_text', [
                                'class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('keywords', [
                                'class' => 'form-control' . ($this->Form->isFieldError('keywords') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('file', [
                                'type' => 'file',
                                'class' => 'form-control' . ($this->Form->isFieldError('file') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $image->file, 
                                ['pathPrefix' => 'files/Images/file/', 'alt' => $image->alt_text, 'class' => 'img-fluid']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Update Image'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
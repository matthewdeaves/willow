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
            'entityDisplayName' => __('Add Image')
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Add Image') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($image, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('name', [
                                'class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                            <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?= $this->Form->control('path', [
                                'type' => 'file',
                                'class' => 'form-control-file' . ($this->Form->isFieldError('path') ? ' is-invalid' : ''),
                                'label' => 'Image',
                                'error' => false, // We'll handle the error message manually
                            ]) ?>
                            <?php if ($this->Form->isFieldError('path')): ?>
                                <div class="invalid-feedback d-block">
                                    <?= $this->Form->error('path') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
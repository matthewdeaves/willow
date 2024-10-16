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
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('alt_text', [
                                'class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                            <?php if ($this->Form->isFieldError('alt_text')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('alt_text') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?= $this->Form->control('keywords', [
                                'class' => 'form-control' . ($this->Form->isFieldError('keywords') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                            <?php if ($this->Form->isFieldError('keywords')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('keywords') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?= $this->Form->control('file', [
                                'type' => 'file',
                                'class' => 'form-control-file' . ($this->Form->isFieldError('file') ? ' is-invalid' : ''),
                                'label' => 'Image',
                                'error' => false,
                            ]) ?>
                            <?php if ($this->Form->isFieldError('file')): ?>
                                <div class="invalid-feedback d-block">
                                    <?= $this->Form->error('file') ?>
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
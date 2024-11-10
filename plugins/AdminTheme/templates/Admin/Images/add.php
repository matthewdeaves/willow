<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<div class="container mt-4">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Image') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($image,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <div class="mb-3">
                            <?php echo $this->Form->control('name', ['class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <?php echo $this->Form->control('alt_text', ['class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('alt_text')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('alt_text') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <?php echo $this->Form->control('keywords', ['class' => 'form-control' . ($this->Form->isFieldError('keywords') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('keywords')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('keywords') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <?= $this->Form->control('file', [
                                'type' => 'file',
                                'label' => [
                                    'text' => __('Image'),
                                    'class' => 'form-label'
                                ],
                                'class' => 'form-control' . ($this->Form->isFieldError('file') ? ' is-invalid' : ''),
                                'id' => 'customFile'
                            ]) ?>
                            <?php if ($this->Form->isFieldError('file')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('file') ?>
                                </div>
                            <?php endif; ?>
                        </div>                                                                     
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
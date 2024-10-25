<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Internationalisation $internationalisation */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Internationalisation',
            'controllerName' => 'Internationalisations',
            'entity' => $internationalisation,
            'entityDisplayName' => $internationalisation->message_id
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Edit Internationalisation') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($internationalisation, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('locale', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('locale') ? ' is-invalid' : ''),
                                    'required' => true,
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('message_id', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('message_id') ? ' is-invalid' : ''),
                                    'required' => true,
                                    'type' => 'textarea',
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('message_str', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('message_str') ? ' is-invalid' : ''),
                                    'required' => false,
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mt-4 mb-3">
                                    <?= $this->Form->button(__('Add Internationalisation'), [
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
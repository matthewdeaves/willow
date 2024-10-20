<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
            echo $this->element('actions_card', [
                'modelName' => 'Aiprompt',
                'controllerName' => 'Aiprompts',
                'entity' => $aiprompt
            ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Add AI Prompt') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($aiprompt, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('task_type', [
                                'class' => 'form-control' . ($this->Form->isFieldError('task_type') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('system_prompt', [
                                'type' => 'textarea',
                                'rows' => '3',
                                'class' => 'form-control' . ($this->Form->isFieldError('system_prompt') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('model', [
                                'class' => 'form-control' . ($this->Form->isFieldError('model') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <?= $this->Form->control('max_tokens', [
                                'type' => 'number',
                                'class' => 'form-control' . ($this->Form->isFieldError('max_tokens') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <?= $this->Form->control('temperature', [
                                'type' => 'number',
                                'step' => '0.1',
                                'min' => '0',
                                'max' => '1',
                                'class' => 'form-control' . ($this->Form->isFieldError('temperature') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Submit'), [
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
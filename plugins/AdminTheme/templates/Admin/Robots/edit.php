<?php
/**
 * @var \App\View\AppView $this
 * @var string $robotsContent
 */
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?= $this->Html->link(
                            __('Reset to Default'),
                            ['action' => 'reset'],
                            [
                                'class' => 'list-group-item list-group-item-action',
                                'confirm' => __('Are you sure you want to reset to the default robots.txt content?')
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Robots.txt') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'class' => 'needs-validation',
                        'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <?= $this->Form->control('robotsContent', [
                            'type' => 'textarea',
                            'value' => $robotsContent,
                            'rows' => 20,
                            'class' => 'form-control font-monospace',
                            'label' => false,
                            'required' => true
                        ]) ?>
                    </fieldset>
                    <div class="form-group mt-3">
                        <?= $this->Form->button(__('Save Changes'), [
                            'class' => 'btn btn-primary'
                        ]) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
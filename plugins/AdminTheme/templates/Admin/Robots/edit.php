<?php
/**
 * @var \App\View\AppView $this
 * @var string $robotsContent
 */
?>
<div class="robots form content">
    <?= $this->Form->create(null) ?>
    <fieldset>
        <legend><?= __('Edit Robots.txt') ?></legend>
        <?= $this->Form->control('robotsContent', [
            'type' => 'textarea',
            'value' => $robotsContent,
            'rows' => 20,
            'class' => 'form-control font-monospace',
            'label' => false
        ]) ?>
    </fieldset>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <?= $this->Html->link(
            __('Reset to Default'),
            ['action' => 'reset'],
            [
                'class' => 'button button-outline',
                'confirm' => __('Are you sure you want to reset to the default robots.txt content?')
            ]
        ) ?>
        <?= $this->Form->button(__('Save Changes'), ['class' => 'button']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
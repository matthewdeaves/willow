<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $connector
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $connector->connector_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $connector->connector_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Connectors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="connectors form content">
            <?= $this->Form->create($connector) ?>
            <fieldset>
                <legend><?= __('Edit Connector') ?></legend>
                <?php
                    echo $this->Form->control('name');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

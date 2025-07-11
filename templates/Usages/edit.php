<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $usage
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $usage->usage_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $usage->usage_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Usages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="usages form content">
            <?= $this->Form->create($usage) ?>
            <fieldset>
                <legend><?= __('Edit Usage') ?></legend>
                <?php
                    echo $this->Form->control('name');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

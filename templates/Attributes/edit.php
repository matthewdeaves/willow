<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $attribute
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $attribute->attribute_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $attribute->attribute_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Attributes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="attributes form content">
            <?= $this->Form->create($attribute) ?>
            <fieldset>
                <legend><?= __('Edit Attribute') ?></legend>
                <?php
                    echo $this->Form->control('name');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

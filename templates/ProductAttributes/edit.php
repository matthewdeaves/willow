<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $productAttribute
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $productAttribute->product_attribute_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $productAttribute->product_attribute_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Product Attributes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productAttributes form content">
            <?= $this->Form->create($productAttribute) ?>
            <fieldset>
                <legend><?= __('Edit Product Attribute') ?></legend>
                <?php
                    echo $this->Form->control('product_id');
                    echo $this->Form->control('attribute_id');
                    echo $this->Form->control('value');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

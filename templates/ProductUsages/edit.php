<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $productUsage
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $productUsage->product_usage_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $productUsage->product_usage_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Product Usages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productUsages form content">
            <?= $this->Form->create($productUsage) ?>
            <fieldset>
                <legend><?= __('Edit Product Usage') ?></legend>
                <?php
                    echo $this->Form->control('product_id');
                    echo $this->Form->control('usage_id');
                    echo $this->Form->control('value');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

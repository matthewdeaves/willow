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
            <?= $this->Html->link(__('Edit Product Usage'), ['action' => 'edit', $productUsage->product_usage_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Product Usage'), ['action' => 'delete', $productUsage->product_usage_id], ['confirm' => __('Are you sure you want to delete # {0}?', $productUsage->product_usage_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Product Usages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Product Usage'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productUsages view content">
            <h3><?= h($productUsage->product_usage_id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Value') ?></th>
                    <td><?= h($productUsage->value) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Usage Id') ?></th>
                    <td><?= $this->Number->format($productUsage->product_usage_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= $this->Number->format($productUsage->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Usage Id') ?></th>
                    <td><?= $this->Number->format($productUsage->usage_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
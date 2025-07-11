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
            <?= $this->Html->link(__('Edit Product Attribute'), ['action' => 'edit', $productAttribute->product_attribute_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Product Attribute'), ['action' => 'delete', $productAttribute->product_attribute_id], ['confirm' => __('Are you sure you want to delete # {0}?', $productAttribute->product_attribute_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Product Attributes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Product Attribute'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productAttributes view content">
            <h3><?= h($productAttribute->product_attribute_id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Value') ?></th>
                    <td><?= h($productAttribute->value) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Attribute Id') ?></th>
                    <td><?= $this->Number->format($productAttribute->product_attribute_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= $this->Number->format($productAttribute->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Attribute Id') ?></th>
                    <td><?= $this->Number->format($productAttribute->attribute_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
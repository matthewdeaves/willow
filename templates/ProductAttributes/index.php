<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $productAttributes
 */
?>
<div class="productAttributes index content">
    <?= $this->Html->link(__('New Product Attribute'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Product Attributes') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('product_attribute_id') ?></th>
                    <th><?= $this->Paginator->sort('product_id') ?></th>
                    <th><?= $this->Paginator->sort('attribute_id') ?></th>
                    <th><?= $this->Paginator->sort('value') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productAttributes as $productAttribute): ?>
                <tr>
                    <td><?= $this->Number->format($productAttribute->product_attribute_id) ?></td>
                    <td><?= $this->Number->format($productAttribute->product_id) ?></td>
                    <td><?= $this->Number->format($productAttribute->attribute_id) ?></td>
                    <td><?= h($productAttribute->value) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $productAttribute->product_attribute_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $productAttribute->product_attribute_id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $productAttribute->product_attribute_id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $productAttribute->product_attribute_id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
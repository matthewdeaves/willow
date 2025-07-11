<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $productUsages
 */
?>
<div class="productUsages index content">
    <?= $this->Html->link(__('New Product Usage'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Product Usages') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('product_usage_id') ?></th>
                    <th><?= $this->Paginator->sort('product_id') ?></th>
                    <th><?= $this->Paginator->sort('usage_id') ?></th>
                    <th><?= $this->Paginator->sort('value') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productUsages as $productUsage): ?>
                <tr>
                    <td><?= $this->Number->format($productUsage->product_usage_id) ?></td>
                    <td><?= $this->Number->format($productUsage->product_id) ?></td>
                    <td><?= $this->Number->format($productUsage->usage_id) ?></td>
                    <td><?= h($productUsage->value) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $productUsage->product_usage_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $productUsage->product_usage_id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $productUsage->product_usage_id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $productUsage->product_usage_id),
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
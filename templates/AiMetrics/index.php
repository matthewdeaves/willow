<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AiMetric> $aiMetrics
 */
?>
<div class="aiMetrics index content">
    <?= $this->Html->link(__('New Ai Metric'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Ai Metrics') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('task_type') ?></th>
                    <th><?= $this->Paginator->sort('execution_time_ms') ?></th>
                    <th><?= $this->Paginator->sort('tokens_used') ?></th>
                    <th><?= $this->Paginator->sort('cost_usd') ?></th>
                    <th><?= $this->Paginator->sort('success') ?></th>
                    <th><?= $this->Paginator->sort('model_used') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aiMetrics as $aiMetric): ?>
                <tr>
                    <td><?= h($aiMetric->id) ?></td>
                    <td><?= h($aiMetric->task_type) ?></td>
                    <td><?= $aiMetric->execution_time_ms === null ? '' : $this->Number->format($aiMetric->execution_time_ms) ?></td>
                    <td><?= $aiMetric->tokens_used === null ? '' : $this->Number->format($aiMetric->tokens_used) ?></td>
                    <td><?= $aiMetric->cost_usd === null ? '' : $this->Number->format($aiMetric->cost_usd) ?></td>
                    <td><?= h($aiMetric->success) ?></td>
                    <td><?= h($aiMetric->model_used) ?></td>
                    <td><?= h($aiMetric->created) ?></td>
                    <td><?= h($aiMetric->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $aiMetric->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiMetric->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $aiMetric->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $aiMetric->id),
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
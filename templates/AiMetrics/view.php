<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Ai Metric'), ['action' => 'edit', $aiMetric->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Ai Metric'), ['action' => 'delete', $aiMetric->id], ['confirm' => __('Are you sure you want to delete # {0}?', $aiMetric->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Ai Metrics'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Ai Metric'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="aiMetrics view content">
            <h3><?= h($aiMetric->task_type) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($aiMetric->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Task Type') ?></th>
                    <td><?= h($aiMetric->task_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Model Used') ?></th>
                    <td><?= h($aiMetric->model_used) ?></td>
                </tr>
                <tr>
                    <th><?= __('Execution Time Ms') ?></th>
                    <td><?= $aiMetric->execution_time_ms === null ? '' : $this->Number->format($aiMetric->execution_time_ms) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tokens Used') ?></th>
                    <td><?= $aiMetric->tokens_used === null ? '' : $this->Number->format($aiMetric->tokens_used) ?></td>
                </tr>
                <tr>
                    <th><?= __('Cost Usd') ?></th>
                    <td><?= $aiMetric->cost_usd === null ? '' : $this->Number->format($aiMetric->cost_usd) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($aiMetric->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($aiMetric->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Success') ?></th>
                    <td><?= $aiMetric->success ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Error Message') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($aiMetric->error_message)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
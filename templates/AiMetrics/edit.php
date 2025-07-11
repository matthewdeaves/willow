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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $aiMetric->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $aiMetric->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Ai Metrics'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="aiMetrics form content">
            <?= $this->Form->create($aiMetric) ?>
            <fieldset>
                <legend><?= __('Edit Ai Metric') ?></legend>
                <?php
                    echo $this->Form->control('task_type');
                    echo $this->Form->control('execution_time_ms');
                    echo $this->Form->control('tokens_used');
                    echo $this->Form->control('cost_usd');
                    echo $this->Form->control('success');
                    echo $this->Form->control('error_message');
                    echo $this->Form->control('model_used');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

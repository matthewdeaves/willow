<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $usage
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Usage'), ['action' => 'edit', $usage->usage_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Usage'), ['action' => 'delete', $usage->usage_id], ['confirm' => __('Are you sure you want to delete # {0}?', $usage->usage_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Usages'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Usage'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="usages view content">
            <h3><?= h($usage->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($usage->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Usage Id') ?></th>
                    <td><?= $this->Number->format($usage->usage_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
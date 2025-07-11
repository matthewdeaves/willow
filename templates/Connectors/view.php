<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $connector
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Connector'), ['action' => 'edit', $connector->connector_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Connector'), ['action' => 'delete', $connector->connector_id], ['confirm' => __('Are you sure you want to delete # {0}?', $connector->connector_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Connectors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Connector'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="connectors view content">
            <h3><?= h($connector->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($connector->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Connector Id') ?></th>
                    <td><?= $this->Number->format($connector->connector_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $productConnector
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Product Connector'), ['action' => 'edit', $productConnector->product_connector_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Product Connector'), ['action' => 'delete', $productConnector->product_connector_id], ['confirm' => __('Are you sure you want to delete # {0}?', $productConnector->product_connector_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Product Connectors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Product Connector'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productConnectors view content">
            <h3><?= h($productConnector->position) ?></h3>
            <table>
                <tr>
                    <th><?= __('Position') ?></th>
                    <td><?= h($productConnector->position) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Connector Id') ?></th>
                    <td><?= $this->Number->format($productConnector->product_connector_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= $this->Number->format($productConnector->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Connector Id') ?></th>
                    <td><?= $this->Number->format($productConnector->connector_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
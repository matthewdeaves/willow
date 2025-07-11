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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $productConnector->product_connector_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $productConnector->product_connector_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Product Connectors'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productConnectors form content">
            <?= $this->Form->create($productConnector) ?>
            <fieldset>
                <legend><?= __('Edit Product Connector') ?></legend>
                <?php
                    echo $this->Form->control('product_id');
                    echo $this->Form->control('connector_id');
                    echo $this->Form->control('position');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

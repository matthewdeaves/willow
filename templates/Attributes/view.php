<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $attribute
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Attribute'), ['action' => 'edit', $attribute->attribute_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Attribute'), ['action' => 'delete', $attribute->attribute_id], ['confirm' => __('Are you sure you want to delete # {0}?', $attribute->attribute_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Attributes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Attribute'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="attributes view content">
            <h3><?= h($attribute->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($attribute->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Attribute Id') ?></th>
                    <td><?= $this->Number->format($attribute->attribute_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
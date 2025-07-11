<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $productLink
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Product Link'), ['action' => 'edit', $productLink->product_link_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Product Link'), ['action' => 'delete', $productLink->product_link_id], ['confirm' => __('Are you sure you want to delete # {0}?', $productLink->product_link_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Product Links'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Product Link'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productLinks view content">
            <h3><?= h($productLink->url) ?></h3>
            <table>
                <tr>
                    <th><?= __('Url') ?></th>
                    <td><?= h($productLink->url) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Link Id') ?></th>
                    <td><?= $this->Number->format($productLink->product_link_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= $this->Number->format($productLink->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Last Verification Date') ?></th>
                    <td><?= h($productLink->last_verification_date) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
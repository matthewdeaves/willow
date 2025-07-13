<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $products
 */
?>
<div class="products index content">
    <?= $this->Html->link(__('New Product'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Products') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('kind') ?></th>
                    <th><?= $this->Paginator->sort('featured') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('lede') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('image') ?></th>
                    <th><?= $this->Paginator->sort('alt_text') ?></th>
                    <th><?= $this->Paginator->sort('keywords') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('dir') ?></th>
                    <th><?= $this->Paginator->sort('size') ?></th>
                    <th><?= $this->Paginator->sort('mime') ?></th>
                    <th><?= $this->Paginator->sort('is_published') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('published') ?></th>
                    <th><?= $this->Paginator->sort('meta_title') ?></th>
                    <th><?= $this->Paginator->sort('word_count') ?></th>
                    <th><?= $this->Paginator->sort('parent_id') ?></th>
                    <th><?= $this->Paginator->sort('lft') ?></th>
                    <th><?= $this->Paginator->sort('rght') ?></th>
                    <th><?= $this->Paginator->sort('main_menu') ?></th>
                    <th><?= $this->Paginator->sort('view_count') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= h($product->id) ?></td>
                    <td><?= h($product->user_id) ?></td>
                    <td><?= h($product->kind) ?></td>
                    <td><?= h($product->featured) ?></td>
                    <td><?= h($product->title) ?></td>
                    <td><?= h($product->lede) ?></td>
                    <td><?= h($product->slug) ?></td>
                    <td><?= h($product->image) ?></td>
                    <td><?= h($product->alt_text) ?></td>
                    <td><?= h($product->keywords) ?></td>
                    <td><?= h($product->name) ?></td>
                    <td><?= h($product->dir) ?></td>
                    <td><?= $product->size === null ? '' : $this->Number->format($product->size) ?></td>
                    <td><?= h($product->mime) ?></td>
                    <td><?= h($product->is_published) ?></td>
                    <td><?= h($product->created) ?></td>
                    <td><?= h($product->modified) ?></td>
                    <td><?= h($product->published) ?></td>
                    <td><?= h($product->meta_title) ?></td>
                    <td><?= $product->word_count === null ? '' : $this->Number->format($product->word_count) ?></td>
                    <td><?= h($product->parent_id) ?></td>
                    <td><?= $this->Number->format($product->lft) ?></td>
                    <td><?= $this->Number->format($product->rght) ?></td>
                    <td><?= h($product->main_menu) ?></td>
                    <td><?= $this->Number->format($product->view_count) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $product->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $product->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $product->id),
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
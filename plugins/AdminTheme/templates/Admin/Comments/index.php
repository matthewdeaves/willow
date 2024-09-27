<?php
use Cake\Utility\Inflector;
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment> $comments
 */
?>
<div class="comments index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('Comments') ?></h3>
        <?= $this->Html->link(__('New Comment'), ['prefix' => 'Admin', 'action' => 'add'], ['class' => 'btn btn-primary my-3 ms-2']) ?>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('display') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td>
                        <?= $this->Html->link(
                            Inflector::singularize($comment->model),
                            [
                                'controller' => $comment->model,
                                'action' => 'view',
                                $comment->foreign_key
                            ]
                        ) ?>
                    </td>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                    <td><?= h($comment->display ? 'Yes' : 'No') ?></td>
                    <td><?= h($comment->created->format('Y-m-d H:i')) ?></td>
                    <td><?= h($comment->modified->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $comment->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $comment->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete this comment?'), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination') ?>
</div>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Comment'), ['action' => 'edit', $comment->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Comment'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $comment->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Comments'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="comments view content">
            <h3><?= h($comment->model) ?></h3>
            <table>
                <tr>
                    <th><?= __('Model') ?></th>
                    <td><?= h($comment->model) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($comment->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Foreign Key') ?></th>
                    <td><?= h($comment->foreign_key) ?></td>
                </tr>
                <tr>
                    <th><?= __('Display') ?></th>
                    <td><?= h($comment->display ? 'Yes' : 'No') ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($comment->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($comment->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Content') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($comment->content)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
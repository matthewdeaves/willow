<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 */

use Cake\Utility\Inflector;
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Comment',
            'controllerName' => 'Comments',
            'entity' => $comment,
            'entityDisplayName' => $comment->user->username,
            'hideNew' => true,
            'confirm' => __('Are you sure you want to delete comment by {0}?', $comment->user->username)
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Comment for {0}', h(Inflector::singularize($comment->model))) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Display') ?></th>
                            <td><?= $comment->display ? __('Yes') : __('No') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Inappropriate') ?></th>
                            <td><?= $comment->is_inappropriate ? __('Yes') : __('No') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is AI Analyzed') ?></th>
                            <td><?= $comment->is_analyzed ? __('Yes') : __('No') ?></td>
                        </tr>
                        <?php if (!empty($comment->inappropriate_reason)) : ?>
                        <tr>
                            <th><?= __('Inappropriate Reason') ?></th>
                            <td><?= h($comment->inappropriate_reason) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($comment->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($comment->modified) ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Content') ?></h5>
                        <div class="border p-3 bg-light">
                            <?= $this->Text->autoParagraph(h($comment->content)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
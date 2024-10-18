<?php 
use Cake\Utility\Inflector;
?>

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
        <td><?= h($comment->display ? __('Yes') : __('No')) ?></td>
        <td><?= h($comment->is_inappropriate ? __('Yes') : __('No')) ?></td>
        <td><?= h($comment->inappropriate_reason) ?></td>
        <td><?= h($comment->is_analyzed ? __('Yes') : __('No')) ?></td>
        <td><?= $this->Text->truncate($comment->content, 50, ['ellipsis' => '...', 'exact' => false]) ?></td>
        <td><?= h($comment->created->format('Y-m-d H:i')) ?></td>
        <td class="actions">
            <?= $this->Html->link(__('View'), ['action' => 'view', $comment->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $comment->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete this comment?'), 'class' => 'btn btn-sm btn-outline-danger']) ?>
        </td>
    </tr>
<?php endforeach; ?>
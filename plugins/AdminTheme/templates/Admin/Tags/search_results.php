<?php foreach ($tags as $tag): ?>
<tr>
    <td><?= h($tag->title) ?></td>
    <td><?= h($tag->slug) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $tag->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $tag->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete {0}?', $tag->title), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>
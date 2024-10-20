<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<?php foreach ($slugs as $slug): ?>
<tr>
    <td><?= h($slug->slug) ?></td>
    <td>
        <?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id]) : '' ?>
    </td>
    <td><?= h($slug->created->format('Y-m-d H:i')) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['action' => 'view', $slug->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $slug->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>
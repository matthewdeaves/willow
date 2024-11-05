<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<?php foreach ($slugs as $slug): ?>
<tr>
    <td>
        <?php $ruleName = ($slug->article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>
        <?php if ($slug->article->is_published == true): ?>

            <?= $this->Html->link(
                $slug->slug,
                [
                    'controller' => 'Articles',
                    'action' => 'view-by-slug',
                    'slug' => $slug->slug,
                    '_name' => $ruleName,
                ],
                ['escape' => false]
            );
            ?>
        <?php else: ?>
            <?= $this->Html->link(
                $slug->slug,
                [
                    'prefix' => 'Admin',
                    'controller' => 'Slugs',
                    'action' => 'view',
                    $slug->id,
                ],
                ['escape' => false]
            ) ?>
        <?php endif; ?>
    </td>
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
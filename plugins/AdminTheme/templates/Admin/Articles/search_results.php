<?php foreach ($articles as $article): ?>
<tr>
    <td>
        <?php if (isset($article->_matchingData['Users']) && $article->_matchingData['Users']->username): ?>
            <?= $this->Html->link(
                h($article->_matchingData['Users']->username),
                ['controller' => 'Users', 'action' => 'view', $article->_matchingData['Users']->id]
            ) ?>
        <?php else: ?>
            <?= h(__('Unknown Author')) ?>
        <?php endif; ?>
    </td>
    <td><?= $article->is_published ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
    <td><?= h($article->title) ?></td>
    <td><?= $this->Html->link(substr($article->slug, 0, 10) . '...', ['prefix' => false, 'controller' => 'Articles', 'action' => 'viewBySlug', $article->slug]) ?></td>
    <td>
        <?= $this->Html->link(
            h($article->pageview_count), 
            [
                'prefix' => 'Admin', 
                'controller' => 'PageViews', 
                'action' => 'pageViewStats', 
                $article->id
            ],
            ['class' => 'btn btn-sm btn-outline-info']
        ) ?>
    </td>
    <td><?= h($article->created->format('Y-m-d H:i')) ?></td>
    <td><?= h($article->modified->format('Y-m-d H:i')) ?></td>
    <td class="actions">
        <?= $this->Html->link(__('View'), ['prefix' => 'Admin', 'action' => 'view', $article->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?= $this->Html->link(__('Edit'), ['prefix' => 'Admin', 'action' => 'edit', $article->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?= $this->Form->postLink(__('Delete'), ['prefix' => 'Admin', 'action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete {0}?', $article->title), 'class' => 'btn btn-sm btn-outline-danger']) ?>
    </td>
</tr>
<?php endforeach; ?>
<?php use App\Utility\SettingsManager; ?>
<?php foreach ($articles as $article): ?>
<tr>
    <td>
        <div class="position-relative">
            <?= $this->Html->image(SettingsManager::read('ImageSizes.small', '200') . '/' . $article->image, 
                ['pathPrefix' => 'files/Articles/image/', 
                'alt' => $article->alt_text, 
                'class' => 'img-thumbnail', 
                'width' => '50',
                'data-bs-toggle' => 'popover',
                'data-bs-trigger' => 'hover',
                'data-bs-html' => 'true',
                'data-bs-content' => $this->Html->image(SettingsManager::read('ImageSizes.large', '400') . '/' . $article->image, 
                    ['pathPrefix' => 'files/Articles/image/', 
                    'alt' => $article->alt_text, 
                    'class' => 'img-fluid', 
                    'style' => 'max-width: 300px; max-height: 300px;'])
                ]) 
            ?>
        </div>
    </td>
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
    <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-secondary">' . __('No') . '</span>' ?></td>
    <td><?= h($article->title) ?></td>
    <td>
    <?php if ($article->is_published == true): ?>
        <?= $this->Html->link(
            substr($article->slug, 0, 15) . '...',
            [
                'controller' => 'Articles',
                'action' => 'view-by-slug',
                'slug' => $article->slug,
                '_name' => 'article-by-slug'
            ],
            ['escape' => false]
        );
        ?>
    <?php else: ?>
        <?= $this->Html->link(
            substr($article->slug, 0, 15) . '...',
            [
                'prefix' => 'Admin',
                'controller' => 'Articles',
                'action' => 'view',
                $article->id
            ],
            ['escape' => false]
        ) ?>
    <?php endif; ?>
    </td>
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